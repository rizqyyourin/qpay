<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * Test that authenticated user can access profile page
     */
    public function test_user_can_access_profile_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('profile.edit');
    }

    /**
     * Test that unauthenticated user is redirected to login
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that profile form displays correct user information
     */
    public function test_profile_form_displays_user_data(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertSee('Test User');
        $response->assertSee('test@example.com');
    }

    /**
     * Test Save Changes button functionality (form submission)
     */
    public function test_profile_update_stores_data(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '123456789',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'Profile updated successfully!');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test Cancel button exists and navigates away
     */
    public function test_cancel_button_exists(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertSee('Cancel');
        // Should be a link to dashboard
        $response->assertSee(route('dashboard'));
    }

    /**
     * Test Change Password section is visible
     */
    public function test_change_password_section_visible(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertSee('Change Password');
        $response->assertSee('Current Password');
        $response->assertSee('New Password');
        $response->assertSee('Confirm Password');
    }

    /**
     * Test password update validation
     */
    public function test_password_update_requires_current_password(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.password'), [
                'current_password' => 'wrong-password',
                'new_password' => 'NewPassword123',
                'new_password_confirmation' => 'NewPassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    /**
     * Test password update with correct password
     */
    public function test_password_update_with_correct_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)
            ->post(route('profile.password'), [
                'current_password' => 'password123',
                'new_password' => 'NewPassword123',
                'new_password_confirmation' => 'NewPassword123',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'Password updated successfully!');
    }

    /**
     * Test Delete Account functionality (requires password confirmation)
     */
    public function test_delete_account_requires_password_confirmation(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response->assertSessionHasErrors('password');
        
        // User should still exist
        $this->assertDatabaseHas('users', ['id' => $this->user->id]);
    }

    /**
     * Test all form inputs have correct attributes
     */
    public function test_form_inputs_have_correct_attributes(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Check for name input
        $response->assertSeeHtml('name="name"');
        
        // Check for email input
        $response->assertSeeHtml('name="email"');
        
        // Check for phone input
        $response->assertSeeHtml('name="phone"');
        
        // Check for password inputs
        $response->assertSeeHtml('name="current_password"');
        $response->assertSeeHtml('name="new_password"');
        $response->assertSeeHtml('name="new_password_confirmation"');
    }

    /**
     * Test form inputs have placeholder text
     */
    public function test_form_inputs_have_placeholders(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertSee('Enter your full name');
        $response->assertSee('Enter email');
        $response->assertSee('Enter phone number');
        $response->assertSee('Enter current password');
        $response->assertSee('Enter new password');
        $response->assertSee('Confirm new password');
    }

    /**
     * Test page title
     */
    public function test_page_has_correct_title(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        $response->assertSee('My Profile');
        $response->assertSee('Manage your account information');
    }

    /**
     * Test profile avatar is displayed
     */
    public function test_profile_avatar_is_displayed(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Avatar should show first letter of name (uppercase)
        $response->assertSee('T');
    }

    /**
     * Test all form sections are present
     */
    public function test_all_form_sections_present(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Profile form section
        $response->assertSee('Full Name');
        $response->assertSee('Email');
        $response->assertSee('Phone Number');

        // Password section
        $response->assertSee('Change Password');

        // Danger zone
        $response->assertSee('Actions below cannot be undone');
    }

    /**
     * Test button styling classes are applied
     */
    public function test_buttons_have_correct_styling(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Save Changes button should be primary
        $response->assertSee('btn btn-primary');

        // Cancel buttons in modals should be ghost
        $response->assertSee('btn btn-ghost');

        // Delete Account button should be error
        $response->assertSee('btn btn-error');
    }

    /**
     * Test input fields have consistent width styling
     */
    public function test_input_fields_have_width_styling(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // All inputs should have w-full class for consistent width
        $response->assertSee('input-bordered w-full');
    }

    /**
     * Test form has CSRF protection (hidden token exists)
     */
    public function test_forms_have_csrf_protection(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Check for hidden CSRF token input
        $response->assertSeeHtml('type="hidden" name="_token"');
    }

    /**
     * Test page is responsive for mobile
     */
    public function test_page_responsive_classes(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Check for responsive padding
        $response->assertSee('px-5 py-12');
        
        // Check for max-width constraint
        $response->assertSee('max-w-2xl');
    }

    /**
     * Test SVG icons are present in buttons
     */
    public function test_buttons_have_icons(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('profile.edit'));

        // Checkmark icon for Save Changes
        $response->assertSee('<svg class="w-5 h-5"', false);
    }
}
