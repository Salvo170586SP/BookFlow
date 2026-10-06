<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'copy_id' => $this->copy_id,
            'loaned_at' => $this->loaned_at,
            'due_at' => $this->due_at,
            'copy' => new CopyResource($this->whenLoaded('copy')),
        ];
    }
}
