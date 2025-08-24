<?php

namespace App\Http\Controllers;

use StockTransferService;
use Illuminate\Http\Request;
use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StockTransferRequest;
use App\Http\Resources\StockTransferResource;

class StockTransferController extends Controller
{
    public function __construct(protected StockTransferService $transferService) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,in_transit,completed,cancelled',
            'from_warehouse_id' => 'nullable|exists:warehouses,id',
            'to_warehouse_id' => 'nullable|exists:warehouses,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = StockTransfer::with(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

        if (!empty($validated['status'])) {
            $query->status($validated['status']);
        }

        if (!empty($validated['from_warehouse_id'])) {
            $query->where('from_warehouse_id', $validated['from_warehouse_id']);
        }

        if (!empty($validated['to_warehouse_id'])) {
            $query->where('to_warehouse_id', $validated['to_warehouse_id']);
        }

        $transfers = $query->orderBy('created_at', 'desc')
            ->paginate($validated['per_page'] ?? 15);

        return StockTransferResource::collection($transfers);
    }

    public function store(StockTransferRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_by'] = $request->user()->id;

            $transfer = $this->transferService->transferStock($data);
            $transfer->load(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

            return response()->json([
                'message' => 'Stock transfer initiated successfully'
            ], JsonResponse::HTTP_CREATED);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['quantity' => [$e->getMessage()]]
            ], 422);
        }
    }

    public function show(StockTransfer $transfer): JsonResponse
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

        return response()->json([
            'data' => new StockTransferResource($transfer)
        ]);
    }

    public function complete(StockTransfer $transfer): JsonResponse
    {
        if ($transfer->status !== StockTransfer::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending transfers can be completed'
            ], 422);
        }

        $this->transferService->completeTransfer($transfer);
        $transfer->refresh();
        $transfer->load(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

        return response()->json(['message' => 'Stock transfer completed successfully']);
    }

    public function cancel(StockTransfer $transfer): JsonResponse
    {
        if ($transfer->status !== StockTransfer::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending transfers can be cancelled'
            ], 422);
        }

        $this->transferService->cancelTransfer($transfer);
        $transfer->refresh();
        $transfer->load(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

        return response()->json([
            'data' => new StockTransferResource($transfer),
            'message' => 'Stock transfer cancelled successfully'
        ]);
    }
}
