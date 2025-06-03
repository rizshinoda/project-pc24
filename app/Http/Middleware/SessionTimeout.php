<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class SessionTimeout
{
    // Timeout dalam detik (contoh: 600 detik = 10 menit)
    protected $timeout = 1200;

    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = Session::get('lastActivityTime');
            $currentTime = Carbon::now()->timestamp;

            if ($lastActivity && ($currentTime - $lastActivity > $this->timeout)) {
                Auth::logout();
                Session::flush();
                return redirect('/login')->with('error', 'Session expired. Silakan login kembali.');
            }

            Session::put('lastActivityTime', $currentTime);
        }

        return $next($request);
    }
}
