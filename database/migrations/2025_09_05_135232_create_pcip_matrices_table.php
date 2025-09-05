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
        Schema::create('pcip_matrices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('region_id')->nullable();
            $table->string('region_name')->nullable();
            $table->bigInteger('province_id')->nullable();
            $table->string('province_name')->nullable();
            $table->bigInteger('commodity_id')->nullable();
            $table->string('commodity_name')->nullable();
            $table->bigInteger('intervention_id')->nullable();
            $table->string('intervention_name')->nullable();
            $table->double('funding_requirement'); // Adjusted cost of intervention
            $table->double('funded'); // Total Intervention Cost
            $table->double('unfunded'); // Unfunded Cost
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcip_matrices');
    }
};
