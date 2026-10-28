<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-success',
            'msgtype'       => 'success',
            'msgtype_title' => 'Success',
            'data'          => $data,
        ], $statusCode);
    }
    
    protected function errorResponse(string $message = 'Error', $data = null, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-danger',
            'msgtype'       => 'val_error',
            'msgtype_title' => 'Error',
            'data'          => $data,
        ], $statusCode);
    }

    protected function validationErrorResponse(array $errors, string $message = 'Validation Error'): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-danger',
            'msgtype'       => 'val_error',
            'msgtype_title' => 'Validation Error',
            'data'          => null,
            'errors'        => $errors,
        ], 422);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-danger',
            'msgtype'       => 'val_error',
            'msgtype_title' => 'Unauthorized',
            'data'          => null,
        ], 401);
    }
  
    protected function notFoundResponse(string $message = 'Not Found'): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-warning',
            'msgtype'       => 'val_error',
            'msgtype_title' => 'Not Found',
            'data'          => null,
        ], 404);
    }
   
    protected function createdResponse($data = null, string $message = 'Created Successfully'): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-success',
            'msgtype'       => 'success',
            'msgtype_title' => 'Success',
            'data'          => $data,
        ], 201);
    }
    
    protected function deletedResponse(string $message = 'Deleted Successfully'): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-success',
            'msgtype'       => 'success',
            'msgtype_title' => 'Success',
            'data'          => null,
        ], 200);
    }
  
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-danger',
            'msgtype'       => 'val_error',
            'msgtype_title' => 'Forbidden',
            'data'          => null,
        ], 403);
    }

    protected function serverErrorResponse(string $message = 'Server Error', $data = null): JsonResponse
    {
        return response()->json([
            'msg'           => $message,
            'msgclass'      => 'bg-danger',
            'msgtype'       => 'val_error',
            'msgtype_title' => 'Server Error',
            'data'          => $data,
        ], 500);
    }
}
