<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Enums\RoleUser;
use App\Models\FoodCategory;
use Spatie\Permission\Models\Role;
use App\Services\Favorite\FavoriteService;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FavoriteTest extends TestCase
{
    use DatabaseTransactions; // This will wrap each test in a transaction

    protected $adminUser;
    protected $customerUser;
    protected $otherCustomerUser;
    protected mixed $favoriteService;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::firstOrCreate(['name' => RoleUser::Admin->value]);
        Role::firstOrCreate(['name' => RoleUser::Customer->value]);

        // Inject the rating service
        $this->favoriteService = app(FavoriteService::class);

        // Create admin and customer users using factories
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(RoleUser::Admin->value);

        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole(RoleUser::Customer->value);

        $this->otherCustomerUser = User::factory()->create();
        $this->otherCustomerUser->assignRole(RoleUser::Customer->value);
    }


    /**
     * Test to check if a user can add an item to the favorites list.
     */
    /** @test */
    public function user_can_add_an_item_to_favorites()
    {
        // Create a new user using the User factory.
        $this->customerUser = User::factory()->create();

        // Create a food category to be added to the favorites.
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => 1,
        ]);

        // Generate a JWT token for the created user.
        $token = JWTAuth::fromUser($this->customerUser);

        // Make a POST request to add the food category to the user's favorites via API.
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', [
            'type' => 'food',
            'id' => $foodCategory->id,
        ]);

        // Use the favoriteService to add the item to the user's favorites.
        $response = $this->favoriteService->addToFavorites($this->customerUser, 'food', $foodCategory->id);

        // Assert that the response indicates a successful operation.
        $this->assertEquals('success', $response['status']);
        $this->assertEquals('Added to favorites successfully', $response['message']);
    }

    /**
     * Test to check if a user can remove an item from the favorites list.
     */
    /** @test */
    public function user_can_remove_an_item_from_favorites()
    {
        // Create a new user using the User factory.
        $this->customerUser = User::factory()->create();

        // Create a food category to be added and removed from the favorites.
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => 1,
        ]);

        // Generate a JWT token for the created user.
        $token = JWTAuth::fromUser($this->customerUser);

        // Add the food category to the user's favorites via API.
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', [
            'type' => 'food',
            'id' => $foodCategory->id,
        ]);

        // Use the favoriteService to add the item to the user's favorites.
        $response1 = $this->favoriteService->addToFavorites($this->customerUser, 'food', $foodCategory->id);

        // Use the favoriteService to remove the item from the user's favorites.
        $response2 = $this->favoriteService->removeFromFavorites($this->customerUser, 'food', $foodCategory->id);

        // Assert that the response indicates a successful operation.
        $this->assertEquals('success', $response2['status']);
        $this->assertEquals('Item Removed successfully', $response2['message']);
    }

    /**
     * Test to check if a user can retrieve the list of their favorite items.
     */
    /** @test */
    public function user_can_show_the_list_of_favorites()
    {
        // Create a new user using the User factory.
        $this->customerUser = User::factory()->create();

        // Create a food category to be added to the favorites.
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => 1,
        ]);

        // Generate a JWT token for the created user.
        $token = JWTAuth::fromUser($this->customerUser);

        // Add the food category to the user's favorites via API.
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/favorites', [
            'type' => 'food',
            'id' => $foodCategory->id,
        ]);

        // Use the favoriteService to add the item to the user's favorites.
        $response1 = $this->favoriteService->addToFavorites($this->customerUser, 'food', $foodCategory->id);

        // Make a GET request to retrieve the user's favorites via API.
        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/favorites');

        // Assert that the response contains the correct favorite item details.
        $response2->assertJsonFragment([
            'type' => 'FoodCategory',
            'value' => $foodCategory->category_name,
        ]);
    }
}
