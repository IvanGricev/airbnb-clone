<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Booking;
class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        try {
            return view('admin.dashboard');
        } catch (\Exception $e) {
            Log::error('Ошибка при отображении админ-панели', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->route('admin.landlord.applications')
                ->with('error', 'Произошла ошибка при загрузке админ-панели');
        }
    }

    public function landlordApplications(Request $request)
    {
        try {
            $status = $request->query('status', 'pending');
            $search = $request->query('search', '');

            $applications = LandlordApplication::with('user')
                ->when($status, function($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->when($search, function($query) use ($search) {
                    return $query->whereHas('user', function($q) use ($search) {
                        $q->where('name', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%");
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $statuses = [
                'pending' => 'На рассмотрении',
                'approved' => 'Одобрено',
                'rejected' => 'Отклонено'
            ];

            return view('admin.landlord_applications', compact('applications', 'status', 'search', 'statuses'));
        } catch (\Exception $e) {
            Log::error('Ошибка при получении списка заявок арендодателей', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Произошла ошибка при загрузке списка заявок');
        }
    }

    public function approveLandlordApplication(LandlordApplication $application)
    {
        try {
            $application->status = 'approved';
            $application->save();

            $user = $application->user;
            $user->role = 'landlord';
            $user->save();

            return redirect()->route('admin.landlord.applications')
                ->with('success', 'Заявка одобрена, пользователь теперь является арендодателем.');
        } catch (\Exception $e) {
            Log::error('Ошибка при одобрении заявки арендодателя', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Произошла ошибка при одобрении заявки');
        }
    }

    public function rejectLandlordApplication(LandlordApplication $application)
    {
        try {
            $application->status = 'rejected';
            $application->save();

            return redirect()->route('admin.landlord.applications')
                ->with('success', 'Заявка отклонена.');
        } catch (\Exception $e) {
            Log::error('Ошибка при отклонении заявки арендодателя', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->with('error', 'Произошла ошибка при отклонении заявки');
        }
    }

     // Отображение списка тикетов поддержки
     public function supportTickets()
     {
         $tickets = SupportTicket::orderBy('updated_at', 'desc')->get();
         return view('admin.support.index', compact('tickets'));
     }
 
     // Отображение конкретного тикета поддержки
     public function showSupportTicket($id)
     {
         $ticket = SupportTicket::findOrFail($id);
         $messages = $ticket->messages()->orderBy('created_at', 'asc')->get();
         $user = $ticket->user;
     
         // Получаем активные бронирования пользователя
         $bookings = Booking::where('user_id', $user->id)
             ->where('status', 'confirmed')
             ->with('property')
             ->get();
     
         return view('admin.support.chat', compact('ticket', 'messages', 'user', 'bookings'));
     }     
 
     // Отправка сообщения в тикете поддержки
     public function sendSupportMessage(Request $request, $id)
     {
         $request->validate([
             'message' => 'required|string',
         ]);
 
         $ticket = SupportTicket::findOrFail($id);
 
         SupportMessage::create([
             'ticket_id' => $ticket->id,
             'user_id' => auth()->id(),
             'message' => $request->message,
         ]);
 
         // Обновляем статус тикета, если необходимо
         if ($ticket->status !== 'answered') {
             $ticket->status = 'answered';
             $ticket->save();
         }
 
         return redirect()->back()->with('success', 'Сообщение отправлено.');
    }

    public function updateSupportTicketStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:open,answered,closed',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        return redirect()->back()->with('success', 'Статус тикета обновлён.');
    }

    public function viewChatBetweenUsers($user1, $user2)
    {
        // Проверяем, что текущий пользователь - администратор
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('home')->with('error', 'У вас нет доступа к этому чату.');
        }

        $userOne = User::findOrFail($user1);
        $userTwo = User::findOrFail($user2);

        $messages = Message::where(function($query) use ($user1, $user2) {
            $query->where('from_user_id', $user1)
                ->where('to_user_id', $user2);
        })->orWhere(function($query) use ($user1, $user2) {
            $query->where('from_user_id', $user2)
                ->where('to_user_id', $user1);
        })->orderBy('created_at', 'asc')->get();

        return view('admin.chat.between_users', compact('userOne', 'userTwo', 'messages'));
    }

}