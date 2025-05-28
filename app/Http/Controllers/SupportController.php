<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SupportController extends Controller
{
    /**
     * Отображает список тикетов поддержки для текущего пользователя.
     */
    public function myTickets()
    {
        try {
            $tickets = SupportTicket::with(['messages' => function($query) {
                    $query->latest()->take(1);
                }])
                ->where('user_id', Auth::id())
                ->orderBy('updated_at', 'desc')
                ->paginate(10);

            return view('support.index', compact('tickets'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении тикетов пользователя', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
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
     * Сохраняет новый тикет поддержки и создаёт начальное сообщение.
     */
    public function store(Request $request)
    {
        $messages = [
            'subject.required' => 'Тема обращения обязательна для заполнения.',
            'subject.string' => 'Тема должна быть текстом.',
            'subject.max' => 'Тема не должна превышать 255 символов.',
            'message.required' => 'Сообщение обязательно для заполнения.',
            'message.string' => 'Сообщение должно быть текстом.',
            'message.min' => 'Сообщение должно содержать минимум 10 символов.',
            'message.max' => 'Сообщение не должно превышать 5000 символов.',
        ];

        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
        ], $messages);

        try {
            DB::beginTransaction();

            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $validatedData['subject'],
                'status' => 'open',
            ]);

            SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validatedData['message'],
            ]);

            DB::commit();

            return redirect()->route('support.show', $ticket->id)
                ->with('success', 'Ваша заявка отправлена в поддержку.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при создании тикета поддержки', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'subject' => $request->subject
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Не удалось создать тикет. Пожалуйста, попробуйте снова.'])
                ->withInput();
        }
    }

    /**
     * Отображает тикет и связанный с ним чат (сообщения).
     */
    public function show($id)
    {
        try {
            $ticket = SupportTicket::with(['messages.user', 'user'])
                ->findOrFail($id);

            // Проверка доступа: тикет должен принадлежать текущему пользователю
            if ($ticket->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                Log::warning('Попытка доступа к чужому тикету', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $id,
                    'ticket_user_id' => $ticket->user_id
                ]);
                return redirect()->route('support.index')
                    ->with('error', 'У вас нет доступа к этому тикету.');
            }

            $messages = $ticket->messages()
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();

            return view('support.chat', compact('ticket', 'messages'));
        } catch (\Exception $e) {
            Log::error('Ошибка при отображении тикета', [
                'error' => $e->getMessage(),
                'ticket_id' => $id,
                'user_id' => Auth::id()
            ]);
            return redirect()->back()->with('error', 'Не удалось загрузить тикет.');
        }
    }

    /**
     * Отправляет новое сообщение в тикете поддержки.
     */
    public function sendMessage(Request $request, $id)
    {
        $messages = [
            'message.required' => 'Сообщение обязательно для заполнения.',
            'message.string' => 'Сообщение должно быть текстом.',
            'message.min' => 'Сообщение должно содержать минимум 10 символов.',
            'message.max' => 'Сообщение не должно превышать 5000 символов.',
        ];

        $validatedData = $request->validate([
            'message' => 'required|string|min:10|max:5000',
        ], $messages);

        try {
            DB::beginTransaction();

            $ticket = SupportTicket::findOrFail($id);

            // Проверка доступа
            if ($ticket->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
                Log::warning('Попытка отправки сообщения в чужой тикет', [
                    'user_id' => Auth::id(),
                    'ticket_id' => $id,
                    'ticket_user_id' => $ticket->user_id
                ]);
                return redirect()->route('support.index')
                    ->withErrors(['error' => 'У вас нет доступа к этому тикету.']);
            }

            // Проверка статуса тикета
            if ($ticket->status === 'closed') {
                return redirect()->back()
                    ->withErrors(['error' => 'Этот тикет закрыт. Вы не можете отправлять сообщения.']);
            }

            // Создание сообщения
            $message = SupportMessage::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $validatedData['message'],
            ]);

            // Обновление статуса тикета
            $ticket->status = Auth::user()->role === 'admin' ? 'answered' : 'open';
            $ticket->save();

            DB::commit();

            return redirect()->back()
                ->with('success', 'Сообщение отправлено.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка при отправке сообщения в тикете', [
                'error' => $e->getMessage(),
                'ticket_id' => $id,
                'user_id' => Auth::id()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Не удалось отправить сообщение. Пожалуйста, попробуйте снова.'])
                ->withInput();
        }
    }
}