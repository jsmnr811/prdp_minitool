<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\Http\Request;

class GeomappingAnalyticsDashboardController extends Controller
{
    public function index(Request $request)
    {
        // ---------------- FILTERED PROVINCES ----------------
        $query = Province::with([
            'region',
            'pcipMatrices.commodity',
            'pcipMatrices.intervention',
            'geoCommodities.interventions',
            'geoCommodities.commodity'
        ])
            ->where('region_code', '!=', 16)
            ->whereHas('geoCommodities');

        // Region filter
        if ($request->filled('region_select') && strtolower($request->region_select) !== 'all') {
            $query->where('region_code', $request->region_select);
        }

        // Province filter
        if ($request->filled('province_select') && strtolower($request->province_select) !== 'all') {
            $query->where('code', $request->province_select);
        }

        // Commodity filter
        if ($request->filled('commodity_select') && strtolower($request->commodity_select) !== 'all') {
            $query->whereHas('geoCommodities', function ($q) use ($request) {
                $q->where('commodity_id', $request->commodity_select);
            });
        }

        // Intervention filter
        if ($request->filled('intervention_select') && strtolower($request->intervention_select) !== 'all') {
            $query->whereHas('geoCommodities.interventions', function ($q) use ($request) {
                $q->where('intervention_id', $request->intervention_select);
            });
        }

        $provinces = $query->get();

        // ---------------- PROCESS MATRICES ----------------
        $provinces->each(function ($province) use ($request) {
            $finalMatrices = collect();

            foreach ($province->geoCommodities as $geoCommodity) {
                // Commodity filter at loop level
                if ($request->filled('commodity_select') && strtolower($request->commodity_select) !== 'all') {
                    if ($geoCommodity->commodity_id != $request->commodity_select) {
                        continue;
                    }
                }

                foreach ($geoCommodity->interventions as $geoIntervention) {
                    // Intervention filter at loop level
                    if ($request->filled('intervention_select') && strtolower($request->intervention_select) !== 'all') {
                        if ($geoIntervention->intervention_id != $request->intervention_select) {
                            continue;
                        }
                    }

                    // Get all PCIP matrices for this commodity & intervention
                    $matches = $province->pcipMatrices
                        ->where('commodity_id', $geoCommodity->commodity_id)
                        ->where('intervention_id', $geoIntervention->intervention_id);

                    // Sum the totals
                    $totalFunded = $matches->sum('funded');
                    $totalRequirement = $matches->sum('funding_requirement');
                    $totalUnfunded = $totalRequirement - $totalFunded;

                    $finalMatrices->push((object)[
                        'commodity' => $geoCommodity->commodity,
                        'intervention' => $geoIntervention->intervention,
                        'commodity_id' => $geoCommodity->commodity_id,
                        'intervention_id' => $geoIntervention->intervention_id,
                        'funding_requirement' => $totalRequirement,
                        'funded' => $totalFunded,
                        'unfunded' => $totalUnfunded,
                    ]);
                }
            }

            // Apply funded/unfunded filter
            if ($request->filled('funded_status')) {
                $statuses = $request->funded_status;
                $finalMatrices = $finalMatrices->filter(function ($matrix) use ($statuses) {
                    $isFunded = $matrix->funded > 0;
                    $isUnfunded = $matrix->unfunded > 0;

                    return ($isFunded && in_array('funded', $statuses)) ||
                        ($isUnfunded && in_array('unfunded', $statuses));
                });
            }

            $province->setRelation('pcipMatrices', $finalMatrices->values());
        });

        // ---------------- CALCULATE TOTALS ----------------
        $totalFundRequirement = 0;
        $totalFunded = 0;
        $totalUnfunded = 0;

        $provinces->each(function ($province) use (&$totalFundRequirement, &$totalFunded, &$totalUnfunded) {
            foreach ($province->pcipMatrices as $matrix) {
                $totalFundRequirement += $matrix->funding_requirement;
                $totalFunded += $matrix->funded;
                $totalUnfunded += $matrix->unfunded;
            }
        });

        // ---------------- PREPARE MAP DATA ----------------
        $geoCommoditiesFunded = [];
        $geoCommoditiesUnfunded = [];

        foreach ($provinces as $province) {
            foreach ($province->pcipMatrices as $matrix) {
                foreach ($province->geoCommodities as $geoCommodity) {
                    if ($geoCommodity->commodity_id != $matrix->commodity_id) continue;

                    foreach ($geoCommodity->interventions as $geoIntervention) {
                        if ($geoIntervention->intervention_id != $matrix->intervention_id) continue;

                        $item = [
                            'province' => $province->name,
                            'region' => $province->region->abbr ?? '',
                            'commodity' => $matrix->commodity->name ?? $geoCommodity->commodity->name ?? '',
                            'intervention' => $matrix->intervention->name
                                ?? $geoIntervention->intervention->name
                                ?? '',
                            'latitude' => $province->latitude,
                            'longitude' => $province->longitude,
                            'icon' => $geoCommodity->commodity->icon,
                            'funded' => $matrix->funded,
                            'unfunded' => $matrix->unfunded,
                        ];

                        if ($matrix->funded > 0) {
                            $geoCommoditiesFunded[] = $item;
                        }

                        if ($matrix->unfunded > 0) {
                            $geoCommoditiesUnfunded[] = $item;
                        }
                    }
                }
            }
        }

        return view('geomapping.iplan.analytics-dashboard', compact(
            'provinces',
            'totalFundRequirement',
            'totalFunded',
            'totalUnfunded',
            'geoCommoditiesFunded',
            'geoCommoditiesUnfunded'
        ));
    }
}
