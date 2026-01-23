<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * EventController
 * 
 * Handles event management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class EventController extends BaseController
{
    /**
     * Display a listing of events.
     * 
     * Returns only events from the user's gym (unless SuperAdmin).
     * All users can view published events.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = now()->startOfDay();
        
        // For members and trainers, show only published events that are not expired
        if ($user->isMember() || $user->isTrainer()) {
            $query = Event::with(['creator', 'attendees'])
                ->where('status', 'Published')
                ->where(function($q) use ($today) {
                    // Show events where event_date is today or in the future
                    $q->whereDate('event_date', '>=', $today);
                });
            
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $query->where('gym_id', $user->gym_id);
            }
            $events = $query->orderBy('event_date', 'asc')->get();
        } else {
            // For Admin/SuperAdmin, show all events (draft, published, cancelled)
            // Check permission for non-SuperAdmin users
            if (!$user->isSuperAdmin()) {
                $this->authorizePermission('events.view');
            }
            $query = Event::with(['creator', 'attendees']);
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('event_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('event_date', '<=', $request->end_date);
            }
            
            // Order by latest first (newest created_at first, then by event_date)
            $events = $this->applyGymFilter($query)
                ->orderBy('created_at', 'desc')
                ->orderBy('event_date', 'desc')
                ->get();
        }

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'events' => $events
                ]
            ]);
        }

        // For web AJAX requests, return HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'html' => view('events._events-list', compact('events'))->render()
            ]);
        }

        return view('events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('events.create');

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('events.create', ['event' => null])->render();
        }

        return view('events.create-page');
    }

    /**
     * Store a newly created event.
     * 
     * Automatically sets gym_id and created_by.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('events.create');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Draft,Published,Cancelled',
        ]);

        $user = Auth::user();

        // Prepare event data
        $eventData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
        ];

        // Set gym_id
        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $eventData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $eventData['gym_id'] = $user->gym_id;
        }

        $eventData['created_by'] = $user->id;

        // Create event
        $event = Event::create($eventData);

        // If event is published, send notification to all users in the gym
        if ($event->status === 'Published') {
            $this->sendEventNotification($event);
        }

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully.',
                'event' => $event
            ]);
        }

        return redirect()->route('events.index')
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified event.
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $event = Event::with(['creator', 'attendees'])->findOrFail($id);
        
        // For members, only show published events
        if ($user->isMember()) {
            if ($event->status !== 'Published') {
                abort(404, 'Event not found.');
            }
            if (!$user->isSuperAdmin() && $event->gym_id !== $user->gym_id) {
                abort(403, 'You do not have access to this event.');
            }
        } else {
            $this->authorizePermission('events.view');
            $this->validateGymAccess($event->gym_id);
        }

        // Get user's response if exists
        $userResponse = $event->attendees()->where('user_id', $user->id)->first();
        $response = $userResponse ? $userResponse->pivot->response : null;

        // Get counts using model attributes
        $attendingCount = $event->attending_count;
        $notAttendingCount = $event->not_attending_count;
        $notSureCount = $event->not_sure_count;

        // Get user lists for admin
        $attendingUsers = collect();
        $notAttendingUsers = collect();
        $notSureUsers = collect();
        
        if (!$user->isMember()) {
            $attendingUsers = $event->attendees()->wherePivot('response', 'Attending')->get();
            $notAttendingUsers = $event->attendees()->wherePivot('response', 'Not Attending')->get();
            $notSureUsers = $event->attendees()->wherePivot('response', 'Not Sure')->get();
        }

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'event' => $event,
                    'response' => $response,
                    'counts' => [
                        'attending' => $attendingCount,
                        'not_attending' => $notAttendingCount,
                        'not_sure' => $notSureCount,
                    ],
                    'attending_users' => $attendingUsers,
                    'not_attending_users' => $notAttendingUsers,
                    'not_sure_users' => $notSureUsers,
                ]
            ]);
        }

        // For web AJAX requests, return JSON with HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'event' => $event,
                'response' => $response,
                'counts' => [
                    'attending' => $attendingCount,
                    'not_attending' => $notAttendingCount,
                    'not_sure' => $notSureCount,
                ],
                'html' => view('events.show', compact('event', 'response', 'attendingCount', 'notAttendingCount', 'notSureCount', 'attendingUsers', 'notAttendingUsers', 'notSureUsers'))->render()
            ]);
        }

        return view('events.show', compact('event', 'response', 'attendingCount', 'notAttendingCount', 'notSureCount', 'attendingUsers', 'notAttendingUsers', 'notSureUsers'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('events.update');

        $event = Event::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($event->gym_id);

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('events.edit', compact('event'))->render();
        }

        return view('events.edit-page', compact('event'));
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('events.update');

        $event = Event::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($event->gym_id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Draft,Published,Cancelled',
        ]);

        $oldStatus = $event->status;

        // Update event
        $event->update($validated);

        // If status changed from Draft to Published, send notification
        if ($oldStatus !== 'Published' && $event->status === 'Published') {
            $this->sendEventNotification($event);
        }

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully.',
                'event' => $event->fresh()
            ]);
        }

        return redirect()->route('events.index')
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('events.delete');

        $event = Event::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($event->gym_id);

        $event->delete();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully.'
            ]);
        }

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Update user's response to an event.
     */
    public function updateResponse(Request $request, string $id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);

        // Only published events can be responded to
        if ($event->status !== 'Published') {
            return response()->json([
                'success' => false,
                'message' => 'You can only respond to published events.'
            ], 422);
        }

        // Validate gym access
        if (!$user->isSuperAdmin() && $event->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this event.'
            ], 403);
        }

        $validated = $request->validate([
            'response' => 'required|in:Attending,Not Attending,Not Sure',
        ]);

        // Update or create user's response
        $event->attendees()->syncWithoutDetaching([
            $user->id => ['response' => $validated['response']]
        ]);

        // Get updated counts
        $attendingCount = $event->attendees()->wherePivot('response', 'Attending')->count();
        $notAttendingCount = $event->attendees()->wherePivot('response', 'Not Attending')->count();
        $notSureCount = $event->attendees()->wherePivot('response', 'Not Sure')->count();

        return response()->json([
            'success' => true,
            'message' => 'Response updated successfully.',
            'counts' => [
                'attending' => $attendingCount,
                'not_attending' => $notAttendingCount,
                'not_sure' => $notSureCount,
            ],
            'response' => $validated['response']
        ]);
    }

    /**
     * Publish an event (change status to Published).
     */
    public function publish(Request $request, string $id)
    {
        $this->authorizePermission('events.update');

        $event = Event::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($event->gym_id);

        if ($event->status === 'Published') {
            return response()->json([
                'success' => false,
                'message' => 'Event is already published.'
            ], 422);
        }

        $event->status = 'Published';
        $event->save();

        // Send notification to all users
        $this->sendEventNotification($event);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event published successfully.',
                'event' => $event->fresh()
            ]);
        }

        return redirect()->route('events.index')
            ->with('success', 'Event published successfully.');
    }

    /**
     * Send notification to all users when event is published.
     */
    private function sendEventNotification(Event $event): void
    {
        // Get all users in the gym (or all users for SuperAdmin)
        $usersQuery = User::where('role', '!=', 'SuperAdmin');
        if ($event->gym_id) {
            $usersQuery->where('gym_id', $event->gym_id);
        }
        $users = $usersQuery->get();

        // Create notification for each user
        foreach ($users as $user) {
            Notification::create([
                'title' => 'New Event: ' . $event->title,
                'message' => 'A new event "' . $event->title . '" has been scheduled for ' . $event->event_date->format('M d, Y') . ' at ' . $event->event_time->format('h:i A') . ($event->location ? ' at ' . $event->location : '') . '. Click to view details.',
                'type' => 'info',
                'target_audience' => 'all',
                'gym_id' => $event->gym_id,
                'created_by' => $event->created_by,
                'is_published' => true,
                'published_at' => now(),
            ]);
        }
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $today = now()->startOfDay();
        
        if ($user->isMember() || $user->isTrainer()) {
            $query = Event::with(['creator', 'attendees'])
                ->where('status', 'Published')
                ->where(function($q) use ($today) {
                    $q->whereDate('event_date', '>=', $today);
                });
            
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $query->where('gym_id', $user->gym_id);
            }
            $events = $query->orderBy('event_date', 'asc')->get();
        } else {
            if (!$user->isSuperAdmin()) {
                $this->authorizePermission('events.view');
            }
            $query = Event::with(['creator', 'attendees']);
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('event_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('event_date', '<=', $request->end_date);
            }
            
            $events = $this->applyGymFilter($query)
                ->orderBy('created_at', 'desc')
                ->orderBy('event_date', 'desc')
                ->get();
        }

        return $this->apiSuccess([
            'events' => $events,
            'count' => $events->count()
        ], 'Events retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $event = Event::with(['creator', 'attendees'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $event->gym_id !== $user->gym_id) {
            return $this->apiForbidden('Unauthorized access.');
        }

        $response = $event->attendees()->where('user_id', $user->id)->first();
        $attendingCount = $event->attendees()->wherePivot('response', 'Attending')->count();
        $notAttendingCount = $event->attendees()->wherePivot('response', 'Not Attending')->count();
        $notSureCount = $event->attendees()->wherePivot('response', 'Not Sure')->count();

        return $this->apiSuccess([
            'event' => $event,
            'response' => $response ? $response->pivot->response : null,
            'counts' => [
                'attending' => $attendingCount,
                'not_attending' => $notAttendingCount,
                'not_sure' => $notSureCount
            ]
        ], 'Event retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('events.create');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Draft,Published,Cancelled',
        ]);

        $user = Auth::user();
        $eventData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'event_date' => $validated['event_date'],
            'event_time' => $validated['event_time'],
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
            'created_by' => $user->id,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $eventData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $eventData['gym_id'] = $user->gym_id;
        }

        $event = Event::create($eventData);

        if ($event->status === 'Published') {
            $this->sendEventNotification($event);
        }

        return $this->apiSuccess($event->load(['creator']), 'Event created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('events.update');

        $event = Event::findOrFail($id);
        $this->validateGymAccess($event->gym_id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Draft,Published,Cancelled',
        ]);

        $oldStatus = $event->status;
        $event->update($validated);

        if ($oldStatus !== 'Published' && $event->status === 'Published') {
            $this->sendEventNotification($event);
        }

        return $this->apiSuccess($event->fresh()->load(['creator']), 'Event updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('events.delete');

        $event = Event::findOrFail($id);
        $this->validateGymAccess($event->gym_id);

        $event->delete();

        return $this->apiSuccess(null, 'Event deleted successfully');
    }

    public function apiUpdateResponse(Request $request, string $id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);

        if ($event->status !== 'Published') {
            return $this->apiError('You can only respond to published events.', null, 422);
        }

        if (!$user->isSuperAdmin() && $event->gym_id !== $user->gym_id) {
            return $this->apiForbidden('You do not have access to this event.');
        }

        $validated = $request->validate([
            'response' => 'required|in:Attending,Not Attending,Not Sure',
        ]);

        $event->attendees()->syncWithoutDetaching([
            $user->id => ['response' => $validated['response']]
        ]);

        $attendingCount = $event->attendees()->wherePivot('response', 'Attending')->count();
        $notAttendingCount = $event->attendees()->wherePivot('response', 'Not Attending')->count();
        $notSureCount = $event->attendees()->wherePivot('response', 'Not Sure')->count();

        return $this->apiSuccess([
            'response' => $validated['response'],
            'counts' => [
                'attending' => $attendingCount,
                'not_attending' => $notAttendingCount,
                'not_sure' => $notSureCount
            ]
        ], 'Response updated successfully');
    }

    public function apiPublish(Request $request, string $id)
    {
        $this->authorizePermission('events.update');

        $event = Event::findOrFail($id);
        $this->validateGymAccess($event->gym_id);

        if ($event->status === 'Published') {
            return $this->apiError('Event is already published.', null, 422);
        }

        $event->status = 'Published';
        $event->save();

        $this->sendEventNotification($event);

        return $this->apiSuccess($event->fresh()->load(['creator']), 'Event published successfully');
    }
}
