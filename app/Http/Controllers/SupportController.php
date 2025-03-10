<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * Отображает список тикетов поддержки, созданных текущим пользователем.
     *
     * @return \Illuminate\View\View
     */
    public function myTickets()
    {
        // Получаем тикеты, принадлежащие текущему пользователю, в порядке убывания создания.
        $tickets = SupportTicket::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return view('support.tickets', compact('tickets'));
    }

    /**
     * Отображает форму создания нового тикета поддержки.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Возвращаем представление для создания тикета.
        // Обратите внимание, что здесь используется view 'support.index', но можно использовать и другую.
        return view('support.index');
    }

    /**
     * Сохраняет новый тикет поддержки и создаёт начальное сообщение.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Валидация входящих данных.
        // subject: обязательное поле, строка, максимум 255 символов.
        // message: обязательное поле, строка (ограничения по длине можно добавить, если потребуется).
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Создание тикета поддержки.
        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'status'  => 'open',
        ]);

        // Создание начального сообщения для тикета.
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'message'   => $request->message,
        ]);

        return redirect()->route('support.show', $ticket->id)
                         ->with('success', 'Ваша заявка отправлена в поддержку.');
    }

    /**
     * Отображает конкретный тикет поддержки и все связанные сообщения.
     *
     * @param int $id Идентификатор тикета.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $ticket = SupportTicket::findOrFail($id);

        // Проверка доступа: тикет должен принадлежать текущему пользователю.
        if ($ticket->user_id !== Auth::id()) {
            return redirect()->route('support.index')->with('error', 'У вас нет доступа к этому тикету.');
        }

        // Получаем сообщения тикета в порядке возрастания времени создания.
        $messages = $ticket->messages()->orderBy('created_at', 'asc')->get();

        return view('support.chat', compact('ticket', 'messages'));
    }

    /**
     * Отправка нового сообщения в рамках указанного тикета поддержки.
     *
     * @param Request $request
     * @param int $id Идентификатор тикета.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request, $id)
    {
        // Валидация входящего сообщения.
        // Ограничение 'required|string' можно расширить, добавив max:5000, если это необходимо.
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        // Проверка доступа: тикет должен принадлежать текущему пользователю.
        if ($ticket->user_id !== Auth::id()) {
            return redirect()->route('support.index')->with('error', 'У вас нет доступа к этому тикету.');
        }

        // Создание нового сообщения для тикета.
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'message'   => $request->message,
        ]);

        return redirect()->back()->with('success', 'Сообщение отправлено.');
    }
}