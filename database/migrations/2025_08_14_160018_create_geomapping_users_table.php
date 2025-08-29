<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('geomapping_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('login_code')->unique();
            $table->string('group')->nullable();
            $table->text('lat_long')->nullable();
            $table->timestamps();
        });

        $this->createProvinces();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geomapping_users');
    }


    private function createProvinces(): void
    {
        $provinces = [
            "Ilocos Norte",
            "Ilocos Sur",
            "La Union",
            "Pangasinan",
            "Batanes",
            "Cagayan",
            "Isabela",
            "Nueva Vizcaya",
            "Quirino",
            "Bataan",
            "Bulacan",
            "Nueva Ecija",
            "Pampanga",
            "Tarlac",
            "Zambales",
            "Aurora",
            "Batangas",
            "Cavite",
            "Laguna",
            "Quezon",
            "Rizal",
            "Albay",
            "Camarines Norte",
            "Camarines Sur",
            "Catanduanes",
            "Masbate",
            "Sorsogon",
            "Aklan",
            "Antique",
            "Capiz",
            "Iloilo",
            "Negros Occidental",
            "Guimaras",
            "Bohol",
            "Cebu",
            "Negros Oriental",
            "Siquijor",
            "Eastern Samar",
            "Leyte",
            "Northern Samar",
            "Samar",
            "Southern Leyte",
            "Biliran",
            "Zamboanga del Norte",
            "Zamboanga del Sur",
            "Zamboanga Sibugay",
            "Bukidnon",
            "Camiguin",
            "Lanao del Norte",
            "Misamis Occidental",
            "Misamis Oriental",
            "Davao del Norte",
            "Davao del Sur",
            "Davao Oriental",
            "Davao de Oro (formerly Compostela Valley)",
            "Davao Occidental",
            "Cotabato (North Cotabato)",
            "South Cotabato",
            "Sultan Kudarat",
            "Sarangani",
            "Abra",
            "Apayao",
            "Benguet",
            "Ifugao",
            "Kalinga",
            "Mountain Province",
            "Agusan del Norte",
            "Agusan del Sur",
            "Dinagat Islands",
            "Surigao del Norte",
            "Surigao del Sur",
            "Basilan",
            "Lanao del Sur",
            "Maguindanao",
            "Sulu",
            "Tawi-Tawi",
            "Marinduque",
            "Occidental Mindoro",
            "Oriental Mindoro",
            "Palawan",
            "Romblon",
            "First District (Manila)",
            "Second District (Mandaluyong, Marikina, Pasig, Quezon City, San Juan)",
            "Third District (Caloocan, Malabon, Navotas, Valenzuela)",
            "Fourth District (Las Piñas, Makati, Muntinlupa, Parañaque, Pasay, Pateros, Taguig)",
            "Cagayan de Oro",
            "Butuan",
        ];

        foreach ($provinces as $province) {
            DB::table('geomapping_users')->insert([
                'name' => $province,
                'login_code' => strtoupper(Str::random(8)),
                'group' => null,
                'lat_long' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
