<?php



$api = app('Dingo\Api\Routing\Router');

$app->get('admin', function () {
    require __DIR__.'/../../admin/admin.php';
});
$app->post('admin', function () {
    require __DIR__.'/../../admin/admin.php';
});


$api->version('v1', ['middleware' => 'cors'], function ($api) {
    // All routes in this callback is prefixed by "/api"
    // To change this, go to .env

    // Authentication routes
    $api->post('auth', 'App\Http\Controllers\AuthController@login');
    $api->get('auth', 'App\Http\Controllers\AuthController@verify');
    $api->delete('auth', 'App\Http\Controllers\AuthController@destroy');

    // The following command will create these routes.
    //   - GET    /api/users
    //   - POST   /api/users
    //   - PATCH  /api/users/{id}
    //   - DELETE /api/users/{id}
    // $api->resource('users', 'App\Http\Controllers\UsersController');

    $api->get('games/hot/',[
        'as'=>'showHotGames',
        'uses'=>'App\Api\v1\Controllers\GamesController@showHotGames'
    ]);
    $api->get('games/new/',[
        'as'=>'showNewGames',
        'uses'=>'App\Api\v1\Controllers\GamesController@showNewGames'
    ]);
    $api->get('game/{id}/',[
        'as'=>'showGames',
        'uses'=>'App\Api\v1\Controllers\GamesController@showGames'
    ]);
    $api->get('games/auth_passport/',[
        'as'=>'authPassport',
        'uses'=>'App\Api\v1\Controllers\GamesController@authPassport'
    ]);
    $api->get('{event_id}/event', [
        'as'   => 'postEvent',
        'uses' => 'App\Api\v1\Controllers\GamesController@postEvent',
    ]);
});

//$app->get('{slug:.*}', 'AngularController@serve');

//$app->get('api', function () {
//    return 'Hello World';
//});

