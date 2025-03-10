<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    /**
     * Отображает чат между текущим пользователем и собеседником.
     */
    public function index($withUserId)
    {
        $withUser = User::findOrFail($withUserId);
        $cacheKey = "chat_" . Auth::id() . "_" . $withUserId;
        $messages = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($withUserId) {
            return Message::where(function($query) use ($withUserId) {
                $query->where('from_user_id', Auth::id())
                      ->where('to_user_id', $withUserId);
            })->orWhere(function($query) use ($withUserId) {
                $query->where('from_user_id', $withUserId)
                      ->where('to_user_id', Auth::id());
            })->orderBy('created_at', 'asc')->get();
        });

        return view('chat.index', compact('messages', 'withUser'));
    }

    /**
     * Отправка сообщения.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'content'    => 'required|string|max:5000',
        ]);

        $message = new Message();
        $message->from_user_id = Auth::id();
        $message->to_user_id   = $request->to_user_id;
        $message->content      = $request->content;
        $message->save();

        Cache::forget("chat_" . Auth::id() . "_" . $request->to_user_id);
        Cache::forget("chat_" . $request->to_user_id . "_" . Auth::id());

        return back()->with('success', 'Сообщение отправлено.');
    }

    /**
     * Получение списка чатов и тикетов поддержки текущего пользователя.
     */
    public function conversations()
    {
        $userId = Auth::id();
        $userMessages = Message::where('from_user_id', $userId)
            ->orWhere('to_user_id', $userId)
            ->get();

        $userIds = $userMessages->map(function ($message) use ($userId) {
            return $message->from_user_id == $userId ? $message->to_user_id : $message->from_user_id;
        })->unique();

        $users = User::whereIn('id', $userIds)->get();
        $supportTickets = SupportTicket::where('user_id', $userId)->get();

        return view('chat.list', compact('users', 'supportTickets'));
    }
}
