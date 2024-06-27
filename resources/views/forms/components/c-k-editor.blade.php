<div>
    <x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" :label="$getLabel()"
        :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()" :hint="$getHint()"
        :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()"
        :state-path="$getStatePath()" wire:ignore>
        <textarea wire:ignore wire:model="{{ $getStatePath() }}" id="{{ $getId() }}" {{ $attributes->merge(['class' => 'form-control']) }}
x-data="initializeCKEditor('{{ $getStatePath() }}')">	</textarea>


        @assets
        <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
        @endassets
        @push('scripts')
        <script>
            CKEDITOR.config.image_previewText = '';

                    function initializeCKEditor(textareaId) {
                        if (CKEDITOR.instances[textareaId]) {
                            CKEDITOR.instances[textareaId].destroy(true);
                        }

                        let editor = CKEDITOR.replace(textareaId, {
                            toolbar: [{
                                    name: 'clipboard',
                                    items: ['Undo', 'Redo']
                                },
                                {
                                    name: 'styles',
                                    items: ['Format', 'Font', 'FontSize']
                                },
                                {
                                    name: 'basicstyles',
                                    items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat', 'CopyFormatting']
                                },
                                {
                                    name: 'colors',
                                    items: ['TextColor', 'BGColor']
                                },
                                {
                                    name: 'align',
                                    items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
                                },
                                {
                                    name: 'links',
                                    items: ['Link', 'Unlink']
                                },
                                {
                                    name: 'paragraph',
                                    items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']
                                },
                                {
                                    name: 'insert',
                                    items: ['Image', 'Table']
                                },
                                {
                                    name: 'tools',
                                    items: ['Maximize']
                                },
                                {
                                    name: 'editing',
                                    items: ['Scayt']
                                },
                                {
                                    name: 'exame',
                                    items: ['Exame']
                                }
                            ],
                            filebrowserUploadMethod: 'form',
                            disallowedContent: 'img{width,height,float}',
                            extraAllowedContent: 'img[width,height,align]',
                            extraPlugins: 'tableresize,justify,colorbutton,colordialog,panelbutton,imageresizerowandcolumn,pagebreak,exame',
                            contentsCss: ['/vendor/ckeditor/document-content.css'],
                            // contentsCss: ['/vendor/ckeditor/contents.css', '/vendor/ckeditor/document.css'],
                            bodyClass: 'document-content',
                            height: 400,
                            font_names: 'Arial/Arial, Helvetica, sans-serif;' +
                                            'Comic Sans MS/Comic Sans MS, cursive;' +
                                            'Courier New/Courier New, Courier, monospace;' +
                                            'Georgia/Georgia, serif;' +
                                            'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' +
                                            'Tahoma/Tahoma, Geneva, sans-serif;' +
                                            'Times New Roman/Times New Roman, Times, serif;' +
                                            'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' +
                                            'Verdana/Verdana, Geneva, sans-serif',
                        });

                        editor.on('change', function() {
                            @this.set(textareaId, editor.getData());
                        });

                        CKEDITOR.on('dialogDefinition', function(ev) {
                            var dialogName = ev.data.name;
                            var dialogDefinition = ev.data.definition;
                        });
                        document.addEventListener('livewire:init', () => {
                            Livewire.on('formReseted', (event) => {
                                editor.setData('');
                            });
                        });
                    }

                    // initializeCKEditor('{{ $getStatePath() }}');

                    // document.addEventListener('livewire:dom:afterUpdate', initializeCKEditor);

        </script>
        @endpush
    </x-dynamic-component>

</div>
