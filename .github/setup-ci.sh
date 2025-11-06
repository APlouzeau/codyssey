#!/bin/bash

# 🔐 Script de setup pour GitHub Actions - Codyssey
# Ce script aide à configurer les secrets nécessaires pour le déploiement automatique

set -e

echo "🤖 Setup GitHub Actions pour Codyssey"
echo "======================================"
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les étapes
step() {
    echo -e "${GREEN}▶${NC} $1"
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

error() {
    echo -e "${RED}✗${NC} $1"
}

# Vérifier si on est sur le VPS ou en local
if [ -d "/home/eyola/codyssey" ] || [ -d "/var/www/codyssey" ]; then
    echo "🖥️  Détection : vous êtes probablement sur le VPS"
    ON_VPS=true
else
    echo "💻 Détection : vous êtes probablement en local"
    ON_VPS=false
fi

echo ""

# 1. Génération de la clé SSH
step "1. Génération de la clé SSH pour GitHub Actions"
echo ""

SSH_KEY_PATH="$HOME/.ssh/github_actions_codyssey"

if [ -f "$SSH_KEY_PATH" ]; then
    warning "La clé $SSH_KEY_PATH existe déjà"
    read -p "Voulez-vous la recréer ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "✓ Utilisation de la clé existante"
    else
        ssh-keygen -t ed25519 -C "github-actions-codyssey" -f "$SSH_KEY_PATH" -N ""
        echo "✓ Nouvelle clé générée"
    fi
else
    ssh-keygen -t ed25519 -C "github-actions-codyssey" -f "$SSH_KEY_PATH" -N ""
    echo "✓ Clé générée"
fi

echo ""
step "2. Affichage de la clé PRIVÉE (pour GitHub Secret: SSH_PRIVATE_KEY)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
cat "$SSH_KEY_PATH"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
warning "⚠️  IMPORTANT : Copie TOUT le contenu ci-dessus (y compris BEGIN et END)"
echo "   et colle-le dans GitHub > Settings > Secrets > SSH_PRIVATE_KEY"
echo ""
read -p "Appuyer sur Entrée quand c'est fait..."

echo ""
step "3. Configuration de la clé PUBLIQUE sur le VPS"
echo ""

if [ "$ON_VPS" = true ]; then
    # On est sur le VPS, on peut l'ajouter directement
    if grep -q "github-actions-codyssey" "$HOME/.ssh/authorized_keys" 2>/dev/null; then
        warning "La clé publique est déjà dans authorized_keys"
    else
        cat "${SSH_KEY_PATH}.pub" >> "$HOME/.ssh/authorized_keys"
        chmod 600 "$HOME/.ssh/authorized_keys"
        echo "✓ Clé publique ajoutée à authorized_keys"
    fi
else
    # On est en local, on doit donner les instructions
    echo "Tu dois ajouter cette clé PUBLIQUE sur ton VPS :"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    cat "${SSH_KEY_PATH}.pub"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Commandes à exécuter sur le VPS :"
    echo ""
    echo "  ssh user@ton-vps"
    echo "  echo '$(cat ${SSH_KEY_PATH}.pub)' >> ~/.ssh/authorized_keys"
    echo "  chmod 600 ~/.ssh/authorized_keys"
    echo ""
    read -p "Appuyer sur Entrée quand c'est fait..."
fi

echo ""
step "4. Récapitulatif des secrets GitHub à configurer"
echo ""
echo "Va sur : https://github.com/APlouzeau/codyssey/settings/secrets/actions"
echo ""
echo "Et configure ces 4 secrets :"
echo ""
echo "┌─────────────────────────────────────────────────────────────┐"
echo "│ Secret            │ Valeur                                  │"
echo "├─────────────────────────────────────────────────────────────┤"
echo "│ SSH_PRIVATE_KEY   │ (déjà copié à l'étape 2)               │"
echo "│ VPS_HOST          │ IP ou hostname de ton VPS              │"
echo "│ VPS_USER          │ Nom d'utilisateur SSH                  │"
echo "│ VPS_PATH          │ Chemin du projet sur le VPS            │"
echo "└─────────────────────────────────────────────────────────────┘"
echo ""

# Essayer de deviner les valeurs
echo "💡 Suggestions basées sur ton environnement :"
echo ""

if [ "$ON_VPS" = true ]; then
    echo "  VPS_USER = $USER"
    echo "  VPS_PATH = $PWD"
else
    echo "  VPS_HOST = codyssey.plouzor.fr (ou l'IP de ton VPS)"
    echo "  VPS_USER = eyola (ou ton nom d'utilisateur SSH)"
    echo "  VPS_PATH = /home/eyola/codyssey (ou le chemin du projet)"
fi

echo ""
step "5. Test de connexion SSH"
echo ""

if [ "$ON_VPS" = false ]; then
    echo "Pour tester la connexion SSH depuis GitHub Actions :"
    read -p "Entre l'host du VPS (ex: 123.45.67.89) : " VPS_HOST
    read -p "Entre le user SSH (ex: eyola) : " VPS_USER
    
    echo ""
    echo "Test de connexion..."
    if ssh -i "$SSH_KEY_PATH" -o StrictHostKeyChecking=no "$VPS_USER@$VPS_HOST" "echo 'Connexion réussie !'"; then
        echo "✓ Connexion SSH OK !"
    else
        error "Échec de connexion SSH"
        echo "Vérifie que :"
        echo "  - La clé publique est bien dans ~/.ssh/authorized_keys sur le VPS"
        echo "  - Le firewall autorise SSH (port 22)"
        echo "  - L'adresse et le user sont corrects"
    fi
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✨ Setup terminé !"
echo ""
echo "📚 Prochaines étapes :"
echo "  1. Configure les 4 secrets dans GitHub"
echo "  2. Fais un commit et push sur main ou pre-prod"
echo "  3. Regarde le workflow se déclencher dans l'onglet Actions"
echo ""
echo "📖 Documentation complète : .github/GITHUB_ACTIONS_SETUP.md"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
