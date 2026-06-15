<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class GoogleAutenticacaoController extends Controller
{
    /**
     * Manipula o retorno da autorização do Google OAuth e grava as credenciais.
     */
    public function callback(Request $request, GoogleCalendarService $servico)
    {
        $codigo = $request->query('code');

        if (!$codigo) {
            Notification::make()
                ->danger()
                ->title('Erro de Autenticação')
                ->body('O código de autorização do Google não foi fornecido.')
                ->send();

            return redirect()->route('filament.admin.pages.agenda');
        }

        $sucesso = $servico->autenticarCodigo($codigo);

        if ($sucesso) {
            Notification::make()
                ->success()
                ->title('Conectado com Sucesso!')
                ->body('O Google Calendar foi integrado com sucesso ao sistema.')
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Falha na Integração')
                ->body('Não foi possível obter os tokens de autenticação do Google.')
                ->send();
        }

        return redirect()->route('filament.admin.pages.agenda');
    }
}
