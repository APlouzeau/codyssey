.PHONY: help dev stop first-install check-deps install-deps bdd bdd-create cache-clear assets-dev assets-build assets-watch

help:
	@echo "Commandes disponibles :"
	@echo "  make first-install  - Installation complète du projet"
	@echo "  make dev           - Démarre le serveur Symfony"
	@echo "  make stop          - Arrête le serveur Symfony"
	@echo "  make bdd-create    - Crée la base de données"
	@echo "  make bdd           - Lance les migrations"
	@echo "  make cache-clear   - Vide le cache Symfony"
	@echo "  make assets-dev    - Compile les assets en mode dev"
	@echo "  make assets-build  - Compile les assets pour la prod"
	@echo "  make assets-watch  - Compile les assets en mode watch"

dev:
	@echo "🚀 Démarrage du serveur de dev..."
	symfony server:start

stop:
	@echo "🛑 Arrêt du serveur..."
	symfony server:stop

check-deps:
	@echo "🔍 Vérification des dépendances..."
	@which composer > /dev/null || (echo "❌ Composer n'est pas installé" && exit 1)
	@which pnpm > /dev/null || (echo "⚠️  pnpm n'est pas installé, installation..." && npm install -g pnpm)
	@echo "✅ Toutes les dépendances système sont présentes"

install-deps: check-deps
	@echo "📦 Installation des dépendances PHP..."
	composer install
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