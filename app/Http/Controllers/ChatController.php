<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Отображает чат между текущим пользователем и собеседником.
     */
    public function index($withUserId)
    {
        try {
            $withUser = User::findOrFail($withUserId);

            $messages = Message::where(function($query) use ($withUserId) {
                $query->where('from_user_id', Auth::id())
                      ->where('to_user_id', $withUserId);
            })->orWhere(function($query) use ($withUserId) {
                $query->where('from_user_id', $withUserId)
                      ->where('to_user_id', Auth::id());
            })
            ->with(['fromUser', 'toUser', 'booking'])
            ->orderBy('created_at', 'asc')
            ->get();

            return view('chat.index', compact('messages', 'withUser'));
        } catch (ModelNotFoundException $e) {
            Log::error('User not found in chat: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Пользователь не найден.');
        } catch (\Exception $e) {
            Log::error('Error in chat: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке чата.');
        }
    }

    /**
     * Отправка сообщения.
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'to_user_id' => 'required|exists:users,id',
                'content'    => 'required|string|max:5000',
                'booking_id' => 'nullable|exists:bookings,id',
            ]);

            $toUser = User::findOrFail($request->to_user_id);

            // Если указан booking_id, проверяем что пользователи связаны с этим бронированием
            if ($request->has('booking_id')) {
                $booking = \App\Models\Booking::findOrFail($request->booking_id);
                if ($booking->user_id !== Auth::id() && $booking->property->user_id !== Auth::id()) {
                    return redirect()->back()->with('error', 'У вас нет доступа к этому бронированию.');
                }
            }

            $message = new Message();
            $message->from_user_id = Auth::id();
            $message->to_user_id = $request->to_user_id;
            $message->content = $request->content;
            $message->booking_id = $request->booking_id;
            $message->save();

            // Загружаем связи для события
            $message->load(['fromUser', 'booking']);

            // Отправка события
            broadcast(new MessageSent($message))->toOthers();

            return back()->with('success', 'Сообщение отправлено.');
        } catch (ModelNotFoundException $e) {
            Log::error('User not found when sending message: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Пользователь не найден.');
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при отправке сообщения.');
        }
    }

    /**
     * Получение списка чатов и тикетов поддержки текущего пользователя.
     */
    public function conversations()
    {
        try {
            $userId = Auth::id();
            
            // Получаем последние сообщения для каждого чата
            $latestMessages = Message::select('id', 'from_user_id', 'to_user_id', 'created_at')
                ->where(function($query) use ($userId) {
                    $query->where('from_user_id', $userId)
                          ->orWhere('to_user_id', $userId);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(function($message) use ($userId) {
                    return $message->from_user_id == $userId ? $message->to_user_id : $message->from_user_id;
                })
                ->map(function($messages) {
                    return $messages->first();
                });

            // Получаем пользователей с их последними сообщениями
            $users = User::whereIn('id', $latestMessages->keys())
                ->with(['messages' => function($query) use ($userId) {
                    $query->where(function($q) use ($userId) {
                        $q->where('from_user_id', $userId)
                          ->orWhere('to_user_id', $userId);
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
                }])
                ->get();

            $supportTickets = SupportTicket::where('user_id', $userId)
                ->with(['messages' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(1);
                }])
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('chat.list', compact('users', 'supportTickets'));
        } catch (\Exception $e) {
            Log::error('Error loading conversations: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке списка чатов.');
        }
    }
}
