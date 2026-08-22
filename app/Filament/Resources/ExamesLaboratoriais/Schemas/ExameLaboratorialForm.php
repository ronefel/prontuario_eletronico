<?php

namespace App\Filament\Resources\ExamesLaboratoriais\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExameLaboratorialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::getComponents());
    }

    public static function getComponents(): array
    {
        return [
            TextInput::make('nome')
                ->label('Nome do Exame')
                ->required()
                ->maxLength(255),
            Toggle::make('ativo')
                ->label('Ativo')
                ->default(true),
            Textarea::make('descricao')
                ->label('Descrição')
                ->columnSpanFull(),
            Repeater::make('parametros')
                ->relationship('parametros')
                ->label('Parâmetros do Exame')
                ->helperText('Cadastre um ou mais parâmetros vinculados a este exame. Exames simples contêm 1 parâmetro (ex: "Resultado").')
                ->schema([
                    TextInput::make('nome_parametro')
                        ->label('Nome do Parâmetro')
                        ->required()
                        ->placeholder('Ex: Hemoglobina, Plaquetas, Resultado'),
                    TextInput::make('unidade_medida')
                        ->label('Unidade de Medida')
                        ->placeholder('Ex: g/dL, mg/dL, /mm³'),
                    TextInput::make('valor_minimo_ideal')
                        ->label('Valor Mínimo Ideal')
                        ->numeric()
                        ->step('0.01')
                        ->nullable(),
                    TextInput::make('valor_maximo_ideal')
                        ->label('Valor Máximo Ideal')
                        ->numeric()
                        ->step('0.01')
                        ->nullable(),
                ])
                ->columns(4)
                ->columnSpanFull()
                ->defaultItems(1)
                ->reorderableWithButtons(),
        ];
    }
}
