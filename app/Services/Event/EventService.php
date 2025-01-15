<?php

namespace App\Services\Event;

use Exception;
use App\Models\Event;
use App\Services\NotificationLogService;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendCustomerCreateEvent;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        $event = Event::create($data);
        // get all email to customer
        $customers = Role::findByName('Customer')->users;
        // send email

        $notificationLogService = new NotificationLogService();
        SendCustomerCreateEvent::dispatch($event, $customers, false, $notificationLogService);
        return $event;
        try {
        } catch (Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            throw new \RuntimeException('Unable to create event.');
        }
    }

    /**
     * Retrieve an event by ID with its reservations.
     *
     * @param int $id
     * @return Event|null
     */
    public function getEventById(int $id)
    {
        try {
            // Attempt to find the event
            $event = Event::findOrFail($id); // Throws ModelNotFoundException if not found
            return $event;
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [   'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));
        } catch (\Exception $e) {
            // Handle any other unexpected errors
            throw new \Exception("An error occurred while retrieving the event: " . $e->getMessage());
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
            // get all email to customer
            $customers = Role::findByName('Customer')->users;

            $isUpdated = true;

            // send email
            $notificationLogService = new NotificationLogService();

            SendCustomerCreateEvent::dispatch($event, $customers, true, $notificationLogService);
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

        /**
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function trashedListEvent($perPage)
    {
        try {
            $trashed_event=Event::onlyTrashed()->paginate($perPage);
            return $trashed_event;
        } catch (Exception $e) {
            Log::error("error in display list of trashed Event" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

     /**
     * Restore a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be restored.
     * @return \App\Models\Event
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the restore process.
     */
    public function restoreEvent($id)
    {
        try {
            $event = Event::onlyTrashed()->findOrFail($id);
            $event->restore();
            return $event;
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error in restore a Event" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

      /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     *
     * @param  int  $id  The ID of the trashed Task to be permanently deleted.
     * @return void
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the Task with the given ID is not found.
     * @throws \Exception If there is an error during the force delete process.
     */
    public function forceDeleteEvent($id)
    {
        try {
            $event = Event::onlyTrashed()->findOrFail($id);

            $event->forceDelete();
        } catch (ModelNotFoundException $e) {
            Log::error("error" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "we didn't find any thing",
                ],
                404
            ));

        } catch (Exception $e) {
            Log::error("error  in forceDelete FoodCategory" . $e->getMessage());

            throw new HttpResponseException(response()->json(
                [
                    'status' => 'error',
                    'message' => "there is something wrong in server",
                ],
                500
            ));
        }
    }

}
