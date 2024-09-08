
### Explications :

- **Fonctionnalités** : L'application permet de gérer des ajout de carte dans des deck Magic et d'expoter sous un format .txt la liste des carte a ajouter. Un système de compte et de restriction sont mit en place et une interface administrateur est disponible.

- **Mise en place** : Seul le conte Admin générer dans AppFisxtures.php possède un deck avec des véritable cartes existante, il est favorable de si connecter pour tester l'aplication (name0: Adminman, mdp: admin1234).

- **Systeme de recherche de deck** : Une fonctionaliter de recherche de deck est gérer dans le DeckControlleur.php avec la function search qui va faire appelle au formulaire DeckSearchType.

- **Requete Ajax pour la liste des cartes** : Dans le script situé dans le template show.html.twig (je savait pas trop ou le déplacer pour faire propre), une requête AJAX est effectuée pour interagir avec le service MtgService.php. Cette requête utilise l'API de Scryfall pour récupérer l'image de la carte qui a été ajoutée. En cas de suppression d'une carte, une autre requête est envoyée, cette fois-ci vers mon propre endpoint /delete/{id} qui se trouve dans CardController.php. Toutes les modifications apportées aux cartes sont bien évidement soumises à l'identification de leur propriétaire. Dans DeckController.php, nous vérifions l'identité de l'utilisateur pour déterminer si les fonctionnalités de modification sont disponibles pour cet utilisateur. Cela garantit que seules les modifications autorisées par le propriétaire du deck peuvent être effectuées.

- **Exporter ses listes** : Dans ExportController.php, on récupère les informations de tous les decks de l'utilisateur connecté (dans security.yaml, la page est configurée pour n'être accessible que par les personnes identifiées). On fait appel au service DeckTextFileGenerator.php, qui envoie des requêtes à l'API Scryfall pour récupérer les données utiles pour l'export. Il génère ensuite des fichiers .txt pour chaque deck avec les informations récupérées, les stocke dans /public/fichiertxt et les rend disponibles au téléchargement. (Ce genre de fichier peut être utile sur d'autres sites).

- **Interface administrateur** : Un espace EasyAdmin, géré par les contrôleurs situés dans le dossier /src/Controller/Admin, est accessible uniquement aux utilisateurs ayant le rôle ROLE_ADMIN, comme défini dans le fichier security.yaml. Cet espace permet à l'administrateur d'effectuer toutes les opérations CRUD sur toute les entités.

- **Systeme de connection** : Liste des fonctionnalités actuelles et en développement.


- **Autre truc annexe** : Un système de récupération de messages flash se trouve dans le fichier base.html.twig, qui, en fonction du type de message flash, affiche une couleur différente. Pour la création de deck on peux choisir de uploader une image ou non pour sont deck qui sera gérer dans le DeckCrontroller et stocker dans le dossier /public/uploads/deck_images.

