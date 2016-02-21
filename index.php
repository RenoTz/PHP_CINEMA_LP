<?php

$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application;
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Fait remonter les erreurs
$app['debug'] = true;

$app['model'] = new Cinema\Model(
    'localhost',  // Hôte
    'info_rentrezieres',    // Base de données
    'root',    // Utilisateur
    ''     // Mot de passe
);

// Page d'accueil
$app->match('/', function() use ($app) {
    return $app['twig']->render('home.html.twig');
})->bind('home');

// Liste des films
$app->match('/films', function() use ($app) {
    return $app['twig']->render('films.html.twig', array(
        'films' => $app['model']->getFilms()
    ));
})->bind('films');

// Fiche film
$app->match('/film/{id}', function($id) use ($app) {
    $request = $app['request'];
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('nom') && $post->has('note') && $post->has('critique')) {
            $app['model']->setCritiques($post,$id);
        }
    }

    return $app['twig']->render('film.html.twig', array(
        'film' => $app['model']->getFilm($id),
        'casting' => $app['model']->getCasting($id),
        'critiques' => $app['model']->getCritique($id),
    ));
})->bind('film');

// Genres
$app->match('/genres', function() use ($app) {
    return $app['twig']->render('genres.html.twig', array(
        'genres' => $app['model']->getGenres()
    ));
})->bind('genres');

// Meilleurs films
$app->match('/meilleurs_films', function() use ($app) {
    return $app['twig']->render('meilleurs_films.html.twig', array(
        'meilleurs_films' => $app['model']->getMeilleursFilms()
    ));
})->bind('meilleurs_films');

// Liste des films par genre
$app->match('/films_par_genre/{id}', function($id) use ($app) {
    return $app['twig']->render('films_par_genre.html.twig', array(
        'films_par_genre' => $app['model']->getFilmsParGenre($id),
        'casting' => $app['model']->getCasting($id),
    ));
})->bind('films_par_genre');

// Ajout d'un film
$app->match('/ajout_film', function() use ($app) {
    $request = $app['request'];
    if ($request->getMethod() == 'POST') {
        $post = $request->request;
        if ($post->has('nom') && $post->has('description') && $post->has('annee') && $post->has('genre')){
            $app['model']->setFilm($post);
        }
    }
    return $app['twig']->render('ajout_film.html.twig');
})->bind('ajout_film');

$app->run();
