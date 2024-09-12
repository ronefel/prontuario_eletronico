CKEDITOR.dialog.add('variavel', function (editor) {
    return {
        title: 'Escolha a Variável',
        minWidth: 150,
        minHeight: 100,
        contents: [
            {
                id: 'main',
                elements: [
                    {
                        type: 'select',
                        id: 'variavelSelect',
                        label: '',
                        size: 16,
                        items: [
                            ['Nome do paciente', '{{PAC_NOME}}'],
                            ['Data de Nascimento', '{{NASCIM}}'],
                            ['Idade', '{{IDADE}}'],
                            ['Sexo', '{{SEXO}}'],
                            ['CPF', '{{PAC_CPF}}'],
                            ['Celular', '{{PAC_CELULAR}}'],
                            ['E-mail', '{{PAC_EMAIL}}'],
                            ['CEP', '{{PAC_CEP}}'],
                            ['Logradouro', '{{PAC_LOGRADOURO}}'],
                            ['Número', '{{PAC_NUMERO}}'],
                            ['Bairro', '{{PAC_BAIRRO}}'],
                            ['Complemento', '{{PAC_COMPLEMENTO}}'],
                            ['Cidade do paciente', '{{PAC_CIDADE}}'],
                            ['Data de Atendimento', '{{DATA_ATENDIMENTO}}'],
                        ],
                        setup: function (widget) {
                            this.setValue(widget.data.variavelSelect);
                        },
                        commit: function (widget) {
                            widget.setData('variavelSelect', this.getValue());
                        },
                        onShow: function () {
                            if (this.getElement().$.firstElementChild && this.getElement().$.firstElementChild.tagName === 'LABEL') {
                                this.getElement().$.firstElementChild.remove();
                            }
                            var selectElement = this.getInputElement().$;
                            selectElement.classList.add('variavel-select');
                        }
                    }
                ]
            }
        ],
    };
});
