<?php

namespace App\Forms\Components;

use App\Models\Cidade;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component as Livewire;

class Cep extends TextInput
{
    protected static array $cacheMemoriaLocal = [];

    public static function obterResultadosBuscaEndereco(?string $uf, ?string $cidade, ?string $logradouro): array
    {
        $uf = trim($uf ?? '');
        $cidade = Str::ascii(trim($cidade ?? ''));
        $logradouro = Str::ascii(trim($logradouro ?? ''));

        if (empty($uf) || strlen($cidade) < 3 || strlen($logradouro) < 3) {
            return ['opcoes' => [], 'descricoes' => []];
        }

        $chaveCache = 'viacep_busca_'.md5("{$uf}_{$cidade}_{$logradouro}");

        if (isset(static::$cacheMemoriaLocal[$chaveCache])) {
            return static::$cacheMemoriaLocal[$chaveCache];
        }

        $resultado = Cache::remember($chaveCache, 3600, function () use ($uf, $cidade, $logradouro) {
            try {
                $url = sprintf(
                    'https://viacep.com.br/ws/%s/%s/%s/json/',
                    rawurlencode($uf),
                    rawurlencode($cidade),
                    rawurlencode($logradouro)
                );
                $resposta = Http::timeout(4)->get($url)->json();

                if (! is_array($resposta) || empty($resposta) || isset($resposta['erro'])) {
                    return ['opcoes' => [], 'descricoes' => []];
                }

                $opcoes = [];
                $descricoes = [];
                foreach ($resposta as $item) {
                    if (! empty($item['cep'])) {
                        $cep = $item['cep'];
                        $logradouroItem = $item['logradouro'] ?? '';
                        $bairroItem = $item['bairro'] ?? '';
                        $localidadeItem = $item['localidade'] ?? '';
                        $complementoItem = ! empty($item['complemento']) ? " ({$item['complemento']})" : '';

                        $opcoes[$cep] = "{$cep} — {$logradouroItem}";
                        $descricoes[$cep] = "Bairro {$bairroItem}, {$localidadeItem}{$complementoItem}";
                    }
                }

                return ['opcoes' => $opcoes, 'descricoes' => $descricoes];
            } catch (\Throwable $e) {
                return ['opcoes' => [], 'descricoes' => []];
            }
        });

        return static::$cacheMemoriaLocal[$chaveCache] = $resultado;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mask('99999-999');
        $this->placeholder('00000-000');
        $this->length(9);
    }

    public function viaCep(
        $errorMessage = 'CEP inválido ou não encontrado.',
        $setFields = []
    ): static {
        $requisicaoViaCep = function ($estado, Livewire $livewire, Set $set, Component $componente, $errorMessage, $setFields, bool $validarSeVazio = false) {
            $caminhoEstado = $componente->getStatePath();
            $chaveComponente = $componente->getKey();

            if (method_exists($livewire, 'resetValidation')) {
                $livewire->resetValidation($caminhoEstado);
                if ($caminhoEstado !== $chaveComponente) {
                    $livewire->resetValidation($chaveComponente);
                }
            }

            $exibirErro = function (string $mensagem) use ($livewire, $caminhoEstado, $chaveComponente) {
                $livewire->addError($caminhoEstado, $mensagem);
                if ($caminhoEstado !== $chaveComponente) {
                    $livewire->addError($chaveComponente, $mensagem);
                }

                Notification::make()
                    ->title('CEP Inválido')
                    ->body($mensagem)
                    ->danger()
                    ->send();

                throw ValidationException::withMessages([
                    $caminhoEstado => $mensagem,
                    $chaveComponente => $mensagem,
                ]);
            };

            if (empty($estado)) {
                if ($validarSeVazio) {
                    $exibirErro('Informe um CEP para pesquisar.');
                }

                return;
            }

            $cepLimpo = preg_replace('/\D/', '', $estado);

            if (strlen($cepLimpo) !== 8) {
                $exibirErro('O CEP deve possuir 8 dígitos.');
            }

            try {
                $requisicao = Http::timeout(5)->get('https://viacep.com.br/ws/'.$cepLimpo.'/json/');

                if (! $requisicao->successful()) {
                    Notification::make()
                        ->title('Serviço ViaCEP Indisponível')
                        ->body('O serviço do ViaCEP está indisponível no momento. Por favor, preencha o endereço manualmente.')
                        ->warning()
                        ->send();

                    return;
                }

                $resposta = $requisicao->json();
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Serviço ViaCEP Indisponível')
                    ->body('Não foi possível se conectar ao serviço do ViaCEP. Por favor, preencha o endereço manualmente.')
                    ->warning()
                    ->send();

                return;
            }

            if (! $resposta || ! empty($resposta['erro'])) {
                $exibirErro($errorMessage);
            }

            foreach ($setFields as $chave => $valor) {
                if ($chave === 'localidade') {
                    if (! empty($resposta['localidade']) && ! empty($resposta['uf'])) {
                        $cidade = Cidade::where('uf', $resposta['uf'])
                            ->where('nome', $resposta['localidade'])
                            ->first();

                        $codigoIbgeViaCep = $resposta['ibge'] ?? null;

                        if ($cidade) {
                            if ($codigoIbgeViaCep && $cidade->codigo_ibge !== $codigoIbgeViaCep) {
                                $cidade->update(['codigo_ibge' => $codigoIbgeViaCep]);
                            }
                        } else {
                            $cidade = Cidade::create([
                                'nome' => $resposta['localidade'],
                                'uf' => $resposta['uf'],
                                'codigo_ibge' => $codigoIbgeViaCep,
                            ]);
                        }

                        if ($cidade) {
                            $set($valor, $cidade->id);
                        }
                    }

                    continue;
                }
                $set($valor, $resposta[$chave] ?? null);
            }
        };

        $this->live(onBlur: true);

        $this->rules([
            fn (): Closure => function (string $atributo, $valor, Closure $falha) use ($errorMessage) {
                if (! $valor) {
                    return;
                }

                $cepLimpo = preg_replace('/\D/', '', $valor);

                if (strlen($cepLimpo) !== 8) {
                    $falha('O CEP deve possuir 8 dígitos.');

                    return;
                }

                try {
                    $resposta = Http::get('https://viacep.com.br/ws/'.$cepLimpo.'/json/')->json();
                    if (! $resposta || ! empty($resposta['erro'])) {
                        $falha($errorMessage);
                    }
                } catch (\Throwable $e) {
                    // Ignora falhas de conexão/timeout da API externa durante o submit
                }
            },
        ]);

        $this->prefixIcon(function ($state, Livewire $livewire, Component $componente) {
            if (empty($state)) {
                return null;
            }

            $cepLimpo = preg_replace('/\D/', '', $state);
            if (strlen($cepLimpo) !== 8) {
                return Heroicon::XMark;
            }

            $temErro = $livewire->getErrorBag()->has($componente->getStatePath())
                || $livewire->getErrorBag()->has($componente->getKey());

            return $temErro ? Heroicon::XMark : Heroicon::Check;
        });

        $this->prefixIconColor(function ($state, Livewire $livewire, Component $componente) {
            if (empty($state)) {
                return null;
            }

            $cepLimpo = preg_replace('/\D/', '', $state);
            if (strlen($cepLimpo) !== 8) {
                return 'danger';
            }

            $temErro = $livewire->getErrorBag()->has($componente->getStatePath())
                || $livewire->getErrorBag()->has($componente->getKey());

            return $temErro ? 'danger' : 'success';
        });

        $this->afterStateUpdated(function ($state, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $requisicaoViaCep) {
            $requisicaoViaCep(
                $state,
                $livewire,
                $set,
                $component,
                $errorMessage,
                $setFields,
                false
            );
        });

        $this->suffixAction(
            Action::make('buscarCepPorEndereco')
                ->icon('heroicon-m-magnifying-glass')
                ->label('Buscar CEP por Endereço')
                ->modalHeading('Buscar CEP por Endereço')
                ->modalDescription('Informe a UF, a cidade e o nome da rua para localizar o CEP.')
                ->modalSubmitActionLabel('Aplicar CEP Selecionado')
                ->modalWidth('lg')
                ->fillForm(function (Get $get): array {
                    $uf = 'RO';
                    $cidadeNome = 'Cacoal';

                    $cidadeId = $get('cidade_id');
                    if ($cidadeId) {
                        $cidadeModel = Cidade::find($cidadeId);
                        if ($cidadeModel) {
                            $cidadeNome = $cidadeModel->nome;
                            $uf = $cidadeModel->uf;
                        }
                    }

                    $logradouro = trim($get('logradouro') ?? '');

                    return [
                        'uf' => $uf,
                        'cidade' => $cidadeNome,
                        'logradouro' => $logradouro,
                        'uf_pesquisada' => $uf,
                        'cidade_pesquisada' => $cidadeNome,
                        'logradouro_pesquisado' => (strlen($logradouro) >= 3) ? $logradouro : null,
                    ];
                })
                ->form([
                    Grid::make(3)->schema([
                        Select::make('uf')
                            ->label('UF')
                            ->options([
                                'AC' => 'AC',
                                'AL' => 'AL',
                                'AM' => 'AM',
                                'AP' => 'AP',
                                'BA' => 'BA',
                                'CE' => 'CE',
                                'DF' => 'DF',
                                'ES' => 'ES',
                                'GO' => 'GO',
                                'MA' => 'MA',
                                'MG' => 'MG',
                                'MS' => 'MS',
                                'MT' => 'MT',
                                'PA' => 'PA',
                                'PB' => 'PB',
                                'PE' => 'PE',
                                'PI' => 'PI',
                                'PR' => 'PR',
                                'RJ' => 'RJ',
                                'RN' => 'RN',
                                'RO' => 'RO',
                                'RR' => 'RR',
                                'RS' => 'RS',
                                'SC' => 'SC',
                                'SE' => 'SE',
                                'SP' => 'SP',
                                'TO' => 'TO',
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        TextInput::make('cidade')
                            ->label('Cidade')
                            ->required()
                            ->minLength(3)
                            ->columnSpan(2),

                        TextInput::make('logradouro')
                            ->label('Nome da Rua / Logradouro')
                            ->placeholder('Ex: Duque de Caxias')
                            ->required()
                            ->minLength(3)
                            ->extraAlpineAttributes([
                                'x-on:keydown.enter.prevent' => '$el.closest(\'.fi-input-wrp\')?.querySelector(\'button\')?.click()',
                            ])
                            ->suffixAction(
                                Action::make('pesquisarLogradouro')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->color('primary')
                                    ->tooltip('Pesquisar CEP por esta rua')
                                    ->action(function (Set $set, Get $get) {
                                        $set('logradouro_pesquisado', $get('logradouro'));
                                        $set('cidade_pesquisada', $get('cidade'));
                                        $set('uf_pesquisada', $get('uf'));
                                    })
                            )
                            ->columnSpan(3),

                        Radio::make('cep_selecionado')
                            ->label('Resultados Encontrados')
                            ->options(function (Get $get) {
                                $logradouro = $get('logradouro_pesquisado');
                                $cidade = $get('cidade_pesquisada');
                                $uf = $get('uf_pesquisada');

                                if (empty($logradouro)) {
                                    return [];
                                }

                                $resultados = Cep::obterResultadosBuscaEndereco($uf, $cidade, $logradouro);

                                return $resultados['opcoes'];
                            })
                            ->descriptions(function (Get $get) {
                                $logradouro = $get('logradouro_pesquisado');
                                $cidade = $get('cidade_pesquisada');
                                $uf = $get('uf_pesquisada');

                                if (empty($logradouro)) {
                                    return [];
                                }

                                $resultados = Cep::obterResultadosBuscaEndereco($uf, $cidade, $logradouro);

                                return $resultados['descricoes'];
                            })
                            ->required()
                            ->live()
                            ->columnSpan(3)
                            ->helperText(function (Get $get) {
                                $logradouro = trim($get('logradouro_pesquisado') ?? '');
                                $cidade = trim($get('cidade_pesquisada') ?? '');
                                $uf = $get('uf_pesquisada');

                                if (empty($logradouro)) {
                                    return 'Digite o nome da rua acima e clique no ícone da lupa para pesquisar.';
                                }

                                $resultados = Cep::obterResultadosBuscaEndereco($uf, $cidade, $logradouro);

                                if (empty($resultados['opcoes'])) {
                                    return 'Nenhum CEP foi encontrado para o endereço informado. Verifique o nome da rua ou a cidade.';
                                }

                                return 'Selecione o CEP correto na lista acima.';
                            }),
                    ]),
                ])
                ->action(function (array $data, Livewire $livewire, Set $set, Component $component) use ($errorMessage, $setFields, $requisicaoViaCep) {
                    $cepEncontrado = $data['cep_selecionado'] ?? null;

                    if (! $cepEncontrado) {
                        Notification::make()
                            ->title('Nenhum CEP selecionado')
                            ->body('Por favor, selecione um CEP nos resultados da pesquisa.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $set('cep', $cepEncontrado);

                    $requisicaoViaCep(
                        $cepEncontrado,
                        $livewire,
                        $set,
                        $component,
                        $errorMessage,
                        $setFields,
                        false
                    );
                })
        );

        return $this;
    }
}
