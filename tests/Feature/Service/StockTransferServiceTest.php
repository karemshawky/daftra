<?php

use Tests\TestCase;
use App\Models\User;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\StockTransfer;
use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Event;
use App\Services\StockTransferService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StockTransferServiceTest extends TestCase
{
    protected User $user;
    protected StockTransferService $service;
    protected InventoryItem $inventoryItem;
    protected Warehouse $warehouseFrom;
    protected Warehouse $warehouseTo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->service = new StockTransferService();
        $this->inventoryItem = InventoryItem::factory()->create();
        $this->warehouseFrom = Warehouse::factory()->create();
        $this->warehouseTo = Warehouse::factory()->create();
    }

    public function test_successful_stock_transfer()
    {
        // Arrange
        $data = [
            'inventory_item_id' => $this->inventoryItem->id,
            'from_warehouse_id' => $this->warehouseFrom->id,
            'to_warehouse_id' => $this->warehouseTo->id,
            'quantity' => 10,
        ];

        $fromStock = Stock::factory()->create(['warehouse_id' => $this->warehouseFrom->id, 'inventory_item_id' => $this->inventoryItem->id, 'quantity' => 20]);
        $toStock = Stock::factory()->create(['warehouse_id' => $this->warehouseTo->id, 'inventory_item_id' => $this->inventoryItem->id, 'quantity' => 0]);

        // Act
        $result = $this->service->transferStock($data);

        // Assert
        $this->assertInstanceOf(StockTransfer::class, $result);
        $this->assertEquals($data['inventory_item_id'], $result->inventory_item_id);
        $this->assertEquals($data['from_warehouse_id'], $result->from_warehouse_id);
        $this->assertEquals($data['to_warehouse_id'], $result->to_warehouse_id);

        // Assert Cache is cleared
        $this->assertNull(Stock::clearWarehousesCache($this->warehouseFrom->id, $this->warehouseTo->id));

        // Assert stock levels
        $this->assertEquals(10, $fromStock->fresh()->quantity);
        $this->assertEquals(10, $toStock->fresh()->quantity);

        // Assert database record
        $this->assertDatabaseHas('stock_transfers', [
            'inventory_item_id' => $data['inventory_item_id'],
            'from_warehouse_id' => $data['from_warehouse_id'],
            'to_warehouse_id' => $data['to_warehouse_id'],
            'quantity' => $data['quantity'],
        ]);
    }

    public function test_insufficient_stock_in_source_warehouse()
    {
        Event::fake();

        // Arrange
        $data = [
            'inventory_item_id' => $this->inventoryItem->id,
            'from_warehouse_id' => $this->warehouseFrom->id,
            'to_warehouse_id' => $this->warehouseTo->id,
            'quantity' => 30,
        ];

        $fromStock = Stock::factory()->create(['warehouse_id' => $this->warehouseFrom->id, 'inventory_item_id' => $this->inventoryItem->id, 'quantity' => 20]);

        // Act and Assert
        $this->expectException(InsufficientStockException::class);
        $this->service->transferStock($data);

        Event::assertDispatched(LowStockDetected::class);
    }

    public function test_invalid_inventory_item_id()
    {
        // Arrange
        $data = [
            'inventory_item_id' => 999,
            'from_warehouse_id' => $this->warehouseFrom->id,
            'to_warehouse_id' => $this->warehouseTo->id,
            'quantity' => 10,
        ];

        // Act and Assert
        $this->expectException(ModelNotFoundException::class);
        $this->service->transferStock($data);
    }

    // public function test_invalid_quantity()
    // {
    //     // Arrange
    //     $data = [
    //         'inventory_item_id' => $this->inventoryItem->id,
    //         'from_warehouse_id' => $this->warehouseFrom->id,
    //         'to_warehouse_id' => $this->warehouseTo->id,
    //         'quantity' => -10,
    //     ];

    //     $fromStock = Stock::factory()->create(['warehouse_id' => $this->warehouseFrom->id, 'inventory_item_id' => $this->inventoryItem->id, 'quantity' => 20]);
    //     $toStock = Stock::factory()->create(['warehouse_id' => $this->warehouseTo->id, 'inventory_item_id' => $this->inventoryItem->id, 'quantity' => 0]);

    //     // Act and Assert
    //     $this->expectException(\Illuminate\Validation\ValidationException::class);
    //     $this->expectException(\Illuminate\Database\QueryException::class);
    //     $this->service->transferStock($data);
    // }
}
