<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BorrowerCollection extends ResourceCollection
{
    public $collects = BorrowerResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'meta' => $this->when(
                $this->resource instanceof \Illuminate\Pagination\AbstractPaginator,
                fn () => [
                    'current_page' => $this->resource->currentPage(),
                    'last_page'    => $this->resource->lastPage(),
                    'per_page'     => $this->resource->perPage(),
                    'total'        => $this->resource->total(),
                    'from'         => $this->resource->firstItem(),
                    'to'           => $this->resource->lastItem(),
                ]
            ),
            'links' => $this->when(
                $this->resource instanceof \Illuminate\Pagination\AbstractPaginator,
                fn () => [
                    'first' => $this->resource->url(1),
                    'last'  => $this->resource->url($this->resource->lastPage()),
                    'prev'  => $this->resource->previousPageUrl(),
                    'next'  => $this->resource->nextPageUrl(),
                ]
            ),
        ];
    }
}
