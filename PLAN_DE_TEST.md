# Plan de Tests — Application HOOMY

**Projet :** HOOMY — Application de domotique intelligente  
**Auteur :** Claire RAMEAU  
**Version :** 1.0  
**Date :** Juin 2025  
**Environnement :** PHP 8.2 / Symfony 7.3 / React / MariaDB / Docker

---

## 1. Objectifs du plan de tests

Ce plan de tests couvre l'ensemble des fonctionnalités retenues pour l'application HOOMY conformément à la **compétence CP9** du référentiel CDA.

Il définit :
- Les types de tests à réaliser (unitaires, intégration, système, sécurité)
- L'environnement de tests dédié
- Les cas de tests avec données en entrée et résultats attendus
- La procédure d'exécution (manuelle et automatique via CI/CD)

---

## 2. Environnements de tests

| Environnement | Usage | Configuration |
|---|---|---|
| **SIT** (System Integration Testing) | Tests d'intégration automatisés | `APP_ENV=test`, MariaDB `domotique_test`, Docker |
| **UAT** (User Acceptance Testing) | Tests d'acceptation utilisateur | Environnement staging, données proches production |
| **Production** | Déploiement final | `APP_ENV=prod`, MariaDB `domotique` |

**Création de l'environnement SIT :**
```bash
# Lancer la stack de test
docker-compose up -d

# Créer la base de test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# Charger les fixtures
php bin/console doctrine:fixtures:load --env=test --no-interaction
```

---

## 3. Fonctionnalités couvertes

| ID | Fonctionnalité | Type de test |
|---|---|---|
| F01 | Authentification (login / JWT) | Unitaire + Sécurité |
| F02 | Gestion des Vibes (CRUD) | Unitaire + Intégration |
| F03 | Critères émotionnels (mood, tone, stress) | Unitaire |
| F04 | Réglages des devices par vibe | Intégration |
| F05 | Envoi de vibe via MQTT | Intégration système |
| F06 | Recommandation de vibes par humeur (IA) | Intégration |
| F07 | Gestion des playlists | Unitaire + Intégration |
| F08 | Planning d'événements | Intégration |
| F09 | Protection IDOR sur toutes les routes | Sécurité |
| F10 | Validation des entrées (payload invalide) | Sécurité |
| F11 | Panneau d'administration (EasyAdmin) | Système |
| F12 | Interface React (composants UI) | Unitaire frontend |

---

## 4. Cas de tests

### 4.1 Tests unitaires — Entités PHP

#### Entité Vibe (F02)

| ID Test | Description | Données en entrée | Résultat attendu | Résultat obtenu |
|---|---|---|---|---|
| UT-V01 | Nouvelle vibe sans id | `new Vibe()` | `getId() === null` | ✅ |
| UT-V02 | Set/get label | `setLabel('Chill')` | `getLabel() === 'Chill'` | ✅ |
| UT-V03 | Modification du label | `setLabel('Chill')` puis `setLabel('Energy')` | `getLabel() === 'Energy'` | ✅ |
| UT-V04 | Association Criteria | `setCriteria($criteria)` | `getCriteria() === $criteria` | ✅ |
| UT-V05 | Suppression Criteria | `setCriteria(null)` | `getCriteria() === null` | ✅ |
| UT-V06 | Collection Settings vide | `new Vibe()` | `count(getSettings()) === 0` | ✅ |
| UT-V07 | Ajout d'un Setting | `addSetting($setting)` | `count(getSettings()) === 1` | ✅ |
| UT-V08 | Pas de doublon Setting | `addSetting()` deux fois | `count(getSettings()) === 1` | ✅ |
| UT-V09 | Suppression Setting | `addSetting()` puis `removeSetting()` | `count(getSettings()) === 0` | ✅ |
| UT-V10 | Fluent interface | `setLabel()` retourne | Instance de `Vibe` | ✅ |

#### Entité Criteria (F03)

| ID Test | Description | Données en entrée | Résultat attendu | Résultat obtenu |
|---|---|---|---|---|
| UT-C01 | Valeurs par défaut null | `new Criteria()` | `getMood() === null` | ✅ |
| UT-C02 | Set/get mood nominal | `setMood(5)` | `getMood() === 5` | ✅ |
| UT-C03 | Set/get tone nominal | `setTone(3)` | `getTone() === 3` | ✅ |
| UT-C04 | Set/get stress nominal | `setStress(7)` | `getStress() === 7` | ✅ |
| UT-C05 | Valeur limite mood = 0 | `setMood(0)` | `getMood() === 0` | ✅ |
| UT-C06 | Valeur limite mood = 10 | `setMood(10)` | `getMood() === 10` | ✅ |
| UT-C07 | Valeur limite stress = 0 | `setStress(0)` | `getStress() === 0` | ✅ |
| UT-C08 | Mise à jour des valeurs | Modification de toutes les valeurs | Nouvelles valeurs retournées | ✅ |

---

### 4.2 Tests d'intégration — API Symfony

| ID Test | Description | Données en entrée | Résultat attendu | Résultat obtenu |
|---|---|---|---|---|
| IT-A01 | Route login accessible | `POST /login-react` sans body | HTTP ≠ 404 et ≠ 500 | ✅ |
| IT-A02 | Login mauvaises credentials | `POST /login-react` username/password incorrects | HTTP 401 | ✅ |
| IT-A03 | Réponse JSON de l'API | `GET /api/vibes` | Content-Type: application/json | ✅ |
| IT-A04 | Payload invalide device-init | `POST /api/device-init` `{"invalid":"data"}` | HTTP 400 ou 401 | ✅ |

---

### 4.3 Tests de sécurité — Protection JWT et IDOR (F09, F10)

**Fonctionnalité la plus représentative :** Protection des endpoints utilisateur contre l'accès non autorisé (IDOR).

| ID Test | Type | Description | Données en entrée | Résultat attendu | Résultat obtenu |
|---|---|---|---|---|---|
| SEC-01 | JWT | `POST /send-vibe` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-02 | JWT | `POST /stop-vibe` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-03 | JWT | `POST /stop-vibes-user` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-04 | JWT | `POST /test-settings` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-05 | JWT | `POST /service-settings-update` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-06 | JWT | `GET /api/vibes` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-07 | JWT | `GET /api/profiles` sans token | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-08 | JWT falsifié | Token `Bearer fake.jwt.token` | Header Authorization invalide | HTTP 401 | ✅ |
| SEC-09 | IDOR | `GET /api/profiles/1` sans JWT | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-10 | IDOR | `GET /api/vibes/1` sans JWT | Aucun header Authorization | HTTP 401 | ✅ |
| SEC-11 | Injection | Payload vide sur /login-react | `{}` | HTTP ≠ 500 | ✅ |
| SEC-12 | Injection | JSON malformé sur /login-react | `{ not_valid_json` | HTTP ≠ 500 | ✅ |
| SEC-13 | Admin | `GET /admin` sans session | Aucune session | HTTP 302 (redirection) | ✅ |
| SEC-14 | Info leak | Réponse d'erreur | Requête non autorisée | Pas de chemin serveur exposé | ✅ |

---

### 4.4 Tests unitaires frontend — React (F12)

| ID Test | Composant | Description | Données en entrée | Résultat attendu | Résultat obtenu |
|---|---|---|---|---|---|
| FE-01 | VibeCard | Affiche le label | `vibe.label = 'Chill'` | Texte "Chill" dans le DOM | ✅ |
| FE-02 | VibeCard | Affiche l'icône | `vibe.icon.imagePath` | Img avec src contenant l'imagePath | ✅ |
| FE-03 | VibeCard | Monte sans erreur | Vibe nominale | Composant présent dans DOM | ✅ |
| FE-04 | VibeCard | Label très long | Label > 80 caractères | Rendu sans crash | ✅ |
| FE-05 | VibeCard | Icône absente | `icon = null` | Pas d'exception levée | ✅ |
| FE-06 | VibeCard | Snapshot non-régression | Vibe nominale | Correspond au snapshot référence | ✅ |

---

## 5. Exécution des tests

### 5.1 Exécution manuelle (locale)

```bash
# Tests unitaires PHP
php bin/phpunit --testdox

# Tests de sécurité uniquement
php bin/phpunit tests/Security/ --testdox

# Tests fonctionnels API
php bin/phpunit tests/Functional/ --testdox

# Tests React
cd www && npm test -- --watchAll=false
```

### 5.2 Exécution automatique (CI/CD GitHub Actions)

Les tests sont automatiquement déclenchés à chaque `push` ou `pull_request` sur les branches `main` et `develop`.

Pipeline : `.github/workflows/ci.yml`

```
Push GitHub
    └── Job Backend (PHPUnit)
    └── Job Frontend (Jest)
    └── Job Qualité (PHP-CS-Fixer + ESLint)
    └── Job Résumé CI
```

### 5.3 Interprétation des rapports

Les rapports sont générés en format XML JUnit et uploadés comme artefacts GitHub Actions.  
En cas d'échec : identifier le test en rouge → corriger le code → re-push.

---

## 6. Veille technologique — Sécurité des tests

| Sujet | Source | Fréquence de veille |
|---|---|---|
| Vulnérabilités PHP | [ANSSI](https://www.ssi.gouv.fr) | Mensuelle |
| CVE Symfony/API Platform | [symfony.com/blog](https://symfony.com/blog) | À chaque mise à jour |
| OWASP Top 10 | [owasp.org](https://owasp.org) | Semestrielle |
| Sécurité JWT | [jwt.io](https://jwt.io) | Semestrielle |
| Évolutions PHPUnit | [phpunit.de](https://phpunit.de) | À chaque release majeure |
| Évolutions Jest | [jestjs.io](https://jestjs.io) | À chaque release majeure |

---

## 7. Critères d'acceptation

Un déploiement est autorisé si et seulement si :

- ✅ 100% des tests unitaires PHP passent
- ✅ 100% des tests de sécurité passent
- ✅ Les tests fonctionnels API ne retournent pas d'erreur inattendue
- ✅ Le build React (`npm run build`) se termine sans erreur
- ✅ Aucune erreur PHP-CS-Fixer bloquante