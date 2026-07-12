<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $notifications = $user->notifications()
            ->take(15)
            ->get()
            ->map(function ($notification) {
                $title = $notification->data['title'] ?? 'Notifica';
                $actionUrl = $notification->data['action_url'] ?? null;

                
                
                $titleLower = strtolower($title);
                if (
                    str_contains($titleLower, 'proposta di cammino approvata') ||
                    str_contains($titleLower, 'proposta di cammino rifiutata') ||
                    str_contains($titleLower, 'cammino annullato dall\'amministratore')
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

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $request->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }
}

