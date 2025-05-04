# 📦 Projet Domotique Hoomy

## Application de bien être qui permet de créer des vibes qui contrôleront des appareils domotiques

## 📋 Prérequis

### ⚠️ BIEN LIRE TOUTE LA DOCUMENTATION

## 🚀 Démarrage de Docker

Pour démarrer les conteneurs Docker, exécutez :

```bash
docker-compose up
```

## ⚙️ Configuration du fichier d'alias

1. Ouvrez le fichier de configuration de votre terminal :

```bash
nano ~/.bashrc
```

1. Ajoutez le script suivant pour charger les alias dynamiquement :

```bash
load_aliases() {
  if [ -f "$(pwd)/aliases.sh" ]; then
      . "$(pwd)/aliases.sh"
  fi
}

# Appeler la fonction chaque fois que le répertoire est changé
cd() {
  builtin cd "$@" && load_aliases
}

# Charger les alias au démarrage du shell si le fichier existe dans le répertoire actuel
load_aliases
```

1. Rechargez votre fichier `.bashrc` :

```bash
source ~/.bashrc
```

1. Configurez le fichier `.bash_profile` (ou `.profile`) :

```bash
nano ~/.bash_profile
```

1. Ajoutez cette ligne si elle n'existe pas :

```bash
if [ -f ~/.bashrc ]; then
    source ~/.bashrc
fi
```

1. Rechargez le fichier `.bash_profile` :

```bash
source ~/.bash_profile
```

1. Dans le fichier `aliases.sh`, redéfinissez les alias comme souhaité.

## 🛠 Technologies utilisées

- ![PHP](https://img.shields.io/badge/PHP-8.x-787CB5?logo=php) PHP 8.x
- ![Symfony](https://img.shields.io/badge/Symfony-7-black?logo=symfony) Symfony 7
- ![MySQL](https://img.shields.io/badge/MySQL-5.7-4479A1?logo=mysql) MySQL
- ![Composer](https://img.shields.io/badge/Composer-2.x-885630?logo=composer) Composer pour la gestion des dépendances
- ![Node.js](https://img.shields.io/badge/Node.js-20.x-339933?logo=node.js) Node pour la gestion des librairies

## 📦 Installation du projet Symfony

```
bash

ccomposer install

```
nnpm
npm i

```

## Import de la base de donnée (pas de fixture)
```
db-import
```

⚠️ **Attention** : Vérifiez votre .env avec les valeurs de vos variables d'environnement définies précédemment.

## 🎉 ENJOY :)


mkdir -p www/config/jwt
openssl genpkey -out www/config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in www/config/jwt/private.pem -out www/config/jwt/public.pem -pubout# domotique_api

pour appairer un device (exemple) : 
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

