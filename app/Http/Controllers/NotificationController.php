<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get the notifications list and unread count for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifications = $user->notifications()
            ->take(15)
            ->get()
            ->map(function ($notification) {
                $title = $notification->data['title'] ?? 'Notifica';
                $actionUrl = $notification->data['action_url'] ?? null;

                // Forziamo a null l'action_url per specifiche notifiche che non devono essere cliccabili
                $titleLower = strtolower($title);
                if (
                    str_contains($titleLower, 'proposta di cammino approvata') ||
                    str_contains($titleLower, 'proposta di cammina approvata') ||
                    str_contains($titleLower, 'proposta di cammino rifiutata') ||
                    str_contains($titleLower, 'proposta di cammina rifiutata') ||
                    str_contains($titleLower, 'cammino annullato dall\'amministratore') ||
                    str_contains($titleLower, 'cammino annullato da amministratore')
                ) {
                    $actionUrl = null;
                }

                return [
                    'id' => $notification->id,
                    'title' => $title,
                    'message' => $notification->data['message'] ?? '',
                    'action_url' => $actionUrl,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at ? $notification->created_at->diffForHumans() : '',
                ];
            });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }
}

