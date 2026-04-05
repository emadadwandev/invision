<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreditAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'store' => new StoreResource($this->whenLoaded('store')),
            'credit_limit' => $this->credit_limit,
            'current_balance' => $this->current_balance,
            'available_credit' => $this->availableCredit(),
            'last_payment_at' => $this->last_payment_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
