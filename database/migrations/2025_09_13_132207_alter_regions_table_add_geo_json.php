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
        Schema::table('regions', function (Blueprint $table) {
            $table->json('boundary_geojson')->after('abbr')->nullable();
            $table->double('longitude')->after('boundary_geojson')->nullable();
            $table->double('latitude')->after('longitude')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('boundary_geojson');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
};
