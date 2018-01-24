<?php

namespace App\Http\Controllers;

use App\Components\Response;
use App\Utils\WPAPISiteUtil ;

/**
 *
 * @author chenyiwei on 20180123
 *
 */
class WPAPIBaseController extends Controller{
    
    public function __construct(){
        if( !WPAPISiteUtil::getSiteId() ){
            Response::sendError( Response::MSG_SITE_NOT_FOUND ) ;
        }
    }
    
}