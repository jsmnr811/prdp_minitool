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
        Schema::table('geomapping_users', function (Blueprint $table) {
            if (Schema::hasColumn('geomapping_users', 'group')) {
                $table->dropColumn('group');
            }

            $table->boolean('is_iplan')->default(false)->after('role');

            if (Schema::hasColumn('geomapping_users', 'lat_long')) {
                $table->dropColumn('lat_long');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geomapping_users', function (Blueprint $table) {
            $table->string('group')->nullable();

            $table->dropColumn('is_iplan');

            $table->string('lat_long')->nullable();
        });
    }
};
