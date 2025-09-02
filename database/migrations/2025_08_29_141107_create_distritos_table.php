<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ubigeo_com', 6)->unique();
            $table->string('ubigeo_pro', 4); // varchar(4)
            // Clave foránea
            $table->foreign('ubigeo_pro')
                ->references('ubigeo_pro')
                 ->on('provincias')
                ->onDelete('cascade');

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distritos');
    }
};
