<?php

namespace App\Http\Resources\Reservation;

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

        return [
        'table' => [
            'table_number' => $this->table ? $this->table->table_number : null,
            'location' => $this->table ? $this->table->location : null,
            'seat_count' => $this->table ? $this->table->seat_count : null,
            'department_id' => $this->table ? $this->table->department_id : null,
        ],
        'reservation' => [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'guest_count' => $this->guest_count,
            'status' => $this->status,
        ],
        // 'message_type' => $this->notification_method ? 'telegram' : 'email', // تحديد نوع الرسالة بناءً على notification_method
        ];

    }}
