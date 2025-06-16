<?php

namespace App\Models\Venda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMovimento extends Model
{
    use HasFactory;
    protected $connection = 'db_client';
    protected $table = 'tipos_movimentos';
    protected $primaryKey = 'id';
}
