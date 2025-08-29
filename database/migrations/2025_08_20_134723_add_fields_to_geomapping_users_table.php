<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geomapping_users', function (Blueprint $table) {
            $table->string('firstname')->nullable()->after('id');
            $table->string('middlename')->nullable()->after('firstname');
            $table->string('lastname')->nullable()->after('middlename');
            $table->string('ext_name')->nullable()->after('lastname');
            $table->enum('sex', ['Male', 'Female'])->nullable()->after('ext_name');

            $table->string('region_id')->nullable()->after('sex');
            $table->string('province_id')->nullable()->after('region_id');

            $table->string('institution')->nullable()->after('province_id');
            $table->string('office')->nullable()->after('institution');
            $table->string('designation')->nullable()->after('office');

            $table->string('email')->nullable()->unique()->after('designation');
            $table->string('contact_number')->nullable()->after('email');
            $table->string('food_restriction')->nullable()->after('contact_number');

            $table->string('image')->nullable()->after('food_restriction');

            $table->string('role')->nullable()->after('login_code');
            $table->string('group_number')->nullable()->after('role');
            $table->string('table_number')->nullable()->after('group_number');
        });
    }

    public function down(): void
    {
        Schema::table('geomapping_users', function (Blueprint $table) {
            $table->dropColumn([
                'firstname',
                'middlename',
                'lastname',
                'ext_name',
                'sex',
                'region_id',
                'province_id',
                'institution',
                'office',
                'designation',
                'email',
                'contact_number',
                'food_restriction',
                'image',
                'role',
                'group_number',
                'table_number',
            ]);
        });
    }
};

