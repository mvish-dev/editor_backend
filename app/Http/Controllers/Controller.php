<?php

namespace App\Http\Controllers;

use App\Constants\ResponseCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

abstract class Controller
{
    protected function handleException(Exception $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'status' => ResponseCode::UNPROCESSABLE_ENTITY,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], ResponseCode::UNPROCESSABLE_ENTITY);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status' => ResponseCode::UNAUTHORIZED,
                'message' => ResponseCode::MSG_UNAUTHORIZED,
            ], ResponseCode::UNAUTHORIZED);
        }

        if ($e instanceof HttpException) {
             $statusCode = $e->getStatusCode();
             $message = $e->getMessage() ?: ResponseCode::MSG_INTERNAL_ERROR;
             return response()->json([
                'status' => $statusCode,
                'message' => $message
             ], $statusCode);
        }

        $statusCode = ResponseCode::INTERNAL_SERVER_ERROR;
        $message = ResponseCode::MSG_INTERNAL_ERROR;

        if (config('app.debug')) {
            $message = $e->getMessage();
        }

        return response()->json([
            'status' => $statusCode,
            'message' => $message,
        ], $statusCode);
    }
}
