<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FoodCategory;
use Illuminate\Support\Facades\Log;
use App\Services\Food\FoodCategoryService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FoodCategoryTest extends TestCase
{
    use DatabaseTransactions;

    protected $foodCategoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->foodCategoryService = new FoodCategoryService();
    }

    /** @test for list categories */
    public function it_can_list_categories()
    {
        // Create multiple FoodCategory records
        FoodCategory::factory()->count(15)->create();

        // Call the listCategory method
        $categories = $this->foodCategoryService->listCategory(10);

        // Assert the paginated result
        $this->assertCount(10, $categories);
    }

    /** @test for create category with image */
    public function it_can_create_category()
    {
        // Create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);
        // Define the data for the new FoodCategory
        $data = [
            'category_name' => 'Fruits',
            'description' => 'Fresh and healthy fruits.',
        ];

        // Call the createCategory method and assert the database has the new category
        $category = $this->foodCategoryService->createCategory($data);
        $this->assertDatabaseHas('food_categories', ['category_name' => 'Fruits']);
        $this->assertEquals('Fruits', $category->category_name);
    }



    /** @test for update category*/
    public function it_can_update_category()
    {
        // Create a FoodCategory for the test
        $category = FoodCategory::factory()->create();

        // Define the new data for the category
        $updatedData = [
            'category_name' => 'Updated Appetizers',
            'description' => 'Updated starters and appetizers.',
        ];

        // Call the updateFoodCategory method
        $updatedCategory = $this->foodCategoryService->updateFoodCategory($updatedData, $category->id);

        // Assert the category is updated
        $this->assertEquals('Updated Appetizers', $updatedCategory->category_name);
        $this->assertEquals('Updated starters and appetizers.', $updatedCategory->description);
    }

    /** @test for delete category*/
    public function it_can_delete_category()
    {
        // Create a FoodCategory for the test
        $category = FoodCategory::factory()->create();

        // Call the deleteFoodCategory method
        $this->foodCategoryService->deleteFoodCategory($category->id);

        // Assert the category is deleted
        $this->assertSoftDeleted('food_categories', ['id' => $category->id]);
    }

    /** @test for list trashed category*/
    public function it_can_list_trashed_categories()
    {
        // Create a FoodCategory and soft delete it
        $category = FoodCategory::factory()->create();
        $category->delete();

        // Call the trashedListFoodCategory method
        $trashedCategories = $this->foodCategoryService->trashedListFoodCategory(10);

        // Assert the trashed category is listed
        $this->assertCount(1, $trashedCategories);
        $this->assertEquals($category->id, $trashedCategories->first()->id);
    }

    /** @test for restore category*/
    public function it_can_restore_category()
    {
        // Create a FoodCategory and soft delete it
        $category = FoodCategory::factory()->create();
        $category->delete();

        // Assert the category is in the trashed state
        $this->assertSoftDeleted('food_categories', ['id' => $category->id]);

        // Call the restoreFoodCategory method
        $restoredCategory = $this->foodCategoryService->restoreFoodCategory($category->id);

        // Assert the category is restored
        $this->assertFalse($restoredCategory->trashed());
    }

    /** @test for force delete category*/
    public function it_can_force_delete_category()
    {
        // Create a FoodCategory and soft delete it
        $category = FoodCategory::factory()->create();
        $category->delete();

        // Call the forceDeleteFoodCategory method
        $this->foodCategoryService->forceDeleteFoodCategory($category->id);

        // Assert the category is permanently deleted
        $this->assertDatabaseMissing('food_categories', ['id' => $category->id]);
    }
}
