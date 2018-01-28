<?php
namespace App\Utils\Filters ;

use App\Models\WPAPIModel\WPAPICategoryModel;



class WPAPICategoryFilter{
    
    
    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;
    
    
    const FORMAT_FUNC_STR = 'formatCategoryObj' ;
    const RULE_PARENT = 'Parent' ;
    const COMMON_RULES_DEFAULT = [self::RULE_PARENT ] ;
    
   
    
    public static function formatMultipleCategoryObjByRules( $categoryMultipleObj , $ruleArr ){
        
        if( !$categoryMultipleObj ){
            return false ;
        }
        $termIdArr = array() ;
        $termIdKeyArr = array() ;
        foreach( $categoryMultipleObj as $key=>$value ){
            $termIdArr[$key] = $value->id ;
            $termIdKeyArr[$value->id] = self::formatSingleCategoryObjByRules( $value , $ruleArr ) ; //change parent to be 0
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
    
    public static function formatSingleCategoryObjByRules( $singleCategoryObj , $ruleArr ){
        if( !$singleCategoryObj ){
            return  $singleCategoryObj ;
        }
        
        foreach( $ruleArr as $key=>$value ){
            $funcName = self::FORMAT_FUNC_STR.$value ;  //formatCommentObjStatus
            $singleCategoryObj = self::$funcName( $singleCategoryObj ) ;
        }
        
        return $singleCategoryObj ;
    }
    
    
    private static function formatCategoryObjParent( $singleCategoryObj ){
        if( !$singleCategoryObj ){
            return $singleCategoryObj ;
        }
        $singleCategoryObj->parent = 0 ;
        return $singleCategoryObj ;
    }
    
    
}