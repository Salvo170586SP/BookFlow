<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'phone' => $this->phone,
            'address' => $this->address,
            'birth_city' => $this->birth_city,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'photo' => $this->photo,
        ];
    }
}
