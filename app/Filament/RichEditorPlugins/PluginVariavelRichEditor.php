<?php

namespace App\Filament\RichEditorPlugins;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\HasToolbarButtons;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class PluginVariavelRichEditor implements HasToolbarButtons, RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getTipTapPhpExtensions(): array
    {
        return [];
    }

    public function getTipTapJsExtensions(): array
    {
        return [];
    }

    public static function obterListaVariaveis(): array
    {
        return [
            '{PAC_NOME}' => 'Nome do paciente',
            '{NASCIM}' => 'Data de Nascimento',
            '{IDADE}' => 'Idade',
            '{SEXO}' => 'Sexo',
            '{TIPO}' => 'Tipo Sanguíneo',
            '{PAC_CPF}' => 'CPF',
            '{PAC_CELULAR}' => 'Celular',
            '{PAC_EMAIL}' => 'E-mail',
            '{PAC_CEP}' => 'CEP',
            '{PAC_LOGRADOURO}' => 'Logradouro',
            '{PAC_NUMERO}' => 'Número',
            '{PAC_BAIRRO}' => 'Bairro',
            '{PAC_COMPLEMENTO}' => 'Complemento',
            '{PAC_CIDADE}' => 'Cidade do paciente',
            '{DATA_ATENDIMENTO}' => 'Data de Atendimento',
            'Página {PAGENO} de {nbpg}' => 'Página X de Y',
        ];
    }

    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('inserirVariavel')
                ->label('Inserir Variável')
                ->icon(Heroicon::OutlinedTag)
                ->action(arguments: '{ editorSelection: $getEditor().state.selection }'),
        ];
    }

    public function getEditorActions(): array
    {
        return [
            Action::make('inserirVariavel')
                ->modalHeading('Escolha a Variável')
                ->modalWidth('md')
                ->schema([
                    Select::make('variavel')
                        ->label('Variável')
                        ->options(self::obterListaVariaveis())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $tagVariavel = $data['variavel'] ?? null;

                    if (! empty($tagVariavel)) {
                        $component->runCommands(
                            [
                                EditorCommand::make('insertContent', arguments: [$tagVariavel]),
                            ],
                            editorSelection: $arguments['editorSelection'] ?? null,
                        );
                    }
                }),
        ];
    }

    public function getEnabledToolbarButtons(): array
    {
        return ['inserirVariavel'];
    }

    public function getDisabledToolbarButtons(): array
    {
        return [];
    }
}
