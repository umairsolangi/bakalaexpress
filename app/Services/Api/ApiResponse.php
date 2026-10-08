<?php

namespace App\Services\Api;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', array|object $meta = [], int $status = 200): JsonResponse
    {
        $metaObj = (is_array($meta) && empty($meta)) || empty($meta) ? new \stdClass() : (is_array($meta) ? (object) $meta : $meta);
        $resolvedData = $data === null ? new \stdClass() : $data;

        return response()->json([
            'success' => true,
            'data' => $resolvedData,
            'message' => $message,
            'meta' => $metaObj,
        ], $status);
    }

    public static function error(string $message, string $code, array|object $errors = [], int $status = 400): JsonResponse
    {
        $errorsObj = (is_array($errors) && empty($errors)) || empty($errors) ? new \stdClass() : (is_array($errors) ? (object) $errors : $errors);

        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $errorsObj,
        ], $status);
    }
}
