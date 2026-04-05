<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_supported_locales(): void
    {
        $response = $this->getJson('/api/v1/locales');

        $response->assertStatus(200)
            ->assertJsonStructure(['locales', 'current'])
            ->assertJsonFragment(['code' => 'en'])
            ->assertJsonFragment(['code' => 'ar'])
            ->assertJsonFragment(['code' => 'fr']);
    }

    public function test_get_translations_for_locale(): void
    {
        $response = $this->getJson('/api/v1/locales/en/translations');

        $response->assertStatus(200)
            ->assertJsonStructure(['locale', 'translations']);
    }

    public function test_update_locale_preference_requires_auth(): void
    {
        $response = $this->putJson('/api/v1/locale', ['locale' => 'ar']);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_locale(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/v1/locale', ['locale' => 'fr']);

        $response->assertStatus(200);
        $user->refresh();
        $this->assertEquals('fr', $user->locale);
    }
}
