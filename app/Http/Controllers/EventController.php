<?php

namespace App\Http\Controllers;

use App\Models\Club_member;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class EventController extends Controller
{
    private function addImageUrls($event)
    {
        $event->banner_url = $event->banner_image 
            ? url('storage/' . $event->banner_image) 
            : null;

        if ($event->recap_images) {
            $images = is_string($event->recap_images) 
                ? json_decode($event->recap_images, true) 
                : $event->recap_images;
            
            if (is_array($images)) {
                $event->recap_images = array_map(function ($path) {
                    return url('storage/' . $path);
                }, $images);
            }
        }

        return $event;
    }

    public function index()
    {
        try {
            $events = Event::all();
            $events->each(function ($event) {
                $this->addImageUrls($event);
            });
            return response()->json($events, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching events: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching events', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $this->addImageUrls($event);
            return response()->json($event, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching event: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching event', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'club_id' => 'required|exists:clubs,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:100',
                'event_date' => 'required|date',
                'registration_deadline' => 'nullable|date',
                'location' => 'nullable|string|max:255',
                'capacity' => 'nullable|integer|min:0',
                'status' => 'nullable|in:pending,approved,completed,cancelled',
                'is_public' => 'nullable|boolean',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'requires_ticket' => 'nullable|boolean',
                'tickets_for_all' => 'nullable|boolean',
                'price' => 'nullable|numeric|min:0',
            ]);

            $validated['created_by'] = auth()->id();
            $validated['registered_count'] = 0;
            $validated['attendees_count'] = 0;
            $validated['status'] = $validated['status'] ?? 'pending';

            if ($request->hasFile('banner_image')) {
                $bannerPath = $request->file('banner_image')->store('events/banners', 'public');
                $validated['banner_image'] = $bannerPath;
            }

            $event = Event::create($validated);
            
            if (!$event || !$event->id) {
                Log::error('Event creation failed - event object is null or has no ID');
                return response()->json([
                    'message' => 'Error creating event - database save failed',
                ], 500);
            }

            $event->refresh();
            $this->addImageUrls($event);

            Log::info('Event created successfully: ' . $event->id . ' by user: ' . auth()->id());

            if ($event->status === 'approved' && $event->tickets_for_all) {
                Log::info('Triggering automatic ticket sending for event: ' . $event->id);
                
                $eventExists = DB::table('event')->where('id', $event->id)->exists();
                if ($eventExists) {
                    $this->sendTicketsToClubMembers($event);
                } else {
                    Log::error('Cannot send tickets - event ' . $event->id . ' does not exist in database');
                }
            }

            return response()->json([
                'message' => 'Event created successfully',
                'event' => $event
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating event: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error creating event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'category' => 'nullable|string|max:100',
                'event_date' => 'nullable|date',
                'registration_deadline' => 'nullable|date',
                'location' => 'nullable|string|max:255',
                'capacity' => 'nullable|integer|min:0',
                'status' => 'nullable|in:pending,approved,completed,cancelled',
                'is_public' => 'nullable|boolean',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'requires_ticket' => 'nullable|boolean',
                'tickets_for_all' => 'nullable|boolean',
                'price' => 'nullable|numeric|min:0',
            ]);

            if ($request->hasFile('banner_image')) {
                if ($event->banner_image && Storage::disk('public')->exists($event->banner_image)) {
                    Storage::disk('public')->delete($event->banner_image);
                }
                $bannerPath = $request->file('banner_image')->store('events/banners', 'public');
                $validated['banner_image'] = $bannerPath;
            }

            $event->update($validated);
            $event->refresh();
            $this->addImageUrls($event);

            Log::info('Event updated: ' . $id . ' by user: ' . auth()->id());

            return response()->json([
                'message' => 'Event updated successfully',
                'event' => $event
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating event: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating event', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            if ($event->banner_image && Storage::disk('public')->exists($event->banner_image)) {
                Storage::disk('public')->delete($event->banner_image);
            }

            $event->delete();

            Log::info('Event deleted: ' . $id . ' by user: ' . auth()->id());

            return response()->json(['message' => 'Event deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting event: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting event', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,approved,completed,cancelled',
            ]);

            $oldStatus = $event->status;
            $event->status = $validated['status'];

            if ($validated['status'] === 'completed') {
                $event->completed_at = now();
            }

            $event->save();
            $event->refresh();
            $this->addImageUrls($event);

            Log::info('Event status updated: ' . $id . ' from ' . $oldStatus . ' to ' . $validated['status']);

            if ($oldStatus !== 'approved' && $validated['status'] === 'approved' && $event->tickets_for_all) {
                Log::info('Triggering ticket sending due to status change for event: ' . $event->id);
                $this->sendTicketsToClubMembers($event);
            }

            return response()->json([
                'message' => 'Event status updated successfully',
                'event' => $event
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating event status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating event status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add recap to event – MERGES existing images with new uploads
     */
    public function addRecap(Request $request, $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $request->validate([
                'recap_description' => 'nullable|string',
                'recap_images' => 'nullable|array',
                'recap_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Update description
            if ($request->has('recap_description')) {
                $event->recap_description = $request->recap_description;
            }

            // Get existing recap images
            $existingImages = [];
            if ($event->recap_images) {
                $existingImages = is_string($event->recap_images) 
                    ? json_decode($event->recap_images, true) 
                    : $event->recap_images;
                if (!is_array($existingImages)) {
                    $existingImages = [];
                }
            }

            // Upload new images and merge
            if ($request->hasFile('recap_images')) {
                foreach ($request->file('recap_images') as $image) {
                    $path = $image->store('events/recaps', 'public');
                    $existingImages[] = $path;
                }
            }

            // Save merged list as JSON
            $event->recap_images = json_encode($existingImages);
            $event->status = 'completed';
            $event->completed_at = now();
            $event->save();
            $event->refresh();

            // Add full URLs for response
            $this->addImageUrls($event);

            Log::info('Event recap added: ' . $id . ' by user: ' . auth()->id());

            return response()->json([
                'message' => 'Event recap added successfully',
                'event' => $event
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error adding recap: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error adding recap',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function sendTicketsToClubMembers($event)
    {
        // ... unchanged ...
    }

    private function generateTicketPDF($ticket, $ticketCode, $qrCodeBase64)
    {
        // ... unchanged ...
    }

    public function upcoming()
    {
        try {
            $events = Event::where('event_date', '>=', now())
                ->where('status', 'approved')
                ->orderBy('event_date', 'asc')
                ->get();
            $events->each(function ($event) {
                $this->addImageUrls($event);
            });
            return response()->json($events, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching upcoming events: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching upcoming events', 'error' => $e->getMessage()], 500);
        }
    }

    public function pastEvents()
    {
        try {
            $events = Event::where('event_date', '<', now())
                ->orWhere('status', 'completed')
                ->orderBy('event_date', 'desc')
                ->get();
            $events->each(function ($event) {
                $this->addImageUrls($event);
            });
            return response()->json($events, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching past events: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching past events', 'error' => $e->getMessage()], 500);
        }
    }

    public function getByClub($clubId)
    {
        try {
            $events = Event::where('club_id', $clubId)
                ->orderBy('event_date', 'desc')
                ->get();
            $events->each(function ($event) {
                $this->addImageUrls($event);
            });
            return response()->json($events, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching events by club: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching events', 'error' => $e->getMessage()], 500);
        }
    }
}