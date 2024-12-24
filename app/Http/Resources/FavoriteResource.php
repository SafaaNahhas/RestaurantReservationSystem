<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type' => class_basename($this->favorable), 
            'value' => $this->favorable instanceof \App\Models\Table
                ? $this->favorable->table_number 
                : ($this->favorable->category_name ?? null), 
        ];
    }
}
