<?php

namespace App\Http\Controllers\Api\Event;


use Exception;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\Event\EventService;
use App\Http\Resources\Event\EventResource;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;

//
 /* EventController - Handles operations related to events.
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
    public function show($id)
    {
        // Use EventService to retrieve the event
        $event = $this->eventService->getEventById($id);
        return self::success(new EventResource($event), 'Event retrieved successfully.');
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
     * Display a paginated listing of the trashed (soft deleted) resources.
     */
    public function showDeleted(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $trashedevent = $this->eventService->trashedListEvent($perPage);
        return $this->success($trashedevent);
    }

         /**
     * Restore a trashed (soft deleted) resource by its ID.
     */
    public function restoreDeleted($id)
    {
        $event = $this->eventService->restoreEvent($id);
        return $this->success("Event restored successfully.");
    }

    /**
     * Permanently delete a trashed (soft deleted) resource by its ID.
     */
    public function forceDeleted($id)
    {
        $this->eventService->forceDeleteEvent($id);
        return $this->success(null, "Event permanently deleted.");
    }

}
