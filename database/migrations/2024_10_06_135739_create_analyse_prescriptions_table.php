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
        // table pivot
        Schema::create('analyse_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->foreignId('analyse_id')->constrained('analyses')->onDelete('cascade');
            $table->decimal('prix', 10, 2)->default(0);
            $table->enum('is_payer', ['PAYE', 'NON_PAYE'])->default('NON_PAYE');
            $table->enum('status', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'VALIDE', 'A_REFAIRE', 'ARCHIVE'])->default('EN_ATTENTE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyse_prescriptions');
    }
};
