<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordApplication;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
}