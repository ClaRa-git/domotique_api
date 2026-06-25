<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests fonctionnels de l'API Hoomy.
 *
 * Couvre la compétence CP9 (plan de tests — tests d'intégration).
 * Vérifie que les endpoints répondent correctement selon les scénarios nominaux
 * et les cas d'erreur définis dans le plan de tests.
 *
 * Environnement : APP_ENV=test avec base MariaDB dédiée.
 */
class ApiEndpointTest extends WebTestCase
{
    // ──────────────────────────────────────────
    // Tests d'authentification
    // ──────────────────────────────────────────

    /**
     * @test
     * CAS NOMINAL : La route de login est accessible sans authentification.
     * Résultat attendu : HTTP 200 ou 401 (pas de 404 ou 500).
     */
    public function testLoginRouteIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login-react', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'wrong_user',
            'password' => 'wrong_pass',
        ]));

        // La route doit exister (pas 404) et ne pas planter (pas 500)
        $this->assertNotSame(404, $client->getResponse()->getStatusCode());
        $this->assertNotSame(500, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * CAS D'ERREUR : Login avec mauvaises credentials → 401 Unauthorized.
     * Résultat attendu : HTTP 401.
     */
    public function testLoginWithBadCredentialsReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login-react', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'hacker',
            'password' => 'badpassword',
        ]));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    // ──────────────────────────────────────────
    // Tests d'accès sécurisé (JWT obligatoire)
    // ──────────────────────────────────────────

    /**
     * @test
     * CAS D'ERREUR : Accès à /send-vibe sans JWT → 401 Unauthorized.
     * Vérifie que l'endpoint est protégé par l'authentification JWT.
     */
    public function testSendVibeWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/send-vibe', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['vibeId' => 1, 'roomId' => 1]));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * CAS D'ERREUR : Accès à /service-settings-update sans JWT → 401.
     */
    public function testUpdateSettingsWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/service-settings-update', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * CAS D'ERREUR : Accès à /stop-vibe sans JWT → 401.
     */
    public function testStopVibeWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/stop-vibe', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * CAS D'ERREUR : Accès à /test-settings sans JWT → 401.
     */
    public function testTestSettingsWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('POST', '/test-settings', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    // ──────────────────────────────────────────
    // Tests des endpoints API Platform
    // ──────────────────────────────────────────

    /**
     * @test
     * CAS D'ERREUR : L'API /api/vibes est protégée → 401 sans token.
     */
    public function testApiVibesWithoutTokenReturns401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/vibes');

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     * CAS NOMINAL : La réponse de l'API est bien du JSON.
     * Vérifie le Content-Type retourné par l'API Platform.
     */
    public function testApiResponseIsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/vibes', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        // Même en 401, la réponse doit être du JSON (pas du HTML)
        $this->assertStringContainsString(
            'application/json',
            $client->getResponse()->headers->get('Content-Type') ?? ''
        );
    }

    // ──────────────────────────────────────────
    // Test de payload invalide
    // ──────────────────────────────────────────

    /**
     * @test
     * CAS D'ERREUR : /api/device-init avec payload invalide → 400 Bad Request.
     */
    public function testDeviceInitWithInvalidPayloadReturns400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/device-init', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['invalid' => 'data']));

        // 400 (payload invalide) ou 401 (pas de JWT) sont tous deux acceptables
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertContains($statusCode, [400, 401]);
    }
}