.PHONY: help dev stop first-install check-deps install-deps bdd bdd-create cache-clear assets-dev assets-build assets-watch prod prod-build prod-up prod-down prod-logs prod-migrate

help:
	@echo "Commandes disponibles :"
	@echo ""
	@echo "🔧 Développement local:"
	@echo "  make first-install  - Installation complète du projet"
	@echo "  make dev           - Démarre le serveur Symfony"
	@echo "  make stop          - Arrête le serveur Symfony"
	@echo "  make bdd-create    - Crée la base de données"
	@echo "  make bdd           - Lance les migrations"
	@echo "  make cache-clear   - Vide le cache Symfony"
	@echo "  make assets-dev    - Compile les assets en mode dev"
	@echo "  make assets-build  - Compile les assets pour la prod"
	@echo "  make assets-watch  - Compile les assets en mode watch"
	@echo ""
	@echo "🚀 Production (Docker + Traefik):"
	@echo "  make prod          - Build et démarre en production"
	@echo "  make prod-build    - Build les images Docker"
	@echo "  make prod-up       - Démarre les conteneurs"
	@echo "  make prod-down     - Arrête les conteneurs"
	@echo "  make prod-logs     - Affiche les logs"
	@echo "  make prod-migrate  - Lance les migrations en prod"

dev: install-deps assets-build
	@echo "🚀 Démarrage du serveur de dev..."
	@echo "💡 Si besoin : 'make assets-watch' dans un autre terminal"
	symfony server:start

stop:
	@echo "🛑 Arrêt du serveur..."
	symfony server:stop

check-deps:
	@echo "🔍 Vérification des dépendances..."
	@which composer > /dev/null || (echo "❌ Composer n'est pas installé\n   👉 Télécharge-le sur https://getcomposer.org/download/" && exit 1)
	@which node > /dev/null || (echo "❌ Node.js n'est pas installé\n   👉 Télécharge-le sur https://nodejs.org/" && exit 1)
	@which npm > /dev/null || (echo "❌ npm n'est pas installé (devrait venir avec Node.js)\n   👉 Réinstalle Node.js depuis https://nodejs.org/" && exit 1)
	@which pnpm > /dev/null || (echo "⚠️  pnpm n'est pas installé, installation..." && npm install -g pnpm)
	@echo "✅ Toutes les dépendances système sont présentes"

install-deps: check-deps
	@echo "📦 Installation des dépendances PHP..."
	@if [ -f "composer.json" ]; then \
		composer install; \
	else \
		echo "⚠️  Pas de composer.json trouvé"; \
	fi
	@echo "📦 Installation des dépendances JS..."
	@if [ ! -d "node_modules" ]; then \
		pnpm install; \
	else \
		echo "✅ node_modules existe déjà"; \
	fi

first-install: install-deps
	@echo "🎨 Compilation des assets..."
	pnpm run build
	@echo "🗄️  Configuration de la base de données..."
	make bdd-create
	make bdd
	@echo "✨ Installation terminée !"
	@echo "👉 Lance 'make dev' pour démarrer le serveur"

bdd-create:
	@echo "🗄️  Création de la base de données..."
	php bin/console doctrine:database:create --if-not-exists

bdd:
	@echo "🔄 Exécution des migrations..."
	php bin/console doctrine:migrations:migrate --no-interaction

cache-clear:
	@echo "🧹 Nettoyage du cache..."
	php bin/console cache:clear

assets-dev:
	@echo "🎨 Compilation des assets (dev)..."
	pnpm run dev

assets-build:
	@echo "🎨 Compilation des assets (prod)..."
	pnpm run build

assets-watch:
	@echo "👀 Compilation des assets en mode watch..."
	pnpm run watch

# ==============================================================================
# PRODUCTION (Docker + Traefik)
# ==============================================================================

prod-build:
	@echo "🏗️  Build des images Docker..."
	docker compose -f docker-compose.prod.yml build --no-cache

prod-up:
	@echo "🚀 Démarrage de la production..."
	docker compose -f docker-compose.prod.yml up -d
	@echo "✅ Codyssey est disponible sur https://codyssey.plouzor.fr"

prod-down:
	@echo "🛑 Arrêt de la production..."
	docker compose -f docker-compose.prod.yml down

prod: prod-build prod-up prod-migrate
	@echo "✨ Déploiement terminé !"

prod-logs:
	@echo "📋 Logs de production..."
	docker compose -f docker-compose.prod.yml logs -f

prod-migrate:
	@echo "🔄 Exécution des migrations en production..."
	docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction
