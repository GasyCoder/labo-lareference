<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('level', ['PARENT', 'CHILD', 'NORMAL']);
            $table->string('parent_code')->nullable();
            $table->string('abr')->nullable();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->decimal('prix', 10, 2)->nullable();
            $table->boolean('is_bold')->default(false);
            $table->unsignedBigInteger('examen_id');
            $table->unsignedBigInteger('analyse_type_id');
            $table->json('result_disponible')->nullable();
            $table->unsignedInteger('ordre')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['level', 'status']);
            $table->index('parent_code');
            $table->index('examen_id');
            $table->index('analyse_type_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('analyses');
    }
}
