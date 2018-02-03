<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/1/23
 * Time: 11:40
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\Response;
use App\Cache\WPAPICache\WPAPIHomeCache;


class WPAPIHomeController extends WPAPIBaseController
{

    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;

    public function __construct(){
        parent::__construct() ;
    }

    public function getHomeList( Request $req ){

        $wpAPIHomeCache = new WPAPIHomeCache() ;
        $homeListObj = $wpAPIHomeCache->getHomeList() ;
        if( !$homeListObj ){
            //Response::sendError( Response::MSG_PARAMETER_ERROR.'this post not found,posid='.$postId ) ;
            Response::sendSuccess( (object)array() ) ;  //empty object {}
        }else{
            Response::sendSuccess($homeListObj);
        }
    }

}
