<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as PagesEditProfile;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends PagesEditProfile
{
    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    public function getView(): string
    {
        return 'filament.pages.auth.edit-profile';
    }

    public static function getLabel(): string
    {
        return 'Editar Perfil';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'me';
    }

    protected function getRedirectUrl(): ?string
    {
        return '/admin/me';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perfil salvo com sucesso';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->autocomplete(false)
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->autocomplete(false)
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->autocomplete('new-password')
                    ->rule(Password::default())
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required(fn (string $context) => $context == 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                    ->confirmed()
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->password()
                    ->autocomplete('new-password')
                    ->requiredWith('password')
                    ->revealable(filament()->arePasswordsRevealable())
                    ->dehydrated(false),
                Select::make('timezone')
                    ->label('Fuso Horário')
                    ->options(User::getAvailableTimezones())
                    ->default('America/Manaus')
                    ->required(),
            ]),
            Action::make('save')
                ->label('Salvar Alterações')
                ->submit('save'),
        ]);
    }
}
