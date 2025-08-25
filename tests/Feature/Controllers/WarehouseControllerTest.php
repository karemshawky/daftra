<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Filters\WarehouseStockFilter;

class WarehouseControllerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_fetch_warehouses(): void
    {
        // Make a request to fetch the list of warehouses
        $response = $this->actingAs($this->user)->getJson('/api/warehouses');

        // Assert the response is successful and contains the expected structure
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'location',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_fetch_single_warehouse(): void
    {
        // Create a warehouse for testing
        $warehouse = Warehouse::factory()->create();

        // Make a request to fetch the warehouse by ID
        $response = $this->actingAs($this->user)->getJson("/api/warehouses/{$warehouse->id}");

        // Assert the response is successful and contains the expected data
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'location',
                ],
            ]);
    }

    public function test_fetch_warehouse_inventory(): void
    {
        // Create a warehouse and associated stock for testing
        $warehouse = Warehouse::factory()->create();
        Stock::factory()->count(5)->create(['warehouse_id' => $warehouse->id]);

        // Make a request to fetch the inventory of the warehouse
        $response = $this->actingAs($this->user)->getJson("/api/warehouses/{$warehouse->id}/inventory");

        // Assert cache is execute
        $this->assertTrue(cache()->has("warehouse_{$warehouse->id}_inventory_" . md5(serialize(new WarehouseStockFilter()))));

        // Assert the response is successful and contains the expected structure
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'warehouse_id',
                        'item' => [
                            'id',
                            'name',
                            'sku',
                        ],
                        'quantity',
                    ],
                ],
                'links',
                'meta',
            ]);
    }
}
