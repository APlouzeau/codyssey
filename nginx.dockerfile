FROM nginx:alpine

# Créer les répertoires nécessaires
RUN mkdir -p /app/public/build

# Copier index.php
COPY public/index.php /app/public/index.php
