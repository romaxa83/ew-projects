<script>
    jQuery(document).ready(function () {
        $.extend($.validator.prototype, {
            ignore: ":hidden, .js-ignore",
            findByName: function (name) {
                return $(this.currentForm).find("[name='" + name + "']:not(.js-ignore)");
            },
            showRequired: function (form, rule) {
                var el = form.find('[name="' + rule + '"]');
                if (!el.length) {
                    el = form.find('[name="' + rule + '[]"]');
                }
                let id = el.attr('id');
                if (id) {
                    var label = form.find('label[for="' + id + '"]');
                    if (label.length) {
                        label.addClass('required');
                    } else {
                        console.warn('Input does`n have label: ', el.attr('name'));
                    }
                }
            },
            showRequiredIf: function (form, rule, control) {
                var element = $('[name="' + control[0] + '"]:not(.js-ignore)');
                var type = element.attr('type');

                if (type === 'checkbox' || type === 'radio') {
                    if (element.prop('checked') && element.val() === control[1]) {
                        form.find('label[for="' + rule + '"]').addClass('required');
                    }
                } else {
                    if (element.val() == control[1]) {
                        form.find('label[for="' + rule + '"]').addClass('required');
                    }
                }

                $(document).on('click change', '[name="' + control[0] + '"]:not(.js-ignore)', function () {
                    var _element = $(this);
                    var type = _element.attr('type');
                    if (type === 'checkbox' || type === 'radio') {
                        if (_element.prop('checked') && _element.val() == control[1]) {
                            form.find('label[for="' + rule + '"]').addClass('required');
                        } else {
                            form.find('label[for="' + rule + '"]').removeClass('required');
                        }
                    } else {
                        if (_element.val() == control[1]) {
                            form.find('label[for="' + rule + '"]').addClass('required');
                        } else {
                            form.find('label[for="' + rule + '"]').removeClass('required');
                        }
                    }
                });
            },
            rulesRequired: function () {
                var _this = this;

                $("<?= $validator['selector']; ?>").each(function () {
                    var form = $(this);
                    var _rules = $(this).validate().settings.rules;

                    Object.keys(_rules).map(function (rule) {
                        if (_rules[rule].laravelValidation) {
                            $.each(_rules[rule].laravelValidation, function (i, arrays) {
                                if (arrays.indexOf('Required') + 1) {
                                    _this.showRequired(form, rule);
                                }

                                if (arrays.indexOf('RequiredIf') + 1) {
                                    if (_rules[rule].laravelValidation[i][1].length) {
                                        var control = _rules[rule].laravelValidation[i][1];
                                        _this.showRequiredIf(form, rule, control);
                                    }
                                }
                            })
                        }
                    });
                });
            }
        });

        $("<?= $validator['selector']; ?>").each(function () {
            $(this).validate({
                errorElement: 'span',
                errorClass: 'invalid-feedback',

                errorPlacement: function (error, element) {
                    if (element.parent('.input-group').length ||
                        element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                        var $parent = element.parent();
                        if ($parent.hasClass('form-check')) {
                            $parent = $parent.parent();
                            error.appendTo($parent);
                        } else {
                            error.insertAfter($parent);
                        }
                        // else just place the validation message immediately after the input
                    } else if (element[0].tagName === 'SELECT' && element.next().hasClass('select2')) {
                        error.insertAfter(element.next());
                    } else if (element[0].tagName === 'SELECT' && element.next().hasClass('ms-container')) {
                        error.insertAfter(element.next());
                    } else {
                        error.insertAfter(element);
                    }
                },

                showErrors: function (errorMap, errorList) {
                    this.defaultShowErrors();

                    var ids = [];

                    errorList.forEach(function (item) {
                        $(item.element).parentsUntil('form', '.tab-pane').each(function (index, tabPane) {
                            ids.push($(tabPane).attr('id'));
                        })
                    });

                    ids = ids.filter(function(item, pos) {
                        return ids.indexOf(item) === pos;
                    });

                    ids.forEach(function (id) {
                        var $tab = $('a[href="#' + id + '"]');

                        if ($tab.hasClass('active')) {
                            return;
                        }
                        $tab.addClass('bg-danger animated flash text-white tab-with-error');
                    });

                    $('.tab-with-error').filter(function (index, el) {
                        return ids.indexOf($(el).attr('href').slice(1)) === -1;
                    }).removeClass('bg-danger flash text-white tab-with-error');
                },

                highlight: function (element) {
                    $(element).closest('.form.control').removeClass('is-valid').addClass('is-invalid'); // add the Bootstrap error class to the control group
                },

                <?php if (isset($validator['ignore']) && is_string($validator['ignore'])): ?>
                ignore: "<?= $validator['ignore']; ?>",
                <?php endif; ?>

                // Uncomment this to mark as validated non required fields
                unhighlight: function (element) {
                    // $(element).closest('.form.control').removeClass('is-invalid').addClass('is-valid');
                },

                success: function (element) {
                    $(element).closest('.form.control').removeClass('is-invalid').addClass('is-valid'); // remove the Boostrap error class from the control group
                },

                focusInvalid: false, // do not focus the last invalid input
                <?php if (Config::get('jsvalidation.focus_on_error')): ?>
                invalidHandler: function (form, validator) {

                    if (!validator.numberOfInvalids())
                        return;

                    $('html, body').animate({
                        scrollTop: $(validator.errorList[0].element).offset().top
                    }, <?= Config::get('jsvalidation.duration_animate') ?>);
                    $(validator.errorList[0].element).focus();

                },
                <?php endif; ?>

                <?php
                $rules = $validator['rules'];
                // Delete rules: min, max if isset Array rule.
                foreach ($rules as $field => $rule) {
                    $hasArrayRule = false;
                    foreach (array_get($rule, 'laravelValidation', []) as $items) {
                        if (array_get($items, 0) === 'Array') {
                            $hasArrayRule = true;
                            break;
                        }
                    }
                    if ($hasArrayRule) {
                        foreach (array_get($rule, 'laravelValidation', []) as $key => $item) {
                            if (in_array(array_get($item, 0), ['Min', 'Max'])) {
                                unset($rules[$field]['laravelValidation'][$key]);
                            }
                        }
                    }
                }
                ?>
                rules: <?= json_encode($rules); ?>
            });
        });


        jQuery.validator.prototype.rulesRequired();
    });
</script>
