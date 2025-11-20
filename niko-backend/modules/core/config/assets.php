<?php

use WezomCms\Core\Contracts\Assets\AssetManagerInterface;

return [
    'styles' => [
        'vendor/cms/core/css/style.css',
    ],
    'scripts' => [
        AssetManagerInterface::POSITION_END_BODY => [
            'vendor/cms/core/plugins/polyfill/es6-promise.auto.min.js',
            'vendor/cms/core/plugins/axios/axios.min.js',
            'vendor/cms/core/plugins/jquery/jquery.min.js',
            'vendor/cms/core/plugins/jquery-ui-1.12.1/jquery-ui.min.js',
            'vendor/cms/core/plugins/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js',
            'vendor/cms/core/plugins/bootstrap/js/bootstrap.bundle.min.js',
            'vendor/cms/core/plugins/sticky-kit/sticky-kit.min.js',
            'vendor/cms/core/plugins/select2/js/select2.full.js',
            'vendor/cms/core/plugins/select2/js/i18n/{original-locale}.js',
            'vendor/jsvalidation/js/jsvalidation.js',
            'vendor/cms/core/plugins/fancybox/jquery.fancybox.js',
            'vendor/cms/core/plugins/moment/moment-with-locales.js',
            'vendor/cms/core/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js',
            'vendor/cms/core/plugins/bootstrap-datepicker/locales/bootstrap-datepicker.{locale}.min.js',
            'vendor/cms/core/plugins/daterangepicker/daterangepicker.js',
            'vendor/cms/core/plugins/tinymce/tinymce.min.js',
            'vendor/cms/core/plugins/tinymce/jquery.tinymce.min.js',
            'vendor/cms/core/plugins/datatables/js/jquery.dataTables.min.js',
            'vendor/cms/core/plugins/datatables-cell-edit/dataTables.cellEdit.js',
            'vendor/cms/core/plugins/dropzone/dropzone.js',
            'vendor/cms/core/plugins/bootstrap-confirmation/bootstrap-confirmation.min.js',
            'vendor/cms/core/plugins/jquery-slimscroll/jquery.slimscroll.js',
            'vendor/cms/core/plugins/sidebarmenu.js',
            'vendor/cms/core/plugins/multi-select/js/jquery.quicksearch.js',
            'vendor/cms/core/plugins/nestable2/jquery.nestable.js',
            'vendor/cms/core/plugins/multi-select/js/jquery.multi-select.js',
            'vendor/cms/core/plugins/bootstrap-toggle/bootstrap-toggle.min.js',
            'vendor/cms/core/plugins/jquery-minicolors/jquery.minicolors.min.js',
            'vendor/cms/core/js/entry.js',
        ],
    ],
];
