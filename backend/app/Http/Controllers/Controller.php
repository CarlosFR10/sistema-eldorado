<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    protected function success(mixed $data = null, string $message = 'Operacion realizada correctamente', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ], $status);
    }

    protected function error(string $message, array $errors = [], string $code = 'ERROR', int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ], $status);
    }
}
