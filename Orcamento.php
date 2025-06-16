<?php

namespace App\Models\Venda;

use App\Models\Cadastro\Empresa;
use App\Models\Cadastro\Pessoa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'orcamentos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'empresa_id',
        'cliente_id',
        'comprador',
        'vendedor_id',
        'status',
        'total_bruto',
        'desconto_valor',
        'desconto_porc',
        'total_liquido',
        'valor_recebido',
        'valor_troco',
        'observacao',
        'tipo_movimento_id',
        'user_idFaturado',
        'user_idCancelado',
        'user_idEstornado',
        'user_idXMLGerado',
        'dh_faturado',
        'dh_cancelado',
        'dh_estornado',
        'dh_xml_gerado',
        'is_xml_gerado',
        'user_cce',
        'modo_pdv'
    ];

    public function cliente()
    {
        return $this->hasOne(Pessoa::class, 'id', 'cliente_id');
    }

    public function vendedor()
    {
        return $this->hasOne(Pessoa::class, 'id', 'vendedor_id');
    }

    public function finalizacao()
    {
        return $this->hasOne(OrcamentoFinalizacao::class, 'orcamento_id', 'id');
    }

    public function tipo_movimento(){
        return $this->hasOne(TipoMovimento::class, 'id', 'tipo_movimento_id');
    }

    public function empresa(){
        return $this->hasOne(Empresa::class, 'id', 'empresa_id');
    }


}
