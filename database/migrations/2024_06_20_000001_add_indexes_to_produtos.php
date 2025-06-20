<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('db_client')->table('produtos', function (Blueprint $table) {
            $table->index('cEAN');
            $table->index('codigo');
            $table->index('descricao');
        });
    }

    public function down(): void
    {
        Schema::connection('db_client')->table('produtos', function (Blueprint $table) {
            $table->dropIndex(['cEAN']);
            $table->dropIndex(['codigo']);
            $table->dropIndex(['descricao']);
        });
    }
};
