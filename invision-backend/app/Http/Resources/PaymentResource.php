<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            'collector' => new UserResource($this->whenLoaded('collector')),
            'payment_method' => $this->payment_method,
            'amount' => $this->amount,
            'reference_number' => $this->reference_number,
            'check_number' => $this->check_number,
            'check_date' => $this->check_date?->toDateString(),
            'bank_name' => $this->bank_name,
            'status' => $this->status,
            'paid_at' => $this->paid_at,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
