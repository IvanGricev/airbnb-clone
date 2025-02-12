<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $messages = [
                'name.required' => 'Поле имя обязательно для заполнения.',
                'name.max' => 'Имя не должно превышать 255 символов.',
                'email.required' => 'Поле email обязательно для заполнения.',
                'email.email' => 'Введите корректный email адрес.',
                'email.max' => 'Email не должен превышать 255 символов.',
                'email.unique' => 'Пользователь с таким email уже существует.',
                'password.required' => 'Поле пароль обязательно для заполнения.',
                'password.confirmed' => 'Пароли не совпадают.',
                'password.min' => 'Пароль должен быть не менее 6 символов.',
            ];

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed|min:6',
            ], $messages);

            // Создание пользователя
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'user', // Установка роли по умолчанию
            ]);

            // Можно добавить отправку письма с подтверждением, если требуется

            // Перенаправление на страницу входа с сообщением об успешной регистрации
            return redirect()->route('login')->with('success', 'Вы успешно зарегистрировались. Войдите, используя ваши учетные данные.');

        } catch (ValidationException $e) {
            // Обработка ошибок валидации
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            // Обработка других ошибок
            return back()->withErrors(['error' => 'Произошла ошибка при регистрации.'])->withInput();
        }
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'Поле email обязательно для заполнения.',
                'email.email' => 'Введите корректный email адрес.',
                'password.required' => 'Поле пароль обязательно для заполнения.',
            ]);

            if (Auth::attempt($credentials)) {
                return redirect()->route('home')->with('success', 'Вы успешно вошли в систему.');
            } else {
                return back()->withErrors(['email' => 'Неверный email или пароль.'])->withInput();
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Произошла ошибка при входе.'])->withInput();
        }
    }

    public function logout()
    {
        try {
            Auth::logout();
            return redirect()->route('home')->with('success', 'Вы вышли из системы.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Произошла ошибка при выходе из системы.']);
        }
    }
}
