# ✅ Checklist - Déploiement Automatique Codyssey

## 🎯 Configuration Initiale (à faire une seule fois)

### Sur ta machine locale

- [ ] Exécuter `.github/setup-ci.sh`
- [ ] Copier la clé SSH privée générée
- [ ] Aller sur GitHub : https://github.com/APlouzeau/codyssey/settings/secrets/actions
- [ ] Créer les 4 secrets :
  - [ ] `VPS_OVH_SSH_KEY` (la clé complète avec BEGIN/END)
  - [ ] `VPS_HOST` (ex: `135.125.106.184` ou `vps.plouzor.fr`)
  - [ ] `VPS_USER` (ex: `eyola`)
  - [ ] `VPS_PATH` (ex: `/home/eyola/projects/projects/prod/codyssey`)

### Sur le VPS

- [ ] S'assurer que le projet est cloné dans `VPS_PATH`
- [ ] S'assurer que le fichier `.env.prod` existe et est configuré
- [ ] Vérifier que Docker et Docker Compose sont installés
- [ ] Vérifier que l'user peut exécuter Docker : `docker ps`
- [ ] Si nécessaire : `sudo usermod -aG docker $USER` puis se reconnecter

## 🚀 Utilisation Quotidienne

### Déploiement Automatique

1. Travaille sur ta branche de développement
2. Crée une PR vers `pre-prod` ou `main`
3. Merge la PR
4. 🎉 Le déploiement se lance automatiquement !

### Déploiement Manuel

1. Va sur GitHub : https://github.com/APlouzeau/codyssey/actions
2. Clique sur "🚀 Deploy to Production"
3. Clique sur "Run workflow"
4. Choisis la branche
5. Clique sur "Run workflow"

## 📊 Vérification

### Après un déploiement

- [ ] Vérifier que le workflow est vert dans l'onglet Actions
- [ ] Tester l'app : https://codyssey.plouzor.fr
- [ ] Si problème, consulter les logs du workflow
- [ ] Si besoin, SSH sur le VPS et vérifier les conteneurs : `docker ps`

### En cas d'erreur

1. Lire les logs dans l'onglet Actions de GitHub
2. Vérifier les secrets GitHub (sont-ils corrects ?)
3. Tester la connexion SSH manuellement :
   ```bash
   ssh -i ~/.ssh/github_actions_codyssey VPS_USER@VPS_HOST
   ```
4. Sur le VPS, vérifier les logs Docker :
   ```bash
   cd VPS_PATH
   docker compose -f docker-compose.prod.yml logs
   ```

## 🔄 Workflow de Développement Recommandé

```
feature/ma-fonctionnalite
          ↓
    (PR + Review)
          ↓
       pre-prod  ← Test en pré-prod
          ↓
    (PR + Review)
          ↓
        main     ← Déploiement en production 🚀
```

## 📚 Documentation

- **Setup complet** : `.github/GITHUB_ACTIONS_SETUP.md`
- **Déploiement manuel** : `DEPLOY.md`
- **Commandes utiles** : `makefile`

## 🆘 Support

En cas de problème, vérifie :
1. Les logs GitHub Actions
2. Les secrets GitHub (Settings > Secrets)
3. La connexion SSH au VPS
4. Les logs Docker sur le VPS
5. Le fichier `.env.prod` sur le VPS

## 🎓 Ressources

- [GitHub Actions Docs](https://docs.github.com/en/actions)
- [Docker Compose Docs](https://docs.docker.com/compose/)
- [Symfony Deployment](https://symfony.com/doc/current/deployment.html)
