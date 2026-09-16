<?php

namespace App\Filament\Central\Resources\Tenants\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Subdomínio')
                    ->badge()
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nome')
                    ->label('Clínica')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('telefone')
                    ->label('Telefone'),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y H:i', Auth::user()?->timezone)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('abrirPainel')
                    ->label('Acessar Clínica')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(function ($record) {
                        $centralDomain = config('tenancy.central_domains.0', 'localhost');
                        $port = request()->getPort();
                        $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';
                        $scheme = request()->getScheme();

                        return "{$scheme}://{$record->id}.{$centralDomain}{$portSuffix}/admin";
                    })
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
