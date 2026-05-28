<?php

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

class ValidationHelper
{
    public static function failed(Validator $validator): JsonResponse
    {
        return ResponseHelper::error(
            'Los datos enviados no son validos.',
            $validator->errors()->toArray(),
            422
        );
    }

    public static function requiredMessage(string $campo): string
    {
        return "El campo {$campo} es obligatorio.";
    }
}
