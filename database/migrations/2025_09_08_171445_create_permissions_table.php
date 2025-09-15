<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique(); // ej: personas.view
            $table->text('descripcion')->nullable();
            $table->string('grupo', 100)->nullable(); // ej: personas
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('permissions');
    }
};