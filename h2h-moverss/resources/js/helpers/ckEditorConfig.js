export function ckEditorConfig() {
    return {
        uiColor: '#FFFFFF',
        fullPage: true,
        allowedContent: true,
        extraAllowedContent: '*{*}',
        // removeButtons: '',
        extraPlugins: 'justify,colorbutton,colordialog',
        colorButton_enableAutomatic: true,
        colorButton_enableMore: true,
        toolbar: [
            {name: 'clipboard', items: ['Undo', 'Redo']},
            {name: 'styles', items: ['Styles', 'Format']},
            {name: 'basicstyles', items: ['Bold', 'Italic', 'Strike', '-', 'RemoveFormat']},
            {name: 'colors', items: ['TextColor', 'BGColor']},
            {name: 'align', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
            {name: 'paragraph', items: ['NumberedList', 'BulletedList', '-']},
            {name: 'links', items: ['Link', 'Unlink']},
            // {name: 'insert', items: ['Image', 'EmbedSemantic', 'Table']},
            {name: 'tools', items: ['Maximize']},
            {name: 'editing', items: ['Scayt', 'Source']},
        ],
    }
}
