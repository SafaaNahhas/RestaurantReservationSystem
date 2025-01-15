<?php
namespace Tests\Feature\Event;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Enums\RoleUser;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EventControllerTest extends TestCase
{
    use DatabaseTransactions; // Use the DatabaseTransactions trait for database rollbacks

    protected $adminUser;
    protected $customerUser;

    // Set up necessary data before each test
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure roles exist in the database
        Role::firstOrCreate(['name' => RoleUser::Admin->value]);
        Role::firstOrCreate(['name' => RoleUser::Customer->value]);


        // Create users (admin and customer) using factories and assign roles
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole(RoleUser::Admin->value);

        $this->customerUser = User::factory()->create();
        $this->customerUser->assignRole(RoleUser::Customer->value);
    }

    /**@test */
    // Test to check if the application can list all events
    public function test_it_can_list_all_events()
    {
        // Create some events in the database using a factory
        Event::factory()->count(3)->create();

        // Get all events as an admin user
        $response = $this->actingAs($this->adminUser)->get('/api/event');

        // Assert that the response is successful and contains the expected message
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Events retrieved successfully.',
        ]);
    }

    /**@test */
    // Test for creating an event with valid data
    public function test_it_can_create_an_event()
    {


        // Event data to create a new event
        $eventData = [
            'event_name' => 'Sample Event',
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
        ];

        // Create an event using the post request
        $response = $this->actingAs($this->adminUser)->postJson('/api/event', $eventData);

        // Assert the event was created successfully and contains the correct data
        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Event created successfully.',
        ]);

    }

    // Test to check if the event creation fails when the name is missing
    public function test_it_fails_to_create_event_without_name()
    {

        // Event data missing the 'event_name'
        $eventData = [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
        ];

        // Try to create the event
        $response = $this->actingAs($this->adminUser)->postJson('/api/event', $eventData);

        // Assert that validation fails for the missing 'event_name'
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['event_name']);
    }

    // Test to check if the event creation fails with invalid date range (end date before start date)
    public function test_it_fails_to_create_event_with_invalid_dates()
    {


        // Event data with invalid dates (end date before start date)
        $eventData = [
            'event_name' => 'Invalid Event',
            'start_date' => '2025-03-02',
            'end_date' => '2025-03-01', // Invalid date order
        ];

        // Try to create the event
        $response = $this->actingAs($this->adminUser)->postJson('/api/event', $eventData);

        // Assert that validation fails for the invalid date
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }
    /**@test */
    // Test to show details of a specific event
    public function test_it_can_show_event_details()
    {
        // Create an event
        $event = Event::factory()->create();

        // Fetch the event details by its ID
        $response = $this->actingAs($this->adminUser)->getJson("/api/event/{$event->id}");

// Assert that the event details are returned successfully
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Event retrieved successfully.',
        ]);
        $response->assertJsonFragment([
            'event_name' => $event->event_name,
        ]);
    }

    /**@test */
    // Test for updating an event's details
    public function test_it_can_update_an_event()
    {
        // Create an event to update
        $event = Event::factory()->create();

        // New data to update the event
        $updatedData = [
            'event_name' => 'Updated Event Name',
            'start_date' => '2025-02-05',
            'end_date' => '2025-02-06',
        ];

        // Send a PUT request to update the event
        $response = $this->actingAs($this->adminUser)->putJson("/api/event/{$event->id}", $updatedData);

        // Assert that the update was successful and the new data is returned
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Event updated successfully.',
        ]);
        $response->assertJsonFragment($updatedData);
    }

    // Test to ensure updating an event fails with invalid date range (end date before start date)
    public function test_it_fails_to_update_event_with_invalid_dates()
    {
        $event = Event::factory()->create();

        // Invalid event data with end date before start date
        $updatedData = [
            'event_name' => 'Updated Event Name',
            'start_date' => '2025-02-06',
            'end_date' => '2025-02-05', // Invalid date
        ];

        // Try to update the event with invalid dates
        $response = $this->actingAs($this->adminUser)->putJson("/api/event/{$event->id}", $updatedData);

        // Assert that validation fails for the invalid end date
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end_date']);
    }

    /**@test */
    // Test to delete an event
    public function test_it_can_delete_an_event()
    {
        // Create an event to delete
        $event = Event::factory()->create();

        // Send a DELETE request to delete the event
        $response = $this->actingAs($this->adminUser)->deleteJson("/api/event/{$event->id}");

        // Assert that the event was deleted successfully
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Event deleted successfully.',
        ]);

        // Assert the event is soft-deleted
        $this->assertSoftDeleted('events', ['id' => $event->id]);
    }

    /**@test */
    // Test to show all soft-deleted events
    public function test_it_can_show_deleted_events()
    {
        // Soft delete an event
        $event = Event::factory()->create();
        $event->delete();

        // Retrieve soft-deleted events
        $response = $this->actingAs($this->adminUser)->getJson('/api/event/showDeleted');

        // Assert that the deleted event is returned
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => null,
        ]);
        $response->assertJsonFragment([
            'id' => $event->id,
        ]);
    }

    /**@test */
    // Test to restore a soft-deleted event
    public function test_it_can_restore_a_deleted_event()
    {
        // Soft delete an event
        $event = Event::factory()->create();
        $event->delete();

        // Restore the event
        $response = $this->actingAs($this->adminUser)->putJson("/api/event/{$event->id}/restore");

// Assert the event is restored successfully
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);

        // Assert the correct message in 'data' instead of 'message'
        $response->assertJsonFragment([
            'data' => 'Event restored successfully.',
        ]);

        // Assert the event is no longer soft-deleted
        $this->assertNotSoftDeleted($event);
    }


    /**@test */
    // Test to permanently delete a soft-deleted event
    public function test_it_can_permanently_delete_a_deleted_event()
    {
        // Soft delete an event
        $event = Event::factory()->create();
        $event->delete();

        // Permanently delete the event
        $response = $this->actingAs($this->adminUser)->deleteJson("/api/event/{$event->id}/delete");

        // Assert that the event is permanently deleted
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Event permanently deleted.',
        ]);

        // Assert the event is removed from the database
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
