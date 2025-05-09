<?php

namespace App\Http\Resources\Reservation;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaildTableReservationResource extends JsonResource
{
    // /**
    //  * Transform the resource into an array.
    //  *
    //  * @return array<string, mixed>
    //  */

        public function toArray(Request $request): array
        {
            $filteredReservations = $this->whenLoaded('reservations', function () {
                return $this->reservations->map(function ($reservation) {
                    return [
                        'start_date' => $reservation->start_date,
                        'end_date' => $reservation->end_date,
                        'location' => $reservation->table->location,
                        'department_id' => $reservation->table->department_id,
                        'status' => in_array($reservation->status, ['cancelled', 'completed'])
                                    ? $reservation->status
                                    : 'reserved',
                    ];
                });
            }, []);

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
