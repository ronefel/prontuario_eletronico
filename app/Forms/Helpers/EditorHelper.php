<?php

namespace App\Forms\Helpers;

use App\Filament\RichEditorPlugins\PluginMascaraRichEditor;
use App\Filament\RichEditorPlugins\PluginVariavelRichEditor;
use App\Models\Setting;
use Filament\Forms\Components\RichEditor;

class EditorHelper
{
    public static function criarRichEditor(string $nomeCampo): RichEditor
    {
        $configuracoes = Setting::getAllSettings();

        $margemSuperior = $configuracoes['margem_superior'] ?? 10;
        $margemInferior = $configuracoes['margem_inferior'] ?? 10;
        $margemEsquerda = $configuracoes['margem_esquerda'] ?? 15;
        $margemDireita = $configuracoes['margem_direita'] ?? 15;

        $estiloMargens = "padding-top: {$margemSuperior}mm; padding-bottom: {$margemInferior}mm; padding-left: {$margemEsquerda}mm; padding-right: {$margemDireita}mm;";

        return RichEditor::make($nomeCampo)
            ->plugins([
                PluginMascaraRichEditor::make(),
                PluginVariavelRichEditor::make(),
            ])
            ->toolbarButtons([
                ['inserirMascara', 'inserirVariavel'],
                ['undo', 'redo'],
                ['bold', 'italic', 'underline', 'strike'],
                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                ['bulletList', 'orderedList'],
                ['table', 'link'],
                ['h1', 'h2', 'h3'],
            ])
            ->extraAttributes([
                'style' => $estiloMargens,
                'class' => 'document-content document-content-editor',
            ]);
    }
}
