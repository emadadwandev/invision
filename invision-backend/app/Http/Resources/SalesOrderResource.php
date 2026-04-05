<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'store' => new StoreResource($this->whenLoaded('store')),
            'salesperson' => new UserResource($this->whenLoaded('salesperson')),
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'total_paid' => $this->totalPaid(),
            'balance_due' => $this->balanceDue(),
            'notes' => $this->notes,
            'items' => SalesOrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'items_count' => $this->whenCounted('items'),
            'payments_count' => $this->whenCounted('payments'),
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
