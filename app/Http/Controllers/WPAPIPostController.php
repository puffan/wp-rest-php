<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cache\WPAPICache\WPAPIPostCache;
use App\Components\Response;
use App\Utils\Filters\WPAPIPostFilter ;

/**
 *
 * @author chenyiwei on 20180120
 *
 */
class WPAPIPostController extends WPAPIBaseController{
    
    public function __construct(){
        parent::__construct() ;
    }
    
    public function getPostDetail( Request $req , $postId ){

        if( !$postId ){
            Response::sendError( Response::MSG_PARAMETER_ERROR.'postid is empty' ) ;
        }
        
        $wpAPIPostCache = new WPAPIPostCache() ;
        $singlePostObj = $wpAPIPostCache->getPostDetail($postId) ;
        if( !$singlePostObj ){
            //Response::sendError( Response::MSG_PARAMETER_ERROR.'this post not found,posid='.$postId ) ;
            Response::sendSuccess( (object)array() ) ;  //empty object {}
        }
        
        WPAPIPostFilter::formatSinglePostObjByRules($singlePostObj, WPAPIPostFilter::COMMON_RULES_UNSET_GZUNCOMPRESS ) ;
        
        Response::sendSuccess( $singlePostObj ) ;
    }
    
    
}
    