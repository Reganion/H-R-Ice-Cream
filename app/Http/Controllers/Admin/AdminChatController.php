<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminChatController extends Controller
{
    /**
     * List customers for chat (search by name or email).
     * GET /admin/chat/customers?q=search
     */
    public function customers(Request $request): JsonResponse
    {
        $query = Customer::query()->orderBy('firstname')->orderBy('lastname');

        if ($search = $request->get('q')) {
            $q = trim($search);
            $query->where(function ($qry) use ($q) {
                $qry->where('firstname', 'like', "%{$q}%")
                    ->orWhere('lastname', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('contact_no', 'like', "%{$q}%");
            });
        }

        $customers = $query->limit(50)->get();

        $list = $customers->map(function (Customer $c) {
            $lastMessage = $c->chatMessages()->orderByDesc('created_at')->first();
            $unreadCount = $c->chatMessages()
                ->where('sender_type', ChatMessage::SENDER_CUSTOMER)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $c->id,
                'full_name' => $c->full_name,
                'firstname' => $c->firstname,
                'lastname' => $c->lastname,
                'email' => $c->email,
                'contact_no' => $c->contact_no ?? '',
                'image_url' => asset($c->image ?? 'img/default-user.png'),
                'last_message_preview' => $lastMessage ? $this->preview($lastMessage) : null,
                'last_message_at' => $lastMessage ? $lastMessage->created_at->toIso8601String() : null,
                'unread_count' => $unreadCount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $list,
        ]);
    }

    /**
     * Unread summary for chat head when panel is minimized.
     * GET /admin/chat/unread-summary
     */
    public function unreadSummary(): JsonResponse
    {
        $lastUnread = ChatMessage::query()
            ->where('sender_type', ChatMessage::SENDER_CUSTOMER)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->first();

        $totalUnread = ChatMessage::query()
            ->where('sender_type', ChatMessage::SENDER_CUSTOMER)
            ->whereNull('read_at')
            ->count();

        $lastFrom = null;
        if ($lastUnread && $lastUnread->customer) {
            $c = $lastUnread->customer;
            $lastFrom = [
                'customer_id' => $c->id,
                'full_name'   => $c->full_name,
                'image_url'   => asset($c->image ?? 'img/default-user.png'),
                'preview'     => $this->preview($lastUnread),
            ];
        }

        return response()->json([
            'success'       => true,
            'unread_count'  => $totalUnread,
            'last_from'     => $lastFrom,
        ]);
    }

    /**
     * Get customer profile and chat messages.
     * GET /admin/chat/customers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $messages = $customer->chatMessages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        // Mark customer messages as read
        $customer->chatMessages()
            ->where('sender_type', ChatMessage::SENDER_CUSTOMER)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'contact_no' => $customer->contact_no ?? '',
                'image_url' => asset($customer->image ?? 'img/default-user.png'),
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Get new messages for a customer after a given message id (for real-time polling).
     * GET /admin/chat/customers/{id}/messages?after_id=123
     */
    public function messagesSince(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }
        $afterId = (int) $request->get('after_id', 0);
        $messages = $customer->chatMessages()
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get()
            ->map(fn (ChatMessage $m) => $this->formatMessage($m));

        // Mark new customer messages as read when we fetch them
        if ($messages->isNotEmpty()) {
            $customer->chatMessages()
                ->where('sender_type', ChatMessage::SENDER_CUSTOMER)
                ->whereNull('read_at')
                ->where('id', '>', $afterId)
                ->update(['read_at' => now()]);
        }

        return response()->json(['success' => true, 'messages' => $messages->values()->all()]);
    }

    /**
     * Send a message to a customer (admin → customer).
     * POST /admin/chat/customers/{id}/messages
     * body: optional text, image: optional file
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found.'], 404);
        }

        $body = $request->input('body');
        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid() && str_starts_with($file->getMimeType(), 'image/')) {
                $dir = public_path('img/chat');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $name = 'chat_' . $customer->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                if ($file->move($dir, $name)) {
                    $imagePath = 'img/chat/' . $name;
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
            'sender_type' => ChatMessage::SENDER_ADMIN,
            'body' => trim($body ?? '') ?: null,
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($message),
        ]);
    }

    private function preview(ChatMessage $m): string
    {
        if ($m->image_path) {
            return '[Image]';
        }
        $text = $m->body ?? '';
        return strlen($text) > 50 ? substr($text, 0, 50) . '…' : $text;
    }

    private function formatMessage(ChatMessage $m): array
    {
        $imageUrl = null;
        if ($m->image_path) {
            $imageUrl = str_starts_with($m->image_path, 'img/') ? asset($m->image_path) : asset('storage/' . $m->image_path);
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
