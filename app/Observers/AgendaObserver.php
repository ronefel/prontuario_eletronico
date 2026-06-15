<?php

namespace App\Observers;

use App\Models\Agenda;
use App\Services\GoogleCalendarService;

class AgendaObserver
{
    /**
     * Sincroniza a criação da consulta com o Google Calendar.
     */
    public function created(Agenda $agenda): void
    {
        app(GoogleCalendarService::class)->criarEvento($agenda);
    }

    /**
     * Sincroniza atualizações da consulta com o Google Calendar.
     */
    public function updated(Agenda $agenda): void
    {
        // Verifica se houve alteração em campos relevantes antes de atualizar no Google
        $camposRelevantes = [
            'data_inicio', 
            'data_fim', 
            'status', 
            'observacoes', 
            'nome_paciente', 
            'whatsapp_paciente', 
            'paciente_id'
        ];

        if ($agenda->isDirty($camposRelevantes)) {
            app(GoogleCalendarService::class)->atualizarEvento($agenda);
        }
    }

    /**
     * Sincroniza a exclusão da consulta deletando o evento correspondente no Google Calendar.
     */
    public function deleted(Agenda $agenda): void
    {
        app(GoogleCalendarService::class)->deletarEvento($agenda);
    }
}
