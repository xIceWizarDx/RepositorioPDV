<?php

namespace App\Models\Cadastro\TabelaAuxiliar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cadastro\TabelaAuxiliar\FormaPagamento;

class ModoPagar extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'formas_pagamentos_cp';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'descricao_id',
        'pag_mod',
        'sequencia'
    ];

    public function descricao_id(){
        return $this->hasOne(FormaPagamento::class, 'id', 'descricao_id');
    }
}
