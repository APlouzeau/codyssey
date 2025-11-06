# 🤖 Configuration GitHub Actions - Déploiement Auto

## 📋 Secrets à configurer

Pour que le déploiement automatique fonctionne, tu dois configurer les secrets suivants dans GitHub :

### Aller dans les paramètres du repo
1. Va sur ton repo GitHub : `https://github.com/APlouzeau/codyssey`
2. Clique sur **Settings** > **Secrets and variables** > **Actions**
3. Clique sur **New repository secret**

### Secrets nécessaires

#### 1. `SSH_PRIVATE_KEY`
La clé SSH privée pour te connecter au VPS.

```bash
# Sur ta machine locale, génère une paire de clés SSH (si pas déjà fait)
ssh-keygen -t ed25519 -C "github-actions-codyssey" -f ~/.ssh/github_actions_codyssey

# Copie le contenu de la clé PRIVÉE
cat ~/.ssh/github_actions_codyssey
# Copie tout le contenu (y compris BEGIN et END)
```

**Important** : Ajoute ensuite la clé PUBLIQUE sur ton VPS :
```bash
# Sur ton VPS
cat ~/.ssh/github_actions_codyssey.pub >> ~/.ssh/authorized_keys
```

#### 2. `VPS_HOST`
L'adresse IP ou le hostname de ton VPS.

Exemple : `123.45.67.89` ou `vps.plouzor.fr`

#### 3. `VPS_USER`
Le nom d'utilisateur SSH pour te connecter au VPS.

Exemple : `eyola` ou `root` (préférable d'utiliser un user non-root)

#### 4. `VPS_PATH`
Le chemin absolu vers le répertoire du projet sur le VPS.

Exemple : `/home/eyola/codyssey` ou `/var/www/codyssey`

## 🚀 Comment ça marche ?

### Déclenchement automatique
L'action se déclenche automatiquement quand tu push sur :
- La branche `main`
- La branche `pre-prod`

### Déclenchement manuel
Tu peux aussi déclencher manuellement le déploiement :
1. Va sur l'onglet **Actions** de ton repo
2. Sélectionne le workflow "🚀 Deploy to Production"
3. Clique sur **Run workflow**
4. Choisis la branche
5. Clique sur **Run workflow**

### Processus de déploiement

Le workflow fait automatiquement :
1. ✅ Checkout du code
2. ✅ Connexion SSH au VPS
3. ✅ Pull des derniers changements
4. ✅ Build des images Docker
5. ✅ Redémarrage des conteneurs
6. ✅ Exécution des migrations
7. ✅ Clear du cache Symfony

## 🔒 Sécurité

### Bonnes pratiques
- ✅ Utilise une clé SSH dédiée pour GitHub Actions
- ✅ Ne partage JAMAIS tes secrets
- ✅ Les secrets sont chiffrés par GitHub
- ✅ Utilise un user non-root sur le VPS (avec sudo si nécessaire)

### User SSH avec sudo (recommandé)
Si tu n'utilises pas `root`, assure-toi que ton user peut exécuter Docker :

```bash
# Sur ton VPS
sudo usermod -aG docker $USER
# Déconnecte/reconnecte pour appliquer les changements
```

## 🧪 Tester le workflow

### Test initial
1. Configure tous les secrets dans GitHub
2. Fais un petit changement (ex: un commentaire dans le README)
3. Push sur `pre-prod` :
   ```bash
   git add .
   git commit -m "test: CI/CD setup"
   git push origin pre-prod
   ```
4. Va sur l'onglet **Actions** pour voir le workflow en cours

### En cas d'erreur
- Vérifie les logs dans l'onglet **Actions**
- Assure-toi que la clé SSH est correcte
- Vérifie que le chemin `VPS_PATH` existe
- Vérifie que ton user a les droits Docker

## 📝 Personnalisation

### Changer les branches de déploiement
Édite `.github/workflows/deploy.yml` :
```yaml
on:
  push:
    branches:
      - main           # Déploie depuis main
      - pre-prod       # Déploie depuis pre-prod
      - production     # Ajoute d'autres branches si besoin
```

### Ajouter des notifications
Tu peux ajouter des notifications Slack, Discord, etc. après le déploiement.

### Build conditionnel
Si tu veux builder seulement quand certains fichiers changent :
```yaml
paths:
  - 'src/**'
  - 'templates/**'
  - 'config/**'
  - 'Dockerfile'
  - 'docker-compose.prod.yml'
```

## 🆘 Dépannage

### Erreur "Permission denied (publickey)"
- Vérifie que la clé publique est bien dans `~/.ssh/authorized_keys` sur le VPS
- Vérifie que la clé privée dans `SSH_PRIVATE_KEY` est complète (avec BEGIN/END)

### Erreur "docker: command not found"
- Assure-toi que Docker est installé sur le VPS
- Vérifie que ton user est dans le groupe `docker`

### Timeout SSH
- Vérifie que le VPS est accessible : `ssh user@host`
- Vérifie les règles de firewall

## 📚 Ressources
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [SSH Agent Action](https://github.com/webfactory/ssh-agent)
