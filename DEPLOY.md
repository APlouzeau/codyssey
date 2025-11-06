# 🚀 Déploiement Codyssey en Production

## Prérequis

1. **Créer le DNS** : Ajoute un enregistrement A pour `codyssey.plouzor.fr` pointant vers l'IP du VPS
2. **Copier les secrets** : Crée `.env.prod` depuis `.env.prod.example` et change les valeurs

## Déploiement

### 1. Configuration initiale

```bash
# Copier et éditer les variables d'environnement
cp .env.prod.example .env.prod
nano .env.prod

# CHANGE OBLIGATOIREMENT :
# - APP_SECRET (génère avec: openssl rand -hex 32)
# - POSTGRES_PASSWORD
# - DATABASE_URL (même password)
```

### 2. Build et démarrage

```bash
# Build les images Docker et démarre
make prod

# OU en commandes séparées :
make prod-build    # Build les images
make prod-up       # Démarre les conteneurs
make prod-migrate  # Lance les migrations
```

### 3. Vérifications

```bash
# Voir les logs
make prod-logs

# Vérifier que tout tourne
docker compose -f docker-compose.prod.yml ps

# Tester l'app
curl https://codyssey.plouzor.fr
```

## Architecture

```
codyssey.plouzor.fr (HTTPS via Traefik)
         ↓
    Nginx (Alpine)
         ↓
  PHP-FPM 8.3 (Symfony)
         ↓
  PostgreSQL 16
```

## Stack Technique

- **Backend**: Symfony 7 + PHP 8.3
- **Frontend**: Webpack Encore + Stimulus + Turbo + React
- **Database**: PostgreSQL 16
- **Web Server**: Nginx Alpine
- **Reverse Proxy**: Traefik (avec SSL Let's Encrypt)

## Commandes utiles

```bash
# Logs en temps réel
make prod-logs

# Redémarrer
make prod-down && make prod-up

# Rebuild complet
make prod

# Exécuter une commande dans PHP
docker compose -f docker-compose.prod.yml exec php php bin/console <commande>

# Accéder au shell PHP
docker compose -f docker-compose.prod.yml exec php sh

# Accéder à PostgreSQL
docker compose -f docker-compose.prod.yml exec database psql -U codyssey -d codyssey_prod
```

## Mise à jour

```bash
# Pull les derniers changements
git pull

# Rebuild et redéploie
make prod

# OU rebuild uniquement l'app (plus rapide)
docker compose -f docker-compose.prod.yml build php
docker compose -f docker-compose.prod.yml up -d php
make prod-migrate
```

## Troubleshooting

### Certificat SSL pas généré
Attends 2-3 minutes après le premier démarrage pour que Traefik génère le certificat Let's Encrypt.

### Erreur 502 Bad Gateway
```bash
# Vérifie que PHP tourne
docker logs codyssey-php

# Vérifie la config Nginx
docker logs codyssey-nginx
```

### Problème de base de données
```bash
# Vérifie que PostgreSQL est ready
docker logs codyssey-db

# Relance les migrations
make prod-migrate
```

## URLs

- **App**: https://codyssey.plouzor.fr
- **Piston API**: https://piston.plouzor.fr (pour l'exécution de code)

## Sécurité

✅ HTTPS forcé (Let's Encrypt)
✅ Secrets dans .env.prod (gitignore)
✅ Network isolé pour la base de données
✅ PHP-FPM en mode production (opcache activé)
✅ Assets compilés et optimisés
