<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type->value,
            'type_label'     => $this->type->label(),
            'amount'         => (float) $this->amount,
            'amount_base'    => (float) $this->amount_base,
            'amount_formatted' => $this->formatted_amount,
            'currency'       => $this->currency,
            'date'           => $this->date->toDateString(),
            'date_formatted' => $this->date->translatedFormat('d F Y'),
            'note'           => $this->note,
            'tags'           => $this->tags ?? [],
            'receipt_url'    => $this->receipt_path
                            ? route('receipt', ['path' => $this->receipt_path])
                            : null,
            'ai_categorized' => $this->ai_categorized,
            'ai_confidence'  => $this->ai_confidence,

            // Relasi
            'account'   => $this->whenLoaded('account', fn() => [
                'id'    => $this->account->id,
                'name'  => $this->account->name,
                'color' => $this->account->color,
                'icon'  => $this->account->icon,
            ]),
            'category'  => $this->whenLoaded('category', fn() => $this->category ? [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'color' => $this->category->color,
                'icon'  => $this->category->icon,
            ] : null),
            'transfer_pair_id' => $this->transfer_pair_id,

            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
