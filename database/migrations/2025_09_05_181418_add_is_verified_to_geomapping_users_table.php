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
            $table->boolean('is_verified')->default(false)->after('is_blocked');
            $table->string('room_assignment')->nullable()->before('table_number');
            $table->boolean('is_livein')->nullable()->after('room_assignment')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geomapping_users', function (Blueprint $table) {
            $table->dropColumn('is_verified');
            $table->dropColumn('room_assignment');
            $table->dropColumn('is_livein');
        });
    }
};
