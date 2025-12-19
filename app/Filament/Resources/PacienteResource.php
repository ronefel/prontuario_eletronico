<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Forms\Components\Cep;
use App\Models\Cidade;
use App\Models\Paciente;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nome', 'cpf', 'nascimento'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        /** @var Paciente $record */
        return $record->nome.' ('.$record->nascimento->format('d/m/Y').')';
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        /** @var Paciente $record */
        return route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $record->id]);
    }

    // Personalizar a query de busca global
    public static function modifyGlobalSearchQuery(Builder $query, string $search): void
    {
        // Formata CPF no banco para somente números
        $query->orWhereRaw("regexp_replace(cpf, '\D', '', 'g') ILIKE ?", ["%{$search}%"]);

        // Formata data no banco para DDMMYYYY
        $query->orWhereRaw("to_char(nascimento, 'DDMMYYYY') ILIKE ?", ["%{$search}%"]);

        // Tentar converter a entrada para data no formato d/m/Y
        try {
            $date = Carbon::createFromFormat('d/m/Y', $search);
            if ($date) {
                $query->orWhereDate('nascimento', $date->format('Y-m-d'));
            }
        } catch (\Exception $e) {
            // Ignorar se não for uma data válida
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Dados pessoais')->schema([
                    TextInput::make('nome')
                        ->required()
                        ->acoff()
                        ->dehydrateStateUsing(fn (string $state): string => ucwords(strtolower($state))), // Capitaliza a primeira letra de cada palavra
                    Grid::make()->schema([
                        DatePicker::make('nascimento')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state): string => $set('idade', Paciente::calcularIdade($state)))
                            ->afterStateHydrated(fn (Set $set, ?string $state): bool => $set('idade', Paciente::calcularIdade($state))),
                        TextInput::make('idade')
                            ->disabled(),
                    ])->columns(['md' => 2])->columnSpan(1),
                    Grid::make()->schema([
                        Radio::make('sexo')
                            ->label('Sexo Biológico')
                            ->options([
                                'F' => 'Feminino',
                                'M' => 'Masculino',
                            ])
                            ->inline()
                            ->inlineLabel(false)
                            ->required(),
                        Select::make('tiposanguineo')
                            ->label('Tipo Sanguíneo')
                            ->options([
                                'A+' => 'A+',
                                'A-' => 'A-',
                                'B+' => 'B+',
                                'B-' => 'B-',
                                'AB+' => 'AB+',
                                'AB-' => 'AB-',
                                'O+' => 'O+',
                                'O-' => 'O-',
                            ]),
                    ])->columns(['md' => 2])->columnSpan(1),
                    TextInput::make('cpf')
                        ->label('CPF')
                        ->required()
                        ->acoff()
                        ->unique(ignorable: fn (?Paciente $record): ?Paciente => $record)
                        ->mask('999.999.999-99')
                        ->placeholder('000.000.000-00')
                        ->minLength(14),
                ])->columns(['md' => 2]),

                Fieldset::make('Contato')->schema([
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->unique(ignoreRecord: true)
                        ->nullable()
                        ->acoff()
                        ->dehydrateStateUsing(function ($state) {
                            return ! empty($state) ? strtolower($state) : null;
                        }),
                    TextInput::make('celular')
                        ->tel()
                        ->acoff()
                        ->mask('(99) 99999-9999')
                        ->placeholder('(00) 00000-0000')
                        ->dehydrateStateUsing(function ($state) {
                            return preg_replace('/[^0-9]/', '', $state) ?? null;
                        }),
                ])->columns(['md' => 2]),

                Fieldset::make('Endereço')->schema([
                    Cep::make('cep')
                        ->acoff()
                        ->viaCep(
                            setFields: [
                                'logradouro' => 'logradouro',
                                'bairro' => 'bairro',
                                'localidade' => 'cidade_id',
                            ],
                        ),
                    TextInput::make('logradouro')
                        ->acoff(),
                    TextInput::make('numero')
                        ->label('Número')
                        ->acoff(),
                    TextInput::make('complemento')
                        ->acoff(),
                    TextInput::make('bairro')
                        ->acoff(),

                    Select::make('cidade_id')
                        ->relationship(
                            name: 'cidade',
                            modifyQueryUsing: fn (Builder $query) => $query->orderBy('nome')->orderBy('uf')
                        )
                        ->getOptionLabelFromRecordUsing(fn (?Cidade $cidade) => $cidade?->nome.' - '.$cidade?->uf)
                        ->searchable()
                        ->preload()
                        ->createOptionForm(CidadeResource::formFields())->createOptionModalHeading('Criar Cidade')
                        ->editOptionForm(CidadeResource::formFields())->editOptionModalHeading('Editar Cidade'),
                ])->columns(['md' => 2]),

                Textarea::make('observacao')
                    ->label('Observação')
                    ->acoff()->rows(4)->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
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
                                ->copyable()
                                ->copyMessage('Email copiado para a área de transferência')
                                ->copyMessageDuration(1500),
                            TextColumn::make('celular')
                                ->url(fn ($state) => "https://wa.me/+55{$state}")
                                ->openUrlInNewTab()
                                ->icon('heroicon-m-phone'),
                        ])->from('xl'),
                    ]),
                ])->from('xl')->collapsible(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn (Paciente $record): string => route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $record->id]),
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
            'prontuario' => Pages\ProntuarioPaciente::route('/{record}/prontuario'),
        ];
    }
}
