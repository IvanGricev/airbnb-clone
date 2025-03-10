<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupportTicketController extends Controller
{
    /**
     * Отображает список тикетов поддержки для администратора.
     */
    public function index()
    {
        try {
            $tickets = SupportTicket::orderBy('updated_at', 'desc')->get();
            return view('admin.support.index', compact('tickets'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении тикетов поддержки', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось загрузить тикеты.');
        }
    }

    /**
     * Отображает список тикетов поддержки для текущего пользователя.
     */
    public function userTickets()
    {
        try {
            $tickets = SupportTicket::where('user_id', Auth::id())
                        ->orderBy('updated_at', 'desc')
                        ->get();
            return view('support.index', compact('tickets'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении тикетов пользователя', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось загрузить ваши тикеты.');
        }
    }

    /**
     * Отображает форму создания нового тикета поддержки.
     */
    public function create()
    {
        return view('support.create');
    }

    /**
     * Сохраняет новый тикет поддержки и создает начальное сообщение.
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

            return redirect()->route('support.show', $ticket->id)->with('success', 'Тикет создан.');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании тикета', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось создать тикет.');
        }
    }

    /**
     * Отображает тикет и чат (сообщения) поддержки.
     */
    public function show($id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);

            // Проверка доступа (администратор или владелец тикета)
            if (Auth::user()->role === 'admin' || $ticket->user_id == Auth::id()) {
                $messages = $ticket->messages()->orderBy('created_at', 'asc')->get();
                return view('support.chat', compact('ticket', 'messages'));
            }

            return redirect()->back()->with('error', 'У вас нет доступа к этому тикету.');
        } catch (\Exception $e) {
            Log::error('Ошибка при отображении тикета', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось загрузить тикет.');
        }
    }

    /**
     * Отправляет новое сообщение в тикете поддержки и обновляет статус.
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            $ticket = SupportTicket::findOrFail($id);

            // Проверка доступа (администратор или владелец тикета)
            if (Auth::user()->role === 'admin' || $ticket->user_id == Auth::id()) {
                SupportMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'message'   => $request->message,
                ]);

                // Обновление статуса
                if (Auth::user()->role === 'admin') {
                    $ticket->status = 'answered';
                } else {
                    $ticket->status = 'open';
                }
                $ticket->save();

                return redirect()->back()->with('success', 'Сообщение отправлено.');
            }

            return redirect()->back()->with('error', 'У вас нет доступа к этому тикету.');
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке сообщения в тикете', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось отправить сообщение.');
        }
    }
}