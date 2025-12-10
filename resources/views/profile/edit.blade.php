<x-app-layout>
    <div class="px-5 py-12 space-y-8 max-w-2xl mx-auto">
                <!-- Header -->
                <div class="space-y-3">
                    <h1 class="text-5xl font-black text-base-content">My Profile</h1>
                    <p class="text-lg text-base-content/70 font-medium">Manage your account information</p>
                </div>

                <!-- Success Message -->
                @if (session('status'))
                    <div class="alert alert-success shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-error shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-bold">Validation Error</h3>
                            <div class="text-sm">{{ $errors->first() }}</div>
                        </div>
                    </div>
                @endif

                <!-- Profile Card -->
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-8">
                        <!-- Avatar Section -->
                        <div class="flex items-center gap-6">
                            <div class="avatar placeholder">
                                <div class="bg-primary text-primary-content rounded-full w-24 h-24 flex items-center justify-center">
                                    <span class="text-4xl font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <h2 class="text-2xl font-bold text-base-content">{{ auth()->user()->name }}</h2>
                                <p class="text-base-content/70">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Profile Form -->
                        <form id="profile_form" method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                            @csrf

                            <!-- Name -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Full Name</span>
                                </label>
                                <input type="text" name="name" value="{{ auth()->user()->name }}" class="input input-bordered w-full @error('name') input-error @enderror text-base" placeholder="Enter your full name" />
                                @error('name')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Email</span>
                                </label>
                                <input type="email" name="email" value="{{ auth()->user()->email }}" class="input input-bordered w-full @error('email') input-error @enderror text-base" placeholder="Enter email" />
                                @error('email')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Phone (Optional) -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Phone Number</span>
                                </label>
                                <input type="tel" name="phone" class="input input-bordered w-full @error('phone') input-error @enderror text-base" placeholder="Enter phone number" />
                                @error('phone')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Save Button -->
                            <div class="flex gap-3 pt-6">
                                <button type="button" onclick="confirm_update_modal.showModal()" class="btn btn-primary gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Changes
                                </button>
                            </div>
                        </form>

                        <!-- Confirmation Modal for Profile Update -->
                        <dialog id="confirm_update_modal" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg">Confirm Profile Update</h3>
                                <p class="py-4">Are you sure you want to save these changes?</p>
                                <div class="modal-action">
                                    <form method="dialog">
                                        <button class="btn btn-ghost">Cancel</button>
                                    </form>
                                    <button type="submit" form="profile_form" class="btn btn-primary">Confirm</button>
                                </div>
                            </div>
                            <form method="dialog" class="modal-backdrop"><button>close</button></form>
                        </dialog>
                    </div>
                </div>

                <!-- Change Password Section -->
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-8">
                        <h2 class="text-2xl font-bold text-base-content">Change Password</h2>

                        <form id="password_form" method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                            @csrf

                            <!-- Current Password -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Current Password</span>
                                </label>
                                <input type="password" name="current_password" class="input input-bordered w-full @error('current_password') input-error @enderror text-base" placeholder="Enter current password" />
                                @error('current_password')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">New Password</span>
                                </label>
                                <input type="password" name="new_password" class="input input-bordered w-full @error('new_password') input-error @enderror text-base" placeholder="Enter new password" />
                                @error('new_password')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Confirm Password</span>
                                </label>
                                <input type="password" name="new_password_confirmation" class="input input-bordered w-full @error('new_password') input-error @enderror text-base" placeholder="Confirm new password" />
                                @error('new_password')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Save Button -->
                            <div class="flex gap-3 pt-6">
                                <button type="button" onclick="confirm_password_modal.showModal()" class="btn btn-primary gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Change Password
                                </button>
                            </div>
                        </form>

                        <!-- Confirmation Modal for Password Change -->
                        <dialog id="confirm_password_modal" class="modal">
                            <div class="modal-box">
                                <h3 class="font-bold text-lg">Confirm Password Change</h3>
                                <p class="py-4">Are you sure you want to change your password?</p>
                                <div class="modal-action">
                                    <form method="dialog">
                                        <button class="btn btn-ghost">Cancel</button>
                                    </form>
                                    <button type="submit" form="password_form" class="btn btn-primary">Confirm</button>
                                </div>
                            </div>
                            <form method="dialog" class="modal-backdrop"><button>close</button></form>
                        </dialog>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card bg-error/10 border border-error/30 shadow-sm">
                    <div class="card-body space-y-6">
                        <h2 class="text-2xl font-bold text-error">⚠️ Danger Zone</h2>
                        <p class="text-base text-base-content/70">Actions below cannot be undone. Please be careful.</p>

                        <button type="button" onclick="confirm_delete_modal.showModal()" class="btn btn-error gap-2 justify-start">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Account
                        </button>
                    </div>
                </div>

                <!-- Confirmation Modal for Account Deletion -->
                <dialog id="confirm_delete_modal" class="modal">
                    <div class="modal-box bg-error/5 border border-error/30">
                        <h3 class="font-bold text-lg text-error">⚠️ Delete Account Permanently</h3>
                        <p class="py-4 font-semibold">This action cannot be undone. All your data will be permanently deleted.</p>
                        
                        <!-- Password Input for Deletion Confirmation -->
                        <form id="delete_form" method="POST" action="{{ route('profile.destroy') }}" class="space-y-6">
                            @csrf
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Enter your password to confirm:</span>
                                </label>
                                <input type="password" name="password" class="input input-bordered w-full input-error text-base" placeholder="Your password" required autofocus />
                                @error('password')
                                    <label class="label pt-2">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </form>

                        <div class="modal-action">
                            <form method="dialog">
                                <button class="btn btn-ghost">Cancel</button>
                            </form>
                            <button type="submit" form="delete_form" class="btn btn-error">Delete My Account</button>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop"><button>close</button></form>
                </dialog>
        </div>
</x-app-layout>
