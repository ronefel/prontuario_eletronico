<div wire:ignore x-ignore ax-load
    ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('ckeditor-component') }}"
    x-data="ckeditorComponent({
        state: $wire.{{ $applyStateBindingModifiers('entangle(\'' . $getStatePath() . '\')') }}
    })">
    <textarea id="{{ $getId() }}" wire:model="{{ $getStatePath() }}"
        {{ $attributes->merge(['class' => 'form-control']) }}></textarea>

    @assets
        <script src="{{ asset('vendor/ckeditor/ckeditor.js') }}"></script>
    @endassets
</div>
