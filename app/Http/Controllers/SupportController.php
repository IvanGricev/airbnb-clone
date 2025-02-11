<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    // Форма отправки заявки в поддержку
    public function index()
    {
        return view('support.index');
    }

    // Отправка заявки
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = new SupportTicket();
        $ticket->user_id = Auth::id();
        $ticket->subject = $request->subject;
        $ticket->message = $request->message;
        $ticket->status = 'open';
        $ticket->save();

        return back()->with('success', 'Ваша заявка отправлена в поддержку.');
    }

    public function myTickets()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('support.tickets', compact('tickets'));
    }

}
