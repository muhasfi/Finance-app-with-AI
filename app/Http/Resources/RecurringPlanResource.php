<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecurringPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type->value,
            'type_label'  => $this->type->label(),
            'amount'      => (float) $this->amount,
            'frequency'   => $this->frequency->value,
            'frequency_label' => $this->frequency->label(),
            'start_date'  => $this->start_date->toDateString(),
            'next_run_at' => $this->next_run_at->toDateString(),
            'ends_at'     => $this->ends_at?->toDateString(),
            'note'        => $this->note,
            'is_active'   => $this->is_active,
            'is_due_today'=> $this->next_run_at->isToday(),

            'account'  => $this->whenLoaded('account', fn() => [
                'id'   => $this->account->id,
                'name' => $this->account->name,
            ]),
            'category' => $this->whenLoaded('category', fn() => $this->category ? [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'color' => $this->category->color,
                'icon'  => $this->category->icon,
            ] : null),
        ];
    }
}
