<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $spent      = $this->spent();
        $percentage = $this->percentage();

        return [
            'id'              => $this->id,
            'amount'          => (float) $this->amount,
            'month'           => $this->month,
            'year'            => $this->year,
            'alert_threshold' => $this->alert_threshold,
            'is_active'       => $this->is_active,

            // Kalkulasi
            'spent'           => $spent,
            'remaining'       => max(0, $this->amount - $spent),
            'percentage'      => $percentage,
            'status'          => $this->status(),
            'status_label'    => $this->statusLabel(),
            'status_color'    => $this->statusColor(),

            'category' => $this->whenLoaded('category', fn() => [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'color' => $this->category->color,
                'icon'  => $this->category->icon,
            ]),

            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
