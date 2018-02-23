<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cache\WPAPICache\WPAPICategoryCache;
use App\Components\Response;
use App\Utils\Filters\WPAPICategoryFilter ;

/**
 *
 * @author chenyiwei on 20180123
 *
 */
class WPAPICategoryController extends WPAPIBaseController{
 
    public function __construct(){
        parent::__construct() ;  
    }
    
    public function getCategoryList( Request $req ){
        $categoryListCache = new WPAPICategoryCache() ;
        $categoryMultipleObjCache = $categoryListCache->getCategoryList() ;
        $categoryMultipleObjCache = WPAPICategoryFilter::formatMultipleCategoryObjByRules( $categoryMultipleObjCache , WPAPICategoryFilter::COMMON_RULES_DEFAULT ) ;
        if( !$categoryMultipleObjCache ){
            Response::sendSuccess( [] ) ;
        }else{
            Response::sendSuccess( $categoryMultipleObjCache ) ;
        }
    }
    
    
}