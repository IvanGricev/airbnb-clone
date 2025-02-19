<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    // Просмотр списка тикетов пользователя
    public function myTickets()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('support.tickets', compact('tickets'));
    }

    // Форма создания нового тикета
    public function create()
    {
        return view('support.index'); // Ваше представление для формы создания тикета
    }

    // Сохранение нового тикета
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'status' => 'open',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return redirect()->route('support.show', $ticket->id)->with('success', 'Ваша заявка отправлена в поддержку.');
    }

    // Просмотр конкретного тикета и сообщений
    public function show($id)
    {
        $ticket = SupportTicket::findOrFail($id);

        // Проверка доступа
        if ($ticket->user_id !== Auth::id()) {
            return redirect()->route('support.index')->with('error', 'У вас нет доступа к этому тикету.');
        }

        $messages = $ticket->messages()->orderBy('created_at', 'asc')->get();

        return view('support.chat', compact('ticket', 'messages'));
    }

    // Отправка сообщения в тикете
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        // Проверка доступа
        if ($ticket->user_id !== Auth::id()) {
            return redirect()->route('support.index')->with('error', 'У вас нет доступа к этому тикету.');
        }

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        return redirect()->back()->with('success', 'Сообщение отправлено.');
    }
}
