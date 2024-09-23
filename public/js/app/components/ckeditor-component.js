export default function ckeditorComponent({ state, record, settings }) {
    return {
        state,
        record, // Objeto record passado como JSON pelo componente App\Forms\Components\CKEditor
        settings, // Objeto settings passado como JSON pelo componente App\Forms\Components\CKEditor
        init() {
            const textareaId = this.$el.querySelector('textarea').id;

            if (window.CKEDITOR.instances[textareaId]) {
                window.CKEDITOR.instances[textareaId].destroy(true);
            }

            window.CKEDITOR.config.image_previewText = '';

            const paddingTop = settings['margem_superior'] + 'mm';
            const paddingBottom = settings['margem_inferior'] + 'mm'
            const paddingLeft = settings['margem_esquerda'] + 'mm'
            const paddingRight = settings['margem_direita'] + 'mm'
            const heightCabecalho = settings['altura_cabecalho'] + 'mm'
            const heightRodape = settings['altura_rodape'] + 'mm'

            // define o layout do editor de acordo do cabecalho ou rodape
            let style = '';
            if (this.record && this.record.key === 'cabecalho') {
                style = `" style="min-height: unset; padding-bottom: 0; height: ${heightCabecalho}; padding-top: ${paddingTop}; padding-right: ${paddingRight}; padding-left: ${paddingLeft};"`;
            } else if (this.record && this.record.key === 'rodape') {
                style = `" style="min-height: unset; padding-top: 0; height: ${heightRodape}; padding-bottom: ${paddingBottom}; padding-right: ${paddingRight}; padding-left: ${paddingLeft};"`;
            }

            let editor = window.CKEDITOR.replace(textareaId, {
                toolbar: [
                    ['mascara', 'variavel'],
                    { name: 'clipboard', items: ['Undo', 'Redo'] },
                    { name: 'styles', items: ['Format', 'FontSize'] },
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'align', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                    { name: 'tools', items: ['Maximize'] },
                    ['Source']
                ],
                filebrowserUploadMethod: 'form',
                // disallowedContent: 'img{width,height,float}',
                // extraAllowedContent: 'img[width,height,align]',
                allowedContent: true,
                extraPlugins: 'tableresize,justify,colorbutton,colordialog,panelbutton,imageresizerowandcolumn,pagebreak,mascara,variavel',
                removePlugins: 'exportpdf',
                contentsCss: ['/vendor/ckeditor/document-content.css'],
                bodyClass: 'document-content document-content-editor ' + style,
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
