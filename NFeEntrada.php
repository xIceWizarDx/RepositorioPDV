<?php

namespace App\Models\Fiscal;

use App\Models\Cadastro\Pessoa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NFeEntrada extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'fiscal_nf_entradas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'empresa_id',
        'ide_cUF',
        'ide_cNF',
        'ide_natOp',
        'ide_indPag',
        'ide_mod',
        'ide_serie',
        'ide_nNF',
        'ide_lote',
        'ide_tpNF',
        'ide_tpEmis',
        'ide_tpAmb',
        'ide_finNFe',
        'fornecedor_id',
        'chave',
        'transportadora_id'
    ];

    public function fornecedor() {
        return $this->hasOne(Pessoa::class, 'id', 'fornecedor_id');
    }

}
