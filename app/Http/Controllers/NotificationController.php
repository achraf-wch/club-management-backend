<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            $limit = $request->get('limit', 50);
            $page = $request->get('page', 1);

            $notifications = Notification::where('person_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            $unreadCount = Notification::where('person_id', $user->id)
                ->where('read', false)
                ->count();

            $formattedNotifications = collect($notifications->items())->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'dashboard_link' => $notification->dashboard_link,
                    'data' => $notification->data,
                    'read' => $notification->read,
                    'read_at' => $notification->read_at,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at,
                ];
            });

            return response()->json([
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total()
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($id, Request $request)
    {
        try {
            $user = $request->user();
            
            $notification = Notification::where('id', $id)
                ->where('person_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json(['message' => 'Notification non trouvée'], 404);
            }

            $notification->markAsRead();

            $unreadCount = Notification::where('person_id', $user->id)
                ->where('read', false)
                ->count();

            return response()->json([
                'message' => 'Notification marquée comme lue',
                'unread_count' => $unreadCount
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors du marquage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            
            $updated = Notification::where('person_id', $user->id)
                ->where('read', false)
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'message' => 'Toutes les notifications ont été marquées comme lues',
                'marked_count' => $updated
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error marking all as read: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors du marquage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $user = $request->user();
            
            $notification = Notification::where('id', $id)
                ->where('person_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json(['message' => 'Notification non trouvée'], 404);
            }

            $notification->delete();

            return response()->json(['message' => 'Notification supprimée'], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();
            
            $unreadCount = Notification::where('person_id', $user->id)
                ->where('read', false)
                ->count();

            return response()->json(['unread_count' => $unreadCount], 200);

        } catch (\Exception $e) {
            \Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors du comptage',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}