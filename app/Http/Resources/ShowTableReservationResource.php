<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowTableReservationResource extends JsonResource
{
     /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'table' => [
                'table_number' => $this->table_number,
                'location' => $this->location,
                'seat_count' => $this->seat_count,
                'department_id' => $this->department_id,
            ],
            'reservations' => $this->reservations->map(function ($reservation) {
                return [
                    'start_date' => $reservation->start_date,
                    'end_date' => $reservation->end_date,
                    'status' => $reservation->status,
                ];
            }),
        ];
    }
}
