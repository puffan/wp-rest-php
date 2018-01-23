<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WPAPIModel\WPAPIPostModel;
use App\Utils\WPAPIUserUtil;
use App\Components\Response;
use App\Models\WPAPIModel\WPAPICategoryModel;

/**
 *
 * @author chenyiwei on 20180120
 *
 */
class WPAPIPostController extends WPAPIBaseController{
    
    public function getPostDetail( Request $req , $postId ){

        if( empty( $postId ) ){
            Response::send( [] , 404 , 1 ) ;
        }
        
        $postModel = new WPAPIPostModel() ;
        $singlePostObj = $postModel->getPostDetailById( $postId ) ;
        
        if( !$singlePostObj ){
            Response::send( [] , 404 , 1 ) ;
        }
        
        $singlePostObj = self::formatPostObjTags( $singlePostObj ) ;
        $singlePostObj = self::formatPostObjUser( $singlePostObj ) ;
        $singlePostObj = self::formatPostObjTerm( $singlePostObj ) ;
        
        Response::sendResult( $singlePostObj , 200 , 0 ) ;
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
}
    