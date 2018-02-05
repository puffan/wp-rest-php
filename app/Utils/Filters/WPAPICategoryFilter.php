<?php
namespace App\Utils\Filters ;

use App\Models\WPAPIModel\WPAPICategoryModel;



class WPAPICategoryFilter{
    
    
    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;
    
    const VALID_DEFAULT_CATEGORY_NAME_CN = '未分类' ;
    const VALID_DEFAULT_CATEGORY_NAME_EN = 'uncategorized' ;
    const VALID_DEFAULT_CATEGORY_ID      =  1 ;
    
    
    const FORMAT_FUNC_STR = 'formatCategoryObj' ;
    const RULE_PARENT = 'Parent' ;
    const RULE_REMOVE_DEFAULT = 'RemoveDefault' ;
    const COMMON_RULES_DEFAULT = [self::RULE_PARENT , self::RULE_REMOVE_DEFAULT ] ;
    
   
    
    public static function formatMultipleCategoryObjByRules( $categoryMultipleObj , $ruleArr ){
        
        if( !$categoryMultipleObj ){
            return false ;
        }
        $termIdArr = array() ;
        $termIdKeyArr = array() ;
        foreach( $categoryMultipleObj as $key=>$value ){
            
            if(    in_array( self::RULE_REMOVE_DEFAULT , $ruleArr )
                && self::isDefaultCategory( $value->id , $value->name ) ){   //default category, not put to category list. by chenyiwei on 20180205
                continue ;
            }
            
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
    
    /**
     * is default category ?
     * 
     * @param unknown $categoryId
     * @param unknown $categoryNameCn
     * @param unknown $categoryNameEn
     * @return boolean
     */
    private static function isDefaultCategory( $categoryId , $categoryName ){
        $categoryId = intval( $categoryId ) ; 
        if( $categoryId == self::VALID_DEFAULT_CATEGORY_ID ){
            if( $categoryName== self::VALID_DEFAULT_CATEGORY_NAME_CN 
                || $categoryName == self::VALID_DEFAULT_CATEGORY_NAME_EN ){
               return true ;   
            }
            
        }
        
        return false ;
    } 
    
    private static function formatCategoryObjParent( $singleCategoryObj ){
        if( !$singleCategoryObj ){
            return $singleCategoryObj ;
        }
        $singleCategoryObj->parent = 0 ;
        return $singleCategoryObj ;
    }
    
    private static function formatCategoryObjRemoveDefault( $singleCategoryObj ){
       //do nothing 
        return $singleCategoryObj ;
    }
    
}