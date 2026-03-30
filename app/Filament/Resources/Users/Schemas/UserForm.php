<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->autocomplete('new-password')
                    ->required(fn (string $context) => $context == 'create')
                    ->dehydrated(fn ($state) => $state !== null && filled($state))
                    ->confirmed()
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->password()
                    ->autocomplete('new-password')
                    ->requiredWith('password')
                    ->dehydrated(false),
                Select::make('timezone')
                    ->label('Fuso Horário')
                    ->options(User::getAvailableTimezones())
                    ->default('America/Manaus')
                    ->required(),
            ]);
    }
}
