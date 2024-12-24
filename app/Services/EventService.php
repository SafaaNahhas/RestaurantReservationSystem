<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Event;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendCustomerCreateEvent;

/**
 * EventService - Provides functionality for managing events.
 *
 * This service class handles the business logic for managing events, including
 * fetching, creating, updating, and deleting events. It includes error handling
 * and logs issues that arise during these operations.
 */
class EventService
{
    /**
     * Get all events with pagination.
     *
     * Retrieves all events from the database with related reservation data.
     * It paginates the results to limit the number of records per page.
     *
     * @param int $perPage The number of items to display per page (default is 10).
     * @return \Illuminate\Pagination\LengthAwarePaginator
     * @throws \RuntimeException if an error occurs while fetching events.
     */
    public function getAllEvents()
    {
        try {
            // Retrieve paginated events with their related reservation.
            return Event::paginate(10);
        } catch (Exception $e) {
            // Log the error and throw a runtime exception.
            Log::error('Error fetching events: ' . $e->getMessage());
            throw new \RuntimeException('Unable to fetch events.');
        }
    }

    /**
     * Create a new event.
     *
     * Creates a new event in the database using the provided data.
     * The event is persisted in the database and returned.
     *
     * @param array $data The data to create a new event.
     * @return \App\Models\Event
     * @throws \RuntimeException if an error occurs while creating the event.
     */
    public function createEvent(array $data)
    {
        try {
            $event = Event::create($data);
            // get all email to customer 
            $customers = Role::findByName('Customer')->users;
            // send email
            SendCustomerCreateEvent::dispatch($event, $customers);
            return $event;
        } catch (Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            throw new \RuntimeException('Unable to create event.');
        }
    }

    /**
     * Update an existing event.
     *
     * Updates the details of a given event based on the provided data.
     * The updated event is returned.
     *
     * @param Event $event The event instance to update.
     * @param array $data The data to update the event with.
     * @return \App\Models\Event
     * @throws \RuntimeException if an error occurs while updating the event.
     */
    public function updateEvent(Event $event, array $data)
    {
        try {
            // Update the event and return the updated instance.
            $event->update($data);
            return $event;
        } catch (Exception $e) {
            // Log the error and throw a runtime exception.
            Log::error('Error updating event (ID: ' . $event->id . '): ' . $e->getMessage());
            throw new \RuntimeException('Unable to update event.');
        }
    }

    /**
     * Delete an event.
     *
     * Deletes the specified event from the database.
     *
     * @param Event $event The event instance to delete.
     * @return bool true if the event was deleted successfully.
     * @throws \RuntimeException if an error occurs while deleting the event.
     */
    public function deleteEvent(Event $event)
    {
        try {
            // Delete the event and return true to indicate success.
            $event->delete();
        } catch (Exception $e) {
            // Log the error and throw a runtime exception.
            Log::error('Error deleting event (ID: ' . $event->id . '): ' . $e->getMessage());
            throw new \RuntimeException('Unable to delete event.');
        }
    }
}
