<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return [
        //     'table' => [
        //         'table_number' => $this->table_number,
        //         'location' => $this->location,
        //         'seat_count' => $this->seat_count,
        //         'department_id' => $this->department_id,
        //     ],
        //     'reservations' => $this->reservations ? $this->reservations->map(function ($reservation) {
        //         return [
        //             'start_date' => $reservation->start_date,
        //             'end_date' => $reservation->end_date,
        //         ];
        //     }) : [],
        // ];
        return [
        //    'table' => [
        //         'table_number' => $this->table_number,
        //         'location' => $this->location,
        //         'seat_count' => $this->seat_count,
        //         'department_id' => $this->department_id,
        //     ],

        'table' => [
            'table_number' => $this->table ? $this->table->table_number : null,
            'location' => $this->table ? $this->table->location : null,
            'seat_count' => $this->table ? $this->table->seat_count : null,
            'department_id' => $this->table ? $this->table->department_id : null,
        ],
            'reservations' => $this->reservations ? $this->reservations->map(function ($reservation) {
                return [
                    'start_date' => $reservation->start_date,
                    'end_date' => $reservation->end_date,
                ];
            }) : [],
        ];

    }}
