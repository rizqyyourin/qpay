<div x-data="{ isOpen: false, mode: 'login' }"
     @open-auth-modal.document="isOpen = true; mode = $event.detail.mode"
     @keydown.escape.window="isOpen = false"
     wire:key="auth-modal">

    <!-- Modal Backdrop & Container -->
    <div x-show="isOpen" 
         @click="isOpen = false"
         class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center"
         x-transition>
        
        <!-- Modal Box -->
        <div @click.stop 
             class="bg-base-100 rounded-lg shadow-2xl p-8 w-full max-w-md max-h-screen overflow-y-auto"
             x-transition>
            
            <!-- Close Button -->
            <button @click="isOpen = false" class="btn btn-sm btn-circle btn-ghost absolute right-4 top-4">
                ✕
            </button>

            <!-- Login Form -->
            <div x-show="mode === 'login'" x-transition>
                <h2 class="font-black text-2xl mb-6 text-base-content">Welcome back</h2>
                
                @if ($errors->any())
                    <div class="alert alert-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m2-2l2 2m-2-2l-2-2m2 2l2-2" /></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                
                <form wire:submit.prevent="login" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Email</span>
                        </label>
                        <input type="email" 
                               wire:model="email"
                               placeholder="your@email.com" 
                               class="input input-bordered focus:input-primary" />
                        @error('email') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Password</span>
                        </label>
                        <input type="password" 
                               wire:model="password"
                               placeholder="••••••••" 
                               class="input input-bordered focus:input-primary" />
                        @error('password') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" 
                                   wire:model="remember"
                                   class="checkbox checkbox-sm checkbox-primary" />
                            <span class="label-text font-semibold">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" 
                            class="btn btn-primary btn-block font-bold mt-6"
                            wire:loading.attr="disabled"
                            wire:loading.class="loading">
                        <span wire:loading.remove>Sign In</span>
                        <span wire:loading>Loading...</span>
                    </button>
                </form>

                <!-- Switch to Register -->
                <div class="text-center mt-6">
                    <p class="text-sm text-base-content/70">
                        Don't have an account?
                        <button type="button"
                                @click="mode = 'register'"
                                class="link link-primary font-semibold">
                            Sign up
                        </button>
                    </p>
                </div>
            </div>

            <!-- Register Form -->
            <div x-show="mode === 'register'" x-transition>
                <h2 class="font-black text-2xl mb-6 text-base-content">Create account</h2>
                
                @if ($errors->any())
                    <div class="alert alert-error mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m2-2l2 2m-2-2l-2-2m2 2l2-2" /></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
                
                <form wire:submit.prevent="register" class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Full Name</span>
                        </label>
                        <input type="text" 
                               wire:model="name"
                               placeholder="John Doe" 
                               class="input input-bordered focus:input-primary" />
                        @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Email</span>
                        </label>
                        <input type="email" 
                               wire:model="email"
                               placeholder="your@email.com" 
                               class="input input-bordered focus:input-primary" />
                        @error('email') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Password</span>
                        </label>
                        <input type="password" 
                               wire:model="password"
                               placeholder="••••••••" 
                               class="input input-bordered focus:input-primary" />
                        @error('password') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Confirm Password</span>
                        </label>
                        <input type="password" 
                               wire:model="password_confirmation"
                               placeholder="••••••••" 
                               class="input input-bordered focus:input-primary" />
                        @error('password_confirmation') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                    </div>

                    <button type="submit" 
                            class="btn btn-primary btn-block font-bold mt-6"
                            wire:loading.attr="disabled"
                            wire:loading.class="loading">
                        <span wire:loading.remove>Create Account</span>
                        <span wire:loading>Loading...</span>
                    </button>
                </form>

                <div class="text-center mt-6">
                    <p class="text-sm text-base-content/70">
                        Already have an account?
                        <button type="button"
                                @click="mode = 'login'"
                                class="link link-primary font-semibold">
                            Sign in
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
