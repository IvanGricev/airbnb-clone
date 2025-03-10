<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Отображает форму регистрации.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Обрабатывает регистрацию нового пользователя.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        try {
            // Определение пользовательских сообщений об ошибках,
            // чтобы вывести понятные сообщения о проблемах валидации.
            $messages = [
                'name.required'       => 'Поле имя обязательно для заполнения.',
                'name.max'            => 'Имя не должно превышать 255 символов.',
                'email.required'      => 'Поле email обязательно для заполнения.',
                'email.email'         => 'Введите корректный email адрес.',
                'email.max'           => 'Email не должен превышать 255 символов.',
                'email.unique'        => 'Пользователь с таким email уже существует.',
                'password.required'   => 'Поле пароль обязательно для заполнения.',
                'password.confirmed'  => 'Пароли не совпадают.',
                'password.min'        => 'Пароль должен быть не менее 6 символов.',
            ];

            // Валидация входных данных.
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|confirmed|min:6',
            ], $messages);

            // Создание нового пользователя с хешированием пароля.
            $user = User::create([
                'name'     => $validatedData['name'],
                'email'    => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role'     => 'user', // Устанавливаем роль по умолчанию для нового пользователя.
            ]);

            // Перенаправляем на страницу входа с сообщением об успешной регистрации.
            return redirect()->route('login')->with('success', 'Вы успешно зарегистрировались. Войдите, используя ваши учетные данные.');
        } catch (ValidationException $e) {
            // Обработка ошибок валидации: возвращаем пользователя обратно с сообщениями об ошибке и сохранением введенных данных.
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            // Обработка любых прочих исключений, возникающих при регистрации.
            return back()->withErrors(['error' => 'Произошла ошибка при регистрации.'])->withInput();
        }
    }

    /**
     * Отображает форму входа в систему.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Обрабатывает вход пользователя в систему.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            // Получаем только необходимые учетные данные (email и password).
            $credentials = $request->only('email', 'password');

            // Валидация входных данных с соответствующими пользовательскими сообщениями.
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ], [
                'email.required'    => 'Поле email обязательно для заполнения.',
                'email.email'       => 'Введите корректный email адрес.',
                'password.required' => 'Поле пароль обязательно для заполнения.',
            ]);

            // Попытка аутентификации с использованием переданных учетных данных.
            if (Auth::attempt($credentials)) {
                return redirect()->route('home')->with('success', 'Вы успешно вошли в систему.');
            } else {
                // Если вход не удался, возвращаем пользователя обратно с сообщением об ошибке.
                return back()->withErrors(['email' => 'Неверный email или пароль.'])->withInput();
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Произошла ошибка при входе.'])->withInput();
        }
    }

    /**
     * Обрабатывает выход пользователя из системы.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
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
