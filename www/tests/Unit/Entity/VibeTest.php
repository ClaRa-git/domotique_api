<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Criteria;
use App\Entity\Icon;
use App\Entity\Playlist;
use App\Entity\Profile;
use App\Entity\Setting;
use App\Entity\Vibe;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Vibe.
 *
 * Couvre la compétence CP3 (composants métier) et CP9 (plan de tests).
 * Fonctionnalité testée : gestion des vibes (création, édition, suppression).
 */
class VibeTest extends TestCase
{
    private Vibe $vibe;

    /**
     * Initialisation d'une Vibe propre avant chaque test.
     */
    protected function setUp(): void
    {
        $this->vibe = new Vibe();
    }

    // ──────────────────────────────────────────
    // Tests des propriétés de base
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie qu'une nouvelle Vibe n'a pas d'id (pas encore persistée).
     */
    public function testNewVibeHasNoId(): void
    {
        $this->assertNull($this->vibe->getId());
    }

    /**
     * @test
     * Vérifie que le label est correctement défini et retourné.
     */
    public function testSetAndGetLabel(): void
    {
        $this->vibe->setLabel('Chill');
        $this->assertSame('Chill', $this->vibe->getLabel());
    }

    /**
     * @test
     * Vérifie que le label peut être modifié (édition).
     */
    public function testLabelCanBeUpdated(): void
    {
        $this->vibe->setLabel('Chill');
        $this->vibe->setLabel('Energy');
        $this->assertSame('Energy', $this->vibe->getLabel());
    }

    /**
     * @test
     * Vérifie qu'une Vibe sans label retourne null.
     */
    public function testDefaultLabelIsNull(): void
    {
        $this->assertNull($this->vibe->getLabel());
    }

    // ──────────────────────────────────────────
    // Tests des relations
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie l'association d'un Criteria à une Vibe.
     */
    public function testSetAndGetCriteria(): void
    {
        $criteria = new Criteria();
        $this->vibe->setCriteria($criteria);
        $this->assertSame($criteria, $this->vibe->getCriteria());
    }

    /**
     * @test
     * Vérifie que le Criteria peut être retiré (null).
     */
    public function testCriteriaCanBeSetToNull(): void
    {
        $criteria = new Criteria();
        $this->vibe->setCriteria($criteria);
        $this->vibe->setCriteria(null);
        $this->assertNull($this->vibe->getCriteria());
    }

    /**
     * @test
     * Vérifie l'association d'un Profile à une Vibe.
     */
    public function testSetAndGetProfile(): void
    {
        $profile = new Profile();
        $this->vibe->setProfile($profile);
        $this->assertSame($profile, $this->vibe->getProfile());
    }

    /**
     * @test
     * Vérifie l'association d'une Playlist à une Vibe.
     */
    public function testSetAndGetPlaylist(): void
    {
        $playlist = new Playlist();
        $this->vibe->setPlaylist($playlist);
        $this->assertSame($playlist, $this->vibe->getPlaylist());
    }

    /**
     * @test
     * Vérifie l'association d'un Icon à une Vibe.
     */
    public function testSetAndGetIcon(): void
    {
        $icon = new Icon();
        $this->vibe->setIcon($icon);
        $this->assertSame($icon, $this->vibe->getIcon());
    }

    // ──────────────────────────────────────────
    // Tests des collections (Settings)
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie que la collection de Settings est vide à l'initialisation.
     */
    public function testSettingsCollectionIsEmptyOnInit(): void
    {
        $this->assertCount(0, $this->vibe->getSettings());
    }

    /**
     * @test
     * Vérifie l'ajout d'un Setting à la Vibe.
     */
    public function testAddSetting(): void
    {
        $setting = new Setting();
        $this->vibe->addSetting($setting);
        $this->assertCount(1, $this->vibe->getSettings());
        $this->assertTrue($this->vibe->getSettings()->contains($setting));
    }

    /**
     * @test
     * Vérifie que le même Setting ne peut pas être ajouté deux fois (unicité).
     */
    public function testAddSameSettingTwiceDoesNotDuplicate(): void
    {
        $setting = new Setting();
        $this->vibe->addSetting($setting);
        $this->vibe->addSetting($setting);
        $this->assertCount(1, $this->vibe->getSettings());
    }

    /**
     * @test
     * Vérifie la suppression d'un Setting de la Vibe.
     */
    public function testRemoveSetting(): void
    {
        $setting = new Setting();
        $this->vibe->addSetting($setting);
        $this->vibe->removeSetting($setting);
        $this->assertCount(0, $this->vibe->getSettings());
    }

    // ──────────────────────────────────────────
    // Test du fluent interface (return $this)
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie que les setters retournent bien l'instance (fluent interface).
     */
    public function testSettersReturnFluentInterface(): void
    {
        $result = $this->vibe->setLabel('Test');
        $this->assertInstanceOf(Vibe::class, $result);
    }
}