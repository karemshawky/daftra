<?php

namespace Tests\Feature\Controllers\Warehouse;

use Tests\TestCase;
use App\Models\User;
use App\Models\Stock;
use App\Models\Warehouse;

class WarehouseControllerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test fetching the list of warehouses.
     *
     * @return void
     */
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

    /**
     * Test fetching a single warehouse by ID.
     *
     * @return void
     */
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

    /**
     * Test fetching the inventory of a specific warehouse.
     *
     * @return void
     */
    public function test_fetch_warehouse_inventory(): void
    {
        // Create a warehouse and associated stock for testing
        $warehouse = Warehouse::factory()->create();
        Stock::factory()->count(5)->create(['warehouse_id' => $warehouse->id]);

        // Make a request to fetch the inventory of the warehouse
        $response = $this->actingAs($this->user)->getJson("/api/warehouses/{$warehouse->id}/inventory");

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
