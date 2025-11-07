# 🔄 Rollback et Gestion des Erreurs

Ce guide explique comment revenir en arrière en cas de problème après un déploiement.

## 🚨 Détection d'un Problème

### Indicateurs d'un problème :
- ❌ Site inaccessible (https://codyssey.plouzor.fr down)
- ❌ Erreur 500 ou 502
- ❌ Fonctionnalité cassée
- ❌ Logs d'erreurs dans GitHub Actions
- ❌ Erreurs dans les logs Docker

## 🔍 Diagnostic Rapide

### 1. Vérifier les logs GitHub Actions
```bash
# Va sur GitHub Actions et regarde le dernier workflow
https://github.com/APlouzeau/codyssey/actions
```

### 2. Vérifier les conteneurs Docker sur le VPS
```bash
ssh user@vps
cd /path/to/codyssey

# Voir les conteneurs en cours
docker ps

# Voir les logs
docker compose -f docker-compose.prod.yml logs --tail=100

# Logs spécifiques
docker compose -f docker-compose.prod.yml logs php --tail=50
docker compose -f docker-compose.prod.yml logs nginx --tail=50
docker compose -f docker-compose.prod.yml logs database --tail=50
```

### 3. Vérifier les logs Symfony
```bash
ssh user@vps
cd /path/to/codyssey

# Logs dans le conteneur PHP
docker compose -f docker-compose.prod.yml exec php tail -f var/log/prod.log
```

## ⏪ Rollback Automatique (Git)

### Option 1 : Revenir au commit précédent

```bash
# Sur ta machine locale
git log --oneline  # Voir les derniers commits

# Revenir au commit précédent (remplace ABC123 par le bon hash)
git revert HEAD
# OU
git reset --hard ABC123
git push origin main --force

# Le déploiement automatique se relancera
```

### Option 2 : Rollback manuel sur le VPS

```bash
# SSH sur le VPS
ssh user@vps
cd /path/to/codyssey

# Voir les derniers commits
git log --oneline -10

# Revenir au commit précédent
git reset --hard HASH_DU_COMMIT_STABLE

# Rebuild et redémarrage
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml up -d --force-recreate
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear --env=prod
```

## 🛠️ Rollback Base de Données

### Si une migration pose problème

```bash
ssh user@vps
cd /path/to/codyssey

# Voir les migrations appliquées
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:status

# Rollback la dernière migration
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate prev --no-interaction

# OU rollback vers une version spécifique
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate DoctrineMigrations\\Version20231106120000 --no-interaction
```

### ⚠️ ATTENTION : Backup de la BDD avant rollback

```bash
# Backup de la base de données
docker compose -f docker-compose.prod.yml exec database pg_dump -U codyssey codyssey_prod > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurer un backup si nécessaire
docker compose -f docker-compose.prod.yml exec -T database psql -U codyssey codyssey_prod < backup_20231106_120000.sql
```

## 🔄 Redémarrage Rapide

### Redémarrer tous les conteneurs

```bash
ssh user@vps
cd /path/to/codyssey

# Redémarrage simple
docker compose -f docker-compose.prod.yml restart

# Redémarrage avec rebuild
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d --force-recreate
```

### Redémarrer un seul service

```bash
# Redémarrer PHP uniquement
docker compose -f docker-compose.prod.yml restart php

# Redémarrer Nginx uniquement
docker compose -f docker-compose.prod.yml restart nginx
```

## 🧹 Nettoyage en cas de problème

### Cleanup Docker complet

```bash
ssh user@vps
cd /path/to/codyssey

# Arrêter tout
docker compose -f docker-compose.prod.yml down

# Nettoyer les volumes orphelins (⚠️ ATTENTION : ne supprime pas la BDD)
docker system prune -f

# Rebuild from scratch
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d
```

### ⚠️ Cleanup complet avec suppression de la BDD (DANGER)

```bash
# BACKUP LA BDD D'ABORD !!!
docker compose -f docker-compose.prod.yml exec database pg_dump -U codyssey codyssey_prod > backup_emergency.sql

# Tout supprimer (y compris la BDD)
docker compose -f docker-compose.prod.yml down -v

# Rebuild et recréer la BDD
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction
```

## 🔧 Déblocage d'Urgence

### Le site est DOWN et tu dois débloquer rapidement

```bash
# 1. SSH sur le VPS
ssh user@vps
cd /path/to/codyssey

# 2. Revenir au dernier commit stable connu
git reset --hard HASH_COMMIT_STABLE

# 3. Restart rapide
docker compose -f docker-compose.prod.yml restart

# 4. Si ça marche pas, rebuild
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d --force-recreate

# 5. Clear cache
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear --env=prod
```

## 📊 Monitoring Post-Rollback

### Vérifier que tout fonctionne

```bash
# 1. Vérifier les conteneurs
docker ps
# Tous doivent être "Up" et "healthy"

# 2. Tester le site
curl -I https://codyssey.plouzor.fr
# Doit retourner 200 OK

# 3. Vérifier les logs
docker compose -f docker-compose.prod.yml logs --tail=20

# 4. Tester une page critique
curl https://codyssey.plouzor.fr/login
```

## 📝 Checklist Post-Incident

Après un rollback :

- [ ] Le site est accessible (https://codyssey.plouzor.fr)
- [ ] Les conteneurs Docker sont tous "Up"
- [ ] Pas d'erreurs dans les logs
- [ ] Les fonctionnalités critiques marchent
- [ ] La base de données est cohérente
- [ ] Un commit est fait pour fixer le problème sur main
- [ ] Documentation de l'incident (qu'est-ce qui a causé le problème)
- [ ] Mise à jour du README si nécessaire

## 🆘 Contacts d'Urgence

Si tu n'arrives pas à résoudre le problème :

1. Regarde les logs détaillés
2. Vérifie la documentation Symfony/Docker
3. Contacte l'équipe

## 🎓 Prévention

Pour éviter les problèmes :

- ✅ Toujours tester en local avant de push
- ✅ Utiliser la branche `pre-prod` pour tester avant `main`
- ✅ Faire des backups réguliers de la BDD
- ✅ Garder les logs GitHub Actions
- ✅ Monitorer les déploiements
- ✅ Ne jamais force-push sur `main` sans raison

## 📚 Ressources

- [Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/3.6/index.html)
- [Docker Compose](https://docs.docker.com/compose/)
- [Symfony Deployment](https://symfony.com/doc/current/deployment.html)
