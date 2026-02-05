<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApiChatController extends Controller
{
    /**
     * Get chat conversation with admin (messages for the authenticated customer).
     * GET /api/v1/chat/messages?page=1&per_page=20
     */
    public function messages(Request $request): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $perPage = min((int) $request->get('per_page', 50), 100);
        $messages = $customer->chatMessages()
            ->orderBy('created_at')
            ->paginate($perPage);

        $items = $messages->getCollection()->map(fn (ChatMessage $m) => $this->formatMessage($m))->values()->all();

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    /**
     * Send a message to admin (customer → admin).
     * POST /api/v1/chat/messages
     * body: optional text, image: optional file (multipart)
     */
    public function store(Request $request): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $body = $request->input('body');
        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid() && str_starts_with($file->getMimeType(), 'image/')) {
                $path = $file->store('chat', 'public');
                if ($path) {
                    $imagePath = $path;
                }
            }
        }

        if (empty(trim($body ?? '')) && !$imagePath) {
            return response()->json([
                'success' => false,
                'message' => 'Provide a message (body) or an image.',
            ], 422);
        }

        $message = $customer->chatMessages()->create([
            'sender_type' => ChatMessage::SENDER_CUSTOMER,
            'body' => trim($body ?? '') ?: null,
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatMessage($message),
        ]);
    }

    /**
     * Get last message preview and unread count (for chat list/badge).
     * GET /api/v1/chat
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $lastMessage = $customer->chatMessages()->orderByDesc('created_at')->first();
        $unreadFromAdmin = $customer->chatMessages()
            ->where('sender_type', ChatMessage::SENDER_ADMIN)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'sender_type' => $lastMessage->sender_type,
                    'body' => $lastMessage->body,
                    'image_url' => $lastMessage->image_path ? asset('storage/' . $lastMessage->image_path) : null,
                    'created_at' => $lastMessage->created_at->toIso8601String(),
                ] : null,
                'unread_count' => $unreadFromAdmin,
            ],
        ]);
    }

    /**
     * Mark admin messages as read.
     * POST /api/v1/chat/read
     */
    public function markRead(Request $request): JsonResponse
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Invalid user.'], 401);
        }

        $customer->chatMessages()
            ->where('sender_type', ChatMessage::SENDER_ADMIN)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Marked as read.',
        ]);
    }

    private function formatMessage(ChatMessage $m): array
    {
        $imageUrl = null;
        if ($m->image_path) {
            $imageUrl = asset('storage/' . $m->image_path);
        }
        return [
            'id' => $m->id,
            'sender_type' => $m->sender_type,
            'body' => $m->body,
            'image_url' => $imageUrl,
            'created_at' => $m->created_at->toIso8601String(),
            'read_at' => $m->read_at?->toIso8601String(),
        ];
    }
}
