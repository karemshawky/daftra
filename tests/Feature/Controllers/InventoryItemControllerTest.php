<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\InventoryItem;

class InventoryItemControllerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_fetch_inventory_items(): void
    {
        // Make a request to fetch the list of inventory items
        $response = $this->actingAs($this->user)->getJson('/api/inventory-items');

        // Assert the response is successful and contains the expected structure
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'description',
                        'price',
                        'is_active',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_fetch_single_inventory_item(): void
    {
        // Create an inventory item for testing
        $inventoryItem = InventoryItem::factory()->create();

        // Make a request to fetch the inventory item by ID
        $response = $this->actingAs($this->user)->getJson("/api/inventory-items/{$inventoryItem->id}");

        // Assert the response is successful and contains the expected data
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'sku',
                    'description',
                    'price',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_failed_create_inventory_item_with_validation_errors(): void
    {
        $unauthorizedResponse = $this->postJson('/api/inventory-items', []);
        $unauthorizedResponse->assertUnauthorized();

        // Create a request to create a new inventory item
        $response = $this->actingAs($this->user)->postJson('/api/inventory-items', []);

        // Assert the response is successful and contains the expected data
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['sku', 'name', 'price']);
    }

    public function test_create_inventory_item(): void
    {
        // Create a request to create a new inventory item
        $response = $this->actingAs($this->user)->postJson('/api/inventory-items', [
            'name' => 'Test Item',
            'description' => 'Test description',
            'price' => 10.99,
            'sku' => 'TES-###-TSKU123',
        ]);

        // Assert the response is successful and contains the expected data
        $response->assertCreated()
            ->assertJson(['message' => 'Inventory item created successfully']);

        $this->assertDatabaseHas('inventory_items', [
            'sku' => 'TES-###-TSKU123',
        ]);
    }

    public function test_update_inventory_item(): void
    {
        // Create an inventory item for testing
        $inventoryItem = InventoryItem::factory()->create();

        // Create a request to update the inventory item
        $response = $this->actingAs($this->user)->putJson("/api/inventory-items/{$inventoryItem->id}", [
            'name' => 'Updated Item',
            'description' => 'Updated description',
            'price' => 15.99,
        ]);

        // Assert the response is successful and contains the expected data
        $response->assertOk()
            ->assertJson(['message' => 'Inventory item updated successfully']);
    }

    public function test_delete_inventory_item(): void
    {
        // Create an inventory item for testing
        $inventoryItem = InventoryItem::factory()->create();

        // Make a request to delete the inventory item
        $response = $this->actingAs($this->user)->deleteJson("/api/inventory-items/{$inventoryItem->id}");

        // Assert the response is successful
        $response->assertNoContent();
    }
}
