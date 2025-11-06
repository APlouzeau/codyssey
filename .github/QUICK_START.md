# 🚀 Configuration Rapide pour TON VPS

## ✅ Informations de ton VPS

- **IP publique** : `135.125.106.184`
- **Port SSH** : `22` (port standard)
- **User** : `eyola`
- **Path projet** : `/home/eyola/projects/projects/prod/codyssey`
- **Hostname** : `vps-34c4513d.vps.ovh.net`

## 📝 Secrets à configurer sur GitHub

Va sur : https://github.com/APlouzeau/codyssey/settings/secrets/actions

### 1. `VPS_OVH_SSH_KEY`
```bash
# Génère la clé SSH (si pas déjà fait)
ssh-keygen -t ed25519 -C "github-actions-codyssey" -f ~/.ssh/github_actions_codyssey

# Affiche la clé PRIVÉE à copier
cat ~/.ssh/github_actions_codyssey
```
⚠️ Copie TOUT le contenu (y compris BEGIN et END)

### 2. `VPS_HOST`
```
135.125.106.184
```
ou
```
vps-34c4513d.vps.ovh.net
```

### 3. `VPS_USER`
```
eyola
```

### 4. `VPS_PATH`
```
/home/eyola/projects/projects/prod/codyssey
```

## 🔑 Configuration de la clé SSH sur le VPS

```bash
# Tu es déjà sur le VPS, donc :

# Affiche ta clé PUBLIQUE
cat ~/.ssh/github_actions_codyssey.pub

# Ajoute-la aux clés autorisées
cat ~/.ssh/github_actions_codyssey.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

## ✅ Test de connexion SSH

```bash
# Depuis une autre machine (pas le VPS) :
ssh eyola@135.125.106.184 -i ~/.ssh/github_actions_codyssey

# Doit afficher un shell sans demander de mot de passe
```

## 🚀 Premier déploiement

```bash
# Sur le VPS (là où tu es maintenant)
git add .
git commit -m "ci: configure GitHub Actions for auto-deployment"
git push origin pre-prod

# Puis va voir sur GitHub Actions :
# https://github.com/APlouzeau/codyssey/actions
```

## 🔍 Vérification

Après configuration des secrets GitHub :

1. **Push un changement** sur `pre-prod` ou `main`
2. **Regarde les logs** : https://github.com/APlouzeau/codyssey/actions
3. **Vérifie le site** : https://codyssey.plouzor.fr

## 🐛 Troubleshooting

### "Connection refused"
- ✅ Vérifie que SSH tourne : `sudo systemctl status sshd`
- ✅ Vérifie que le firewall autorise le port 22

### "Permission denied (publickey)"
- ✅ La clé publique est dans `~/.ssh/authorized_keys` ?
- ✅ La clé privée complète est dans `VPS_OVH_SSH_KEY` ?
- ✅ Les permissions : `chmod 600 ~/.ssh/authorized_keys`

### Le workflow échoue
- ✅ Regarde les logs GitHub Actions
- ✅ Vérifie que tous les 4 secrets sont configurés (plus besoin de VPS_SSH_PORT)
- ✅ Test SSH manuel : `ssh eyola@135.125.106.184`

## 📊 État actuel

```bash
# Vérifier que tout tourne
docker compose -f docker-compose.prod.yml ps

# Vérifier les logs
docker compose -f docker-compose.prod.yml logs --tail=50

# Vérifier le site
curl -I https://codyssey.plouzor.fr
```

---

**Tu es prêt ! Il ne reste plus qu'à configurer les 5 secrets sur GitHub et ça tournera tout seul ! 🎉**
