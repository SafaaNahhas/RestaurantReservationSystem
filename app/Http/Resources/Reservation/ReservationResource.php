<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_name' => $this->user->name,
            'manager_name' => $this->manager->name,
            'table_id' => $this->table_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'guest_count' => $this->guest_count,
            'services' => $this->services,
            'status' => $this->status,
        ];
    }
}
