<?php
namespace App\Utils\Filters ;

use App\Models\WPAPIModel\WPAPICategoryModel;
use App\Models\WPAPIModel\WPAPIModel;
use App\Utils\WPAPIUserUtil;


class WPAPIPostFilter{
    
    const FORMAT_FUNC_STR = 'formatPostObj' ;
    
    const RULE_TAGS = 'Tags' ;
    const RULE_USER = 'User' ;
    const RULE_TERM = 'Term' ;
    const RULE_IMGDATA = 'Imgdata' ;
    const RULE_GZCOMPRESS = 'ContentGzcompress' ;
    const RULE_GZUNCOMPRESS = 'ContentGzuncompress' ;
    const RULE_UNSETCOMMENTCOUNT = 'Unsetcommentcount' ;
    
    const COMMON_RULES_ONLY_GZCOMPRESS = [self::RULE_GZCOMPRESS] ;
    const COMMON_RULES_ONLY_GZUNCOMPRESS = [self::RULE_GZUNCOMPRESS] ;
    const COMMON_RULES_DEFAULT_AND_GZ = [self::RULE_TAGS , self::RULE_USER , self::RULE_TERM , self::RULE_IMGDATA , self::RULE_GZCOMPRESS ] ;
    const COMMON_RULES_UNSET_GZUNCOMPRESS = [ self::RULE_UNSETCOMMENTCOUNT , self::RULE_GZUNCOMPRESS ] ;
    
    public static function formatSinglePostObjByRules( $singlePostObj , $ruleArr ){
        if( !$singlePostObj ){
            return  $singlePostObj ;
        }
        
        foreach( $ruleArr as $key=>$value ){
            $funcName = self::FORMAT_FUNC_STR.$value ;  //formatCommentObjStatus
            if( config( 'content.gzcompress' ) == 'off' &&  $value == self::RULE_GZCOMPRESS ) {
                //do nothing
            }else{
                $singlePostObj = self::$funcName( $singlePostObj ) ;
            }
        }
        
        return $singlePostObj ;
    }
    
    private static function formatPostObjTags( $singlePostObj ){
        if( !$singlePostObj ){
            return $singlePostObj ;
        }
        $singlePostObj->tags = array() ;
        return $singlePostObj ;
    }
    
    private static function formatPostObjUser( $singlePostObj ){
        $wpSingleUserMeta = WPAPIUserUtil::getWPSingleUserMetaById( $singlePostObj->author ) ;
        $wpSingleUser = WPAPIUserUtil::getWPSingleUserById( $singlePostObj->author ) ;
        
        if( !$wpSingleUserMeta || !$wpSingleUserMeta->meta_value ){
            $singlePostObj->welink_accountid = '' ; // not find accountid , set accountid to emtpy
        }else{
            $singlePostObj->welink_accountid = $wpSingleUserMeta->meta_value ;
        }
        
        if( !$wpSingleUser || !$wpSingleUser->user_nicename ){
            $singlePostObj->welink_nameCn = '' ;
        }else{
            $singlePostObj->welink_nameCn = $wpSingleUser->user_nicename ;
        }
        
        return $singlePostObj ;
        
    }
    
    
    private static function formatPostObjTerm( $singlePostObj ){
        if( !$singlePostObj ){
            return $singlePostObj ;
        }
        $wpApiCategoyModel = new WPAPICategoryModel() ;
        $postTermMultipleObj = $wpApiCategoyModel->getPostTermByPostId( $singlePostObj->id ) ;
        if( !$postTermMultipleObj ){
            return $singlePostObj ;
        }
        
        $singlePostObj->categories = $postTermMultipleObj ;
        return $singlePostObj ;
        
    }
    
    private static function formatPostObjImgdata( $singlePostObj ){
        if( !$singlePostObj ){
            return $singlePostObj ;
        }
        $wpApiModel = new WPAPIModel() ;
        $postImgDataStr = $wpApiModel->getPostImgData( $singlePostObj->id ) ;
        if( !$postImgDataStr ){
            return $singlePostObj ;
        }else{
            $singlePostObj->welink_imgData = $postImgDataStr ;
            return $singlePostObj ;
        }
    }
    
    private static function formatPostObjContentGzcompress( $singlePostObj ){   // compress content
        if( !$singlePostObj ){
            return $singlePostObj ;
        }
        $singlePostObj->content = gzcompress ( $singlePostObj->content ) ;
        return $singlePostObj ;
    }
    
    private static function formatPostObjContentGzuncompress( $singlePostObj ){   // compress content
        if( !$singlePostObj ){
            return $singlePostObj ;
        }
        
        $temp = $singlePostObj->content ;
        try{
            $temp = gzuncompress ( $singlePostObj->content ) ;
        }catch( \Exception $e ){
            $temp = $singlePostObj->content ;
        }
        
        $singlePostObj->content = $temp ;
        return $singlePostObj ;
    }
    
    private static function formatPostObjUnsetcommentcount( $singlePostObj ){ 
        if( !$singlePostObj ){
            return $singlePostObj ;
        }
        if( isset( $singlePostObj->comment_count ) ){
            unset( $singlePostObj->comment_count ) ;
        }
        return $singlePostObj ;
    }
    
}
    