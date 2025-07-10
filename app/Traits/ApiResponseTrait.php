<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponseTrait
{
    /**
     * Send a success response.
     */
    protected function successResponse($data, string $message = 'Success', int $statusCode = 200)
    {
        if ($data instanceof JsonResource)
            $data = $data->response()->getData(true);

        $data = $data ?? ['data' => null];

        return response()->json([
            'success' => true,
            'message' => $message,
        ] + $data, $statusCode);
    }

    /**
     * Send an error response.
     */
    protected function errorResponse(string $message = 'An unexpected error occurred', int $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $statusCode);
    }


    /**
     * Send an error response.
     */
    public static function handlingErrorResponse(string $message = 'Something went wrong', int $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $statusCode);
    }

    /**
     * Send an error response.
     */
    public static function handlingFailedValidation($validator)
    {
        $errors = collect($validator->errors())->map(function ($messages, $key) {
            return implode(', ', $messages);
        })->values();

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => __('validation.validation_failed') . ": " . $errors->join(" | "),
            'data' => null
        ], 422));
    }
}
