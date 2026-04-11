<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Ambil semua notifikasi (AJAX — untuk navbar dropdown).
     */
    public function index(): JsonResponse
    {
        $user          = auth()->user();
        $notifications = $user->notifications()
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'message'    => $n->data['message'],
                'icon'       => $n->data['icon'] ?? 'bi-bell',
                'color'      => $n->data['color'] ?? 'secondary',
                'url'        => $n->data['url'] ?? '#',
                'read'       => ! is_null($n->read_at),
                'time'       => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(string $id): JsonResponse
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(): JsonResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Hapus satu notifikasi.
     */
    public function destroy(string $id): JsonResponse
    {
        auth()->user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        return response()->json(['success' => true]);
    }
}
