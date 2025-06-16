<?php

namespace App\Models\Fiscal;

use App\Models\Cadastro\Empresa;
use App\Models\Cadastro\PerfilFiscal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NFeNFCe extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'fiscal_nf';
    protected $primaryKey = 'id';

    protected $fillable = [
        'empresa_id',
        'orcamento_id',
        'fiscal_perfil_id',
        'fiscal_natureza_operacao_id',
        'ide_cUF',
        'ide_cNF',
        'ide_natOp',
        'ide_indPag',
        'ide_mod',
        'ide_serie',
        'ide_nNF',
        'ide_lote',
        'ide_dhEmi',
        'ide_dhSaiEnt',
        'ide_tpNF',
        'ide_idDest',
        'ide_cMunFG',
        'ide_tpImp',
        'ide_tpEmis',
        'ide_tpAmb',
        'ide_finNFe',
        'ide_indFinal',
        'ide_indPres',
        'ide_procEmi',
        'ide_verProc',
        'emit_cnpj_cpf',
        'emit_xNome',
        'emit_xFant',
        'emit_xLgr',
        'emit_nro',
        'emit_xCpl',
        'emit_xBairro',
        'emit_cMun',
        'emit_xMun',
        'emit_UF',
        'emit_CEP',
        'emit_cPais',
        'emit_xPais',
        'emit_fone',
        'emit_IE',
        'emit_IEST',
        'emi_IM',
        'emi_CNAE',
        'emi_CRT',
        'dest_cpf_cnpj',
        'dest_xNome',
        'dest_xLgr',
        'dest_nro',
        'dest_xCpl',
        'dest_xBairro',
        'dest_cMun',
        'dest_xMun',
        'dest_UF',
        'dest_CEP',
        'dest_cPais',
        'dest_xPais',
        'dest_fone',
        'dest_indIEDest',
        'dest_IE',
        'dest_ISUF',
        'dest_IM',
        'dest_email',
        'autXML_cpfcnpj',
        'cStat',
        'nRec',
        'prot_cStat',
        'prot_nProt',
        'path_nf',
        'path_inutilizacao',
        'chave',
        'is_ref',
        'chave_ref',
        'consumidor_nome',
        'consumidor_cpf',
        'total_frete',
        'total_seguro',
        'total_outro',
        'total_desconto',
        'modalidade_frete',
        'porc_pis',
        'porc_cofins',
        'obs_orcamento',
        'is_transmitido',
        'is_autorizado',
        'is_cancelado',
        'is_validado',
        'is_email_enviado',
        'obs_fiscal',
        'user_idCreated',
        'user_idUpdated',
        'user_cce'
    ];

    public function empresa() {
        return $this->setConnection('mysql')
            ->hasOne(Empresa::class, 'id', 'empresa_id');
    }

    public function perfil_fiscal() {
        return $this->hasOne(PerfilFiscal::class, 'id', 'fiscal_perfil_id');
    }

}
