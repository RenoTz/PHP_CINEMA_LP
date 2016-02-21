Jeu de données pour la table critiques :

INSERT INTO `critiques` (`id`, `nom`, `commentaire`, `note`, `film_id`) VALUES
(1, 'Carl', 'pas mal à part la fin qui est con', 3, 2),
(2, 'Glenn', 'pas mal', 4, 2),
(3, 'no comment', 'une fin incroyable mais nous laisse dubitatif..', 4, 4),
(4, 'John', 'Yipikay, pauvre c$% !', 5, 5),
(5, 'Butch', 'Un putain de film, pas vrai la crempe?', 5, 6),
(6, 'Loo', 'Superbe actrice!!', 4, 1),
(7, 'l''homme au chapeau', 'commentaire de base', 4, 3);

1) - autoloader : la ligne $loader->add('', 'src') sert pour l'import des classes contenues dans le dossier 'src'

2) - match() : permet d'appeler une méthode si elle existe

3) - La classe Model sert à modéliser la base de données. Elle permet d'effectuer la préparation de la connexion, de l'éxécuter, de récupérer les éléments si trouvés.

4) - Les variables passées en paramètre à la méthode render() sert à faire le lien avec le controleur (fichier twig) pour l'affichage de la page. 