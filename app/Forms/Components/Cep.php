<?php

namespace App\Forms\Components;

use App\Models\Cidade;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Component as Livewire;

class Cep extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mask('99999-999');
        $this->placeholder('00000-000');
        $this->length(9);
    }

    public function viaCep(
        $errorMessage = 'CEP inválido',
        $setFields = []
    ): static {
        $viaCepRequest = function (
            $state,
            Livewire $livewire,
            Set $set,
            Component $component,
            $errorMessage,
            $setFields
        ) {

            $livewire->validateOnly($component->getKey());

            $request = Http::get('viacep.com.br/ws/' . $state . '/json/')->json();
            if (!$request) return;

            foreach ($setFields as $key => $value) {
                if ($key === 'localidade') {
                    $cidade = Cidade::where('uf', $request['uf'])->where('nome', $request['localidade'])->first();
                    if ($cidade) {
                        $set($value, $cidade->id);
                        return;
                    }
                }
                $set($value, $request[$key] ?? null);
            }

            if (Arr::has($request, 'erro')) {
                throw ValidationException::withMessages([
                    $component->getKey() => $errorMessage
                ]);
            }
        };

        $this->hintAction(
            Action::make('viaCep')
                ->icon('heroicon-o-magnifying-glass')
                ->label('Pesquisar CEP')
                ->action(function ($state, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $viaCepRequest) {
                    $viaCepRequest(
                        $state,
                        $livewire,
                        $set,
                        $component,
                        $errorMessage,
                        $setFields
                    );
                })

        );

        return $this;
    }
}
