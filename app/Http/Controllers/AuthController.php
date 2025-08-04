<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function showLoginAdminForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        if (app()->environment('local')) {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required',
                'g-recaptcha-response' => 'required|captcha'
            ], [
                'g-recaptcha-response.required' => 'Captcha wajib diisi.',
                'g-recaptcha-response.captcha' => 'Captcha tidak valid.'
            ]);
        }
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('username', $request->username)->first();
        if ($user && password_verify($request->password, $user->password)) {
            if (!$user->is_active) {
                return back()->withErrors(['login' => 'Akun Anda tidak aktif. Silakan hubungi admin.']);
            }
            Session::put('user_id', $user->uuid);
            Session::put('unit', $user->unit);
            if ($user->is_admin) {
                Session::put('is_admin', true);
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('booking');
        }
        return back()->withErrors(['login' => 'Username atau password salah']);
    }

    public function loginAdmin(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required',
                'password' => 'required',
                'captcha'  => 'required|captcha'
            ]);

            $user = User::where('username', $request->username)->first();
            if ($user && password_verify($request->password, $user->password) && $user->is_admin) {
                // Tambahkan pengecekan status aktif
                if (!$user->is_active) {
                    return back()->withErrors(['login' => 'Akun Anda tidak aktif. Silakan hubungi Developer.']);
                }
                Session::put('user_id', $user->uuid);
                Session::put('is_admin', true);
                return redirect()->route('admin.dashboard');
            }
            return back()->withErrors(['login' => 'Username atau password salah']);
        } catch (\Exception $e) {
            if ($e->getMessage()=="validation.captcha") {
                // Tangani kesalahan captcha
                return back()->withErrors(['login' => 'Captcha tidak valid. Silakan coba lagi.']);
            }
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
            'role' => 'required|in:0,1',
            'is_active' => 'required|in:0,1',
            'password' => 'required|string|min:6',
        ]);

        $user = new \App\Models\User();
        $user->name = $request->name;
        $user->whatsapp = $request->whatsapp;
        $user->unit = $request->unit;
        $user->username = $request->username;
        $user->is_admin = $request->role;   
        $user->is_active = $request->is_active;
        $user->password = password_hash($request->password, PASSWORD_DEFAULT);
        $user->save();

        // Jika request AJAX, balas JSON
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pengguna berhasil ditambahkan!']);
        }

        Session::put('uuid', $user->uuid);

        return redirect()->route('booking');
    }

    public function editUser(Request $request, $uuid)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'whatsapp' => 'required|string|max:20',
            'unit' => 'required|string|max:20',
            'username' => 'required|string|unique:users,username,' . $uuid . ',uuid',
            'role' => 'required|in:admin,user,0,1',
            'is_active' => 'required|in:0,1',
            // Password opsional saat edit
            'password' => 'nullable|string|min:6',
        ]);

        $user = \App\Models\User::where('uuid', $uuid)->firstOrFail();
        $user->name = $request->name;
        $user->whatsapp = $request->whatsapp;
        $user->unit = $request->unit;
        $user->username = $request->username;
        $user->is_admin = $request->role;
        $user->is_active = $request->is_active;
        if ($request->filled('password')) {
            $user->password = password_hash($request->password, PASSWORD_DEFAULT);
        }
        $user->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pengguna berhasil diupdate!']);
        }

        return redirect()->back()->with('success', 'Pengguna berhasil diupdate!');
    }

    public function changePass(Request $request, $uuid)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string',
        ]);
        $user = User::where('uuid', $request->uuid)->firstOrFail();
        if ($user && !password_verify($request->old_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Password lama salah!']);
        }
        $user->password = password_hash($request->new_password, PASSWORD_DEFAULT);

        $user->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Password berhasil diubah!']);
        }

        return redirect()->back()->with('success', 'Password berhasil diubah!');
    }

    public function getUser($uuid)
    {
        $user = \App\Models\User::where('uuid', $uuid)->firstOrFail();

        // Jika request AJAX, balas JSON
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'uuid' => $user->uuid,
                    'name' => $user->name,
                    'whatsapp' => $user->whatsapp,
                    'unit' => $user->unit,
                    'username' => $user->username,
                    'is_admin' => $user->is_admin,
                    'is_active' => $user->is_active,
                ]
            ]);
        }

        // Jika bukan AJAX, bisa redirect atau tampilkan view detail user
        return view('admin.user-detail', compact('user'));
    }

    public function forceUpdate()
    {
        $users = \App\Models\User::where('is_admin', false)->get();
        foreach ($users as $user) {
            if (substr($user->whatsapp, 0, 1) === '8') {
                $user->whatsapp = '0' . $user->whatsapp;
            }
            $user->whatsapp = str_replace('-', '', $user->whatsapp);
            $user->password = password_hash($user->unit, PASSWORD_DEFAULT);
            $user->save();
        }
        return response()->json(['success' => true, 'message' => 'Pengguna berhasil diupdate!']);
    }
}