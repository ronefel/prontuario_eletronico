<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div wire:ignore x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('ckeditor-component') }}"
        x-data="ckeditorComponent({
            state: $wire.{{ $applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')') }},
            record: {{ json_encode($getRecord()) }},
            settings: {{ json_encode($getSettings()) }},
        })">

        <textarea id="{{ $getId() }}"
            {{ $attributes->merge(['class' => 'form-control']) }}>{!! $getState() !!}</textarea>
    </div>
</x-dynamic-component>

@assets
    <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
@endassets
