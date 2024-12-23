<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaildTableReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $filteredReservations = $this->reservations
        ->map(function ($reservation) {
            return [
                'start_date' => $reservation->start_date,
                'end_date' => $reservation->end_date,
                'status' => in_array($reservation->status, ['cancelled', 'completed'])
                            ? $reservation->status
                            : '_',
            ];
        });

    return [
        'table' => [
            'table_number' => $this->table_number,
            'location' => $this->location,
            'seat_count' => $this->seat_count,
            'department_id' => $this->department_id,
        ],
        'reservations' => $filteredReservations,
    ];
        }
}
