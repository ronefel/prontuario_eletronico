<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore x-ignore ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('ckeditor-component') }}"
        x-data="ckeditorComponent({
            state: $wire.{{ $applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')') }},
            record: {{ json_encode($getRecord()) }},
        })">

        <textarea id="{{ $getId() }}" wire:model.defer="{{ $getStatePath() }}"
            {{ $attributes->merge(['class' => 'form-control']) }}></textarea>

        @assets
            <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
        @endassets
    </div>
</x-dynamic-component>
