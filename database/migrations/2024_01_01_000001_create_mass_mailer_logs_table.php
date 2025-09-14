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
        Schema::create(config('mass-mailer.logging.table', 'mass_mailer_logs'), function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->nullable()->index();
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('body')->nullable();
            $table->json('variables')->nullable(); // Store the variables used for personalization
            $table->json('attachments')->nullable(); // Store attachment info
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('recipient_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('mass-mailer.logging.table', 'mass_mailer_logs'));
    }
};
