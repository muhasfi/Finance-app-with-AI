<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'type'         => $this->type->value,
            'type_label'   => $this->type->label(),
            'balance'      => (float) $this->balance,
            'balance_formatted' => 'Rp ' . number_format($this->balance, 0, ',', '.'),
            'currency'     => $this->currency,
            'color'        => $this->color,
            'icon'         => $this->icon,
            'description'  => $this->description,
            'is_active'    => $this->is_active,
            'credit_limit' => $this->credit_limit ? (float) $this->credit_limit : null,
            'due_date_day' => $this->due_date_day,
            'created_at'   => $this->created_at->toIso8601String(),
        ];
    }
}
