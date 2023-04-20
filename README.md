## Projet 2 ENI sortir.com

Créer une application de gestion de sortie pour l'école ENI en utilisant le Framework PHP Symfony
1. [Howto](#how-to)
2. [Todos](#todos)

## How to
### Installation des librairies et composer
- `composer install`  pour installer les librairies nécessaires de l'application

### Migration de la base de données
Pour appliquer la modification de la base de donnée ou des entités il faut utiliser les commandes
- `php bin/console doctrine:migrations:migrate`

### Appliquer le jeu de données dans les fixtures
- `php bin/console doctrine:fixtures:load`

### Lancement de l'application
- `symfony server:start` pour lancer l'application

## Todos