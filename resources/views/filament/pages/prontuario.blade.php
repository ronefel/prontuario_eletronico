<x-filament-panels::page>
    <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2">
            <x-filament-panels::form>
                {{ $this->form }}

                <x-filament-panels::form.actions :actions="$this->getActions()" />
            </x-filament-panels::form>
        </div>
        <div class="col-span-1">sd</div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>