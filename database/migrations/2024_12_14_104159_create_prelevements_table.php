<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Création de la table principale des prélèvements
        Schema::create('prelevements', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->comment('Nom du prélèvement');
            $table->string('description')->nullable()->comment('Description du prélèvement');
            $table->decimal('prix', 10, 2)->comment('Prix du prélèvement');
            $table->integer('quantite')->default(1)->comment('Quantité disponible');
            $table->boolean('is_active')->default(true)->comment('Indique si le prélèvement est actif');
            $table->timestamps();
            $table->softDeletes(); // Pour permettre la suppression logique
        });

        // Création de la table pivot pour la relation entre prélèvements et prescriptions
        Schema::create('prelevement_prescription', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Clé étrangère vers prescriptions');

            $table->foreignId('prelevement_id')
                ->constrained()
                ->onDelete('cascade')
                ->comment('Clé étrangère vers prélèvements');

            $table->decimal('prix_unitaire', 10, 2)->comment('Prix unitaire du prélèvement dans la prescription');
            $table->integer('quantite')->default(1)->comment('Quantité commandée dans la prescription');
            $table->enum('is_payer', ['PAYE', 'NON_PAYE'])->default('NON_PAYE')->comment('Statut de paiement');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prelevement_prescription');
        Schema::dropIfExists('prelevements');
    }
};
