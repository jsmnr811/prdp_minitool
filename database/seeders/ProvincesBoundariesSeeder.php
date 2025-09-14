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

        $maguindanaoDelSurGeometry = null;
        $maguindanaoDelNorteGeometry = null;

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $jsonContent = File::get($file->getPathname());
                $data = json_decode($jsonContent, true);

                if ($data && isset($data['features'])) {
                    foreach ($data['features'] as $feature) {
                        $properties = $feature['properties'];
                        $geometry = $feature['geometry'];

                        // Check for Maguindanao del Sur
                        if (isset($properties['adm2_en']) && $properties['adm2_en'] === 'Maguindanao del Sur') {
                            $maguindanaoDelSurGeometry = $geometry;
                            continue;
                        }

                        // Check for Maguindanao del Norte
                        if (isset($properties['adm2_psgc']) && $properties['adm2_psgc'] == '1909900000') {
                            $maguindanaoDelNorteGeometry = $geometry;
                            continue;
                        }

                        $provinceName = $properties['adm2_en'];
                        $finalProvinceName = $provinceName;

                        if ($finalProvinceName === 'Cagayan') {
                            $finalProvinceName = 'Cagayan de Oro';
                        }

                        if ($finalProvinceName === 'Cotabato') {
                            $finalProvinceName = 'North Cotabato';
                        }

                        $province = Province::where('name', $finalProvinceName)->first();

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

        // Process combined Maguindanao geometry
        if ($maguindanaoDelSurGeometry || $maguindanaoDelNorteGeometry) {
            $combinedPolygons = [];

            if ($maguindanaoDelSurGeometry) {
                if ($maguindanaoDelSurGeometry['type'] === 'Polygon') {
                    $combinedPolygons[] = $maguindanaoDelSurGeometry['coordinates'];
                } elseif ($maguindanaoDelSurGeometry['type'] === 'MultiPolygon') {
                    $combinedPolygons = array_merge($combinedPolygons, $maguindanaoDelSurGeometry['coordinates']);
                }
            }

            if ($maguindanaoDelNorteGeometry) {
                if ($maguindanaoDelNorteGeometry['type'] === 'Polygon') {
                    $combinedPolygons[] = $maguindanaoDelNorteGeometry['coordinates'];
                } elseif ($maguindanaoDelNorteGeometry['type'] === 'MultiPolygon') {
                    $combinedPolygons = array_merge($combinedPolygons, $maguindanaoDelNorteGeometry['coordinates']);
                }
            }

            $combinedGeometry = [
                'type' => 'MultiPolygon',
                'coordinates' => $combinedPolygons
            ];

            $province = Province::where('name', 'Maguindanao')->first();

            if ($province) {
                $centroid = $this->calculateCentroid($combinedGeometry);

                $province->update([
                    'boundary_geojson' => json_encode($combinedGeometry),
                    // 'latitude' => $centroid['lat'],
                    // 'longitude' => $centroid['lng']
                ]);
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
