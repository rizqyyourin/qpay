<?php

namespace App\Livewire;

use Livewire\Component;

class NavAuthButtons extends Component
{
    public function openLogin(): void
    {
        $this->dispatch('authModalOpenLogin');
    }

    public function openRegister(): void
    {
        $this->dispatch('authModalOpenRegister');
    }

    public function render()
    {
        return view('livewire.nav-auth-buttons');
    }
}
