<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         return [
            'id' => $this->id,
            'name' => $this->name ,
            'description' => $this->description,
            'image' => $this->image,
            'manager' => $this->manager ? $this->manager->name : 'manger is not found',  
            // 'manager' => new UserResource($this->whenLoaded('manager')),
            // 'image' => new ImageResource($this->whenLoaded('image')),  
            // 'tables' => TableResource::collection($this->whenLoaded('tables')),  
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
