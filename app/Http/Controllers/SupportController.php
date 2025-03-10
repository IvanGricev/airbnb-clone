<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    /**
     * Отображает список тикетов поддержки для текущего пользователя.
     */
    public function myTickets()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('support.tickets', compact('tickets'));
    }

    /**
     * Отображает форму создания нового тикета поддержки.
     */
    public function create()
    {
        return view('support.index');
    }

    /**
     * Сохраняет новый тикет поддержки и создаёт начальное сообщение.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $request->subject,
                'status'  => 'open',
            ]);

            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'message'   => $request->message,
            ]);

            return redirect()->route('support.show', $ticket->id)
                             ->with('success', 'Ваша заявка отправлена в поддержку.');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании тикета', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось создать тикет.');
        }
    }

    /**
     * Отображает тикет и связанный с ним чат (сообщения).
     */
    public function show($id)
    {
        $ticket = SupportTicket::findOrFail($id);

        // Проверка доступа: тикет должен принадлежать текущему пользователю.
        if ($ticket->user_id !== Auth::id()) {
            return redirect()->route('support.index')->with('error', 'У вас нет доступа к этому тикету.');
        }

        $messages = $ticket->messages()->orderBy('created_at', 'asc')->get();

        return view('support.chat', compact('ticket', 'messages'));
    }

    /**
     * Отправка нового сообщения в тикете поддержки.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        if ($ticket->user_id !== Auth::id()) {
            return redirect()->route('support.index')->with('error', 'У вас нет доступа к этому тикету.');
        }

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'message'   => $request->message,
        ]);

        return redirect()->back()->with('success', 'Сообщение отправлено.');
    }
}