<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'type'       => $this->type,
            'color'      => $this->color,
            'icon'       => $this->icon,
            'is_default' => $this->is_default,
            'parent_id'  => $this->parent_id,
            'sort_order' => $this->sort_order,
            'children'   => $this->whenLoaded('children',
                fn() => CategoryResource::collection($this->children)
            ),
        ];
    }
}
