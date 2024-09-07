CKEDITOR.plugins.add('mascara', {
    requires: 'widget',
    icons: 'mascara',
    init: function (editor) {
        CKEDITOR.dialog.add('mascaraDialog', function () {
            return {
                title: 'Selecionar Máscara',
                minWidth: 400,
                minHeight: 200,
                contents: [
                    {
                        id: 'main',
                        elements: [
                            {
                                type: 'select',
                                id: 'mascaraSelect',
                                label: 'Escolha a Máscara',
                                items: [],
                                onShow: function () {
                                    var selectElement = this.getInputElement().$;
                                    // Buscar as máscaras do servidor
                                    fetch('/mascaras')
                                        .then(response => response.json())
                                        .then(data => {
                                            // Limpar opções existentes
                                            selectElement.innerHTML = '';

                                            // Adicionar novas opções
                                            data.forEach(mascara => {
                                                var option = document.createElement('option');
                                                option.text = mascara.nome;
                                                option.value = mascara.descricao;
                                                selectElement.add(option);
                                            });
                                        })
                                        .catch(error => console.error('Erro ao buscar máscaras:', error));
                                }
                            }
                        ]
                    }
                ],
                buttons: [
                    CKEDITOR.dialog.okButton,
                    CKEDITOR.dialog.cancelButton
                ],
                onOk: function () {
                    const dialog = this;
                    const descricao = dialog.getValueOf('main', 'mascaraSelect');

                    // Inserir a descrição selecionada no texto do editor
                    editor.insertHtml(descricao);
                }
            };
        });

        // Registrar o comando que abre o diálogo
        editor.addCommand('mascara', new CKEDITOR.dialogCommand('mascaraDialog'));

        // Adicionar o widget à barra de ferramentas
        editor.ui.addButton('mascara', {
            label: 'Inserir Máscara',
            command: 'mascara',
        });
    }
});
