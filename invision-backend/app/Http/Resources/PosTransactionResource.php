<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'type' => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
                'color' => $this->type->color(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'store' => $this->whenLoaded('store', fn () => [
                'id' => $this->store->id,
                'name' => $this->store->name,
            ]),
            'terminal' => $this->whenLoaded('terminal', fn () => [
                'id' => $this->terminal->id,
                'name' => $this->terminal->name,
                'terminal_code' => $this->terminal->terminal_code,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
            ]),
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'payment_method' => $this->payment_method,
            'items_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'items' => PosTransactionItemResource::collection($this->whenLoaded('items')),
            'notes' => $this->notes,
            'synced_at' => $this->synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
