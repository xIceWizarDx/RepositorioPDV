<?php

namespace App\Models\Venda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoParcela extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'orcamentos_parcelas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'orcamento_id',
        'sequencial',
        'valor',
        'vencimento',
        'forma_pagamento_id',
        'bandeira_cartao_id',
        'cAut',
        'avista'
    ];

}
