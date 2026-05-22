<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bidder_name' => ['required', 'string', 'min:2', 'max:100'],
            'amount'      => ['required', 'integer', 'min:1'], // in cents
        ];
    }

    public function messages(): array
    {
        return [
            'bidder_name.required' => 'Please enter your name.',
            'bidder_name.min'      => 'Name must be at least 2 characters.',
            'amount.required'      => 'Bid amount is required.',
            'amount.min'           => 'Bid amount must be a positive number.',
        ];
    }
}
