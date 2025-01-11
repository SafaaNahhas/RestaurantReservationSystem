<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Restaurant;
use App\Models\Email;
use App\Models\PhoneNumber;
use App\Models\Image;

class RestaurantTest extends TestCase
{
    use DatabaseTransactions;
    protected $adminUser;
    protected $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin and customer users using factories
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole("admin");  // Assign "admin" role to the admin user
        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole("customer");  // Assign "customer" role to the customer user
    }


    public function testGetRestaurantData()
    {
        // Call the API endpoint to fetch restaurant data as the admin user
        $response = $this->actingAs($this->adminUser)->get('/api/restaurant');  // Assuming '/restaurant' is the correct endpoint

        // Check if the request was successful (status 200)
        $response->assertStatus(200);

        // Check if the response contains the expected restaurant data
        $response->assertJson([
            'status' => 'success',
            'message' => 'Restaurant data',
            'data' => [
                [
                    'id' => 1,
                    'name' => 'Alsaadde_Restaurant',
                    'location' => 'Lattacia',
                    'opening_hours' => '9:00 AM',
                    'closing_hours' => '11:00 PM',
                    'rating' => 5,
                    'website' => 'https://www.alsaadderestaurant.com',
                    'description' => 'Alsaadde Restaurant - Best Food in Town',
                    'emails' => [
                        [
                            'id' => 1,
                            'email' => 'mohammedalmosaytf@gmail.com',
                            'description' => 'Restaurant Manager Email',
                            'restaurant_id' => 1,
                        ]
                    ],
                    'phone_numbers' => [
                        [
                            'id' => 1,
                            'PhoneNumber' => '0991851269',
                            'description' => 'Restaurant Manager Phone Number',
                            'restaurant_id' => 1,
                        ]
                    ],
                    'images' => []  // No images provided in this test
                ]
            ]
        ]);
    }

    /** @test */

    public function it_cant_store_resturant_data()
    {
        $restaurantData = [
            'name' => 'Alsaadde_Restaurant',
            'location' => 'Lattacia',
            'opening_hours' => '9:00 AM',
            'closing_hours' => '11:00 PM',
            'rating' => 5,
            'website' => 'https://www.alsaadderestaurant.com',
        ];

        // Attempt to create a new restaurant and check for error
        $response = $this->actingAs($this->adminUser)->postJson('/api/restaurant', $restaurantData);

        // Assert that the response status is 500 (Internal Server Error)
        $response->assertStatus(500);

        // Check if the response contains the correct error message
        $response->assertJson([
            "status" => "error",
            "message" => "Cannot insert restaurant data: Only one restaurant allowed."
        ]);
    }

    /** @test */

    public function validation_error_resturant_data()
    {
        // Attempt to create a new restaurant with no data and check for validation errors
        $response = $this->actingAs($this->adminUser)->postJson('/api/restaurant');

        // Assert the response status is 422 (Unprocessable Entity)
        $response->assertStatus(422);

        // Check if the response contains the expected validation error messages
        $response->assertJson([
            "status" => "error",
            "message" => "Validation failed.",
            "errors" => [
                "name" => ["The name field is required."],
                "location" => ["The location field is required."],
                "opening_hours" => ["The opening hours field is required."],
                "rating" => ["The rating field is required."],
                "website" => ["The website field is required."]
            ]
        ]);
    }

    /** @test */

    public function update_restaurant_data()
    {
        $restaurantData = [
            'name' => 'Alsaadde_Restaurant',
            'location' => 'Lattacia',
            'opening_hours' => '9:00 AM',
            'closing_hours' => '11:00 PM',
            'rating' => 5,
            'website' => 'https://www.alsaadderestaurant.com',
        ];

        // Get the current restaurant data
        $restaurant = $this->actingAs($this->adminUser)->get('/api/restaurant');
        $restaurantData = json_decode($restaurant->getContent(), true);
        $restaurantId = $restaurantData['data'][0]['id'];
        $restaurantcreatedby = $restaurantData['data'][0]['created_at'];
        $restaurantupdataedby = $restaurantData['data'][0]['updated_at'];

        // Update the restaurant details via the API
        $response = $this->actingAs($this->adminUser)->putJson('/api/restaurant/' . $restaurantId, $restaurantData);

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the updated restaurant data is returned in the response
        $response->assertJson([
            'status' => 'success',
            'message' => 'Restaurant data updated',
            'data' => [
                'id' => 1,
                'name' => 'Alsaadde_Restaurant',
                'location' => 'Lattacia',
                'opening_hours' => '9:00 AM',
                'closing_hours' => '11:00 PM',
                'rating' => 5,
                'website' => 'https://www.alsaadderestaurant.com',
                'description' => 'Alsaadde Restaurant - Best Food in Town',
                'created_at' =>  $restaurantcreatedby,
                'updated_at' =>   $restaurantupdataedby,
            ]
        ]);
    }

    /** @test */

    public function testDeleteRestaurantData()
    {
        // Create a restaurant record to delete
        $restaurant = Restaurant::factory()->create();

        // Call the API to delete the restaurant by its ID
        $response = $this->actingAs($this->adminUser)->delete('/api/restaurant/' . $restaurant->id);

        // Assert the response status is 200 (OK) for successful deletion
        $response->assertStatus(200);

        // Assert the response message indicates successful deletion
        $response->assertJson([
            'status' => 'success',
            'message' => 'Restaurant data deleted',  // Check for correct message
            'data' => null,  // No data should be returned after deletion
        ]);
    }

    /** @test */

    public function testDeleteEmail()
    {
        // Create an email record to delete
        $email = Email::factory()->create();

        // Call the API to delete the email by its ID
        $response = $this->actingAs($this->adminUser)->delete('/api/restaurant/email/' . $email->id);

        // Assert the response status is 200 (OK) for successful deletion
        $response->assertStatus(200);

        // Assert the response message indicates successful deletion
        $response->assertJson([
            "status" => "success",
            "message" => "Email deleted",
        ]);
    }

    /** @test */

    public function testDeletePhoneNumber()
    {
        // Create a phone number record to delete
        $phoneNumber = PhoneNumber::factory()->create();

        // Call the API to delete the phone number by its ID
        $response = $this->actingAs($this->adminUser)->delete('/api/restaurant/phone-number/' . $phoneNumber->id);

        // Assert the response status is 200 (OK) for successful deletion
        $response->assertStatus(200);

        // Assert the response message indicates successful deletion
        $response->assertJson([
            "status" => "success",
            "message" => "Phone number deleted",
        ]);
    }

    /** @test */

    public function testSoftDeleteRestaurantImage()
    {
        $restaurant = Restaurant::factory()->create();
        $image = Image::factory()->create(['imagable_id' => $restaurant->id, 'imagable_type' => Restaurant::class]);

        // Perform soft deletion of the image
        $response = $this->actingAs($this->adminUser)->delete('/api/restaurant/' . $restaurant->id . '/imageSoftDelet/' . $image->id);

        // Assert the correct response indicating the image was soft deleted
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Restaurant Image soft deleted',
        ]);
    }

    /** @test */

    public function testRestoreRestaurantImage()
    {
        $restaurant = Restaurant::factory()->create();
        $image = Image::factory()->create(['imagable_id' => $restaurant->id, 'imagable_type' => Restaurant::class]);

        // Soft delete the image first
        $image->delete();

        // Perform restoration of the image
        $response = $this->actingAs($this->adminUser)->post('/api/restaurant/' . $restaurant->id . '/imageRestore/' . $image->id);

        // Assert the correct response indicating the image was restored
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Restaurant Image restored',
        ]);
    }

    /** @test */

    public function testPermanentlyDeleteImage()
    {
        $restaurant = Restaurant::factory()->create();
        $image = Image::factory()->create(['imagable_id' => $restaurant->id, 'imagable_type' => Restaurant::class]);

        // Soft delete the image first
        $image->delete();

        // Perform permanent deletion of the image
        $response = $this->actingAs($this->adminUser)->delete('/api/restaurant/' . $restaurant->id . '/imageDdelet/' . $image->id);

        // Assert the correct response indicating the image was permanently deleted
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Restaurant Image permanently deleted',
        ]);
    }
}
