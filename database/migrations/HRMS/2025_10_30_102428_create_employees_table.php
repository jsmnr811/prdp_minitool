<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql2')->create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no', 20)->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 10)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->string('address')->nullable();

            $table->unsignedBigInteger('component_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();

            $table->enum('employment_status', ['Active', 'Resigned', 'Terminated', 'Retired'])->default('Active');
            $table->date('date_hired')->nullable();
            $table->date('date_ended')->nullable();
            $table->timestamps();

            $table->foreign('component_id')->references('id')->on('components')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');

            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    public function down(): void
    {
        Schema::connection('mysql2')->dropIfExists('employees');
    }
};
