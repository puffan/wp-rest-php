<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\Response;


/**
 * 
 * @author chenyiwei on 20180120
 *
 */
class WPAPIController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getPostDetail( Request $req ){
        Response::sendCreated( 'OK result' , 'location=good' ) ;
    }
}
