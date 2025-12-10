<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthModal extends Component
{
    public string $email = '';
    public string $password = '';
    public string $name = '';
    public string $password_confirmation = '';
    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $this->remember)) {
            $this->redirect('/dashboard', navigate: true);
        } else {
            $this->addError('email', 'Email atau password salah');
        }
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Auth::login($user);
            $this->redirect('/dashboard', navigate: true);
        } catch (\Exception $e) {
            $this->addError('email', 'Gagal membuat akun. Silakan coba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.auth-modal');
    }
}


