<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use App\Enums\StockTransferStatus;
use App\Filters\StockTransferFilter;
use App\Services\StockTransferService;
use App\Http\Requests\StockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Http\Requests\ListStockTransferRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockTransferController extends Controller
{
    public function __construct(protected StockTransferService $transferService) {}

    public function index(ListStockTransferRequest $request, StockTransferFilter $filters): AnonymousResourceCollection
    {
        $transfers = StockTransfer::listStockTransfer($filters, $request);

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
        if ($transfer->status !== StockTransferStatus::Pending) {
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
        if ($transfer->status !== StockTransferStatus::Pending) {
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
