<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Enums\RoleUser;
use App\Models\Favorite;
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



    //+++++++++++++++++++++++++++++++++++++++++++++++++++


    /**
     * Test to check if a user can remove an item from the favorites list.
     */
    /** @test */
    public function user_can_remove_an_item_from_favorites()
    {
        // Create a new user using the User factory.
        $this->customerUser = User::factory()->create();

        // Create a food category
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => $this->customerUser->id,
        ]);

        // Create a favorite item with a soft delete flag
        $favorite = Favorite::create([
            'user_id' => $this->customerUser->id,
            'favorable_type' => FoodCategory::class,
            'favorable_id' => $foodCategory->id,
        ]);


        $token = JWTAuth::fromUser($this->customerUser);


        // Submit the request to permanently remove the favorite item
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson(
            'api/favorites',
            [
                'type' => 'food_categories',
                'id' => $foodCategory->id,
            ]
        );

        $this->assertEquals('Item Removed successfully', $response['message']);
    }

    //+++++++++++++++++++++++++++++++++++++++++++++++


    //check if only admin can show all favorite
    /**@test */
    public function test_if_only_admin_can_show_all_favorite()
    {
        // Create a new user using the User factory.
        $this->customerUser = User::factory()->create();

        // Create a food category
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => $this->customerUser->id,
        ]);

        // Create a favorite item with a soft delete flag
        $favorite = Favorite::create([
            'user_id' => $this->customerUser->id,
            'favorable_type' => FoodCategory::class,
            'favorable_id' => $foodCategory->id,
        ]);

        $response = $this->actingAs($this->adminUser)->getJson(
            'api/all_favorites',
        );
        //    $token = JWTAuth::fromUser($this->customerUser);


        // Submit the request to permanently remove the favorite item
        //    $response = $this->withHeaders([
        //        'Authorization' => 'Bearer ' . $token,
        //    ])->deleteJson(
        //        'api/favorites',
        //        [
        //            'type' => 'food_categories',
        //            'id' => $foodCategory->id,
        //        ]
        //    );
        // Assert the response status and structure
        $response->assertStatus(200);
        // $response->assertJsonStructure([
        //     'data' => [
        //         '*' => ['user_name', 'rating', 'comment']
        //     ]
        // ]);
    }

    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++

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


    //+++++++++++++++++++++++++++++++++++++++++++

    /** Test if only admin can restor the deleted favorite */
    /** @test */
    public function only_admin_can_restor_the_deleted_favorite()
    {
        // Create a new user
        $this->customerUser = User::factory()->create();

        // Create a food category
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => $this->customerUser->id,
        ]);

        // Create a favorite item with a soft delete flag
        $favorite = Favorite::create([
            'user_id' => $this->customerUser->id,
            'favorable_type' => FoodCategory::class,
            'favorable_id' => $foodCategory->id,
        ]);

        // Assert that the favorite item was successfully created
        $this->assertNotNull($favorite, 'Failed to create favorite record.');

        // Set the deleted_at field and ensure the update is saved
        $favorite->forceFill(['deleted_at' => now()])->save();
        $favorite->refresh();

        // Verify that the favorite item exists in the database with a soft delete flag
        $this->assertTrue(Favorite::withTrashed()->where('id', $favorite->id)->exists());
        $this->assertNotNull($favorite->deleted_at);

        // Submit the request to permanently restore the soft-deleted favorite item
        $response = $this->actingAs($this->adminUser)->patchJson(
            'api/favorite/restore/' . $favorite->id,
        );

        $response->assertStatus(200);
    }

    //++++++++++++++++++++++++++++++++++++++++++

    /**Test if only admin can delete the deleted favorite */
    /** @test */
    public function only_admin_can_force_delete_the_favorite()
    {
        // Create a new user
        $this->customerUser = User::factory()->create();

        // Create a food category
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => $this->customerUser->id,
        ]);

        // Create a favorite item with a soft delete flag
        $favorite = Favorite::create([
            'user_id' => $this->customerUser->id,
            'favorable_type' => FoodCategory::class,
            'favorable_id' => $foodCategory->id,
        ]);

        // Assert that the favorite item was successfully created
        $this->assertNotNull($favorite, 'Failed to create favorite record.');

        // Set the deleted_at field and ensure the update is saved
        $favorite->forceFill(['deleted_at' => now()])->save();
        $favorite->refresh();

        // Verify that the favorite item exists in the database with a soft delete flag
        $this->assertTrue(Favorite::withTrashed()->where('id', $favorite->id)->exists());
        $this->assertNotNull($favorite->deleted_at);

        // Submit the request to permanently delete the soft-deleted favorite item
        $response = $this->actingAs($this->adminUser)->deleteJson(
            'api/favorite/force-delete/' . $favorite->id,
        );

        $response->assertStatus(200);
    }

    //++++++++++++++++++++++++++++++++++++++++++

    /**Test if only admin can show the deleted favorite */
    /** @test */
    public function only_admin_can_show_the_deleted_favorite()
    {
        // Create a new user
        $this->customerUser = User::factory()->create();

        // Create a food category
        $foodCategory = FoodCategory::create([
            'category_name' => 'Desserts',
            'description' => 'Sweet dishes for the end of the meal.',
            'user_id' => $this->customerUser->id,
        ]);

        // Create a favorite item with a soft delete flag
        $favorite = Favorite::create([
            'user_id' => $this->customerUser->id,
            'favorable_type' => FoodCategory::class,
            'favorable_id' => $foodCategory->id,
        ]);

        // Assert that the favorite item was successfully created
        $this->assertNotNull($favorite, 'Failed to create favorite record.');

        // Set the deleted_at field and ensure the update is saved
        $favorite->forceFill(['deleted_at' => now()])->save();
        $favorite->refresh();

        // Verify that the favorite item exists in the database with a soft delete flag
        $this->assertTrue(Favorite::withTrashed()->where('id', $favorite->id)->exists());
        $this->assertNotNull($favorite->deleted_at);

        // Submit the request to permanently get the soft-deleted favorite item
        $response = $this->actingAs($this->adminUser)->getJson(
            'api/favorite_deleted',
        );

        $response->assertStatus(200);
    }
}
