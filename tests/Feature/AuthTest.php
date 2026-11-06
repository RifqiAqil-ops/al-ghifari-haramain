<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('Masuk Portal Internal');
    }

    public function test_user_can_authenticate_using_valid_credentials(): void
    {
        $user = User::where('email', 'superadmin@alghifari.com')->first();
        if (!$user) {
            $user = User::factory()->create([
                'email' => 'testuser@alghifari.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::where('email', 'superadmin@alghifari.com')->first();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password-123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('email', 'superadmin@alghifari.com')->first();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }
}
