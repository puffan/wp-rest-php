<?php

use function foo\func;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


$router->get('/', function () use ($router) {
    return $router->app->version();
});


//add by chenyiwei on 20180120 21:45
$router->group(['prefix' => config('app.api.rootname').'/'.config('app.version')] , function() use( $router ){
    
    $router->get( 'posts/{postId}' , 'WPAPIController@getPostDetail' ) ;
    $router->get( 'siteid' , 'WPAPIController@getAllCategoriesByTenant' ) ;  //  {"api_version":"v1","result":2}
    
    //category 20180123
    $router->get( 'categories' , 'WPAPICategoryController@getCategoryList' ) ;
    
    //comments
    $router->get( 'comments' , 'WPAPICommentController@getCommentList' ) ;

});
//end add


