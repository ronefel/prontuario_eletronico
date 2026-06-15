<?php

namespace App\Models;

/**
 * @property int $id
 * @property string|null $client_id
 * @property string|null $client_secret
 * @property string|null $redirect_uri
 * @property string|null $calendario_id
 * @property string|null $token_acesso
 * @property string|null $token_atualizacao
 * @property \Illuminate\Support\Carbon|null $token_expira_em
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GoogleConfiguracao extends BaseModel
{
    protected $table = 'google_configuracoes';

    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'calendario_id',
        'token_acesso',
        'token_atualizacao',
        'token_expira_em',
    ];

    protected $casts = [
        'token_expira_em' => 'datetime',
    ];

    /**
     * Retorna a única configuração do sistema, ou cria uma em branco caso não exista.
     */
    public static function obterConfiguracao(): self
    {
        return self::firstOrCreate([], [
            'calendario_id' => 'primary',
        ]);
    }

    /**
     * Verifica se o sistema possui conexão autenticada (refresh token ativo).
     */
    public function estaConectado(): bool
    {
        return ! empty($this->token_atualizacao);
    }
}
