<?php

namespace App\Filament\Pages;

use App\Models\Paciente;
use Filament\Pages\Page;

class Consultorio extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $title = '';

    protected static string $view = 'filament.pages.consultorio';

    protected static ?string $slug = 'consultorio/{paciente}';

    protected static bool $shouldRegisterNavigation = false;

    public ?Paciente $paciente = null;

    public string $activeTab = 'prontuario';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/pacientes' => 'Pacientes',
            '#' => $this->paciente->nome ?? '',
            'Consultório',
        ];
    }
}
