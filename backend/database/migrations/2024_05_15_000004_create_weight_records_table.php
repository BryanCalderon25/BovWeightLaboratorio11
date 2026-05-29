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
        Schema::create('weight_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->decimal('peso_estimado', 8, 2);
            $table->date('fecha_pesaje');
            $table->foreignId('animal_image_id')->nullable()->constrained()->onDelete('set null');
            $table->json('datos_modelo')->nullable(); // Para guardar la respuesta del modelo (bbox, confidence, etc)
            $table->string('estado_sincronizacion')->default('sincronizado'); // offline, sincronizado
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_records');
    }
};
