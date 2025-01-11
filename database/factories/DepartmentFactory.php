<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User; // إذا كنت تحتاج إلى استخدام مدير (Manager)
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    // تحديد نموذج الـ Department الذي يرتبط بهذا الـ Factory
    protected $model = Department::class;

    /**
     * تعريف قيم الخصائص النموذجية
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company,  // اسم القسم (مثلاً: "IT Department")
            'description' => $this->faker->sentence,  // وصف للقسم (مثلاً: "This department handles all IT related tasks.")
            'manager_id' => User::factory(),  // تعيين مدير للقسم باستخدام User Factory
        ];
    }
}
