<?php

namespace App\Models\Venda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoFinalizacao extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'orcamentos_finalizacoes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'orcamento_id',
        'condicao_pagamento',
        'forma_pagamento',
        'pagamento_personalizado',  // ← adicionado aqui
        'dia_vencimento',
        'valor_entrada',
        'centavo_ultima_parcela'
    ];
}
