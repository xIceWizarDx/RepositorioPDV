<?php

namespace App\Models\Venda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoItens extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'orcamentos_itens';
    protected $primaryKey = 'id';

    protected $fillable = [
        'orcamento_id',
        'produto_id',
        'sequencial',
        'quantidade',
        'preco',
        'desconto',
        'subtotal'
    ];
}
