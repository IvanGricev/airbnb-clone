<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordApplication;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Применение middleware для проверки роли администратора.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Отображение главной страницы админки.
     */
    public function index()
    {
        return view('admin.dashboard');
    }

    /**
     * Отображение списка заявок на роль арендодателя.
     */
    public function landlordApplications()
    {
        $applications = LandlordApplication::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.landlord_applications', compact('applications'));
    }

    /**
     * Одобрение заявки на роль арендодателя.
     */
    public function approveLandlordApplication(LandlordApplication $application)
    {
        $application->status = 'approved';
        $application->save();

        $user = $application->user;
        $user->role = 'landlord';
        $user->save();

        return back()->with('success', 'Заявка одобрена, пользователь теперь является арендодателем.');
    }

    /**
     * Отклонение заявки на роль арендодателя.
     */
    public function rejectLandlordApplication(LandlordApplication $application)
    {
        $application->status = 'rejected';
        $application->save();

        return back()->with('success', 'Заявка отклонена.');
    }
}
