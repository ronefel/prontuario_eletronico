<?php

namespace App\Filament\Central\Resources\CentralUsers\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CentralUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique('central_users', 'email', ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->autocomplete('new-password')
                    ->required(fn (string $context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->confirmed()
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->label('Confirmar Senha')
                    ->password()
                    ->autocomplete('new-password')
                    ->requiredWith('password')
                    ->dehydrated(false),
                Select::make('timezone')
                    ->label('Fuso Horário')
                    ->options(User::getAvailableTimezones())
                    ->default('America/Sao_Paulo')
                    ->required(),
            ]);
    }
}
