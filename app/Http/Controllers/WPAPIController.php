<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WPAPIModel\WPAPIModel;
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
        $a = new WPAPIModel() ;
        $rs = $a->getPostDetail() ;
        $aaa = $rs->post_content ;
        
        Response::sendCreated( $aaa , 'location=good' ) ;
    }
}
