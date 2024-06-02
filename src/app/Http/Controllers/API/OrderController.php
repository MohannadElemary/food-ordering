<?php

namespace App\Http\Controllers\API;

use App\Exceptions\InsufficientIngredientsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\Interfaces\OrderServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    public function __invoke(StoreOrderRequest $request): JsonResponse
    {
        try {
            $this->orderService->createOrder($request->validated()['products']);
            return response()->json(['message' => 'Order placed successfully']);
        } catch (InsufficientIngredientsException $e) {
            return $e->render();
        } catch (Exception) {
            return response()->json(['error' => 'An error occurred while processing the order.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
