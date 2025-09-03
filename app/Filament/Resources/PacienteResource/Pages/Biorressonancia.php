<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use App\Http\Helpers\AgentHelper;
use App\Models\CategoriaTestador;
use App\Models\Exame;
use App\Models\Paciente;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Pages\PageRegistration;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

class Biorressonancia extends Page
{
    protected static string $view = 'filament.pages.biorressonancia';

    protected static ?string $title = 'Biorressonância';

    public Paciente|int|string|null $paciente;

    public Exame|int|string|null $exame;

    public bool $isMobile = false;

    public $datas = null;

    public array $tableData = [];

    public static function route(string $path): PageRegistration
    {
        return new PageRegistration(
            page: static::class,
            route: fn (Panel $panel): Route => RouteFacade::get($path, static::class)
                ->middleware(static::getRouteMiddleware($panel))
                ->withoutMiddleware(static::getWithoutRouteMiddleware($panel)),
        );
    }

    public function mount(int|string $record): void
    {
        $this->paciente = Paciente::findOrFail($record);
        $this->isMobile = AgentHelper::isMobile();

        $this->getExameData();
    }

    public function getBreadcrumbs(): array
    {
        return [
            PacienteResource::getUrl() => 'Pacientes',
            PacienteResource::getUrl('edit', ['record' => $this->paciente]) => $this->paciente->nome,
            'Biorressonância',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prontuário')
                ->url(route('filament.admin.resources.pacientes.protuario', ['record' => $this->paciente->id])),
        ];
    }

    public function createExameAction(): Action
    {
        // Carregar as categorias com seus testadores
        $categorias = CategoriaTestador::with(['testadores' => function ($query) {
            $query->orderBy('numero', 'asc');
        }])->orderBy('ordem')->get();

        return Action::make('createExame')
            ->form(array_merge(
                [
                    Grid::make(4)
                        ->schema([
                            DateTimePicker::make('data')
                                ->seconds(false)
                                ->native()
                                ->displayFormat('d/m/Y H:i')
                                ->firstDayOfWeek(7)
                                ->closeOnDateSelection()
                                ->maxDate(now()->endOfDay())
                                ->default(now())
                                ->required(),
                        ]),
                ],
                [
                    Grid::make(1)
                        ->schema(
                            // Mapear as categorias para criar selects múltiplos para cada categoria
                            $categorias->map(function ($categoria) {
                                return Select::make('testadores_'.$categoria->id)
                                    ->label($categoria->nome) // Nome da categoria como label
                                    ->options($categoria->testadores->pluck('nome', 'id')->mapWithKeys(function ($nome, $id) use ($categoria) {
                                        // Concatena número e nome
                                        return [$id => $categoria->testadores->find($id)->numero.' - '.$nome];
                                    }))
                                    ->multiple(); // Permitir seleção múltipla
                            })->toArray()
                        ),
                ]
            ))
            ->action(function (array $data): void {
                $this->createExame($data);
            })
            ->label('Novo Exame')
            ->icon('heroicon-o-plus')
            ->size(ActionSize::Small)
            ->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::SixExtraLarge)
            ->extraModalWindowAttributes(AgentHelper::isMobile() ? ['style' => 'overflow: auto'] : ['style' => 'padding: 0px 37.5px'])
            // ->modalHeading(' ')
            ->modalAutofocus(false)
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('Salvar');
    }

    public function createExame(array $data)
    {
        $exame = Exame::create([
            'paciente_id' => $this->paciente->id,
            'data' => $data['data'],
            'tratamento' => $data['tratamento'] ?? null,
        ]);

        // Itera sobre as categorias e associa os testadores
        $categorias = CategoriaTestador::all();
        foreach ($categorias as $categoria) {
            if (isset($data['testadores_'.$categoria->id])) {
                $testadores = $data['testadores_'.$categoria->id];
                $exame->testadores()->attach($testadores);
            }
        }

        $this->getExameData();
    }

    public function editExameAction(): Action
    {
        // Carregar as categorias com seus testadores
        $categorias = CategoriaTestador::with(['testadores' => function ($query) {
            $query->orderBy('numero', 'asc');
        }])->orderBy('ordem')->get();

        return Action::make('editExame')
            ->form(array_merge(
                [
                    Grid::make(4)
                        ->schema([
                            DateTimePicker::make('data')
                                ->seconds(false)
                                ->native()
                                ->displayFormat('d/m/Y H:i')
                                ->firstDayOfWeek(7)
                                ->closeOnDateSelection()
                                ->maxDate(now()->endOfDay())
                                ->required(),
                        ]),
                ],
                [
                    Grid::make(1)
                        ->schema(
                            // Adicione a lógica para carregar testadores aqui
                            $categorias->map(function ($categoria) {
                                return Select::make('testadores_'.$categoria->id)
                                    ->label($categoria->nome)
                                    ->options($categoria->testadores->pluck('nome', 'id')->mapWithKeys(function ($nome, $id) use ($categoria) {
                                        return [$id => $categoria->testadores->find($id)->numero.' - '.$nome];
                                    }))
                                    ->multiple();
                            })->toArray()
                        ),
                ]
            ))
            ->fillForm(function (array $arguments) {
                // Encontre o exame pelo ID passado nos argumentos, carregando os testadores
                $this->exame = Exame::with(['testadores' => function ($query) {
                    $query->orderBy('numero');
                }])->find($arguments['id']);

                // Criar um array para preencher os dados do formulário
                $formData = [
                    'data' => $this->exame->data, // Preencher o campo de data
                ];

                // Carregar as categorias novamente, caso precise
                $categorias = CategoriaTestador::with(['testadores' => function ($query) {
                    $query->orderBy('numero', 'asc');
                }])->orderBy('ordem')->get();

                // Iterar sobre as categorias para preencher os testadores
                foreach ($categorias as $categoria) {
                    // Preencher o array para cada categoria com os testadores relacionados ao exame
                    $formData['testadores_'.$categoria->id] = $this->exame->testadores
                        ->where('categoria_testador_id', $categoria->id)
                        ->pluck('id')
                        ->toArray();
                }

                return $formData; // Retornar o array com todos os dados
            })
            ->action(function (array $arguments, array $data): void {
                $this->updateExame($arguments['id'], $data);
            })
            ->label('Editar Exame')
            ->icon('heroicon-o-pencil')
            ->extraModalFooterActions([
                Action::make('Excluir')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-trash')
                    ->modalHeading('Excluir exame')
                    ->modalDescription('Tem certeza de que deseja excluir este exame? Isto não pode ser desfeito.')
                    ->modalSubmitActionLabel('Sim, exclua-o')
                    ->action(function () {
                        $this->delete($this->exame);
                    })
                    ->cancelParentActions()
                    ->color('danger')
                    ->extraAttributes(['style' => 'position: absolute; right: 24px;']),
            ])
            ->size(ActionSize::Small)
            ->modalWidth(AgentHelper::isMobile() ? MaxWidth::Screen : MaxWidth::SixExtraLarge)
            ->extraModalWindowAttributes(AgentHelper::isMobile() ? ['style' => 'overflow: auto'] : ['style' => 'padding: 0px 37.5px'])
            ->modalAutofocus(false)
            ->closeModalByClickingAway(false)
            ->modalSubmitActionLabel('Salvar');
    }

    public function delete(Exame $exame): void
    {
        if ($exame->paciente_id === $this->paciente->id) {
            $this->exame->delete();
            $this->paciente->refresh();

            Notification::make()
                ->title('Exame excluído com sucesso!')
                ->success()
                ->send();
            $this->getExameData();
        } else {
            Notification::make()
                ->title('Exame não encontrado ou não pertence a este paciente.')
                ->error()
                ->send();
        }
    }

    public function updateExame($id, array $data): void
    {
        $exame = Exame::with('testadores')->find($id);

        // Atualizar os dados do exame
        $exame->data = $data['data'];
        $exame->save(); // Salvar as mudanças no exame

        // Carregar as categorias para processar os testadores
        $categorias = CategoriaTestador::with('testadores')->get();

        // Remover todos os testadores relacionados ao exame antes de adicionar os novos
        $exame->testadores()->detach();

        // Iterar sobre as categorias para atualizar os testadores
        foreach ($categorias as $categoria) {
            // Verifique se há testadores para essa categoria na entrada de dados
            if (isset($data['testadores_'.$categoria->id])) {
                // Adicionar os testadores selecionados ao exame
                $exame->testadores()->attach($data['testadores_'.$categoria->id]);
            }
        }

        // Atualiza a lista de exames ou qualquer outra lógica necessária
        $this->getExameData();
    }

    public function getExameData()
    {
        $this->tableData = [];

        // Recuperar exames e testadores com suas respectivas datas
        $exames = Exame::with(['testadores' => function ($query) {
            $query->orderBy('numero');
        }])->where('paciente_id', $this->paciente->id)->orderBy('data')->get();

        if ($exames->count() == 0) {
            return;
        }
        // Obter as datas exclusivas dos exames (para usar como cabeçalho)
        $this->datas = $exames->map(function ($exame) {
            return [
                'id' => $exame->id, // Retorna o ID do exame
                'data' => \Carbon\Carbon::parse($exame->data)->format('d/m'), // Formata a data
            ];
        });

        // Obter categorias e seus testadores
        $categorias = CategoriaTestador::with(['testadores' => function ($query) {
            $query->orderBy('numero', 'asc');
        }])->orderBy('ordem')->get();

        foreach ($categorias as $categoria) {
            // Filtrar os testadores que não participaram de nenhum exame
            $categoria->testadores = $categoria->testadores->filter(function ($testador) use ($exames) {
                // Verifica se o testador participou de pelo menos um exame
                return $exames->contains(function ($exame) use ($testador) {
                    return $exame->testadores->contains($testador);
                });
            });

            // Preencher a tabela somente com os testadores que sobraram após o filtro
            foreach ($categoria->testadores as $testador) {
                $row = [
                    'numero' => $testador->numero,
                    'nome' => $testador->nome,
                    'categoria' => $categoria->nome,
                ];

                $hasExame = false; // Flag para verificar se há um "X" na linha

                // Preencher a coluna de cada data com X se o testador participou do exame
                foreach ($exames as $exame) {
                    $row['id_'.$exame->id] =
                        $exame->testadores->contains($testador) ? 'X' : '';
                }

                $this->tableData[] = $row;
            }
        }
    }

    // public function printAction(): Action
    // {
    //     return Action::make('print')
    //         ->icon('heroicon-o-printer')
    //         ->outlined()
    //         ->label('Imprimir')
    //         ->url(fn(): string => route('biorressonancia.print', ['id' => $this->paciente->id]), true);
    // }
}
