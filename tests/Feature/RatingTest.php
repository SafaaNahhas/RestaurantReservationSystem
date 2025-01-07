<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Table;
use App\Models\Rating;
use App\Enums\RoleUser;
use App\Models\Reservation;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $customerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = Role::create([
            'name' => RoleUser::Admin->value
        ]);

        $customer = Role::create([
            'name' => RoleUser::Customer->value
        ]);
            

        // Create admin and customer users using factories
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($admin);

        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole($customer);
    }

    /** @test */
    public function it_can_fetch_all_ratings()
    {
        // Create some ratings
        Rating::factory()->count(5)->create();
        Table::factory()->count(5)->create();

        // Fetch ratings as an admin
        $response = $this->actingAs($this->customerUser)->get('/api/ratings');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['user_name','rating', 'comment']
            ]
        ]);
    }

    /** @test */
    public function it_can_store_a_new_rating()
    {
        $reservation = Reservation::factory()->create(['user_id' => $this->customerUser->id]);

        $ratingData = [
            'reservation_id' => $reservation->id,
            'rating' => 5,
            'comment' => 'Excellent service!'
        ];

        $response = $this->actingAs($this->customerUser)->postJson('/api/ratings', $ratingData);

        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'rating created successfully'
        ]);

        $this->assertDatabaseHas('ratings', $ratingData);
    }

    /** @test */
    public function it_prevents_unauthorized_rating_creation()
    {
        $reservation = Reservation::factory()->create();

        $ratingData = [
            'reservation_id' => $reservation->id,
            'rating' => 4,
            'comment' => 'Good service.'
        ];

        $response = $this->actingAs($this->customerUser)->postJson('/api/ratings', $ratingData);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => true,
            'message' => 'You can only rate your own reservations.'
        ]);
    }

    /** @test */
    public function it_can_view_a_specific_rating()
    {
        $rating = Rating::factory()->create();

        $response = $this->actingAs($this->adminUser)->get('/api/ratings/' . $rating->id);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'user_id' => $rating->user_id,
                'rating' => $rating->rating,
                'comment' => $rating->comment
            ]
        ]);
    }

    /** @test */
    public function it_can_update_a_rating()
    {
        $rating = Rating::factory()->create(['user_id' => $this->customerUser->id]);

        $updatedData = [
            'rating' => 4,
            'comment' => 'Updated comment.'
        ];

        $response = $this->actingAs($this->customerUser)->putJson('/api/ratings/' . $rating->id, $updatedData);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Rating updated successfully'
        ]);

        $this->assertDatabaseHas('ratings', $updatedData);
    }

    /** @test */
    public function it_can_delete_a_rating()
    {
        $rating = Rating::factory()->create(['user_id' => $this->customerUser->id]);

        $response = $this->actingAs($this->customerUser)->delete('/api/ratings/' . $rating->id);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Rating deleted successfully'
        ]);

        $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
    }

    /** @test */
    public function it_prevents_unauthorized_rating_deletion()
    {
        $rating = Rating::factory()->create();

        $response = $this->actingAs($this->customerUser)->delete('/api/ratings/' . $rating->id);

        $response->assertStatus(403);
        $response->assertJson([
            'error' => true,
            'message' => 'This action is unauthorized.'
        ]);
    }
}
