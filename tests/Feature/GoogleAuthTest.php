<?php

namespace Tests\Feature;

use App\Models\GoogleAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_route_redirects_to_google_oauth(): void
    {
        $redirectUrl = 'https://accounts.google.com/o/oauth2/auth?client_id=test';

        $provider = Mockery::mock();
        $provider->shouldReceive('scopes')
            ->with([
                'https://www.googleapis.com/auth/gmail.readonly',
                'https://www.googleapis.com/auth/calendar.events',
            ])
            ->andReturnSelf();
        $provider->shouldReceive('with')
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->andReturnSelf();
        $provider->shouldReceive('redirect')
            ->andReturn(new RedirectResponse($redirectUrl));

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->get('/auth/google');

        $response->assertStatus(302);
        $response->assertRedirect($redirectUrl);
    }

    public function test_callback_redirects_back_to_auth_google_on_failure(): void
    {
        Socialite::shouldReceive('driver')
            ->with('google')
            ->andThrow(new \Exception('OAuth fail'));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/auth/google');
        $response->assertSessionHasErrors(['auth']);
    }

    public function test_callback_creates_user_and_google_account(): void
    {
        $socialiteUser = new class {
            public string $email = 'test@example.com';
            public string $name = 'Test User';
            public string $id = 'google-id-123';
            public string $token = 'access-token';
            public string $refreshToken = 'refresh-token';
            public int $expiresIn = 3600;
        };

        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $this->assertDatabaseHas('google_accounts', [
            'google_id' => 'google-id-123',
            'refresh_token' => 'refresh-token',
        ]);
    }
}
