<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('email');
            $table->integer('status')->default(1); // 1 = active, 0 = inactive
            $table->string('otp')->nullable()->after('status');
            $table->string('otp_sent_at')->nullable()->after('otp');
            $table->string('otp_expires_at')->nullable()->after('otp_sent_at');
            $table->softDeletes(); // Soft deletes should come last
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['contact_number', 'status', 'otp', 'otp_sent_at', 'otp_expires_at']);
        });
    }
};
