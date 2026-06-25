# Procédure de Déploiement — Application HOOMY

**Projet :** HOOMY — Application de domotique intelligente  
**Auteur :** Claire RAMEAU  
**Version :** 1.0  
**Date :** Juin 2025  
**Stack :** PHP 8.2 / Symfony 7.3 / React / MariaDB / Docker / MQTT

---

## 1. Prérequis et dépendances

### 1.1 Logiciels requis

| Composant | Version minimale | Rôle |
|---|---|---|
| Docker | 24.x | Conteneurisation de la stack |
| Docker Compose | 2.x | Orchestration multi-conteneurs |
| Git | 2.x | Versioning et déploiement du code |
| Bash | 5.x | Exécution des scripts |

### 1.2 Ports requis (libres sur le serveur)

| Port | Service | Protocole |
|---|---|---|
| 8082 | Application web (Apache) | HTTP |
| 3308 | MariaDB | TCP |
| 1883 | MQTT Broker (Mosquitto) | TCP |
| 9001 | MQTT WebSocket | WS |

---

## 2. Environnements définis

| Environnement | Branche Git | Base de données | Usage |
|---|---|---|---|
| **Développement** | `develop` | `domotique` (locale) | Développement actif |
| **Test (SIT)** | `develop` | `domotique_test` | Tests automatisés CI |
| **Préproduction (UAT)** | `main` | `domotique_staging` | Validation client |
| **Production** | `main` (tag) | `domotique` | Utilisation finale |

---

## 3. Procédure de déploiement

### Étape 1 — Récupération du code

```bash
# Cloner le dépôt (première installation)
git clone https://github.com/<votre-repo>/hoomy.git
cd hoomy

# Ou mettre à jour (déploiement suivant)
git pull origin main
```

### Étape 2 — Démarrage des conteneurs Docker

```bash
# Démarrer tous les services en arrière-plan
docker-compose up -d

# Vérifier que les conteneurs tournent
docker-compose ps
```

Résultat attendu :
```
apache_domotique    Up    0.0.0.0:8082->80/tcp
mariadb_domotique   Up    0.0.0.0:3308->3306/tcp
mqtt-broker         Up    0.0.0.0:1883->1883/tcp
```

### Étape 3 — Installation des dépendances PHP

```bash
# Via l'alias (configuré dans aliases.sh)
ccomposer install --no-dev --optimize-autoloader

# Ou directement dans le conteneur
docker compose run --rm apache_domotique composer install \
  --no-dev --optimize-autoloader
```

### Étape 4 — Installation et build des assets JS

```bash
# Entrer dans le conteneur Apache
nnpm

# Dans le conteneur :
npm install
npm run build    # Build de production (minification, optimisation)
exit
```

### Étape 5 — Configuration de l'environnement

```bash
# Dans le dossier www/, créer/vérifier le fichier .env.local
cp www/.env www/.env.local

# Adapter les variables :
# DATABASE_URL="mysql://admin:admin@mariadb_domotique:3306/domotique"
# APP_ENV=prod
# APP_SECRET=<générer avec: php -r "echo bin2hex(random_bytes(32));">
# JWT_PASSPHRASE=<votre_passphrase>
```

### Étape 6 — Génération des clés JWT

```bash
mkdir -p www/config/jwt

# Clé privée (protégée par passphrase)
openssl genpkey -out www/config/jwt/private.pem \
  -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

# Clé publique
openssl pkey -in www/config/jwt/private.pem \
  -out www/config/jwt/public.pem -pubout

# Vérification
ls -la www/config/jwt/
```

### Étape 7 — Migration de la base de données

```bash
# Importer le snapshot de référence (première installation)
db-import

# Ou exécuter les migrations (mise à jour)
cconsole doctrine:migrations:migrate --no-interaction

# Vérification
cconsole doctrine:schema:validate
```

### Étape 8 — Vider le cache Symfony

```bash
ccc
# Équivalent : php bin/console cache:clear
```

### Étape 9 — Vérification du déploiement

```bash
# Tester l'accès à l'application
curl -I http://localhost:8082

# Vérifier les logs Apache
docker logs apache_domotique --tail=50

# Vérifier les logs MariaDB
docker logs mariadb_domotique --tail=20
```

---

## 4. Scripts de déploiement

### 4.1 Script de déploiement automatisé

```bash
#!/bin/bash
# deploy.sh — Script de déploiement HOOMY
# Usage : ./deploy.sh [env]
# Exemple : ./deploy.sh prod

set -e  # Arrêt immédiat en cas d'erreur

ENV=${1:-prod}
echo "======================================"
echo "  HOOMY — Déploiement [$ENV]"
echo "======================================"

echo "[1/6] Mise à jour du code..."
git pull origin main

echo "[2/6] Démarrage des conteneurs..."
docker-compose up -d

echo "[3/6] Installation PHP..."
docker compose run --rm apache_domotique \
  composer install --no-dev --optimize-autoloader

echo "[4/6] Build des assets..."
docker compose exec apache_domotique bash -c "npm install && npm run build"

echo "[5/6] Migration base de données..."
docker compose run --rm apache_domotique \
  php bin/console doctrine:migrations:migrate --no-interaction --env=$ENV

echo "[6/6] Vider le cache..."
docker compose run --rm apache_domotique \
  php bin/console cache:clear --env=$ENV

echo "======================================"
echo "  ✅ Déploiement terminé !"
echo "  Application : http://localhost:8082"
echo "======================================"
```

### 4.2 Script de rollback

```bash
#!/bin/bash
# rollback.sh — Retour arrière sur le dernier déploiement stable
# Usage : ./rollback.sh

set -e

echo "⚠️  Rollback en cours..."

# Revenir au commit précédent
git log --oneline -5
read -p "Entrez le hash du commit cible : " COMMIT_HASH
git checkout $COMMIT_HASH

# Réimporter la base de données depuis le dernier snapshot
db-import

# Redéployer
./deploy.sh

echo "✅ Rollback terminé."
```

### 4.3 Script d'export de la base de données

```bash
# Exporter un snapshot avant déploiement (précaution)
db-export
# Fichier créé dans ./db/
```

---

## 5. Procédure d'exécution des tests avant déploiement

Avant tout déploiement en UAT ou production, les tests suivants doivent être exécutés et validés :

### 5.1 Tests d'intégration (SIT — automatique)

```bash
# Lancer la suite complète en environnement test
docker compose run --rm apache_domotique \
  php bin/phpunit --testdox --env=test
```

Critère de passage : **0 test en échec**.

### 5.2 Tests système — checklist manuelle

| # | Test | Procédure | Résultat attendu |
|---|---|---|---|
| 1 | Login utilisateur | Aller sur `/`, se connecter | Redirection vers l'accueil |
| 2 | Création d'une vibe | Menu Ambiances > Créer une vibe | Vibe apparaît dans la liste |
| 3 | Envoi d'une vibe | Sélectionner une vibe > Valider | Confirmation + MQTT envoyé |
| 4 | Gestion de planning | Menu Planning > Créer un événement | Événement visible dans le planning |
| 5 | Interface admin | `/admin` avec compte admin | Dashboard EasyAdmin accessible |
| 6 | Accès non autorisé | Tenter d'accéder à `/api/vibes` sans JWT | HTTP 401 retourné |

### 5.3 Tests d'acceptation (UAT)

À réaliser par l'utilisateur final sur l'environnement de staging :
- Parcours complet : connexion → création vibe → réglage devices → envoi
- Vérification du planning automatique
- Validation de la recommandation par humeur

---

## 6. Différents types de mise en production

| Type | Description | Usage Hoomy |
|---|---|---|
| **Totale** | Remplacement complet de l'application | Nouvelle version majeure |
| **Partielle** | Mise à jour d'un ou plusieurs services | Mise à jour d'un composant (ex: MQTT) |
| **Progressive (Blue-Green)** | Basculement progressif du trafic | Non applicable (usage mono-instance) |

Pour ce projet, le type de mise en production retenu est **totale**, via `docker-compose down && docker-compose up -d`.

---

## 7. Veille technologique — Sécurité du déploiement

| Sujet | Source | Action |
|---|---|---|
| Failles Docker | [docker.com/security](https://docker.com/security) | Mise à jour des images à chaque déploiement |
| CVE MariaDB | [mariadb.com/kb/en/security/](https://mariadb.com/kb/en/security/) | Surveiller les bulletins de sécurité |
| Symfony Security | [symfony.com/blog/category/security](https://symfony.com/blog/category/security) | `composer audit` avant chaque déploiement |
| ANSSI recommandations | [ssi.gouv.fr](https://www.ssi.gouv.fr) | Revue semestrielle |
| Dépendances NPM | `npm audit` | Avant chaque build de production |

```bash
# Vérification des vulnérabilités avant déploiement
docker compose run --rm apache_domotique composer audit
docker compose exec apache_domotique npm audit
```