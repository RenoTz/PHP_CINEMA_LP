<?php

namespace Cinema;

/**
 * Représente le "Model", c'est à dire l'accès à la base de
 * données pour l'application cinéma basé sur MySQL
 */
class Model
{
    protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host,
                $user,
                $password
            );
        } catch (\PDOException $error) {
            die('Unable to connect to database.');
        }
        $this->pdo->exec('SET CHARSET UTF8');
    }

    protected function execute(\PDOStatement $query, array $variables = array())
    {
        if (!$query->execute($variables)) {
            $errors = $query->errorInfo();
            throw new ModelException($errors[2]);
        }

        return $query;
    }

    /**
     * Récupère un résultat exactement
     */
    protected function fetchOne(\PDOStatement $query)
    {
        if ($query->rowCount() != 1) {
            return false;
        } else {
            return $query->fetch();
        }
    }

    /**
     * Base de la requête pour obtenir un film
     */
    protected function getFilmSQL()
    {
        return
            'SELECT films.image, films.id, films.nom, films.description, genres.nom as genre_nom FROM films 
             INNER JOIN genres ON genres.id = films.genre_id ';
    }

    /**
     * Récupère la liste des films
     */
    public function getFilms()
    {
        $sql = $this->getFilmSQL();

        return $this->execute($this->pdo->prepare($sql));
    }

    /**
     * Récupère la liste des films par genre
     */
    public function getFilmsParGenre($id)
    {
        $sql = $this->getFilmSQL().
        'WHERE films.genre_id = :id'
        ;

        $query = $this->pdo->prepare($sql);
        /*$this->execute($query, array($id));*/
        $query->execute(array('id' => $id));

        /*return $this->fetchOne($query);*/
        return $query;
    }

    /**
     * Base de la requête pour obtenir la liste des meilleurs films avec la moyenne des notes 
     */
    protected function getMeilleursFilmSQL()
    {
        return
             'SELECT films.image, films.id, films.nom,COALESCE (AVG(critiques.note)) as moyenne
             FROM films  INNER JOIN critiques ON films.id = critiques.film_id
             GROUP BY films.nom
             HAVING moyenne > 3
             ORDER by moyenne DESC'
             ;
    }

    /**
     * Récupère la liste des meilleurs films
     */
    public function getMeilleursFilms()
    {
        $sql = $this->getMeilleursFilmSQL();

        return $this->execute($this->pdo->prepare($sql));

    }

    /**
     * Récupère un film
     */
    public function getFilm($id)
    {
        $sql = 
            $this->getFilmSQL() . 
            'WHERE films.id = ?'
            ;

        $query = $this->pdo->prepare($sql);
        $this->execute($query, array($id));

        return $this->fetchOne($query);
    }

    /**
     * Récupérer le casting pour un film
     */
    public function getCasting($filmId)
    {
        $sql = 
            $this->getCastingSQL().
            'WHERE roles.film_id = :film_id'
            ;

        $query = $this->pdo->prepare($sql);
        $query->execute(array('film_id' => $filmId));

        return $query;
    }

    /**
     * Base de la requête pour obtenir le casting
     */
    protected function getCastingSQL(){
        return 
            'SELECT acteurs.nom, acteurs.prenom ,acteurs.image, roles.role FROM roles
            INNER JOIN acteurs ON roles.acteur_id = acteurs.id
            INNER JOIN films ON roles.film_id = films.id ';           
    }

    /**
     * Récupérer les critiques d'un un film
     */
    public function getCritique($filmId)
    {
        $sql = 
            $this->getCritiqueSQL().
            'WHERE critiques.film_id = :film_id'
            ;

        $query = $this->pdo->prepare($sql);
        $query->execute(array('film_id' => $filmId));

        return $query;
    }

    /**
     * Base de la requête pour obtenir les critiques
     */
    protected function getCritiqueSQL(){
        return 
            'SELECT critiques.nom, critiques.commentaire ,critiques.note FROM critiques
            INNER JOIN films ON critiques.film_id = films.id ';           
    }

    /**
     * Genres
     */
    public function getGenres()
    {
        $sql = 
            'SELECT genres.nom,genres.id, COUNT(*) as nb_films FROM genres '.
            'INNER JOIN films ON films.genre_id = genres.id '.
            'GROUP BY genres.id'
            ;

        return $this->execute($this->pdo->prepare($sql));
    }

    /**
     *  Ajout d'une critique
     */
    public function setCritiques($post,$filmId){

        $nom = "";
        $note = "";
        $critique = "";

        foreach ($post as $key => $value) {
            if($key == 'nom'){
                $nom = $value;
            }
            if($key == 'note'){
                $note = $value;
            }
            if($key == 'critique'){
                $critique = $value;
            }            
        }
    
        $sql =
            "INSERT INTO critiques (nom,commentaire,note,film_id) VALUES ('".$nom."','".$critique."','".$note."','".$filmId."')";

        $req = $this->pdo->prepare($sql); 

        $req->execute(array(
            "nom" => $nom, 
            "commentaire" => $critique,
            "note" => $note,
            "film_id" => $filmId
            ));

        $data = $req->fetchAll();

    }

    /**
     *   Ajout d'un film dans la base de données
     */
    public function setFilm($post){

        $nom = "";
        $description = "";
        $annee = "";
        $genre = 0;
        $image = "http://static.omelete.uol.com.br/media/extras/conteudos/icone_filmes_AzUbJNm.jpg.160x235_q85_crop_upscale.jpg";

        foreach ($post as $key => $value) {
            if($key == 'nom'){
                $nom = $value;
            }
            if($key == 'description'){
                $description = $value;
            }
            if($key == 'annee'){
                $annee = $value;
            } 
            if($key == 'genre'){
                $genre = $value;
            }            
        }

        $sql =
            "INSERT INTO films (nom,description,annee,genre_id,image) VALUES ('".$nom."','".$description."','".$annee."','".$genre."','".$image."')";

        $req = $this->pdo->prepare($sql); 

        $req->execute(array(
            "nom" => $nom, 
            "description" => $description,
            "annee" => $annee,
            "genre_id" => $genre,
            "image" => $image
            ));

        $data = $req->fetchAll();

    }

    /**
     *   Suppression d'un film de la base de données
     */
    public function setFilmASupprimer($id){

        $sql = "DELETE FROM films WHERE films.id = :id";

        $query = $this->pdo->prepare($sql);
        $query->execute(array('id' => $id));
    }
}
