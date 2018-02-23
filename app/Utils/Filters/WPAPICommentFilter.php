<?php
namespace App\Utils\Filters ;

use App\Cache\WPAPICache\WPAPIUserCache;


class WPAPICommentFilter{
    
    private $singleCommentObj = null ;
    private $mutipleCommentObj = null ;
    
    const FORMAT_FUNC_STR = 'formatCommentObj' ;

    const RULE_STATUS = 'Status' ;
    const RULE_USER = 'User' ;
    const RULE_PARENT = 'Parent' ;
    const RULE_GZCOMPRESS = 'ContentGzcompress' ;
    const RULE_GZUNCOMPRESS = 'ContentGzuncompress' ;
    
    const COMMON_RULES_ONLY_GZCOMPRESS = [self::RULE_GZCOMPRESS] ;
    const COMMON_RULES_ONLY_GZUNCOMPRESS = [self::RULE_GZUNCOMPRESS] ;
    const COMMON_RULES_DEFAULT_AND_GZ = [self::RULE_STATUS , self::RULE_USER , self::RULE_PARENT , self::RULE_GZCOMPRESS ] ;
    const COMMON_RULES_DEFAULT_NO_GZ = [self::RULE_STATUS , self::RULE_USER , self::RULE_PARENT ] ;
    
    
    public static function formatSingleCommentObjByRules( $singleCommentObj , $ruleArr ){

        if( !$singleCommentObj ){
            return  $singleCommentObj ;
        }
        
        foreach( $ruleArr as $key=>$value ){
            $funcName = self::FORMAT_FUNC_STR.$value ;  //formatCommentObjStatus
            if( ( config( 'content.gzcompress' ) == 'off' &&  $value == self::RULE_GZCOMPRESS ) ||  $value == self::RULE_PARENT ) {
                //do nothing
            }else{
                $singleCommentObj = self::$funcName( $singleCommentObj ) ;
            }
        }
        
        return $singleCommentObj ;
    }
    
    public static function formatMultipleCommentObjByRules( $mutipleCommentObj , $ruleArr ){
        if( !$mutipleCommentObj ){
            return $mutipleCommentObj ;
        }
        $resultArr = array() ;
        foreach ( $mutipleCommentObj as $key=> $value ){
            $resultArr[$key] = self::formatSingleCommentObjByRules( $value , $ruleArr ) ;
        }
        return $resultArr ;
    }

    private static function formatCommentObjStatus( $singleCommentObj ){
        if( !$singleCommentObj ){
            return $singleCommentObj ;
        }
        
        if( $singleCommentObj->status == 1 ){
            $singleCommentObj->status = 'approved' ;
        }
        
        return $singleCommentObj ;
    }
    
    private static function formatCommentObjParent( $singleCommentObj ){
        if( !$singleCommentObj ){
            return $singleCommentObj ;
        }
        
        $singleCommentObj->parent = 0 ;
        return $singleCommentObj ;
    }
    
    private static function formatCommentObjUser( $singleCommentObj ){
        //$wpSingleUserMeta = WPAPIUserUtil::getWPSingleUserMetaById( $singleCommentObj->author ) ;
        $wpAPIUserCache = new WPAPIUserCache() ;
        $user = $wpAPIUserCache->getUser( $singleCommentObj->author ) ;
        if( !$user || !$user->accountid ){
            $singleCommentObj->accountid = '' ; // not find accountid , set accountid to emtpy
            return $singleCommentObj ;
        }else{
            $singleCommentObj->accountid = $user->accountid ;
            return $singleCommentObj ;
        }
    }
    
    
    private static function formatCommentObjContentGzcompress( $singleCommentObj ){   // compress content
        $singleCommentObj->content = gzcompress ( $singleCommentObj->content ) ;
        return $singleCommentObj ;
    }
    
    private static function formatCommentObjContentGzuncompress( $singleCommentObj ){   // compress content
        
        $temp = $singleCommentObj->content ;
        try{
            $temp = gzuncompress ( $singleCommentObj->content ) ;
        }catch( \Exception $e ){
            $temp = $singleCommentObj->content ;
        }
        
        $singleCommentObj->content = $temp ;
        
        return $singleCommentObj ;
    }
    
   
}