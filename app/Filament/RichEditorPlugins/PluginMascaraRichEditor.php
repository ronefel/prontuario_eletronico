<?php

namespace App\Filament\RichEditorPlugins;

use App\Models\Mascara;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\HasToolbarButtons;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;

class PluginMascaraRichEditor implements HasToolbarButtons, RichContentPlugin
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

    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('inserirMascara')
                ->label('Inserir Máscara')
                ->icon(Heroicon::OutlinedDocumentText)
                ->action(arguments: '{ editorSelection: $getEditor().state.selection }'),
        ];
    }

    public function getEditorActions(): array
    {
        return [
            Action::make('inserirMascara')
                ->modalHeading('Selecionar Máscara')
                ->modalWidth('md')
                ->schema([
                    Select::make('mascara_id')
                        ->label('Escolha a Máscara')
                        ->options(fn (): array => Mascara::query()->orderBy('nome')->pluck('nome', 'id')->toArray())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $arguments, array $data, RichEditor $component): void {
                    $mascara = Mascara::find($data['mascara_id'] ?? null);

                    if ($mascara && ! empty($mascara->descricao)) {
                        $component->runCommands(
                            [
                                EditorCommand::make('insertContent', arguments: [$mascara->descricao]),
                            ],
                            editorSelection: $arguments['editorSelection'] ?? null,
                        );
                    }
                }),
        ];
    }

    public function getEnabledToolbarButtons(): array
    {
        return ['inserirMascara'];
    }

    public function getDisabledToolbarButtons(): array
    {
        return [];
    }
}
