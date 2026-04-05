<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'receipt_number' => $this->receipt_number,
            'amount' => $this->amount,
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'user' => new UserResource($this->whenLoaded('user')),
            'deposited_at' => $this->deposited_at,
            'bank_name' => $this->bank_name,
            'branch' => $this->branch,
            'photo_path' => $this->photo_path,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
