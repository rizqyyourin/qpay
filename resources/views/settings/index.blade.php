<x-app-layout>
    <div class="px-5 py-12 space-y-8 max-w-2xl mx-auto">
                <!-- Header -->
                <div class="space-y-3">
                    <h1 class="text-5xl font-black text-base-content">Settings</h1>
                    <p class="text-lg text-base-content/70 font-medium">Manage your application settings</p>
                </div>

                <!-- General Settings -->
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-8">
                        <h2 class="text-2xl font-bold text-base-content">⚙️ General Settings</h2>

                        <form class="space-y-6">
                            <!-- Store Name -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Store Name</span>
                                </label>
                                <input type="text" placeholder="Your store name" class="input input-bordered w-full text-base" />
                            </div>

                            <!-- Phone -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Phone Number</span>
                                </label>
                                <input type="tel" placeholder="+62 XXX XXXX XXXX" class="input input-bordered w-full text-base" />
                            </div>

                            <!-- Address -->
                            <div class="form-control w-full">
                                <label class="label pb-3">
                                    <span class="label-text font-semibold text-base">Store Address</span>
                                </label>
                                <textarea placeholder="Your complete store address" class="textarea textarea-bordered w-full text-base" rows="4"></textarea>
                            </div>

                            <!-- Save Button -->
                            <div class="flex gap-3 pt-6">
                                <button type="submit" class="btn btn-primary gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- About -->
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-6">
                        <h2 class="text-2xl font-bold text-base-content">ℹ️ About QPAY</h2>
                        <div class="text-base text-base-content/70 space-y-3">
                            <p><strong class="text-base-content">Version:</strong> 1.0.0</p>
                            <p><strong class="text-base-content">Built with:</strong> Laravel 12 + Livewire 3 + DaisyUI</p>
                            <p class="pt-3 border-t border-base-300">© 2025 QPAY. All rights reserved.</p>
                        </div>
                    </div>
                </div>
        </div>
</x-app-layout>
