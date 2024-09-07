export default function ckeditorComponent({ state }) {
    return {
        state,
        init() {
            const textareaId = this.$el.querySelector('textarea').id;

            if (window.CKEDITOR.instances[textareaId]) {
                window.CKEDITOR.instances[textareaId].destroy(true);
            }

            window.CKEDITOR.config.image_previewText = '';

            let editor = window.CKEDITOR.replace(textareaId, {
                toolbar: [
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'styles', items: ['Format', 'FontSize'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'CopyFormatting'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'align', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table'] },
                    { name: 'tools', items: ['Maximize'] }
                ],
                filebrowserUploadMethod: 'form',
                disallowedContent: 'img{width,height,float}',
                extraAllowedContent: 'img[width,height,align]',
                extraPlugins: 'tableresize,justify,colorbutton,colordialog,panelbutton,imageresizerowandcolumn,pagebreak',
                removePlugins: 'exportpdf',
                contentsCss: ['/vendor/ckeditor/document-content.css'],
                bodyClass: 'document-content document-content-editor',
                height: 400,
                width: window.innerWidth < 768 ? 771 : 0,
                font_names: 'Inter',
            });

            editor.on('change', () => {
                this.state = editor.getData();
            });

            // CKEDITOR.on('dialogDefinition', (ev) => {
            //     const dialogName = ev.data.name;
            //     const dialogDefinition = ev.data.definition;
            // });

            document.addEventListener('formReseted', () => {
                if (window.CKEDITOR.instances[textareaId]) {
                    if (textareaId.includes('mountedActionsData')) {
                        window.CKEDITOR.instances[textareaId].destroy()
                    } else {
                        window.CKEDITOR.instances[textareaId].setData('');
                    }
                }
            });
        }
    }
}
