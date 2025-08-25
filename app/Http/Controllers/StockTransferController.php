<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use Illuminate\Http\JsonResponse;
use App\Filters\StockTransferFilter;
use App\Services\StockTransferService;
use App\Http\Resources\StockTransferResource;
use App\Exceptions\InsufficientStockException;
use App\Http\Requests\ListStockTransferRequest;
use App\Http\Requests\StoreStockTransferRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockTransferController extends Controller
{
    /**
     * Returns a paginated list of stock transfers with their status, source warehouse,
     * destination warehouse, inventory item, quantity, and creation date.
     *
     * @param ListStockTransferRequest $request
     * @param StockTransferFilter $filters
     *
     * @return AnonymousResourceCollection
     */
    public function index(ListStockTransferRequest $request, StockTransferFilter $filters): AnonymousResourceCollection
    {
        $transfers = StockTransfer::listStockTransfer($filters, $request);

        return StockTransferResource::collection($transfers);
    }

    /**
     * Initiates a stock transfer.
     *
     * @param StockTransferService $stockTransferService
     * @param StoreStockTransferRequest $request
     * @return JsonResponse
     *
     * @throws InsufficientStockException
     */
    public function store(StockTransferService $stockTransferService, StoreStockTransferRequest $request): JsonResponse
    {
        // $this->authorize('create', StockTransfer::class);

        try {
            $stockTransferService->transferStock($request->validated());

            return response()->json(['message' => 'Stock transfer initiated successfully'], JsonResponse::HTTP_CREATED);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['quantity' => [$e->getMessage()]]
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Returns a single stock transfer by ID, including its status, source warehouse,
     * destination warehouse, inventory item, quantity, and creation date.
     *
     * @param StockTransfer $stockTransfer
     *
     * @return JsonResponse
     */
    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        $stockTransfer->load(['fromWarehouse', 'toWarehouse', 'inventoryItem', 'createdBy']);

        return response()->json([
            'data' => new StockTransferResource($stockTransfer)
        ]);
    }
}
