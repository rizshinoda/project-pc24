<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Request\ResetPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPasswordMail;
use App\Mail\RegisterMail;

class AuthController extends Controller
{
    public function login()
    {

        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function register_post(Request $request)
    {
        $messages = [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar, gunakan email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'confirm_password.required_with' => 'password wajib diisi.',
            'confirm_password.same' => 'Password harus sama.',
            'is_role.required' => 'Silakan pilih divisi.',
        ];

        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'required_with:password|same:password|min:6',
            'is_role' => 'required'
        ], $messages);

        $user = new User;
        $user->name = trim($validatedData['name']);
        $user->email = trim($validatedData['email']);
        $user->password = Hash::make($validatedData['password']);
        $user->is_role = trim($validatedData['is_role']);
        $user->remember_token = Str::random(50);
        $user->save();

        Mail::to($user->email)->send(new RegisterMail($user));

        return redirect('login')->with('success', 'Registrasi berhasil. Silakan cek email untuk verifikasi.');
    }



    public function verify($token)
    {
        $user = User::where('remember_token', '=', $token)->first();
        if (!empty($user)) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->remember_token = Str::random(50);
            $user->save();
            return redirect('login')->with('success', 'Akun Anda Berhasil diverify');
        } else {
            abort(404);
        }
    }

    public function login_post(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            if (!empty(Auth::user()->email_verified_at)) {
                if (Auth::user()->is_role == 0) {
                    return redirect()->intended('superadmin/dashboard');
                } else if (Auth::user()->is_role == 1) {
                    return redirect()->intended('admin/dashboard');
                } else if (Auth::user()->is_role == 2) {
                    return redirect()->intended('ga/dashboard');
                } else if (Auth::user()->is_role == 3) {
                    return redirect()->intended('helpdesk/dashboard');
                } else if (Auth::user()->is_role == 4) {
                    return redirect()->intended('noc/dashboard');
                } else if (Auth::user()->is_role == 5) {
                    return redirect()->intended('psb/dashboard');
                } else if (Auth::user()->is_role == 6) {
                    return redirect()->intended('na/dashboard');
                }
            } else {
                $user_id = Auth::user()->id;
                Auth::logout();
                $user = User::getSingle($user_id);
                $user->remember_token = Str::random(50);
                $user->save();

                Mail::to($user->email)->send(new RegisterMail($user));

                return redirect('login')->with('success', 'Silahkan Verifikasi Email terlebih dahulu');
            }
        } else {
            return redirect()->back()->with('error', 'Masukkan email dan password yang benar');
        }
    }

    public function forgot()
    {
        return view('auth.forgot');
    }

    public function forgot_post(Request $request)
    {
        // dd($request->all());
        $count = User::where('email', '=', $request->email)->count();
        if ($count > 0) {
            $user = User::where('email', '=', $request->email)->first();
            // $user->remember_token = Str::random(50);
            $user->save();

            Mail::to($user->email)->send(new ForgotPasswordMail($user));

            return redirect()->back()->with('success', 'Password berhasil direset');
        } else {
            return redirect()->back()->with('error', 'Email tidak ditemukan');
        }
    }

    public function getReset(Request $request, $token)
    {
        // dd($token);
        $user = User::where('remember_token', '=', $token);
        if ($user->count() == 0) {
            abort(403);
        }

        $user == $user->first();
        $data['token'] = $token;

        return view('auth.reset', $data);
    }

    public function postReset($token, Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|same:confirm_password',
            'confirm_password' => 'required|min:6',
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.same' => 'Password dan konfirmasi harus sama.',
            'confirm_password.required' => 'Konfirmasi password wajib diisi.',
            'confirm_password.min' => 'Konfirmasi password minimal 6 karakter.',
        ]);

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            abort(403);
        }

        $user->password = Hash::make($request->password);
        $user->remember_token = Str::random(50);
        $user->save();

        return redirect('login')->with('success', 'Successfully Password Reset');
    }


    public function logout()
    {
        Auth::logout();
        return redirect(url('/'));
    }
}
