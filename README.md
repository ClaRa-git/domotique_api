# 🏠 Projet Domotique Hoomy

Application de bien-être permettant de créer des **vibes** qui contrôlent des appareils domotiques via MQTT.

---

## Prérequis

- [Docker](https://www.docker.com/) et Docker Compose installés
- Bash (Linux / macOS) ou WSL (Windows)

> **Lire en entier avant de commencer.**

---

## Technologies

| Technologie | Version |
|---|---|
| PHP | 8.2 |
| Symfony | 7.3 |
| API Platform | 4.1 |
| EasyAdmin | 4.24 |
| MariaDB | latest |
| Apache | php:8.2-apache |
| Node.js | 20 |
| Composer | 2 |
| Webpack Encore | 6.0 |
| Bootstrap | 5.3 |
| Eclipse Mosquitto (MQTT) | latest |

---

## Ports exposés

| Service | URL / Port |
|---|---|
| Application web | [http://localhost:8082](http://localhost:8082) |
| MariaDB | `localhost:3308` |
| MQTT broker | `localhost:1883` |
| MQTT WebSocket | `localhost:9001` |

---

## Installation

### 1. Démarrer les conteneurs Docker

```bash
docker-compose up -d
```

### 2. Configurer les alias (une seule fois)

Ajoutez ce bloc dans votre `~/.bashrc` :

```bash
load_aliases() {
  if [ -f "$(pwd)/aliases.sh" ]; then
      . "$(pwd)/aliases.sh"
  fi
}

cd() {
  builtin cd "$@" && load_aliases
}

load_aliases
```

Puis rechargez :

```bash
source ~/.bashrc
```

Vérifiez aussi que `~/.bash_profile` (ou `~/.profile`) contient :

```bash
if [ -f ~/.bashrc ]; then
    source ~/.bashrc
fi
```

> Les alias ne sont disponibles **que depuis la racine du projet** (là où se trouve `aliases.sh`).

### 3. Installer les dépendances PHP

```bash
ccomposer install
```

> En cas d'erreur de dépendances, lancer `ccomposer update`.

### 4. Installer les dépendances JavaScript

Entrez dans le conteneur Apache, puis installez :

```bash
nnpm
npm install
```

### 5. Compiler les assets

```bash
# Compilation unique (développement)
npm run dev

# Compilation en mode watch (rechargement automatique)
npm run watch

# Build de production
npm run build
```

### 6. Générer les clés JWT

La commande lit automatiquement la passphrase depuis le `.env` :

```bash
cconsole lexik:jwt:generate-keypair --overwrite
```

Les fichiers sont générés avec le propriétaire `root` (dans le conteneur). Corrigez les permissions pour qu'Apache puisse lire la clé privée :

```bash
sudo chmod 644 www/config/jwt/private.pem
```

### 7. Configurer l'environnement

Vérifiez le fichier `www/.env` et adaptez si besoin :

```env
DATABASE_URL="mysql://admin:admin@mariadb_domotique:3306/domotique"
JWT_PASSPHRASE=votre_passphrase
```

### 8. Importer la base de données

```bash
db-import
```

### 9. Configurer Ollama (fonctionnalité IA — Noctys)

Ollama doit être installé sur la **machine hôte** (pas dans Docker) :

```bash
curl -fsSL https://ollama.com/install.sh | sh
```

Démarrez le serveur en écoutant sur toutes les interfaces :

```bash
OLLAMA_HOST=0.0.0.0 ollama serve
```

> **Si le port 11434 est déjà occupé**, identifiez le processus :
> ```bash
> sudo lsof -i :11434
> ```
> - Si c'est un service `ollama` existant (ex : installé via snap) : `sudo systemctl stop ollama` ou désinstallez-le.
> - Sinon : `kill -9 <PID>`

Téléchargez le modèle :

```bash
ollama pull llama3.2
```

> **Linux + Docker** : depuis le conteneur, `localhost` pointe le conteneur lui-même, pas l'hôte. Utilisez l'IP de la gateway Docker dans `www/.env` :
>
> ```env
> OLLAMA_URL=http://172.21.0.1:11434
> ```
>
> Vérifiez l'IP exacte avec :
> ```bash
> docker network inspect domotique_api_default | grep Gateway
> ```

---

## Tests & CI

Le projet inclut une suite de tests automatisés exécutés via GitHub Actions à chaque push sur `main` ou `develop`.

### Lancer les tests en local

```bash
# Tous les tests
php bin/phpunit --testdox

# Tests unitaires uniquement
php bin/phpunit tests/Unit/ --testdox

# Tests de sécurité uniquement
php bin/phpunit tests/Security/ --testdox

# Tests fonctionnels API
php bin/phpunit tests/Functional/ --testdox
```

### Structure des tests

```
www/tests/
├── Unit/Entity/
│   ├── VibeTest.php          # Tests entité Vibe
│   └── CriteriaTest.php      # Tests entité Criteria
├── Functional/
│   └── ApiEndpointTest.php   # Tests endpoints API
└── Security/
    └── SecurityTest.php      # Tests IDOR, JWT, injections
```

### Pipeline CI (`.github/workflows/ci.yml`)

| Job | Description |
|---|---|
| Backend | PHPUnit + migrations BDD test + rapport XML |
| Frontend | Build Webpack Encore |
| Qualité | PHP-CS-Fixer + ESLint |

---

## Simulateur MQTT

Pour voir l'application interagir en temps réel avec les appareils, utilisez **MQTT Explorer**.

| Paramètre | Valeur |
|---|---|
| Protocole | `mqtt://` |
| Host | `localhost` |
| Port | `1883` |
| Username / Password | *(laisser vide)* |

---

## Identifiants

### Base de données (MariaDB)

| Paramètre | Valeur |
|---|---|
| Hôte | `localhost` |
| Port | `3308` |
| Base | `domotique` |
| Utilisateur | `admin` |
| Mot de passe | `admin` |
| Root password | `superAdmin` |

### Compte applicatif (test)

| Paramètre | Valeur |
|---|---|
| Utilisateur | `admin` |
| Mot de passe | `admin` |

---

## Alias disponibles

Définis dans `aliases.sh`, accessibles depuis la racine du projet.

| Alias | Commande équivalente |
|---|---|
| `ccomposer` | `composer` dans le conteneur |
| `cconsole` | `symfony console` dans le conteneur |
| `nnpm` | Bash dans le conteneur Apache (pour npm) |
| `s777` | `chmod 777 -R ./` |
| `me` | `symfony console make:entity` |
| `mm` | `symfony console make:migration` |
| `dmm` | `symfony console doctrine:migrations:migrate` |
| `dfl` | `symfony console doctrine:fixtures:load` |
| `ddd` | `symfony console doctrine:database:drop --force` |
| `ddc` | `symfony console doctrine:database:create` |
| `ccc` | `symfony console cache:clear` |
| `db-export` | Exporter un snapshot de la BDD |
| `db-import` | Restaurer un snapshot de la BDD |

---

## Exemple : appairer un appareil

```bash
curl -X POST http://localhost:8082/api/device-init \
  -H "Content-Type: application/json" \
  -d '{
    "label": "Smart Bulb",
    "address": "192.168.1.45",
    "brand": "Philips",
    "reference": "Hue123",
    "deviceType": "light",
    "protocole": "Zigbee",
    "settings": [
      { "feature": "On/Off", "value": true },
      { "feature": "Brightness", "value": 80 },
      { "feature": "Hue", "value": 46720 }
    ]
  }'
```

> **Note :** l'URL de la route est `/api/device-init` (avec tiret), pas `/api/device/init`.

---

## Dépendances principales

### PHP (Composer)

| Package | Rôle |
|---|---|
| `api-platform/core` | API REST automatique |
| `easycorp/easyadmin-bundle` | Interface d'administration |
| `lexik/jwt-authentication-bundle` | Authentification JWT |
| `php-mqtt/client` | Client MQTT |
| `doctrine/orm` | ORM base de données |
| `symfony/messenger` | Bus de messages / workers |
| `symfony/scheduler` | Tâches planifiées |
| `vich/uploader-bundle` | Upload de fichiers |
| `nelmio/cors-bundle` | Gestion CORS |

### JavaScript (npm)

| Package | Rôle |
|---|---|
| `@symfony/webpack-encore` | Bundler assets Symfony |
| `bootstrap` | Framework CSS |
| `sass` / `sass-loader` | Compilation SCSS |
| `uuid` | Génération d'identifiants uniques |