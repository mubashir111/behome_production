<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Standardized success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Standardized error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $code = 422, $data = []): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
