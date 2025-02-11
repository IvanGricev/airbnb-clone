<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
