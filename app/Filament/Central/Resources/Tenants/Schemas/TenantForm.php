<?php

namespace App\Filament\Central\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->label('Identificador / Subdomínio')
                    ->helperText('Ex: clinica-alfa (acesso via clinica-alfa.seudominio.com.br)')
                    ->required()
                    ->alphaDash()
                    ->unique('tenants', 'id', ignoreRecord: true)
                    ->disabled(fn (string $context) => $context === 'edit')
                    ->maxLength(50),
                TextInput::make('nome')
                    ->label('Nome da Clínica')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cnpj')
                    ->label('CNPJ')
                    ->mask('99.999.999/9999-99')
                    ->maxLength(20),
                TextInput::make('email')
                    ->label('E-mail de Contato')
                    ->email()
                    ->maxLength(255),
                TextInput::make('telefone')
                    ->label('Telefone / WhatsApp')
                    ->tel()
                    ->maxLength(30),
                Toggle::make('ativo')
                    ->label('Clínica Ativa')
                    ->default(true)
                    ->required(),
            ]);
    }
}
