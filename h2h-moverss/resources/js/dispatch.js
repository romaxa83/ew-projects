window.Dispatch = {

    isTruckMoveMode: false,

    init() {
        this.bindEvents();

        $("body").tooltip({
            selector: ".has-tooltip",
        });

        $('.truck-checkbox input').prop('disabled', true);
    },

    bindEvents() {
        // Выбор работника/трака на интервал времени (слева юзернейм)
        $(document).on("change", ".crew-checkbox input,.truck-checkbox input", function (e) {
            let state = $(this);

            let type = $(e.target).parents().attr('class');
            if (type.includes('crew')) {
                type = 'crew';
            } else if (type.includes('truck')) {
                type = 'truck';
            }

            if (state.prop("checked")) {
                Dispatch.assignWorkToTimeline(type, $(this).closest(".gantt__row")[0]);
            } else {
                // Анчек работы с сотрудника
                let row = state.closest(".gantt__row");

                let work = $(`.${type}-work-checkbox input:checked`)
                    .closest(".work-snippet")
                    .first();
                let work_id = work.data("id");

                let tile = $(`.work-snippet[data-id="${work_id}"]`, row);
                if (tile) {
                    let randomRef = tile.data("randomRef");

                    window.Dispatch.saveAndDraw({
                        type,
                        randomRef,
                        work_id,
                        entity_id: null,
                    })
                        .then(() => {
                            tile.closest(".cell").remove();
                        })
                        .catch(() => {
                            state.prop("checked", true);
                        });
                }
            }
        });

        // Отметка в плитке работ
        $(document).on("change", ".crew-work-checkbox input,.truck-work-checkbox input", function (e) { // TODO назвать как-то типа crew-tile-header-checkbox
            let type = $(e.target).parents().attr('class');
            if (type.includes('crew')) {
                type = 'crew';
            } else if (type.includes('truck')) {
                type = 'truck';
                $('.truck-checkbox input').prop('disabled', false);
            }

            Dispatch.isTruckMoveMode = type === 'truck' ? (!!$(this).closest(".gantt__row").length) : false;

            const state = $(this).prop("checked");
            const workStart = +$(this).closest(".work-snippet").data("start");
            const workDuration = +$(this).closest(".work-snippet").data("duration");
            const workId = +$(this).closest(".work-snippet").data("id");

            Dispatch.unselectWorkTimeline(type);
            if (state) {
                Dispatch.clearCheckedWorkers(type, e, $(this).closest(".work-snippet")[0]);
                $(`.${type}-work-checkbox input:checked`).each(function () {
                    if (+$(this).closest(".work-snippet").data("id") != workId)
                        $(this).prop("checked", false);
                });
                if (type === 'crew' || !Dispatch.isTruckMoveMode) {
                    // Отмечаем юзеров заказа
                    $(`.gantt__row .work-snippet[data-id="${workId}"] .${type}-work-checkbox input:not(:checked)`)
                        .each(function () {
                            $(this).prop("checked", true);
                        });
                }

                Dispatch.markWorkTimeline(type, workStart, workDuration);
            } else {
                Dispatch.clearCheckedWorkers(type, e);
                $(`.${type}-work-checkbox input:checked`).each(function () {
                    if (+$(this).closest(".work-snippet").data("id") == workId)
                        $(this).prop("checked", false);
                });

                if (type === 'truck')
                    $('.truck-checkbox input').prop('disabled', true);
            }
        });
    },

    /**
     * Снимаем активность зон
     */
    unselectWorkTimeline(type) {
        $(`.gantt__row--lines-${type} .gantt__row--lines-timeline .marker`).each(function () {
            $(this).removeClass("marker");
        });
    },

    /**
     * Рисуем разметку возможного времени где можно назначить
     * @param start
     * @param duration
     */
    markWorkTimeline(type, start, duration) {
        for (let i = start; i < start + duration; i++) {
            $(`.gantt__row--lines-${type} .gantt__row--lines-timeline .c-start-` + i).addClass("marker");
        }
    },

    // копируем сниппет выделеной работы
    async assignWorkToTimeline(type, workerRow) {
        let work = $(`.${type}-work-checkbox input:checked`)
            .closest(".work-snippet")
            .first();
        let work_id = work.data("id"),
            start = work.data("start"),
            move = null,
            randomRef = work.data("randomRef"),
            duration = work.data("duration"),
            entity_id = type === 'crew' ? $(workerRow).data("employee-id") : $(workerRow).data("truck-id");

        // Пробуем найти первую не перенесенную работу
        if (type === 'truck')
            move = $(`.moveToSchedule-${type} .panel-tag[data-random-ref="${randomRef}"]`).first();
        else
            move = $(`.moveToSchedule-${type} .panel-tag[data-id="${work_id}"]`).first();

        if (type === 'truck' && Dispatch.isTruckMoveMode) {
            // allow move item
            let move = $(`.gantt-${type}s .panel-tag[data-id="${work_id}"]`).first(),
                parent = move.parent();

            Dispatch.clearCheckedWorkers(type);

            $(`#${type}_${entity_id}`).prop("checked", true);

            window.Dispatch.saveAndDraw({
                type,
                randomRef: move.data("randomRef"),
                work_id: move.data("id"),
                entity_id,
            })
                .then(() => {
                    // Move
                    $(".gantt__row-bars", $(`[data-truck-id="${entity_id}"].gantt__row`)).append(parent);

                })
                .catch(() => {
                    $(`#${type}_${entity_id}`).prop("checked", false);
                });
        } else if (move.length) {
            // Переносим в расписание
            let randomRef = move.data("randomRef"),
                hasBusy = false,
                dataName = type === 'crew' ? 'employee-id' : 'truck-id';

            // Проверяем можно ли занять это время
            $(`.gantt__row[data-${dataName}="${entity_id}"] .busy`).each(
                function () {
                    let el = $(this),
                        classes = el.attr("class"),
                        findClass = /c-start-(\d) c-span-(\d)/;

                    let ma = classes.match(findClass);
                    if (ma) {
                        let reserveFrom = parseInt(ma[1]),
                            reserveTo = reserveFrom + parseInt(ma[2]);

                        if (start >= reserveFrom && start <= reserveTo) {
                            hasBusy = true;
                        }
                    }
                }
            );

            if (hasBusy) {
                App.Forms.showAlert(
                    "error",
                    "This time slot is not available, choose another option"
                );
                $(`#${type}_${entity_id}`).prop("checked", false);
                return;
            }

            window.Dispatch.saveAndDraw({
                type,
                randomRef,
                work_id,
                entity_id,
            })
                .then(() => {

                    let cell = $(`<div class="cell ${type}-work c-start-${start} c-span-${duration}"></div>`);
                    $(".gantt__row-bars", $(workerRow)).append(cell.append(move));
                    Dispatch.isTruckMoveMode = true;

                    if (type === 'truck') {
                        $(`.gantt__row input:checked`).prop("checked", false);

                        $(`#${type}_${entity_id}`).prop("checked", true);
                    }
                    $(`[data-random-ref="${randomRef}"] input`, workerRow).prop("checked", true);

                })
                .catch(() => {
                    $(`#${type}_${entity_id}`).prop("checked", false);
                });
        } else {
            $(`#${type}_${entity_id}`).prop("checked", false);

            if (work_id) {
                App.Forms.showAlert(
                    "warning",
                    "Assigned more than required workers for this service!"
                );
            } else {
                App.Forms.showAlert(
                    "warning",
                    "You need to choose order service for assign at first"
                );
            }
        }
    },

    clearCheckedWorkers(type, e, workSnippet) {
        // Uncheck horizontal
        let checked = false;
        if (e) {
            $('.works-horizontal input:checked').not(e.target).prop("checked", false);
            checked = $(e.target).prop('checked');
        }

        let workId = workSnippet ? $(workSnippet).data("id") : undefined,
            ev = null,
            truckId = null;

        if (type === 'truck') {
            ev = e ? e.target : workSnippet,
                truckId = $(ev).closest(".work-snippet").attr("data-truck-id"); // через data не апдейтит
        }

        $(`.${type}-checkbox input:checked`)
            .each(function () {
                $(this).prop("checked", false);
            });

        if ((type === 'crew' && workId) || (type === 'truck' && workId && !Dispatch.isTruckMoveMode)) {
            $(`.${type}-checkbox input`).each(function () {
                if (
                    $(this)
                        .closest(".gantt__row")
                        .find(`.work-snippet[data-id="${workId}"]`).length > 0
                ) {
                    // Активация на расписании gantt__row
                    $(this).prop("checked", true);
                }
            });
        } else if (type === 'truck' && truckId && Dispatch.isTruckMoveMode) {
            $(`#truck_${truckId}`).prop("checked", checked); // Activate truck
            $(`.gantt__row .work-snippet input:checked`).not(e.target).prop("checked", false); // uncheck not clicked ch-box
        }
    },

    uncheckCheckboxes(type) {
        if (type === 'truck') {
            $('.work-snippet input:checked').prop('checked', false);
            $('.truck-checkbox input').prop('checked', false);
            $('.truck-checkbox input').prop('disabled', true);
        }
    },

    ucFirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    // Toggle trucks/crews. If empty entity_id - remove
    async saveAndDraw({type, randomRef, work_id, entity_id = null}) {
        try {
            await window.VueApp.$store
                .dispatch("dispatch/updateVirtualWork" + this.ucFirst(type), {
                    randomRef,
                    work_id,
                    entity_id,
                })

            await window.VueApp.$refs.Dispatch.submit();

            if (!entity_id) {
                // move to top (not dispatched)
                let src = $(`.gantt-wrapper .panel-tag[data-random-ref="${randomRef}"]`); // FIXME fix class not gantt-wrapper find by type
                $(`.moveToSchedule-${type}`).append(src);
            }
            // else {
            //     // assign
            // }

            if (type === 'truck' && !entity_id) {
                Dispatch.unselectWorkTimeline(type);
                Dispatch.clearCheckedWorkers(type);
                Dispatch.uncheckCheckboxes(type);
            }
        } catch (e) {
            return e;
        }

    }
};
