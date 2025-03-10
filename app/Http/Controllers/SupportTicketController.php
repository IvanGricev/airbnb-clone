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
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            // Получаем все тикеты, сортируя их по дате последнего обновления (от новых к старым)
            $tickets = SupportTicket::orderBy('updated_at', 'desc')->get();
            return view('admin.support.index', compact('tickets'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении тикетов поддержки', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось загрузить тикеты.');
        }
    }

    /**
     * Отображает тикеты поддержки, принадлежащие текущему пользователю.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('support.create');
    }

    /**
     * Сохраняет новый тикет поддержки и создает начальное сообщение.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных с ограничением максимальной длины для subject
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            // Создание тикета с присвоением статус "open"
            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $request->subject,
                'status'  => 'open',
            ]);

            // Создание начального сообщения тикета
            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'message'   => $request->message,
            ]);

            return redirect()->route('support.show', $ticket->id)
                             ->with('success', 'Тикет создан.');
        } catch (\Exception $e) {
            Log::error('Ошибка при создании тикета', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Не удалось создать тикет.');
        }
    }

    /**
     * Отображает тикет поддержки и связанный с ним чат (сообщения).
     *
     * @param int $id Идентификатор тикета
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);

            // Проверяем доступ: тикет может быть просмотрен администратором или владельцем тикета
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
     * Отправляет новое сообщение в тикете поддержки.
     * Обновляет статус тикета в зависимости от роли отправителя.
     *
     * @param Request $request
     * @param int $id Идентификатор тикета
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request, $id)
    {
        // Валидация входящих данных
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            $ticket = SupportTicket::findOrFail($id);

            // Проверка доступа: сообщение может быть отправлено администратором или владельцем тикета
            if (Auth::user()->role === 'admin' || $ticket->user_id == Auth::id()) {
                SupportMessage::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => Auth::id(),
                    'message'   => $request->message,
                ]);

                // Обновление статуса тикета.
                // Если отправитель — администратор, статус меняется на "answered", иначе остается "open"
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