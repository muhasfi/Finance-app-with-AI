<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'email'                 => $this->email,
            'role'                  => $this->role->value,
            'currency'              => $this->currency,
            'timezone'              => $this->timezone,
            'avatar'                => $this->avatar
                                        ? asset('storage/' . $this->avatar)
                                        : null,
            'email_verified'        => ! is_null($this->email_verified_at),
            'two_factor_enabled'    => ! is_null($this->two_factor_confirmed_at),
            'last_login_at'         => $this->last_login_at?->toIso8601String(),
            'created_at'            => $this->created_at->toIso8601String(),
        ];
    }
}
