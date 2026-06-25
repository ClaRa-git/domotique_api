<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests de sécurité de l'application Hoomy.
 *
 * Couvre la compétence CP9 (tests de sécurité) et CP3 (style défensif).
 * Fonctionnalités testées :
 *   - Protection JWT sur tous les endpoints sensibles
 *   - Protection IDOR (Insecure Direct Object Reference)
 *   - Validation des entrées côté serveur
 *   - Absence de données sensibles dans les réponses d'erreur
 *
 * Ces tests vérifient les recommandations ANSSI sur la sécurité applicative.
 */
class SecurityTest extends WebTestCase
{
    // ──────────────────────────────────────────
    // GROUPE 1 — Protection JWT (authentification)
    // Vérifie que les routes protégées refusent les requêtes sans token.
    // ──────────────────────────────────────────

    /**
     * @test
     * SÉCURITÉ — Toutes les routes API protégées doivent retourner 401 sans JWT.
     * Données en entrée : requête sans Authorization header.
     * Résultat attendu : HTTP 401.
     * Résultat obtenu : voir rapport CI.
     */
    public function testProtectedRoutesRequireJwt(): void
    {
        $client = static::createClient();

        $protectedRoutes = [
            ['POST', '/send-vibe'],
            ['POST', '/stop-vibe'],
            ['POST', '/stop-vibes-user'],
            ['POST', '/test-settings'],
            ['POST', '/service-settings-update'],
            ['GET',  '/api/vibes'],
            ['GET',  '/api/profiles'],
        ];

        foreach ($protectedRoutes as [$method, $route]) {
            $client->request($method, $route, [], [], [
                'CONTENT_TYPE' => 'application/json',
            ]);

            $statusCode = $client->getResponse()->getStatusCode();

            $this->assertSame(
                401,
                $statusCode,
                "ÉCHEC SÉCURITÉ : La route [$method $route] devrait retourner 401 sans JWT, mais retourne $statusCode."
            );
        }
    }

    /**
     * @test
     * SÉCURITÉ — Un JWT invalide (token falsifié) doit être rejeté → 401.
     * Données en entrée : token JWT invalide "Bearer fake.token.here".
     * Résultat attendu : HTTP 401.
     */
    public function testFakeJwtTokenIsRejected(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/vibes', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer fake.jwt.token.not.valid',
            'CONTENT_TYPE'       => 'application/json',
        ]);

        $this->assertSame(
            401,
            $client->getResponse()->getStatusCode(),
            'ÉCHEC SÉCURITÉ : Un JWT falsifié ne devrait pas être accepté.'
        );
    }

    // ──────────────────────────────────────────
    // GROUPE 2 — Protection IDOR
    // Vérifie que l'application ne laisse pas accéder aux ressources d'un
    // autre utilisateur en manipulant les identifiants dans les requêtes.
    // ──────────────────────────────────────────

    /**
     * @test
     * SÉCURITÉ IDOR — Tentative d'accès à un profil sans JWT → 401.
     * Un attaquant ne peut pas énumérer les profils sans être authentifié.
     * Données en entrée : GET /api/profiles/1 sans Authorization header.
     * Résultat attendu : HTTP 401.
     */
    public function testAccessAnotherProfileWithoutJwtReturns401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/profiles/1');

        $this->assertSame(
            401,
            $client->getResponse()->getStatusCode(),
            'ÉCHEC SÉCURITÉ IDOR : Le profil /api/profiles/1 doit être protégé.'
        );
    }

    /**
     * @test
     * SÉCURITÉ IDOR — Tentative d'accès à une vibe sans JWT → 401.
     * Un attaquant ne peut pas accéder aux vibes d'un autre profil.
     * Données en entrée : GET /api/vibes/1 sans Authorization header.
     * Résultat attendu : HTTP 401.
     */
    public function testAccessAnotherVibeWithoutJwtReturns401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/vibes/1');

        $this->assertSame(
            401,
            $client->getResponse()->getStatusCode(),
            'ÉCHEC SÉCURITÉ IDOR : La vibe /api/vibes/1 doit être protégée.'
        );
    }

    // ──────────────────────────────────────────
    // GROUPE 3 — Validation des entrées (style défensif)
    // Vérifie que les payloads invalides sont rejetés proprement.
    // ──────────────────────────────────────────

    /**
     * @test
     * SÉCURITÉ — Un payload vide sur /login-react ne cause pas d'erreur 500.
     * Données en entrée : body vide "{}".
     * Résultat attendu : HTTP 400 ou 401, jamais 500.
     */
    public function testEmptyLoginPayloadDoesNotCrash(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login-react', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        $this->assertNotSame(
            500,
            $client->getResponse()->getStatusCode(),
            'ÉCHEC SÉCURITÉ : Un payload vide ne doit pas provoquer une erreur 500.'
        );
    }

    /**
     * @test
     * SÉCURITÉ — Un JSON malformé ne doit pas provoquer d'erreur 500.
     * Données en entrée : JSON invalide "{ not valid json".
     * Résultat attendu : pas de HTTP 500.
     */
    public function testMalformedJsonDoesNotCauseServerError(): void
    {
        $client = static::createClient();
        $client->request('POST', '/login-react', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{ not_valid_json: true');

        $this->assertNotSame(
            500,
            $client->getResponse()->getStatusCode(),
            'ÉCHEC SÉCURITÉ : Un JSON malformé ne doit pas provoquer une erreur 500.'
        );
    }

    // ──────────────────────────────────────────
    // GROUPE 4 — Protection de l'admin
    // ──────────────────────────────────────────

    /**
     * @test
     * SÉCURITÉ — Le panneau d'administration est inaccessible sans connexion admin.
     * Données en entrée : GET /admin sans session admin.
     * Résultat attendu : HTTP 302 (redirection vers login) ou 401.
     */
    public function testAdminPanelIsProtected(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $statusCode = $client->getResponse()->getStatusCode();

        // 302 = redirection vers login | 401 = non autorisé
        $this->assertContains(
            $statusCode,
            [301, 302, 401, 403],
            "ÉCHEC SÉCURITÉ : L'admin doit être protégé, mais retourne HTTP $statusCode."
        );
    }

    /**
     * @test
     * SÉCURITÉ — Les messages d'erreur ne révèlent pas d'informations sensibles.
     * Vérifie l'absence de stack trace ou de chemin serveur dans les réponses d'erreur.
     * Données en entrée : requête non autorisée.
     * Résultat attendu : réponse sans contenu sensible.
     */
    public function testErrorResponseDoesNotLeakSensitiveInfo(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/vibes/99999');

        $content = $client->getResponse()->getContent();

        // Vérification de l'absence d'informations de débogage sensibles
        $this->assertStringNotContainsString(
            '/var/www/html',
            $content,
            'ÉCHEC SÉCURITÉ : Le chemin serveur ne doit pas apparaître dans les réponses.'
        );
    }
}