## Magic Deck Manager

## Table des matières

- [À propos du projet](#à-propos-du-projet)
- [Structure du projet](#structure-du-projet)
- [Utilisation](#utilisation)
- [Sécurité](#sécurité)
- [Les Entités](#les-entités)
- [Notes](#notes-ptit-truc-en-plus)

---

### À propos du projet

Magic Deck Manager est une application conçue pour gérer des ajout de carte de deck magic. Les utilisateurs peuvent créer, éditer et gérer leurs propres decks, ajouter des cartes via l'API Scryfall, et exporter les listes de cartes a ajouter au format `.txt` (utile pour certain site qui pourront l'importer). Le projet propose des comptes utilisateurs avec des restrictions basées sur les rôles et la possession des deck, garantissant un accès sécurisé à la modification des decks et à une interface administrateur. 

Seul le conte Admin générer dans `AppFixtures.php` possède un deck avec des véritable cartes existante, il est favorable de si connecter pour tester l'aplication (name0: Adminman, mdp: admin1234).

---

### Structure du projet

Voici un aperçu des principales parties du projet :

- **Gestion des decks** : Les utilisateurs peuvent créer, modifier et gérer des decks Magic: The Gathering. La fonctionnalité de recherche de deck est gérée dans `DeckController.php` via le formulaire `DeckSearchType`.
  
- **Gestion des cartes et intégration API** : L'application va requêter l'API Scryfall pour récupérer les détails et images des cartes. Des requêtes AJAX sont effectuées dans `show.html.twig` pour ajouter ou supprimer des cartes via `CardController.php`(faute de savoir où ranger le script, je l'ai laissé là), tandis que `MtgService.php` gère les interactions avec l'API.

- **Export des decks** : Dans `ExportController.php`, les decks de l'utilisateur connecté peuvent être exportés au format `.txt`. Le service `DeckTextFileGenerator.php` récupère les données de l'API Scryfall pour générer les fichiers, stockés dans le répertoire `/public/fichiertxt` pour être téléchargés (Il serait préférable de rendre ces fichiers temporaires, mais je n'ai pas eu le temps de le faire).

- **Interface administrateur** : L'interface administrateur, gérée via EasyAdmin, se trouve dans le dossier `/src/Controller/Admin`. Seuls les utilisateurs avec le rôle `ROLE_ADMIN` peuvent y accéder, et gérer toutes les entités comme configurer dans `security.yaml`(utilisateurs, decks, cartes).

---

### Utilisation

Une fois l'application en marche, les utilisateurs peuvent :

- **Gestion des decks** : Ajouter, modifier et gérer leurs decks Magic: The Gathering.

- **Gestion des cartes** : Ajouter des cartes à un deck à partir de l'API Scryfall, afficher les images des cartes, et les supprimer. une requête AJAX est effectuée pour interagir avec le service `MtgService.php`. Cette requête utilise l'API de Scryfall pour récupérer l'image de la carte qui a été ajoutée. En cas de suppression d'une carte, une autre requête est envoyée, cette fois-ci vers mon propre endpoint /delete/{id} qui se trouve dans `CardController.php`.

- **Export des decks** : Exporter la liste des cartes d'un deck au format `.txt` pour l'utiliser sur d'autres plateformes. On fait appel au service `DeckTextFileGenerator.php`, qui envoie des requêtes à l'API Scryfall pour récupérer les données utiles pour l'export. Il génère ensuite des fichiers .txt pour chaque deck avec les informations récupérées.

- **Recherche de decks** : Rechercher des decks via la fonctionnalité de recherche.

- **Consulter des decks** : Voir sans avoir accer a la modification des deck des autres (Il n'est d'ailleurs pas nécessaire de se connecter pour cela).

Pour les administrateurs :
- **Tableau de bord Admin** : Gérer les utilisateurs, les decks et les cartes via l'interface EasyAdmin.

---

### Sécurité

**Admin préchargé** :  
- Nom d'utilisateur : `Adminman`  
- Mot de passe : `admin1234`  

- **Authentification des utilisateurs** : Les utilisateurs peuvent s'inscrire, se connecter, et gérer leurs comptes. L'authentification est gérée via `SecurityController.php`.
  
- **Contrôle d'accès basé sur les rôles** : Seuls les utilisateurs avec le rôle `ROLE_ADMIN` peuvent accéder à l'interface administrateur. Cette configuration se trouve dans `security.yaml`.

- **Autorisation pour la modification des decks** : Seul le propriétaire d'un deck peut le modifier, et cette identité est vérifiée dans `DeckController.php`.

---

### Les Entitées

Les entités principales, telles que `Deck`, `Card` et `User`, sont définies dans `src/Entity/`. (J'aurais bien imé renommer la propriété AddTo dans Deck, mais cela chamboulerait pas mal de choses. J'avoue avoir un peu la lemme)
  
---

### Notes, ptit truc en plus
  
- **Messages flash personnalisés** : Les messages flash affichent des retours à l'utilisateur avec différentes couleurs en fonction du type de message. Cette logique se trouve dans `base.html.twig`.
  
- **Images des decks** : Les utilisateurs peuvent télécharger des images personnalisées pour leurs decks, stockées dans le répertoire `/public/uploads/deck_images`.
