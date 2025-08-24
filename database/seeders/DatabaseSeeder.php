<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@daftra.com',
            'password' => bcrypt('password@123')
        ]);

        // Create warehouses
        $warehouses = Warehouse::factory(5)->create();

        // Create inventory items
        $items = InventoryItem::factory(50)->create();

        // Create stock for each warehouse
        foreach ($warehouses as $warehouse) {
            foreach ($items->random(30) as $item) {
                Stock::create([
                    'warehouse_id' => $warehouse->id,
                    'inventory_item_id' => $item->id,
                    'quantity' => rand(10, 200),
                    'reserved_quantity' => 0,
                ]);
            }
        }
    }
}
