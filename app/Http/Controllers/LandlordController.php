<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LandlordController extends Controller
{
    public function showApplyForm()
    {
        try {
            $user = Auth::user();
            
            return view('landlord.apply', [
                'firstName' => old('first_name', $user->first_name),
                'middleName' => old('middle_name', $user->middle_name),
                'lastName' => old('last_name', $user->last_name),
                'passportNumber' => old('passport_number', $user->passport_number),
                'passportExpirationDate' => old('passport_expiration_date', $user->passport_expiration_date ? $user->passport_expiration_date->format('Y-m-d') : null)
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при отображении формы подачи заявки арендодателя', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->back()->with('error', 'Произошла ошибка при загрузке формы');
        }
    }

    public function apply(Request $request)
    {
        try {
            $this->validate($request, [
                'first_name' => 'required|string|max:50',
                'middle_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'passport_number' => ['required', 'string', 'regex:/^\d{2}\s\d{2}\s\d{6}\s\d{2}$/'],
                'passport_expiration_date' => [
                    'required',
                    'date_format:Y-m-d',
                    'after:' . Carbon::now()->format('Y-m-d'),
                    function ($attribute, $value, $fail) {
                        $expirationDate = Carbon::parse($value);
                        if ($expirationDate->lt(Carbon::now()->addYears(5))) {
                            $fail('Срок действия паспорта должен быть не менее 5 лет');
                        }
                    }
                ],
            ]);

            // Используем метод update вместо прямого обновления
            $user = Auth::user();
            $user->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'passport_number' => $request->passport_number,
                'passport_expiration_date' => Carbon::parse($request->passport_expiration_date),
            ]);

            // Создаем заявку на роль арендодателя
            LandlordApplication::create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            return redirect()->route('home')
                ->with('success', 'Ваша заявка успешно подана на рассмотрение');

        } catch (\Exception $e) {
            Log::error('Ошибка при обработке заявки арендодателя', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return redirect()->back()
                ->with('error', 'Произошла ошибка при отправке заявки')
                ->withInput();
        }
    }
}