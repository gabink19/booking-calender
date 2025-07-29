<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $user = User::where('username', $request->username)->first();
            if ($user && password_verify($request->password, $user->password)) {
                Session::put('user_id', $user->uuid);
                Session::put('unit', $user->unit);
                return redirect()->route('booking');
            }
            return back()->withErrors(['login' => 'Username atau password salah']);
        } catch (\Exception $e) {
            return back()->withErrors(['login' => 'Terjadi kesalahan. Silakan coba lagi nanti.']);
        }
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }

    public function showRegisterForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'whatsapp' => 'required|string|max:20',
            'unit' => 'required|string|max:20',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = new \App\Models\User();
        $user->name = $request->name;
        $user->whatsapp = $request->whatsapp;
        $user->unit = $request->unit;
        $user->username = $request->username;
        $user->password = password_hash($request->password, PASSWORD_DEFAULT);
        $user->save();

        Session::put('uuid', $user->uuid);

        return redirect()->route('booking');
    }
}