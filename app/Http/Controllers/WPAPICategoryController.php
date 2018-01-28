<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\Response;
use App\Models\WPAPIModel\WPAPICategoryModel;
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
        $categoryModel = new WPAPICategoryModel() ;
        $categoryMultipleObj = $categoryModel->getCategoryList() ;
        $categoryMultipleObj = WPAPICategoryFilter::formatMultipleCategoryObjByRules( $categoryMultipleObj , WPAPICategoryFilter::COMMON_RULES_DEFAULT ) ;
        if( !$categoryMultipleObj ){
            Response::sendSuccess( [] ) ;
        }else{
            Response::sendSuccess( $categoryMultipleObj ) ;
        }
    }
    
    
}