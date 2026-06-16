<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\AgendaConfiguracao;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    /**
     * Gera a URL de autorização OAuth 2.0 para o Google Calendar.
     */
    public function obterUrlAutorizacao(): string
    {
        $configuracao = AgendaConfiguracao::obterConfiguracao();

        if (empty($configuracao->client_id) || empty($configuracao->redirect_uri)) {
            return '';
        }

        $parametros = [
            'client_id' => $configuracao->client_id,
            'redirect_uri' => $configuracao->redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($parametros);
    }

    /**
     * Troca o código recebido no redirecionamento por tokens do Google.
     */
    public function autenticarCodigo(string $codigo): bool
    {
        $configuracao = AgendaConfiguracao::obterConfiguracao();

        if (empty($configuracao->client_id) || empty($configuracao->client_secret) || empty($configuracao->redirect_uri)) {
            Log::error('Erro ao autenticar código: Credenciais do Google incompletas.');
            return false;
        }

        $resposta = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $codigo,
            'client_id' => $configuracao->client_id,
            'client_secret' => $configuracao->client_secret,
            'redirect_uri' => $configuracao->redirect_uri,
            'grant_type' => 'authorization_code',
        ]);

        if ($resposta->failed()) {
            Log::error('Falha ao obter token do Google:', $resposta->json() ?: ['body' => $resposta->body()]);
            return false;
        }

        $dados = $resposta->json();

        $configuracao->update([
            'token_acesso' => $dados['access_token'] ?? null,
            'token_atualizacao' => $dados['refresh_token'] ?? $configuracao->token_atualizacao,
            'token_expira_em' => isset($dados['expires_in']) ? now()->addSeconds((int) $dados['expires_in']) : null,
        ]);

        return true;
    }

    /**
     * Garante que o token de acesso esteja ativo, renovando-o se necessário.
     */
    protected function garantirTokenValido(AgendaConfiguracao $configuracao): bool
    {
        if (empty($configuracao->token_acesso) || empty($configuracao->token_expira_em) || $configuracao->token_expira_em->isPast()) {
            if (empty($configuracao->token_atualizacao)) {
                Log::error('Erro de renovação do Google Calendar: Refresh token ausente.');
                return false;
            }

            $resposta = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $configuracao->client_id,
                'client_secret' => $configuracao->client_secret,
                'refresh_token' => $configuracao->token_atualizacao,
                'grant_type' => 'refresh_token',
            ]);

            if ($resposta->failed()) {
                Log::error('Falha ao renovar token do Google:', $resposta->json() ?: ['body' => $resposta->body()]);
                return false;
            }

            $dados = $resposta->json();

            $configuracao->update([
                'token_acesso' => $dados['access_token'] ?? null,
                'token_expira_em' => isset($dados['expires_in']) ? now()->addSeconds((int) $dados['expires_in']) : null,
            ]);
        }

        return true;
    }

    /**
     * Cria um evento no Google Calendar para o agendamento.
     */
    public function criarEvento(Agenda $agenda): bool
    {
        $configuracao = AgendaConfiguracao::obterConfiguracao();

        if (!$configuracao->estaConectado()) {
            return false;
        }

        if (!$this->garantirTokenValido($configuracao)) {
            return false;
        }

        $calendarioId = $configuracao->calendario_id ?: 'primary';

        $corpo = [
            'summary' => 'Consulta: ' . $agenda->obter_nome_paciente,
            'description' => 'WhatsApp: ' . $agenda->obter_whatsapp_paciente . ($agenda->observacoes ? "\nObservações: " . $agenda->observacoes : ''),
            'start' => [
                'dateTime' => $agenda->data_inicio->toIso8601String(),
            ],
            'end' => [
                'dateTime' => $agenda->data_fim->toIso8601String(),
            ],
        ];

        $resposta = Http::withToken($configuracao->token_acesso)
            ->post("https://www.googleapis.com/calendar/v3/calendars/{$calendarioId}/events", $corpo);

        if ($resposta->failed()) {
            Log::error('Erro ao criar evento no Google Calendar:', $resposta->json() ?: ['body' => $resposta->body()]);
            return false;
        }

        $evento = $resposta->json();
        $googleEventoId = $evento['id'] ?? null;

        if ($googleEventoId) {
            $agenda->updateQuietly([
                'google_evento_id' => $googleEventoId,
            ]);
        }

        return true;
    }

    /**
     * Atualiza o evento correspondente no Google Calendar.
     */
    public function atualizarEvento(Agenda $agenda): bool
    {
        $configuracao = AgendaConfiguracao::obterConfiguracao();

        if (!$configuracao->estaConectado()) {
            return false;
        }

        if (empty($agenda->google_evento_id)) {
            return $this->criarEvento($agenda);
        }

        if (!$this->garantirTokenValido($configuracao)) {
            return false;
        }

        $calendarioId = $configuracao->calendario_id ?: 'primary';

        $corpo = [
            'summary' => 'Consulta: ' . $agenda->obter_nome_paciente,
            'description' => 'WhatsApp: ' . $agenda->obter_whatsapp_paciente . ($agenda->observacoes ? "\nObservações: " . $agenda->observacoes : ''),
            'start' => [
                'dateTime' => $agenda->data_inicio->toIso8601String(),
            ],
            'end' => [
                'dateTime' => $agenda->data_fim->toIso8601String(),
            ],
        ];

        $resposta = Http::withToken($configuracao->token_acesso)
            ->put("https://www.googleapis.com/calendar/v3/calendars/{$calendarioId}/events/{$agenda->google_evento_id}", $corpo);

        if ($resposta->status() === 404 || $resposta->status() === 410) {
            return $this->criarEvento($agenda);
        }

        if ($resposta->failed()) {
            Log::error('Erro ao atualizar evento no Google Calendar:', $resposta->json() ?: ['body' => $resposta->body()]);
            return false;
        }

        return true;
    }

    /**
     * Remove o evento correspondente no Google Calendar.
     */
    public function deletarEvento(Agenda $agenda): bool
    {
        $configuracao = AgendaConfiguracao::obterConfiguracao();

        if (!$configuracao->estaConectado() || empty($agenda->google_evento_id)) {
            return false;
        }

        if (!$this->garantirTokenValido($configuracao)) {
            return false;
        }

        $calendarioId = $configuracao->calendario_id ?: 'primary';

        $resposta = Http::withToken($configuracao->token_acesso)
            ->delete("https://www.googleapis.com/calendar/v3/calendars/{$calendarioId}/events/{$agenda->google_evento_id}");

        if ($resposta->status() === 404 || $resposta->status() === 410) {
            return true;
        }

        if ($resposta->failed()) {
            Log::error('Erro ao remover evento no Google Calendar:', $resposta->json() ?: ['body' => $resposta->body()]);
            return false;
        }

        return true;
    }
}
