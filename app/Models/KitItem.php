<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kit_id
 * @property int $produto_id
 * @property int $quantidade
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Kit $kit
 * @property Produto|null $produto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem whereKitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem whereProdutoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem whereQuantidade($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KitItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KitItem extends Model
{
    use HasFactory;

    protected $table = 'kit_produto';

    protected $fillable = [
        'kit_id',
        'produto_id',
        'quantidade',
    ];

    public function kit()
    {
        return $this->belongsTo(Kit::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
