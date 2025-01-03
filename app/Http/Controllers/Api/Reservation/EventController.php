<?php

namespace App\Http\Controllers\Api\Reservation;

use Log;
use Exception;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;


/**
 * EventController - Handles operations related to events.
 *
 * This controller provides the basic functions for managing events such as displaying all events,
 * creating a new event, showing event details, updating an event, and deleting an event.
 */
class EventController extends Controller
{
    /**
     * Event service instance.
     *
     * @var EventService
     */
    protected $eventService;

    /**
     * Initialize the controller with the event service.
     *
     * @param EventService $eventService
     */
    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    /**
     * Display a listing of all events.
     *
     * Retrieves all events from the database using the event service.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $events = $this->eventService->getAllEvents();

        // Return JSON response containing the events data.
        return self::paginated($events, EventResource::class, 'Events retrieved successfully.', 200);
    }

    /**
     * Store a newly created event.
     *
     * This method handles creating a new event using the provided data from the request.
     * The input data is validated using the StoreEventRequest.
     *
     * @param StoreEventRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreEventRequest $request)
    {
        // Validate the data and create the event.
        $event = $this->eventService->createEvent($request->validated());

        // Return a success response indicating that the event was created successfully.
        return self::success(new EventResource($event), 'Event created successfully.', 201);

    }

    /**
     * Display a specific event by its ID.
     *
     * Fetches the event details using its ID and includes the related reservation data.
     *
     * @param Event $event
     * @return JsonResponse
     */
    public function show(Event $event)
    {
        // Return the event data along with its related reservation.
        return self::success(new EventResource($event->load('reservation')), 'Event retrieved successfully.');

    }

    /**
     * Update the specified event.
     *
     * This method handles updating the event data using the provided validated data from the request.
     * The input data is validated using the UpdateEventRequest.
     *
     * @param UpdateEventRequest $request
     * @param Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        // Validate the data and update the event.
        $updatedEvent = $this->eventService->updateEvent($event, $request->validated());

        // Return a success response indicating that the event was updated successfully.
        return self::success(new EventResource($updatedEvent), 'Event updated successfully.');

    }

    /**
     * Delete the specified event.
     *
     * This method handles deleting the event using its ID.
     *
     * @param Event $event
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Event $event)
    {
        // Delete the event using the event service.
        $this->eventService->deleteEvent($event);

        // Return a success response indicating that the event was deleted successfully.
        return self::success(null, 'Event deleted successfully.');

    }


 /**
     * Retrieve a list of soft-deleted Event.
     *
     * @return JsonResponse
     *
     * @throws Exception
     */

    public function showDeleted(): JsonResponse
    {
        try {
            $softDeleted = Event::onlyTrashed()->get();
            if ($softDeleted->isEmpty()) {
                return self::error(null, 'No deleted Event found.', 404);
            }
            return self::success($softDeleted, 'Soft-deleted Event retrieved successfully.');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error retrieving soft-deleted events: ' . $e->getMessage());
            return self::error(null, 'An error occurred while retrieving deleted events.', 500);
        }
    }

    /**
     * Restore a soft-deleted event.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function restoreDeleted(string $id): JsonResponse
    {
        try {
            $event = Event::onlyTrashed()->findOrFail($id);
            $event->restore();
            return self::success($event, 'Event restored successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return self::error(null, 'Event not found.', 404);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error restoring event: ' . $e->getMessage());
            return self::error(null, 'An error occurred while restoring the event.', 500);
        }
    }

    /**
     * Permanently delete a soft-deleted event.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function forceDeleted(string $id): JsonResponse
    {
        try {
            $event = Event::onlyTrashed()->findOrFail($id);
            $event->forceDelete();
            return self::success(null, 'Event permanently deleted.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return self::error(null, 'Event not found.', 404);
        } catch (Exception $e) {
            // Log the error
            \Log::error('Error permanently deleting event: ' . $e->getMessage());
            return self::error(null, 'An error occurred while permanently deleting the event.', 500);
        }
    }

}
