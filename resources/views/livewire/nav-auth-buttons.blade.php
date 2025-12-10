<div class="flex items-center gap-3 text-sm">
    <button 
        onclick="document.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { mode: 'login' } }))"
        class="btn btn-sm btn-ghost font-semibold"
    >
        Login
    </button>
    <button 
        onclick="document.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { mode: 'register' } }))"
        class="btn btn-sm btn-primary font-semibold"
    >
        Register
    </button>
</div>
