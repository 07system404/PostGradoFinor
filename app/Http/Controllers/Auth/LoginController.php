<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class LoginController extends Controller
{
    public function showForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $this->autoBackup();

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Correo o contraseña incorrectos.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function autoBackup()
    {
        $dbPath = database_path('database.sqlite');
        if (!file_exists($dbPath)) return;

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $hoy = date('Y-m-d');
        $autoFile = $backupDir . '/backup_diario_' . $hoy . '.sqlite';

        if (file_exists($autoFile)) return;

        $archivos = glob($backupDir . '/backup_diario_*.sqlite');
        foreach ($archivos as $antiguo) {
            $nombre = basename($antiguo);
            if (strpos($nombre, 'backup_diario_' . $hoy) === false) {
                File::delete($antiguo);
            }
        }

        copy($dbPath, $autoFile);
    }
}