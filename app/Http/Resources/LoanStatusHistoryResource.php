<?php

namespace App\Http\Resources;

class LoanStatusHistoryResource extends GracimorResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'previous_status' => $this->previous_status
                ? $this->statusBadge($this->previous_status)
                : null,
            'new_status'      => $this->statusBadge($this->new_status),
            'notes'           => $this->notes,
            'is_system'       => is_null($this->changed_by),
            'changed_by'      => $this->whenLoaded('changedBy', fn () => $this->changedBy
                ? ['id' => $this->changedBy->id, 'name' => $this->changedBy->name]
                : ['id' => null, 'name' => 'System']
            ),
            'changed_at'      => $this->dt($this->created_at),
        ];
    }
}
