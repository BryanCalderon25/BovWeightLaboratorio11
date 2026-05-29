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
        // Crear tabla de invitaciones de finca
        Schema::create('farm_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->onDelete('cascade');
            $table->string('role'); // 'veterinario' o 'comprador'
            $table->string('token', 80)->unique();
            $table->dateTime('expires_at');
            $table->integer('max_uses')->nullable();
            $table->integer('uses_count')->default(0);
            $table->timestamps();
        });

        // Modificar tabla users para accesos de invitado
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('invited_farm_id')->nullable()->constrained('farms')->onDelete('set null');
            $table->dateTime('guest_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['invited_farm_id']);
            $table->dropColumn(['invited_farm_id', 'guest_expires_at']);
        });

        Schema::dropIfExists('farm_invitations');
    }
};
