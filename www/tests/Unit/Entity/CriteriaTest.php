<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Criteria;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Criteria.
 *
 * Couvre la compétence CP3 (composants métier) et CP9 (plan de tests).
 * Fonctionnalité testée : gestion des critères émotionnels (humeur, ton, stress).
 * Cas nominaux et cas limites (valeurs extrêmes).
 */
class CriteriaTest extends TestCase
{
    private Criteria $criteria;

    protected function setUp(): void
    {
        $this->criteria = new Criteria();
    }

    // ──────────────────────────────────────────
    // Tests valeurs nominales
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie qu'un nouveau Criteria n'a pas d'id.
     */
    public function testNewCriteriaHasNoId(): void
    {
        $this->assertNull($this->criteria->getId());
    }

    /**
     * @test
     * Vérifie la valeur de mood (humeur) — cas nominal.
     */
    public function testSetAndGetMood(): void
    {
        $this->criteria->setMood(5);
        $this->assertSame(5, $this->criteria->getMood());
    }

    /**
     * @test
     * Vérifie la valeur de tone (ton) — cas nominal.
     */
    public function testSetAndGetTone(): void
    {
        $this->criteria->setTone(3);
        $this->assertSame(3, $this->criteria->getTone());
    }

    /**
     * @test
     * Vérifie la valeur de stress — cas nominal.
     */
    public function testSetAndGetStress(): void
    {
        $this->criteria->setStress(7);
        $this->assertSame(7, $this->criteria->getStress());
    }

    // ──────────────────────────────────────────
    // Tests cas limites (valeurs extrêmes)
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie que mood accepte la valeur minimale (0).
     */
    public function testMoodAcceptsMinValue(): void
    {
        $this->criteria->setMood(0);
        $this->assertSame(0, $this->criteria->getMood());
    }

    /**
     * @test
     * Vérifie que mood accepte la valeur maximale (10).
     */
    public function testMoodAcceptsMaxValue(): void
    {
        $this->criteria->setMood(10);
        $this->assertSame(10, $this->criteria->getMood());
    }

    /**
     * @test
     * Vérifie que stress accepte la valeur minimale (0).
     */
    public function testStressAcceptsMinValue(): void
    {
        $this->criteria->setStress(0);
        $this->assertSame(0, $this->criteria->getStress());
    }

    /**
     * @test
     * Vérifie que stress accepte la valeur maximale (10).
     */
    public function testStressAcceptsMaxValue(): void
    {
        $this->criteria->setStress(10);
        $this->assertSame(10, $this->criteria->getStress());
    }

    // ──────────────────────────────────────────
    // Tests valeurs par défaut
    // ──────────────────────────────────────────

    /**
     * @test
     * Vérifie que les valeurs par défaut sont null avant initialisation.
     */
    public function testDefaultValuesAreNull(): void
    {
        $this->assertNull($this->criteria->getMood());
        $this->assertNull($this->criteria->getTone());
        $this->assertNull($this->criteria->getStress());
    }

    /**
     * @test
     * Vérifie que les valeurs peuvent être mises à jour (édition de criteria).
     */
    public function testCriteriaValuesCanBeUpdated(): void
    {
        $this->criteria->setMood(3)->setTone(5)->setStress(2);
        $this->criteria->setMood(8)->setTone(1)->setStress(9);

        $this->assertSame(8, $this->criteria->getMood());
        $this->assertSame(1, $this->criteria->getTone());
        $this->assertSame(9, $this->criteria->getStress());
    }

    /**
     * @test
     * Vérifie que les setters retournent l'instance (fluent interface).
     */
    public function testSettersReturnFluentInterface(): void
    {
        $result = $this->criteria->setMood(5);
        $this->assertInstanceOf(Criteria::class, $result);
    }
}