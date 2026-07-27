<?php

namespace App\Filament\Pages;

use App\Models\ConfiguracaoNotaFiscal;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class ConfiguracaoNotaFiscalPage extends Page
{
    protected string $view = 'filament.pages.configuracao-nota-fiscal';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $title = 'Config. Nota Fiscal';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 404;

    public ?array $dados = [];

    public function mount(): void
    {
        $record = $this->getRecord();
        $this->dados = $record->attributesToArray();

        // Preencher a senha descriptografada para exibição/edição se existir
        if ($record->senha_certificado_descriptografada) {
            $this->dados['senha_certificado'] = $record->senha_certificado_descriptografada;
        }

        $this->form->fill($this->dados);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Grid::make(2)->schema([
                        Fieldset::make('Dados do Emitente (Clínica / Prestador)')
                            ->schema([
                                TextInput::make('cnpj')
                                    ->label('CNPJ')
                                    ->required()
                                    ->mask('99.999.999/9999-99')
                                    ->columnSpan(1),

                                TextInput::make('inscricao_municipal')
                                    ->label('Inscrição Municipal')
                                    ->columnSpan(1),

                                TextInput::make('razao_social')
                                    ->label('Razão Social')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('nome_fantasia')
                                    ->label('Nome Fantasia')
                                    ->columnSpan(1),

                                TextInput::make('codigo_municipio_ibge')
                                    ->label('Código IBGE do Município')
                                    ->default('1100049')
                                    ->helperText('Cacoal / RO = 1100049')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('uf')
                                    ->label('UF')
                                    ->default('RO')
                                    ->maxLength(2)
                                    ->required()
                                    ->columnSpan(1),
                            ])
                            ->columnSpan(2),

                        Fieldset::make('Parâmetros Fiscais (ABRASF v2.02)')
                            ->schema([
                                Select::make('regime_especial_tributacao')
                                    ->label('Regime Especial de Tributação')
                                    ->options([
                                        0 => 'Nenhum',
                                        1 => 'Microempresa Municipal',
                                        2 => 'Estimativa',
                                        3 => 'Sociedade de Profissionais',
                                        4 => 'Cooperativa',
                                        5 => 'Microempresário Individual (MEI)',
                                        6 => 'Microempresário e Empresa de Pequeno Porte (ME/EPP)',
                                    ])
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('item_lista_servico')
                                    ->label('Item da Lista de Serviço (LC 116)')
                                    ->default('04.01')
                                    ->helperText('Ex: 04.01 - Medicina e biomedicina')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('codigo_tributacao_municipio')
                                    ->label('Código de Tributação Municipal')
                                    ->columnSpan(1),

                                TextInput::make('aliquota_iss')
                                    ->label('Alíquota ISS (%)')
                                    ->numeric()
                                    ->default(2.00)
                                    ->required()
                                    ->columnSpan(1),

                                Toggle::make('optante_simples_nacional')
                                    ->label('Optante Simples Nacional')
                                    ->default(true)
                                    ->columnSpan(1),

                                Toggle::make('incentivador_cultural')
                                    ->label('Incentivador Cultural')
                                    ->default(false)
                                    ->columnSpan(1),
                            ])
                            ->columnSpan(2),

                        Fieldset::make('Certificado Digital A1')
                            ->schema([
                                FileUpload::make('caminho_certificado')
                                    ->label('Arquivo do Certificado A1 (.pfx / .p12)')
                                    ->directory('certificados')
                                    ->acceptedFileTypes(['application/x-pkcs12', 'application/x-pkcs12-certificate', 'application/octet-stream'])
                                    ->columnSpan(1),

                                TextInput::make('senha_certificado')
                                    ->label('Senha do Certificado Digital')
                                    ->password()
                                    ->revealable()
                                    ->columnSpan(1),
                            ])
                            ->columnSpan(2),

                        Fieldset::make('Ambiente e Controle de Numeração')
                            ->schema([
                                Select::make('ambiente')
                                    ->label('Ambiente de Emissão')
                                    ->options([
                                        'homologacao' => 'Homologação (Testes)',
                                        'producao' => 'Produção (Real)',
                                    ])
                                    ->default('homologacao')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('serie_rps')
                                    ->label('Série do RPS')
                                    ->default('1')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('ultimo_numero_rps')
                                    ->label('Último Número de RPS Gerado')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('url_webservice_homologacao')
                                    ->label('URL WebService Homologação')
                                    ->placeholder('https://...')
                                    ->columnSpan(1),

                                TextInput::make('url_webservice_producao')
                                    ->label('URL WebService Produção')
                                    ->placeholder('https://...')
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(2),
                    ]),
                ])
                    ->livewireSubmitHandler('salvar')
                    ->footer([
                        Actions::make([
                            Action::make('salvar')
                                ->label('Salvar Configurações')
                                ->submit('salvar')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('dados');
    }

    public function salvar(): void
    {
        $dadosForm = $this->form->getState();
        $configuracao = $this->getRecord();

        $configuracao->fill($dadosForm);
        $configuracao->save();

        Notification::make()
            ->success()
            ->title('Configurações Salvas!')
            ->body('As configurações da nota fiscal foram atualizadas com sucesso.')
            ->send();
    }

    public function getRecord(): ConfiguracaoNotaFiscal
    {
        $config = ConfiguracaoNotaFiscal::first();

        if (! $config) {
            $config = ConfiguracaoNotaFiscal::create([
                'cnpj' => '00000000000191',
                'razao_social' => 'Clínica Médica Exemplo',
                'nome_fantasia' => 'Clínica Cacoal',
                'codigo_municipio_ibge' => '1100049',
                'uf' => 'RO',
                'regime_especial_tributacao' => 0,
                'optante_simples_nacional' => true,
                'incentivador_cultural' => false,
                'item_lista_servico' => '04.01',
                'aliquota_iss' => 2.00,
                'serie_rps' => '1',
                'ultimo_numero_rps' => 0,
                'ambiente' => 'homologacao',
            ]);
        }

        return $config;
    }
}
