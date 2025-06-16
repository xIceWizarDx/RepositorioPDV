<?php

namespace App\Models\Cadastro;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdutoPreco extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'produtos_precos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'empresa_id',
        'produto_id',
        'preco_vista',
        'preco_prazo'
    ];
}
