<?php



$api = app('Dingo\Api\Routing\Router');

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


    /*
     * -------
     * Index hot list
     * -------
     */
    $api->get('games/hot/',[
        'as'=>'showHotGames',
        'uses'=>'App\Api\v1\Controllers\GamesController@showHotGames'
    ]);


    /*
     * -------
     * Index new list
     * -------
     */
    $api->get('games/new/',[
        'as'=>'showNewGames',
        'uses'=>'App\Api\v1\Controllers\GamesController@showNewGames'
    ]);

    /*
     * -------
     * auth passport
     * -------
     */
    $api->get('games/auth_passport/',[
        'as'=>'authPassport',
        'uses'=>'App\Api\v1\Controllers\GamesController@authPassport'
    ]);

    /*
     * -------
     * get event
     * -------
     */
    $api->get('{event_id}/event/', [
        'as'   => 'getEvent',
        'uses' => 'App\Api\v1\Controllers\GamesController@getEvent',
    ]);

    /*
     * -------
     * get event data
     * -------
     */
    $api->post('{event_id}/event', [
        'as'   => 'postEvent',
        'uses' => 'App\Api\v1\Controllers\GamesController@postEvent',
    ]);

    /*
     * -------
     * get my package
     * -------
     */
    $api->get('my/package', [
        'as'   => 'getMyPackage',
        'uses' => 'App\Api\v1\Controllers\GamesController@getMyPackage',
    ]);

    /*
     * -------
     * search
     * -------
     */
    $api->get('search', [
        'as'   => 'search',
        'uses' => 'App\Api\v1\Controllers\GamesController@search',
    ]);

    /*
     * -------
     * taohao
     * -------
     */
    $api->post('{event_id}/event/tao', [
        'as'   => 'postTaohaoEvent',
        'uses' => 'App\Api\v1\Controllers\GamesController@postTaohaoEvent',
    ]);

    /*
     * ----------------------------------------------------------------------------------------------------------------
     * pc
     * ----------------------------------------------------------------------------------------------------------------
     */


    $api->group(['prefix' => 'pc'], function($api) {

        /*
         * -------
         * position
         * -------
         */
        $api->get('{position_id}/pcposition', [
            'as'   => 'showPosition',
            'uses' => 'App\Api\v1\Controllers\PcController@showPosition',
        ]);

        /*
         * -------
         * pc index libao
         * -------
         */
        $api->get('pcevents', [
            'as'   => 'showEvents',
            'uses' => 'App\Api\v1\Controllers\PcController@showEvents',
        ]);

        /*
         * -------
         * pc random libao
         * -------
         */
        $api->get('pcrevents', [
            'as'   => 'showRandomEvents',
            'uses' => 'App\Api\v1\Controllers\PcController@showRandomEvents',
        ]);

        /*
         * -------
         * pc taohao paihang
         * -------
         */
        $api->get('pctaohaos', [
            'as'   => 'showTaohaos',
            'uses' => 'App\Api\v1\Controllers\PcController@showTaohaos',
        ]);

        /*
         * -------
         * pc taohao paihang
         * -------
         */
        $api->get('{event_id}/eventinfo', [
            'as'   => 'showEventinfo',
            'uses' => 'App\Api\v1\Controllers\PcController@showEventinfo',
        ]);
    });

});

//$app->get('{slug:.*}', 'AngularController@serve');

//$app->get('api', function () {
//    return 'Hello World';
//});

