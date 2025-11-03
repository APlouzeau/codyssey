# 🚀 Projet Symfony 7.3

Bienvenue dans le projet Symfony basé sur la version stable 7.3.

Ce guide contient les instructions nécessaires pour configurer et lancer l'application en environnement de développement local.

---

## 📋 Prérequis

Pour lancer ce projet, assurez-vous que les éléments suivants sont installés sur votre machine :

* **PHP** : Version **8.2.0** ou supérieure (requis par Symfony 7.3).
* **Composer** : Gestionnaire de dépendances pour PHP.
* **Symfony CLI** : Outil en ligne de commande de Symfony (fortement recommandé).

## ⚙️ Installation du Projet (Setup)

Suivez ces étapes pour installer toutes les dépendances et configurer le projet.

### 1. Cloner le Dépôt

```bash
git clone https://github.com/APlouzeau/codyssey.git odyssey

cd odyssey
```

### 2. Installer les Dépendances PHP
Utilisez Composer pour télécharger toutes les bibliothèques PHP nécessaires :

```bash
composer install
```

### 3. Installer les Dépendances Front-end (si applicable)
Si le projet utilise des assets (CSS, JS) gérés par Webpack Encore ou similaire, installez les dépendances Node.js :

```bash
# Avec yarn
yarn install

# OU avec npm
npm install
```

### 4. Construire les Assets Front-end (si applicable)
Lancez la compilation des assets :

```bash
# En mode développement
yarn dev

# OU en mode production
yarn run build
```

## 🔑 Base de Données (BDD) et Configuration

NOTE IMPORTANTE :

Pour toute information concernant la connexion à la base de données (identifiants, nom de la BDD, etc.), veuillez contacter un administrateur ou le chef de projet.

Les variables de connexion sont généralement définies dans le fichier .env ou .env.local à la racine du projet (qui ne devrait pas être commité pour des raisons de sécurité).

Commandes Utiles pour la BDD (Après configuration)

- Créer la base de données (si elle n'existe pas) :

    ```bash
    php bin/console doctrine:database:create
    ```
- Exécuter les migrations (créer/mettre à jour le schéma de la BDD) :
    ```bash
    php bin/console doctrine:migrations:migrate
    ```

## Commandes de Démarrage et Utilitaires

Démarrer le Serveur Web Local (Recommandé pour le développement)

Symfony fournit un serveur web local pour un développement facile :

```
symfony server:start

# ou pour éviter de voir tte les logs server mettre -d

symfony server -d
```

Le projet sera accessible sur https://localhost:8000 (ou un autre port indiqué).

Autres Commandes Utiles

- Vider le Cache (Souvent nécessaire après un changement de configuration) :
    ```bash
    php bin/console cache:clear
    ```