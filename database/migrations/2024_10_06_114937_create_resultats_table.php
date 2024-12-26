<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->foreignId('analyse_id')->constrained('analyses')->onDelete('cascade');
            $table->text('resultats')->nullable(); // Absense ou Negatif ou Rare ou NUll
            $table->text('valeur')->nullable(); // 1 ou null ou 5 ou
            $table->enum('interpretation', ['NORMAL', 'PATHOLOGIQUE'])->nullable();
            $table->text('conclusion')->nullable();
            $table->enum('status', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'VALIDE', 'ARCHIVE'])->default('EN_ATTENTE');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete(NULL);
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};
