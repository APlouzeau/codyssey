## /
### config/
- Le dossier config/ contient la configuration du projet : routes, services, packages tiers (Doctrine, Twig, Security, etc.).
C’est ici qu’on définit les paramètres globaux de l’application (parameters.yaml), les routes (routes.yaml) et les services (services.yaml).

### migrations/
- Le dossier migrations/ contient les fichiers générés par Doctrine Migrations.
Chaque fichier décrit une évolution du schéma de base de données (création ou modification de tables, suppression de colonnes, ajout d’index, etc.) sous forme de script versionné.
Ces fichiers permettent de synchroniser la structure de la base entre les environnements (dev, préproduction, production) en exécutant php bin/console doctrine:migrations:migrate.

### template/
- Le dossier templates/ regroupe les vues de l’application écrites en Twig, le moteur de templating de Symfony.
Ces fichiers définissent la structure HTML et la mise en page des pages affichées au navigateur.
Les contrôleurs y injectent des variables qui sont ensuite affichées dans le rendu final.
On y retrouve souvent des sous-dossiers correspondant aux modules ou entités de l’application, ainsi qu’un layout principal (ex : base.html.twig) partagé par toutes les pages.

## src/
### Controllers
- Les contrôleurs reçoivent les requêtes HTTP et orchestrent la logique métier.
Ils appellent les services ou les repositories nécessaires, préparent les données et retournent une réponse (page Twig, JSON pour une API, redirection, etc.).
Chaque méthode publique correspond généralement à une route définie via les annotations, les attributs PHP 8 (#[Route()]) ou les fichiers YAML/PHP de configuration.

### Entity
- Les entités représentent les objets métiers mappés à la base de données via l’ORM Doctrine.
Chaque entité correspond à une table, chaque propriété à une colonne, et les annotations (ou attributs PHP) définissent les relations (OneToMany, ManyToOne, etc.), les contraintes et les types.
Elles peuvent également contenir de la logique métier simple (méthodes utilitaires, calculs dérivés, etc.).

### Repository
- Les repositories sont les classes associées à chaque entité pour interagir avec la base de données.
Ils étendent généralement ServiceEntityRepository et exposent des méthodes comme find(), findAll(), findBy(), findOneBy() ou des requêtes personnalisées construites avec le QueryBuilder ou en DQL.
Leur rôle est de centraliser et encapsuler la logique d’accès aux données.
