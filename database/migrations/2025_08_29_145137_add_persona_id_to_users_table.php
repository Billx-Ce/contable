<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable por si ya existen users; luego lo puedes rellenar y volver NOT NULL
            $table->foreignId('persona_id')
                  ->nullable()
                  ->unique()
                  ->constrained('personas')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('persona_id'); // elimina FK + columna
        });
    }
};

