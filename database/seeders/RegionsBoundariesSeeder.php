<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Region;

class RegionsBoundariesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medresPath = public_path('medres_region');
        $files = File::files($medresPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $jsonContent = File::get($file->getPathname());
                $data = json_decode($jsonContent, true);

                if ($data && isset($data['features'])) {
                    foreach ($data['features'] as $feature) {
                        $properties = $feature['properties'];
                        $geometry = $feature['geometry'];

                        $provinceName = $properties['adm1_en'];
                        $finalRegionName = $provinceName;

                        $province = Region::where('name', $finalRegionName)->first();

                        if ($province) {
                            // Calculate centroid from geometry

                            $province->update([
                                'boundary_geojson' => json_encode($geometry),
                            ]);
                        }
                    }
                }
            }
        }
    }
}
