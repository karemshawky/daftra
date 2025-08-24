<?php

namespace App\Models;

use App\Enums\StockTransferStatus;
use App\Filters\StockTransferFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockTransfer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'transferred_at' => 'datetime',
        'status' => StockTransferStatus::class,
    ];

    protected static function booted()
    {
        static::creating(function ($transfer) {
            $transfer->transfer_number = 'TR-' . date('Ymd') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $transfer->created_by = auth()->id();
        });
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public static function listStockTransfer(StockTransferFilter $filters, object $request)
    {
        $query = self::with(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

        return $filters->apply($query, $request)
            ->latest()
            ->paginate()
            ->withQueryString();
    }
}
