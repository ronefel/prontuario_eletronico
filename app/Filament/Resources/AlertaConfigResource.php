<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlertaConfigResource\Pages;
use App\Models\AlertaConfig;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertaConfigResource extends Resource
{
    protected static ?string $model = AlertaConfig::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Configuração de Alertas';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                    ->options([
                        'estoque_baixo' => 'Estoque Baixo',
                        'validade_proxima' => 'Validade Próxima',
                        'excesso' => 'Excesso de Estoque',
                        'discrepancia' => 'Discrepância no Inventário',
                    ])
                    ->required(),
                Forms\Components\Select::make('produto_id')
                    ->relationship('produto', 'nome')
                    ->nullable(),
                Forms\Components\TextInput::make('threshold')
                    ->numeric()
                    ->required()
                    ->label(fn ($get) => $get('tipo') === 'validade_proxima' ? 'Dias para Validade' : 'Quantidade'),
                Forms\Components\Select::make('metodo_notificacao')
                    ->options([
                        'email' => 'Email',
                        'in_app' => 'Notificação no Sistema',
                        'sms' => 'SMS',
                    ])
                    ->default('in_app'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'estoque_baixo' => 'Estoque Baixo',
                        'validade_proxima' => 'Validade Próxima',
                        'excesso' => 'Excesso de Estoque',
                        'discrepancia' => 'Discrepância no Inventário',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('produto.nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('threshold')
                    ->label('Limite'),
                Tables\Columns\TextColumn::make('metodo_notificacao')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'email' => 'Email',
                        'in_app' => 'Notificação no Sistema',
                        'sms' => 'SMS',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'estoque_baixo' => 'Estoque Baixo',
                        'validade_proxima' => 'Validade Próxima',
                        'excesso' => 'Excesso de Estoque',
                        'discrepancia' => 'Discrepância no Inventário',
                    ]),
                Tables\Filters\SelectFilter::make('produto')
                    ->relationship('produto', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlertaConfigs::route('/'),
            'create' => Pages\CreateAlertaConfig::route('/create'),
            'edit' => Pages\EditAlertaConfig::route('/{record}/edit'),
        ];
    }
}
