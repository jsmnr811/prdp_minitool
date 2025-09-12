<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use App\Models\Province;

class ProvincesBoundariesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medresPath = public_path('medres');
        $files = File::files($medresPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $jsonContent = File::get($file->getPathname());
                $data = json_decode($jsonContent, true);

                if ($data && isset($data['features'])) {
                    foreach ($data['features'] as $feature) {
                        $properties = $feature['properties'];
                        $geometry = $feature['geometry'];

                        $provinceName = $properties['adm2_en'];

                        $province = Province::where('name', $provinceName)->first();

                        if ($province) {
                            // Calculate centroid from geometry
                            $centroid = $this->calculateCentroid($geometry);

                            $province->update([
                                'boundary_geojson' => json_encode($geometry),
                                // 'latitude' => $centroid['lat'],
                                // 'longitude' => $centroid['lng']
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Calculate the centroid of a GeoJSON geometry
     */
    private function calculateCentroid($geometry)
    {
        if ($geometry['type'] === 'Polygon') {
            return $this->calculatePolygonCentroid($geometry['coordinates'][0]);
        } elseif ($geometry['type'] === 'MultiPolygon') {
            // For MultiPolygon, calculate centroid of the largest polygon
            $largestPolygon = $geometry['coordinates'][0];
            $maxArea = 0;

            foreach ($geometry['coordinates'] as $polygon) {
                $area = $this->calculatePolygonArea($polygon[0]);
                if ($area > $maxArea) {
                    $maxArea = $area;
                    $largestPolygon = $polygon;
                }
            }

            return $this->calculatePolygonCentroid($largestPolygon[0]);
        }

        // Default fallback coordinates (center of Philippines)
        return ['lat' => 12.8797, 'lng' => 121.7740];
    }

    /**
     * Calculate centroid of a polygon using average of coordinates
     */
    private function calculatePolygonCentroid($coordinates)
    {
        $latSum = 0;
        $lngSum = 0;
        $count = 0;

        foreach ($coordinates as $coord) {
            // GeoJSON format is [longitude, latitude]
            $lngSum += $coord[0];
            $latSum += $coord[1];
            $count++;
        }

        return [
            'lat' => $latSum / $count,
            'lng' => $lngSum / $count
        ];
    }

    /**
     * Calculate approximate area of a polygon (for finding largest polygon in MultiPolygon)
     */
    private function calculatePolygonArea($coordinates)
    {
        $area = 0;
        $n = count($coordinates);

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $area += $coordinates[$i][0] * $coordinates[$j][1];
            $area -= $coordinates[$j][0] * $coordinates[$i][1];
        }

        return abs($area) / 2;
    }
}
