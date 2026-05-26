# Projet Domotique Hoomy

Application de bien-être permettant de créer des **vibes** qui contrôlent des appareils domotiques via MQTT.

---

## Prérequis

- [Docker](https://www.docker.com/) et Docker Compose installés
- Bash (Linux / macOS) ou WSL (Windows)

> **A lire en entier avant de commencer.**

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

Ajoutez ce bloc dans votre `~/.bashrc` pour charger automatiquement les alias du projet :

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

### 4. Installer les dépendances JavaScript

Entrez dans le conteneur Apache puis installez les dépendances :

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

```bash
mkdir -p www/config/jwt
openssl genpkey -out www/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in www/config/jwt/private.pem -out www/config/jwt/public.pem -pubout
```

### 7. Configurer l'environnement

Vérifiez votre fichier `.env` dans `www/` et adaptez si besoin :

```env
DATABASE_URL="mysql://admin:admin@mariadb_domotique:3306/domotique"
```

### 8. Importer la base de données

```bash
db-import
```

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

Ces alias sont définis dans `aliases.sh` et accessibles une fois la configuration shell effectuée.

| Alias | Description |
|---|---|
| `ccomposer` | Exécuter Composer dans le conteneur |
| `cconsole` | Exécuter `symfony console` dans le conteneur |
| `nnpm` | Ouvrir un bash dans le conteneur Apache (pour npm) |
| `s777` | Donner les permissions 777 sur le répertoire courant |
| `me` | `symfony console make:entity` |
| `mm` | `symfony console make:migration` |
| `dmm` | `symfony console doctrine:migrations:migrate` |
| `dfl` | `symfony console doctrine:fixtures:load` |
| `ddd` | `symfony console doctrine:database:drop --force` |
| `ddc` | `symfony console doctrine:database:create` |
| `ccc` | `symfony console cache:clear` |
| `db-export` | Exporter un snapshot de la base de données |
| `db-import` | Restaurer un snapshot de la base de données |

---

## Exemple : appairer un appareil

```bash
curl -X POST http://localhost:8082/api/device/init \
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
