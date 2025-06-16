<?php

namespace App\Models\Cadastro\TabelaAuxiliar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'formas_pagamentos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'descricao',
        'sequencial',
        'idNFCe',
        'is_active',
        'livre_pag'
    ];


    protected $casts = [
        'id' => 'string'
    ];
}
