<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
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

    public static function getSlug(): string
    {
        return 'me';
    }

    protected function getRedirectUrl(): ?string
    {
        return '/';
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perfil salvo com sucesso';
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->acoff()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->acoff()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('password')
                        ->password()
                        ->acoff()
                        ->rule(Password::default())
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required(fn(string $context) => $context == 'create')
                        ->dehydrated(fn($state): bool => filled($state))
                        ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                        ->confirmed()
                        ->maxLength(255),
                    TextInput::make('password_confirmation')
                        ->password()
                        ->acoff()
                        ->requiredWith('password')
                        ->revealable(filament()->arePasswordsRevealable())
                        ->dehydrated(false),
                    Select::make('timezone')
                        ->label('Fuso Horário')
                        ->options(User::getAvailableTimezones())
                        ->default('America/Manaus')
                        ->required()
                ])

            ]);
    }
}
