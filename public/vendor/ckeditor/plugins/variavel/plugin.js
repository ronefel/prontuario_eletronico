CKEDITOR.plugins.add('variavel', {
    requires: 'widget',

    icons: 'variavel',

    init: function (editor) {
        CKEDITOR.dialog.add('variavel', this.path + 'dialogs/variavel.js');

        editor.widgets.add('variavel', {

            // usar este template para inserir o que foi selecionado no editor
            template: '<span class="h-var"></span>',

            allowedContent: 'span(!h-var);',

            requiredContent: 'span(h-var)',

            dialog: 'variavel',

            upcast: function (element) {
                return element.name == 'span' && element.hasClass('h-var');
            },

            // Atualiza o HTML do widget com o conteúdo selecionado
            data: function (evt) {
                if (this.data.variavelSelect) {
                    this.element.$.innerHTML = this.data.variavelSelect;
                }
            }

        });

        editor.ui.addButton('variavel', {
            label: 'Inserir Variável',
            command: 'variavel',
        });
    }
});
