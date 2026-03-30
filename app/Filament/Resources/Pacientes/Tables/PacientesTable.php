<?php

namespace App\Filament\Resources\Pacientes\Tables;

use App\Models\Paciente;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PacientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    TextColumn::make('nome')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    Split::make([
                        TextColumn::make('nascimento')->grow(false)
                            ->formatStateUsing(fn ($state) => Paciente::calcularIdade($state)),
                        TextColumn::make('sexo'),
                        TextColumn::make('tiposanguineo')
                            ->label('Tipo Sanguíneo'),
                    ]),
                ])->from('xl'),
                Split::make([
                    Panel::make([
                        Split::make([
                            TextColumn::make('email')
                                ->icon('heroicon-m-envelope')
                                ->iconColor('primary')
                                ->copyable()
                                ->copyMessage('Email copiado para a área de transferência')
                                ->copyMessageDuration(1500)
                                ->extraAttributes([
                                    'class' => 'text-primary-600',
                                ]),
                            TextColumn::make('celular')
                                ->url(fn ($state) => "https://wa.me/+55{$state}")
                                ->openUrlInNewTab()
                                ->icon('heroicon-m-phone')
                                ->iconColor('primary')
                                ->extraAttributes([
                                    'class' => 'text-primary-600',
                                ]),
                        ])->from('xl'),
                    ]),
                ])->from('xl')->collapsible(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn (Paciente $record): string => route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $record->id]),
            );
    }
}
