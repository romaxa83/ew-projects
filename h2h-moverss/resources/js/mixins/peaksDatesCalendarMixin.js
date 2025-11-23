export default {
    methods: {
        // Вызываем календарь и выбор времени + апдейт значений
        initPeaksDatesCalendarMixin(fp, vm, preloader = '') {
            fp.config.onDayCreate = [
                function (dObj, dStr, fp, dayElem) {
                    let res = vm.$store.getters['getPeaksDates'] ? vm.$store.getters['getPeaksDates'].dates
                            .find(function (item) {
                                return item.date === moment(dayElem.dateObj).format('YYYY-MM-DD')
                            })
                        : false;

                    // Чекаем данные про выходные
                    if (!res) {
                        let weekDayNum = +moment(dayElem.dateObj).format('e');
                        res = vm.$store.getters['getPeaksDates'] ?
                            vm.$store.getters['getPeaksDates'].weeks.includes(weekDayNum)
                            : false;
                        if (res) {
                            res = {
                                type_id: 3,
                                type: {
                                    title: "Peak",
                                    color: '#b56ce2',
                                }
                            };
                        }
                    }

                    if (res) {
                        dayElem.innerHTML += `<span style="background:${res.type.color}" title="${res.type.title}" class="event busy type-${res.type_id}"></span>`;
                    }
                }
            ];

            if (preloader)
                bindLoader(preloader);
        },
    }
}

function bindLoader(preloader) {
    let txt = '<div class="loader-div frame-wrap position-absolute w-50 h-50 opacity-50">\n' +
        '                        <div class="w-100 d-flex justify-content-center align-items-center">\n' +
        '                            <div class="spinner-border text-info position-absolute" style="top:50%;" role="status">\n' +
        '                                <span class="sr-only">Loading...</span>\n' +
        '                            </div>\n' +
        '                        </div>\n' +
        '                    </div>';

    $(preloader).before(txt);
}
