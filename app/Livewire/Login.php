<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:4')]
    public string $password = '';

    public bool $remember = false;

    public string $errorMessage = '';

    public function authenticate(): void
    {
        $this->validate();

        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            session()->regenerate();
            $this->redirect(route('dashboard'), navigate: true);
        } else {
            $this->errorMessage = 'Email atau password salah.';
            $this->password = '';
        }
    }

    public function render(): View
    {
        return view('livewire.login');
    }
}
