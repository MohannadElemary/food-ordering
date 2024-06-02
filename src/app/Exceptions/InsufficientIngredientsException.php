<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InsufficientIngredientsException extends Exception
{
    protected $message;

    public function __construct($message = "Insufficient ingredients for the requested product.")
    {
        $this->message = $message;
    }

    public function render(): JsonResponse
    {
        return response()->json(['error' => $this->message], Response::HTTP_BAD_REQUEST);
    }
}
