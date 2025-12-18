<?php

namespace App\Filament\Resources\TratamentoResource\Pages;

use App\Filament\Resources\TratamentoResource;
use Filament\Resources\Pages\Page;

class RelatorioTratamentos extends Page
{
    protected static string $resource = TratamentoResource::class;

    protected static string $view = 'filament.resources.tratamento-resource.pages.relatorio-tratamentos';
}
