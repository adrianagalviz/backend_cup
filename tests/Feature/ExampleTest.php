<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_api_health_endpoint_returns_a_successful_response(): void
    {
        $response = $this->getJson('/api/v1/salud');

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'mensaje' => 'API REST del sistema CUP FICCT disponible.',
        ]);
    }

    public function test_the_api_returns_json_when_route_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/ruta-inexistente');

        $response->assertStatus(404);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Ruta API no encontrada.',
            'errores' => [],
        ]);
    }

    public function test_the_api_returns_json_for_missing_post_route(): void
    {
        $response = $this->postJson('/api/v1/ruta-inexistente');

        $response->assertStatus(404);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Ruta API no encontrada.',
            'errores' => [],
        ]);
    }

    public function test_login_requires_user_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_student_login_requires_code(): void
    {
        $response = $this->postJson('/api/v1/auth/alumno/login', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_firebase_login_requires_token(): void
    {
        $response = $this->postJson('/api/v1/auth/firebase', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_profile_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/auth/perfil');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_logout_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }
}
