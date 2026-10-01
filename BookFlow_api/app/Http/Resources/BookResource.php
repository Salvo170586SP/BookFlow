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
            'id'             => $this->id,
            'title'          => $this->title,
            'isbn'           => $this->isbn,
            'published_year' => $this->published_year,
            // whenLoaded: la chiave compare solo se la relazione è stata caricata con with()
            'publisher'      => $this->whenLoaded('publisher', fn() => $this->publisher?->only(['id', 'name'])),
            'category'       => $this->whenLoaded('category', fn() => $this->category->only(['id', 'name'])),
            'authors'        => $this->whenLoaded('authors', fn() => $this->authors->map->only(['id', 'name'])->values()),
        ];
    }
}
