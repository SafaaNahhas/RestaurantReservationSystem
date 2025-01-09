<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Dish;
use App\Models\FoodCategory;
use App\Models\User;
use App\Services\DishService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;

class DishTest extends TestCase
{
    use DatabaseTransactions;

    protected $dishService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dishService = new DishService();
    }

    /** @test list dishes*/
    public function it_can_list_dishes()
    {
        // Create multiple Dish records
        Dish::factory()->count(15)->create();

        // Call the listDish method
        $dishes = $this->dishService->listDish(10);

        // Assert the paginated result
        $this->assertCount(10, $dishes);
    }

    /** @test for create dish with image*/
    public function it_can_create_dish_with_images()
    {
        // Create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a FoodCategory for the dish
        $category = FoodCategory::factory()->create();

        // Define the data for the new Dish, including dummy files
        $data = [
            'name' => 'Pizza',
            'description' => 'Delicious cheese pizza.',
            'category_id' => $category->id,
            'images' => [
                UploadedFile::fake()->create('dummy1.jpg', 100), 
                UploadedFile::fake()->create('dummy2.jpg', 100),
            ],
        ];

        // Mock the Storage facade
        Storage::fake('public');

        // Call the createDish method
        $dish = $this->dishService->createDish($data);

        // Assert the database has the new dish
        $this->assertDatabaseHas('dishes', ['name' => 'Pizza']);

        // Reload the dish to ensure the image is correctly associated
        $dish->load('image');

        // Assert that the dish has an image
        $this->assertNotNull($dish->image);

        // Log the image path for debugging
        Log::info('Dish Image Path: ' . $dish->image->image_path);

        // Use the relative path for file existence check in the mocked storage
        $relativePath = str_replace('/storage', '', $dish->image->image_path);
        $this->assertTrue(Storage::disk('public')->exists($relativePath), "File does not exist at path: $relativePath");
    }

    /** @test for create dish without image*/
    public function it_can_create_dish_without_images()
    {
        // Create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a FoodCategory for the dish
        $category = FoodCategory::factory()->create();

        // Define the data for the new Dish, without images
        $data = [
            'name' => 'Salad',
            'description' => 'Healthy vegetable salad.',
            'category_id' => $category->id,
        ];

        // Call the createDish method
        $dish = $this->dishService->createDish($data);

        // Assert the database has the new dish
        $this->assertDatabaseHas('dishes', ['name' => 'Salad']);
    }

    /** @test to get dish by id */
    public function it_can_get_dish_by_id()
    {
        // Create a FoodCategory and Dish for the test
        $category = FoodCategory::factory()->create();
        $dish = Dish::factory()->create(['category_id' => $category->id]);

        // Call the getDish method
        $foundDish = $this->dishService->getDish($dish->id);

        // Assert the correct dish is returned
        $this->assertEquals($dish->id, $foundDish->id);
        $this->assertEquals($category->id, $foundDish->category->id);
    }

    /** @test for update dish*/
    public function it_can_update_dish()
    {
        // Create a FoodCategory and Dish for the test
        $category = FoodCategory::factory()->create();
        $dish = Dish::factory()->create(['category_id' => $category->id]);

        // Define the new data for the Dish
        $updatedData = [
            'name' => 'Updated Pizza',
            'description' => 'Updated delicious cheese pizza.',
            'category_id' => $category->id,
        ];

        // Call the updateDish method
        $updatedDish = $this->dishService->updateDish($updatedData, $dish->id);

        // Assert the dish is updated
        $this->assertEquals('Updated Pizza', $updatedDish->name);
        $this->assertEquals('Updated delicious cheese pizza.', $updatedDish->description);
    }

    /** @test for delete dish*/
    public function it_can_delete_dish()
    {
        // Create a FoodCategory and Dish for the test
        $category = FoodCategory::factory()->create();
        $dish = Dish::factory()->create(['category_id' => $category->id]);

        // Call the deleteDish method
        $this->dishService->deleteDish($dish->id);

        // Assert the dish is soft deleted
        $this->assertSoftDeleted('dishes', ['id' => $dish->id]);
    }

    /** @test for list trashed dish*/
    public function it_can_list_trashed_dishes()
    {
        // Create a FoodCategory and Dish, then soft delete the Dish
        $category = FoodCategory::factory()->create();
        $dish = Dish::factory()->create(['category_id' => $category->id]);
        $dish->delete();

        // Call the trashedListDish method
        $trashedDishes = $this->dishService->trashedListDish(10);

        // Assert the soft deleted dish is listed
        $this->assertCount(1, $trashedDishes);
        $this->assertEquals($dish->id, $trashedDishes->first()->id);
    }

    /** @test for restore dish*/
    public function it_can_restore_dish()
    {
        // Create a FoodCategory and Dish, then soft delete the Dish
        $category = FoodCategory::factory()->create();
        $dish = Dish::factory()->create(['category_id' => $category->id]);
        $dish->delete();

        // Assert the dish is in the trashed state
        $this->assertSoftDeleted('dishes', ['id' => $dish->id]);

        // Call the restoreDish method
        $restoredDish = $this->dishService->restoreDish($dish->id);

        // Assert the dish is restored
        $this->assertFalse($restoredDish->trashed());
    }

    /** @test for force delete dish*/
    public function it_can_force_delete_dish()
    {
        // Create a FoodCategory and Dish, then soft delete the Dish
        $category = FoodCategory::factory()->create();
        $dish = Dish::factory()->create(['category_id' => $category->id]);
        $dish->delete();

        // Call the forceDeleteDish method
        $this->dishService->forceDeleteDish($dish->id);

        // Assert the dish is permanently deleted
        $this->assertDatabaseMissing('dishes', ['id' => $dish->id]);
    }
}
