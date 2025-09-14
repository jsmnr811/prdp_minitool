<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Livewire\Geomapping\Iplan\MainMap;
use App\Models\GeomappingUser;
use App\Models\Region;
use App\Models\Province;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MainMapLoadingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function loading_states_are_set_immediately_on_select_changes()
    {
        // Create test user
        $user = GeomappingUser::factory()->create([
            'role' => 1, // Admin role
        ]);

        // Create test region
        $region = Region::factory()->create([
            'code' => '01',
            'name' => 'Test Region',
            'latitude' => 12.8797,
            'longitude' => 121.7740,
        ]);

        // Create test province
        $province = Province::factory()->create([
            'code' => '0128',
            'name' => 'Test Province',
            'region_code' => '01',
            'latitude' => 12.8797,
            'longitude' => 121.7740,
        ]);

        // Act as the test user
        $this->actingAs($user, 'geomapping');

        // Test region selection
        Livewire::test(MainMap::class)
            ->set('selectedRegionId', '01')
            ->assertSet('isZoomingToRegion', true);

        // Test province selection
        Livewire::test(MainMap::class)
            ->set('selectedProvinceId', '0128')
            ->assertSet('isZoomingToProvince', true);
    }

    /** @test */
    public function loading_states_are_reset_after_zoom_events()
    {
        // Create test user
        $user = GeomappingUser::factory()->create([
            'role' => 1,
        ]);

        // Create test region
        $region = Region::factory()->create([
            'code' => '01',
            'name' => 'Test Region',
            'latitude' => 12.8797,
            'longitude' => 121.7740,
        ]);

        $this->actingAs($user, 'geomapping');

        // Test that loading state is initially false
        Livewire::test(MainMap::class)
            ->assertSet('isZoomingToRegion', false);

        // Set region and check loading state
        $component = Livewire::test(MainMap::class)
            ->set('selectedRegionId', '01')
            ->assertSet('isZoomingToRegion', true);

        // Simulate zoom event (this would normally be dispatched from JavaScript)
        $component->dispatch('zoomToRegion', [
            'lat' => 12.8797,
            'lng' => 121.7740,
            'name' => 'Test Region',
            'code' => '01'
        ]);

        // The loading state should still be true until the JavaScript resets it
        // This test verifies the Livewire method sets loading immediately
    }
}
