<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MascaraResource\Pages;
use App\Forms\Components\CKEditor;
use App\Http\Helpers\AgentHelper;
use App\Models\Mascara;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class MascaraResource extends Resource
{
    protected static ?string $model = Mascara::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Máscara';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make()->schema([
                    TextInput::make('nome')
                        ->required(),
                    CKEditor::make('descricao')
                        ->hiddenLabel()
                        ->required(),
                ])->columns(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::FiveExtraLarge),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nome');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMascaras::route('/'),
        ];
    }
}
