<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_name' => 'required|string|max:255',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'details' => 'nullable|string',
            'reservation_id' => 'required|exists:reservations,id',
        ];
    }

      /**
     * رسائل التحقق المخصصة.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'event_name.required' => 'اسم الحدث مطلوب.',
            'start_date.after_or_equal' => 'تاريخ البداية يجب أن يكون في المستقبل أو اليوم.',
            'end_date.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية.',
            'reservation_id.exists' => 'الحجز المحدد غير موجود.',
        ];
    }
}
