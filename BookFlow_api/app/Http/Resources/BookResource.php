<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'published_year' => $this->published_year,
            'member_id' => $this->member_id,
            'copies' => $this->whenLoaded('copies', fn () => $this->copies->map(function ($copy) {
                return [
                    'id' => $copy->id,
                    'barcode' => $copy->barcode,
                    'status' => $copy->status,
                    'loans' => $copy->relationLoaded('loans')
                        ? LoanResource::collection($copy->loans)
                        : [],
                ];
            })->values()),
            'total_copies_count' => $this->when(
                isset($this->total_copies_count),
                (int) ($this->total_copies_count ?? 0)
            ),
            'borrowed_copies_count' => $this->when(
                isset($this->borrowed_copies_count),
                (int) ($this->borrowed_copies_count ?? 0)
            ),
            'remaining_copies_count' => $this->when(
                isset($this->total_copies_count) && isset($this->borrowed_copies_count),
                max(0, (int) $this->total_copies_count - (int) $this->borrowed_copies_count)
            ),
            'available_copies_count' => $this->when(
                isset($this->available_copies_count),
                (int) ($this->available_copies_count ?? 0)
            ),
            // whenLoaded: la chiave compare solo se la relazione è stata caricata con with()
            'publisher' => $this->whenLoaded('publisher', fn () => $this->publisher?->only(['id', 'name'])),
            'category' => $this->whenLoaded('category', fn () => $this->category->only(['id', 'name'])),
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->map->only(['id', 'name'])->values()),
        ];
    }
}
