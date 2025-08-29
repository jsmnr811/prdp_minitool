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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number');
            $table->string('rank')->nullable();
            $table->string('variety');
            $table->string('green_grade')->nullable();
            $table->string('origin')->nullable();
            $table->string('process')->nullable();
            $table->integer('elevation')->nullable(); 
            $table->float('cup_score')->nullable();
            $table->text('cup_profile')->nullable();
            $table->integer('status')->default(1); // 1 = active, 0 = inactive

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
