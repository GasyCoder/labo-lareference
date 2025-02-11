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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secretaire_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('prescripteur_id')->nullable()->constrained('prescripteurs')->onDelete('set null');
            $table->enum('patient_type', ['HOSPITALISE', 'EXTERNE', 'URGENCE-NUIT', 'URGENCE-JOUR'])->default('EXTERNE');
            $table->integer('age');
            $table->enum('unite_age', ['Ans', 'Mois', 'Jours'])->default('Ans');
            $table->decimal('poids', 5, 2)->nullable(); // en kg
            $table->text('renseignement_clinique')->nullable();
            $table->decimal('remise', 10, 2)->default(0);
            $table->boolean('is_archive')->default(false);
            $table->enum('status', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'VALIDE', 'A_REFAIRE', 'ARCHIVE'])->default('EN_ATTENTE');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
