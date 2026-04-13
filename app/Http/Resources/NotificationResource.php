<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'type'    => $this->data['type']    ?? null,
            'message' => $this->data['message'] ?? null,
            'icon'    => $this->data['icon']    ?? 'bi-bell',
            'color'   => $this->data['color']   ?? 'secondary',
            'url'     => $this->data['url']     ?? null,
            'data'    => $this->data['data']    ?? null,
            'read'    => ! is_null($this->read_at),
            'read_at' => $this->read_at?->toIso8601String(),
            'time'    => $this->created_at->diffForHumans(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
