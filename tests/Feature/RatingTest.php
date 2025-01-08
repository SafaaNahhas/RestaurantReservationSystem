<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Models\Rating;
use App\Enums\RoleUser;
use App\Models\Reservation;
use Spatie\Permission\Models\Role;
use App\Services\Rating\RatingService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RatingTest extends TestCase
{
    use DatabaseTransactions; // This will wrap each test in a transaction

    protected $adminUser;
    protected $customerUser;
    protected $otherCustomerUser;
    protected mixed $ratingService;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        Role::firstOrCreate(['name' => RoleUser::Admin->value]);
        Role::firstOrCreate(['name' => RoleUser::Customer->value]);

        // Inject the rating service
        $this->ratingService = app(RatingService::class);

        // Create admin and customer users using factories
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(RoleUser::Admin->value);

        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole(RoleUser::Customer->value);

        $this->otherCustomerUser = User::factory()->create();
        $this->otherCustomerUser->assignRole(RoleUser::Customer->value);
    }

    /** @test 
     * check if user can fetch all rating
     */
    public function user_can_fetch_all_ratings()
    {
        // Create some ratings associated with users
        Rating::factory()->count(5)->create([
            'user_id' => $this->customerUser->id
        ]);

        // Act as the customer user and fetch ratings
        $response = $this->actingAs($this->customerUser)->get('/api/ratings');

        // Assert the response status and structure
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['user_name', 'rating', 'comment']
            ]
        ]);
    }


    /** @test 
     * check if user can store rating to his resrvation
     */
    public function user_can_store_a_new_rating()
    {

        // Create a reservation associated with users
        $reservation = Reservation::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        // Rating data
        $ratingData = [
            'rating' => 5,
            'comment' => 'Excellent service!',
        ];

        // Submit the request as an existing user with the IDs in the link
        $response = $this->actingAs($this->customerUser)->postJson(
            '/api/ratings?user_id=' . $this->customerUser->id . '&reservation_id=' . $reservation->id,
            $ratingData
        );

        // Checking the response status
        $response->assertStatus(201);

        // Checking the presence of the assessment in the database
        $this->assertDatabaseHas('ratings', [
            'reservation_id' => $reservation->id,
            'rating' => 5,
            'comment' => 'Excellent service!',
        ]);
    }






    /** @test
     * check if user can rate other reservation
     */
    public function user_prevents_unauthorized_rating_creation()
    {
        // Create a reservation associated with users

        $reservation = Reservation::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        // Rating data
        $ratingData = [
            'rating' => 5,
            'comment' => 'Excellent service!',
        ];

        // Submit the request as an existing user with the IDs in the link
        $response = $this->actingAs($this->otherCustomerUser)->postJson(
            '/api/ratings?user_id=' . $this->otherCustomerUser->id . '&reservation_id=' . $reservation->id,
            $ratingData
        );

        // Checking the response status
        $response->assertStatus(403);

        $response->assertJson([
            'error' => 'You can only rate your own reservations.'
        ]);
    }

    /** @test
     * check if user can view his ratin
     */
    public function user_can_view_a_specific_rating()
    {

        // Create a reservation associated with users
        $reservation = Reservation::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        //create a rating associated with users
        $rating =  Rating::create([

            'user_id' => $this->customerUser->id,
            'reservation_id' => $reservation->id,
            'rating' => 3,
            'comment' => "good",
        ]);

        // Submit the request as an existing user with the IDs in the link

        $response = $this->actingAs($this->customerUser)->get('/api/ratings/' . $rating->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'rating' => 3,
                'comment' => "good",
                'user_name' => $this->customerUser->name,
            ],
        ]);
    }

    //     /** @test */
    //     public function it_can_update_a_rating()
    //     {
    //         $rating = Rating::factory()->create(['user_id' => $this->customerUser->id]);

    //         $updatedData = [
    //             'rating' => 4,
    //             'comment' => 'Updated comment.'
    //         ];

    //         $response = $this->actingAs($this->customerUser)->putJson('/api/ratings/' . $rating->id, $updatedData);

    //         $response->assertStatus(200);
    //         $response->assertJson([
    //             'status' => 'success',
    //             'message' => 'Rating updated successfully'
    //         ]);

    //         $this->assertDatabaseHas('ratings', $updatedData);
    //     }

    //     /** @test */
    //     public function it_can_delete_a_rating()
    //     {
    //         $rating = Rating::factory()->create(['user_id' => $this->customerUser->id]);

    //         $response = $this->actingAs($this->customerUser)->delete('/api/ratings/' . $rating->id);

    //         $response->assertStatus(200);
    //         $response->assertJson([
    //             'status' => 'success',
    //             'message' => 'Rating deleted successfully'
    //         ]);

    //         $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
    //     }

    //     /** @test */
    //     public function it_prevents_unauthorized_rating_deletion()
    //     {
    //         $rating = Rating::factory()->create();

    //         $response = $this->actingAs($this->customerUser)->delete('/api/ratings/' . $rating->id);

    //         $response->assertStatus(403);
    //         $response->assertJson([
    //             'error' => true,
    //             'message' => 'This action is unauthorized.'
    //         ]);
    //     }
}
