<?php

namespace App\Http\Requests\Sales;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'check_number' => ['nullable', 'required_if:payment_method,check', 'string', 'max:255'],
            'check_date' => ['nullable', 'required_if:payment_method,check', 'date'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
