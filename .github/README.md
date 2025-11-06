# 🎉 GitHub Actions - Déploiement Automatique Configuré !

## ✅ Ce qui a été créé

### 📁 Workflows GitHub Actions

1. **`.github/workflows/deploy.yml`**
   - Déploie automatiquement sur `main` et `pre-prod`
   - Se connecte au VPS via SSH
   - Build les images Docker
   - Lance les migrations
   - Clear le cache Symfony
   - Peut être déclenché manuellement

2. **`.github/workflows/test.yml`**
   - Teste et lint le code sur les PRs
   - Vérifie la syntaxe PHP
   - Lance PHPUnit
   - Build les assets JS

### 📚 Documentation

1. **`.github/GITHUB_ACTIONS_SETUP.md`**
   - Guide complet pour configurer les secrets GitHub
   - Instructions détaillées pour générer les clés SSH
   - Troubleshooting

2. **`.github/CHECKLIST.md`**
   - Checklist rapide pour la configuration initiale
   - Checklist pour vérifier les déploiements
   - Workflow de développement recommandé

3. **`.github/ROLLBACK.md`**
   - Guide de rollback en cas de problème
   - Diagnostic et résolution d'erreurs
   - Gestion des migrations de BDD

4. **`.github/NOTIFICATIONS.md`** (optionnel)
   - Exemples pour Discord, Slack, Telegram
   - Webhooks personnalisés

### 🔧 Scripts

1. **`.github/setup-ci.sh`** (exécutable)
   - Script interactif pour générer les clés SSH
   - Configure automatiquement les authorized_keys
   - Aide à la configuration des secrets

### 📝 Fichiers modifiés

1. **`README.md`**
   - Ajout de badges GitHub Actions
   - Section déploiement automatique

## 🚀 Prochaines Étapes

### 1. Configuration (À FAIRE UNE SEULE FOIS)

```bash
# Exécute le script de setup
.github/setup-ci.sh

# Ensuite, va sur GitHub et configure les 4 secrets :
# https://github.com/APlouzeau/codyssey/settings/secrets/actions
```

**Les 4 secrets à configurer :**
- `SSH_PRIVATE_KEY` : Clé SSH générée par le script
- `VPS_HOST` : Adresse du VPS (ex: `vps.plouzor.fr`)
- `VPS_USER` : User SSH (ex: `eyola`)
- `VPS_PATH` : Chemin du projet (ex: `/home/eyola/codyssey`)

### 2. Premier Déploiement

```bash
# Commite et push ces changements
git add .github/ README.md
git commit -m "ci: setup GitHub Actions for automatic deployment"
git push origin pre-prod

# Le workflow se déclenchera automatiquement !
# Regarde dans : https://github.com/APlouzeau/codyssey/actions
```

### 3. Utilisation Quotidienne

Désormais, chaque fois que tu push sur `main` ou `pre-prod`, le déploiement se fera automatiquement ! 🎉

```bash
# Workflow classique
git checkout -b feature/ma-fonctionnalite
# ... code code code ...
git commit -m "feat: nouvelle fonctionnalité"
git push origin feature/ma-fonctionnalite

# Créer une PR vers pre-prod
# Tester en pré-production
# Merger vers main pour déployer en production
```

## 📊 Monitoring

### Voir les déploiements en cours
- GitHub Actions : https://github.com/APlouzeau/codyssey/actions
- Cliquer sur un workflow pour voir les logs détaillés

### Vérifier le site
- Production : https://codyssey.plouzor.fr

### Logs sur le VPS
```bash
ssh user@vps
cd /path/to/codyssey
docker compose -f docker-compose.prod.yml logs -f
```

## 🔔 Améliorations Optionnelles

### Ajouter des notifications
Consulte `.github/NOTIFICATIONS.md` pour :
- Discord
- Slack
- Telegram
- Email
- Webhooks personnalisés

### Optimiser le workflow
- Ajouter des caches pour les dépendances
- Paralléliser les builds
- Ajouter des tests d'intégration
- Déploiement Blue/Green

### Protection des branches
Configure dans GitHub :
- Settings > Branches > Add rule
- Protéger `main` : require PR + reviews
- Protéger `pre-prod` : require status checks

## 🆘 En Cas de Problème

### Le déploiement échoue
1. Regarde les logs dans GitHub Actions
2. Vérifie les secrets GitHub
3. Teste la connexion SSH manuellement
4. Consulte `.github/ROLLBACK.md`

### Le site est down après déploiement
1. SSH sur le VPS
2. Vérifie les logs Docker
3. Rollback si nécessaire (voir `.github/ROLLBACK.md`)

### Questions ?
- Documentation complète : `.github/GITHUB_ACTIONS_SETUP.md`
- Checklist : `.github/CHECKLIST.md`
- Rollback : `.github/ROLLBACK.md`

## 🎓 Ressources

- [GitHub Actions](https://docs.github.com/en/actions)
- [Docker Compose](https://docs.docker.com/compose/)
- [Symfony Deployment](https://symfony.com/doc/current/deployment.html)
- [SSH Key Management](https://docs.github.com/en/authentication/connecting-to-github-with-ssh)

## 🏆 Avantages du Déploiement Automatique

✅ **Gain de temps** : Plus besoin de SSH manuellement  
✅ **Moins d'erreurs** : Process automatisé et testé  
✅ **Traçabilité** : Logs de tous les déploiements  
✅ **Rollback facile** : Retour arrière en un clic  
✅ **CI/CD** : Tests automatiques avant déploiement  
✅ **Collaboration** : Toute l'équipe peut déployer  

## 🎉 Félicitations !

Ton projet Codyssey est maintenant configuré pour le déploiement automatique ! 

Chaque push sur `main` ou `pre-prod` déclenchera un déploiement automatique sur ton VPS. 🚀

---

**Happy Deploying! 🎊**
