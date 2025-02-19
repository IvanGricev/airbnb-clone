<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupportTicket;
class ChatController extends Controller
{
    // Отображение чата
    public function index($withUserId)
    {
        $withUser = User::findOrFail($withUserId);

        $messages = Message::where(function($query) use ($withUserId) {
            $query->where('from_user_id', Auth::id())
                  ->where('to_user_id', $withUserId);
        })->orWhere(function($query) use ($withUserId) {
            $query->where('from_user_id', $withUserId)
                  ->where('to_user_id', Auth::id());
        })->orderBy('created_at', 'asc')->get();

        return view('chat.index', compact('messages', 'withUser'));
    }

    // Отправка сообщения
    public function sendMessage(Request $request)
    {
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $message = new Message();
        $message->from_user_id = Auth::id();
        $message->to_user_id = $request->to_user_id;
        $message->content = $request->content;
        $message->save();

        return back()->with('success', 'Сообщение отправлено.');
    }

    public function conversations()
    {
        $userId = Auth::id();

        // Получаем уникальные ID пользователей, с которыми у текущего пользователя есть сообщения
        $userMessages = Message::where('from_user_id', $userId)
            ->orWhere('to_user_id', $userId)
            ->get();

        $userIds = $userMessages->map(function ($message) use ($userId) {
            return $message->from_user_id == $userId ? $message->to_user_id : $message->from_user_id;
        })->unique();

        $users = User::whereIn('id', $userIds)->get();

        // Получаем тикеты поддержки
        $supportTickets = SupportTicket::where('user_id', $userId)->get();

        return view('chat.list', compact('users', 'supportTickets'));
    }

}
