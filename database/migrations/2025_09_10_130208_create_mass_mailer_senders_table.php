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
        Schema::create('mass_mailer_senders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('host', 255);
            $table->integer('port');
            $table->string('username', 255);
            $table->text('password');
            $table->string('encryption', 10)->default('tls');
            $table->timestamps();

            $table->index('user_id');
            $table->index('email');

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mass_mailer_senders');
    }
};
