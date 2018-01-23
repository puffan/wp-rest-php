<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Components\Response;
use App\Models\WPAPIModel\WPAPICategoryModel;


/**
 *
 * @author chenyiwei on 20180123
 *
 */
class WPAPICategoryController extends WPAPIBaseController{
    
    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;
    
    public function __construct(){}
    
    public function getCategoryList( Request $req ){
        $categoryModel = new WPAPICategoryModel() ;
        $categoryMultipleObj = $categoryModel->getCategoryList() ;
        $categoryMultipleObj = self::formatMultipleCategoryObj( $categoryMultipleObj ) ;
        if( !$categoryMultipleObj ){
            Response::sendError(500) ;
        }else{
            Response::sendResult( $categoryMultipleObj , 200 , 0 ) ;
        }
    }
    
    
    private static function formatMultipleCategoryObj( $categoryMultipleObj ){
        
        if( !$categoryMultipleObj ){
           return false ;
        }
        $termIdArr = array() ;
        $termIdKeyArr = array() ;
        foreach( $categoryMultipleObj as $key=>$value ){
            $termIdArr[$key] = $value->id ;
            $termIdKeyArr[$value->id] = self::formatParentSingleCategoryObj( $value ) ; //change parent to be 0
        }
        
        $categoryModel = new WPAPICategoryModel() ;
        $termmetaMultipleObj = $categoryModel->getTermmeta( $termIdArr ) ;
        
        if( !$termmetaMultipleObj ){
            return $categoryMultipleObj ; //return do nothing
        }
        
        foreach( $termmetaMultipleObj as $key=>$value ){
            if( array_key_exists( $value->term_id , $termIdKeyArr ) ){
                if( $value->meta_key == self::VALID_TERMMETA_META_KEY_HIDE && $value->meta_value == self::VALID_TERMMETA_META_VALUE_YES ) { // hide , not show
                    unset( $termIdKeyArr[$value->term_id] ) ;
                }
            }
        }

        if( !$termIdKeyArr ){
            return false ;
        }else{
            return array_values( $termIdKeyArr ) ;  //reset array index to keep continue, from 0,1,2.......
        }
    }
    
    private static function formatParentSingleCategoryObj( $categorySingleObj ){
        $categorySingleObj->parent = 0 ;
        return $categorySingleObj ;
    }
    
    
}