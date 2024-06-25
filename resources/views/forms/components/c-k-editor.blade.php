<div>
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>

    <x-dynamic-component :component="$getFieldWrapperView()" :id="$getId()" :label="$getLabel()" :label-sr-only="$isLabelHidden()" :helper-text="$getHelperText()"
        :hint="$getHint()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :required="$isRequired()" :state-path="$getStatePath()" wire:ignore>
        <textarea wire:ignore wire:model.lazy="{{ $getId() }}" id="{{ $getId() }}"
            {{ $attributes->merge(['class' => 'form-control']) }}>	</textarea>

        @once
            @push('scripts')
                <script>
                    CKEDITOR.config.image_previewText = '';

                    function initializeCKEditor() {
                        if (CKEDITOR.instances['content']) {
                            CKEDITOR.instances['content'].destroy(true);
                        }

                        let editor = CKEDITOR.replace('{{ $getId() }}', {
                            {{-- filebrowserUploadUrl: '{{ route('upload', ['_token' => csrf_token()]) }}', --}}
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
                                }
                            ],
                            filebrowserUploadMethod: 'form',
                            disallowedContent: 'img{width,height,float}',
                            extraAllowedContent: 'img[width,height,align]',
                            extraPlugins: 'tableresize,justify,colorbutton,colordialog,panelbutton,imageresizerowandcolumn,pagebreak',
                            // contentsCss: ['/vendor/ckeditor/contents.css', '/vendor/ckeditor/document.css'],
                            bodyClass: 'document-editor',
                            height: 400,
                        });

                        editor.on('change', function() {
                            @this.set('{{ $getId() }}', editor.getData());
                        });

                        CKEDITOR.on('dialogDefinition', function(ev) {
                            var dialogName = ev.data.name;
                            var dialogDefinition = ev.data.definition;
                        });
                    }

                    initializeCKEditor();

                    document.addEventListener('livewire:dom:afterUpdate', initializeCKEditor);
                </script>
            @endpush
        @endonce
    </x-dynamic-component>

</div>
