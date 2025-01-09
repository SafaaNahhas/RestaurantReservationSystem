<?php

namespace App\Http\Requests\TableRequest;

use App\Services\TableRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class FillterTabelRequest extends BaseTableRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, array>
     */
    public function rules(): array
    {
        return [
            'table_number' => ['sometimes', 'string'],
            'location' => ['sometimes', 'string'],
            'seat_count' => ['sometimes', 'integer'],
        ];
    }
}
