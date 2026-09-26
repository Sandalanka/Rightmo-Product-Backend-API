<?php

namespace App\Http\Requests;

use App\Constants\StatusCodeConstant;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class BaseRequest extends FormRequest
{
    /**
     * Request fields that must never be written to the logs.
     */
    protected const SENSITIVE_FIELDS = ['password', 'password_confirmation', 'current_password'];

    /**
     * Summary: Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Summary: Request failedValidation response
     */
    protected function failedValidation(Validator $validator): void
    {
        Log::error('Validation failed', [
            'request_class' => static::class,
            'controller_action' => $this->route()?->getActionName(),
            'request_data' => $this->except(self::SENSITIVE_FIELDS),
            'validation_errors' => $validator->errors()->all(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        $response = [
            'status' => 'failed',
            'message' => 'Validation errors',
            'errors' => $validator->errors(),
            'timestamp' => now()->toDateTimeString(),
        ];

        throw new HttpResponseException(response()->json($response, StatusCodeConstant::UNPROCESSABLE_ENTITY, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => 'application/json',
        ]));
    }
}
