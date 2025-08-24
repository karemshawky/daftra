<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListStockTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'nullable|in:1,2,3,4',  // pending, In_Transit, Completed, Cancelled
            'from_warehouse_id' => 'nullable|exists:warehouses,id',
            'to_warehouse_id' => 'nullable|exists:warehouses,id',
        ];
    }
}
