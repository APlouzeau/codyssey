FROM nginx:alpine

# Créer les répertoires nécessaires
RUN mkdir -p /app/public/build /app/public/images

# Copier les fichiers statiques
COPY public/index.php /app/public/index.php
COPY public/images /app/public/images
