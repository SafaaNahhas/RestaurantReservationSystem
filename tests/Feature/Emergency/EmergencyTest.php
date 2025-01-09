<?php

namespace Tests\Feature\Emergency;

use App\Jobs\SendEmergencyClosureJob;
use App\Models\Emergency;
use App\Models\Reservation;
use App\Services\EmergencyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;


/**
 * Class EmergencyTest
 *
 * This test suite validates the functionality of the Emergency management feature.
 * It covers creating, retrieving, updating, and deleting emergencies, as well as ensuring
 * that related reservations are correctly updated during emergencies.
 *
 * @package Tests\Feature\Emergency
 */
class EmergencyTest extends TestCase
{
    use DatabaseTransactions;


    /**
     * Authenticate as an admin user and retrieve a valid token for requests.
     *
     * @return string The access token for authenticated requests.
     */
    protected function authenticateAs()
    {
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('original.access_token');
        $this->assertNotNull($token, 'Token should not be null');
        return $token;
    }

    /**
     * Test storing a new emergency and verifying that overlapping reservations are canceled.
     *
     * - Creates test reservations with different timeframes.
     * - Submits a POST request to store a new emergency.
     * - Asserts the cancellation of overlapping reservations and retention of unaffected ones.
     *
     * @return void
     */
    public function test_store_emergency_with_cancel_reservations()
    {
        // Create test reservations
        $reservation1 = Reservation::factory()->create([
            'start_date' => "2025-01-30 10:00:00",
            'end_date' => "2025-01-30 12:00:00",
            'status' => 'confirmed',
        ]);
        $reservation2 = Reservation::factory()->create([
            'start_date' => "2025-01-09 10:00:00",
            'end_date' => "2025-01-09 12:00:00",
            'status' => 'confirmed',
        ]);
        $token = $this->authenticateAs();
        $response = $this->withToken(
            $token
        )->postJson('api/emergencies', [
            "name"       => "fire in kitchen",
            "start_date" => "2025-01-09 10:00:00",
            "end_date"   => "2025-01-11 10:00:00",
        ]);
        $response->assertStatus(201);
        // Assert: Affected reservations are canceled
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation2->id,
            'status' => 'cancelled',
        ]);

        // Assert: Unaffected reservations remain unchanged
        $this->assertDatabaseHas('reservations', [
            'id' => $reservation1->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test retrieving a list of all emergencies.
     *
     * - Creates multiple emergency records.
     * - Submits a GET request to retrieve all emergencies.
     * - Asserts a successful response and correct message.
     *
     * @return void
     */
    public function test_list_all_emergencies()
    {
        Emergency::factory()->count(3)->create();
        $token = $this->authenticateAs();

        $response = $this->withToken(
            $token
        )->getJson('/api/emergencies');

        $response->assertStatus(200)
            ->assertJson([
                "message" => "Emergencies Retrieved Successfully",
            ]);
    }


    /**
     * Test updating an existing emergency.
     *
     * - Creates a single emergency record.
     * - Submits a PUT request with updated data.
     * - Asserts a successful response and database update.
     *
     * @return void
     */
    public function test_update_emergency()
    {
        $emergency = Emergency::factory()->create();

        $updatedData = [
            'description' => 'Updated description for emergency',
        ];
        $token = $this->authenticateAs();
        $response = $this->withToken(
            $token
        )->putJson("/api/emergencies/{$emergency->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment($updatedData);

        $this->assertDatabaseHas('emergencies', $updatedData);
    }


    /**
     * Test retrieving details of a specific emergency.
     *
     * - Creates a single emergency record.
     * - Submits a GET request to fetch the emergency.
     * - Asserts a successful response and correct message.
     *
     * @return void
     */
    public function test_show_emergency()
    {
        $emergency = Emergency::factory()->create();

        $token = $this->authenticateAs();
        $response = $this->withToken(
            $token
        )->getJson("/api/emergencies/{$emergency->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                "message" => "Emergency Retrieved Successfully",
            ]);
    }

    /**
     * Test deleting an emergency record.
     *
     * - Creates a single emergency record.
     * - Submits a DELETE request to remove the emergency.
     * - Asserts a successful response and verifies deletion in the database.
     *
     * @return void
     */
    public function test_delete_emergency()
    {
        $emergency = Emergency::factory()->create();

        $token = $this->authenticateAs();
        $response = $this->withToken(
            $token
        )->deleteJson("/api/emergencies/{$emergency->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                "message" => "Emergency Deleted Successfully",
            ]);

        $this->assertDatabaseMissing('emergencies', ['id' => $emergency->id]);
    }
}
