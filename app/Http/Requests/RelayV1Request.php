<?php

namespace App\Http\Requests;

use App\Services\InstanceService;
use Illuminate\Foundation\Http\FormRequest;

class RelayV1Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $apiKey = $this->bearerToken();
        if (! $apiKey) {
            return false;
        }

        return in_array($apiKey, InstanceService::getKeys(), true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string|starts_with:Expo',
            'type' => 'required|string|in:follow,like,comment,mention',
            'actor' => 'sometimes|string',
        ];
    }
}
