<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WPAPIModel\WPAPICommentModel;
use App\Cache\WPAPICache\WPAPICommentCache;
use App\Cache\WPAPICache\WPAPIPostCache;
use App\Components\Response;
use App\Utils\PageUtil ;
use App\Utils\Filters\WPAPICommentFilter ;


/**
 *
 * @author chenyiwei on 20180120
 *
 */
class WPAPICommentController extends WPAPIBaseController{
    
    const VALID_ORDER_VALUE = array( 'desc'=>'desc' , 'asc'=>'asc' ) ;
    const VALID_ORDER_DEFAULT = 'desc' ;
    const VALID_PER_PAGE_DEFAULT  = 20 ;
    const VALID_PER_PAGE_MAX  = 50 ;
    const VALID_CHILD_COMMENT_NUM_DEFAULT = 3 ;
    const VALID_CURRENT_PAGE_NUM_DEFAULT = 1 ;
    
    public function __construct(){
        parent::__construct() ;
    }
    
    public function getCommentList( Request $req ){
        $postId = intval( $req->input( 'post' ) ) ; 
        if( !$postId  ){
            Response::sendError( Response::MSG_PARAMETER_ERROR.'postid is empty' ) ;
        }

        $currentPageNum = empty( intval( $req->input( 'page' ) ) ) ? self::VALID_CURRENT_PAGE_NUM_DEFAULT : intval( $req->input( 'page' ) ) ;
        $perPage = empty( intval( $req->input( 'per_page' ) ) ) ? self::VALID_PER_PAGE_DEFAULT : intval( $req->input( 'per_page' ) ) ;
        if( $perPage > self::VALID_PER_PAGE_MAX ){
            $perPage = self::VALID_PER_PAGE_MAX ;
        }
        
        $order = strtolower( trim( $req->input( 'order' ) ) ) ; 
        if( !array_key_exists( $order , self::VALID_ORDER_VALUE ) ) {
            $order = self::VALID_ORDER_DEFAULT ;
        }

        $parentCommentId = intval( $req->input( 'parent' ) ) ;
        
        $wpAPIPostCache = new WPAPIPostCache() ;
        $postDetail = $wpAPIPostCache->getPostDetail($postId) ;
        if( !$postDetail ){
            Response::sendError( Response::MSG_PARAMETER_ERROR.'this post not found,posid='.$postId ) ;
        }
        
        $wpAPICommentCache = new WPAPICommentCache() ;
        $isCommentCanCache = $wpAPICommentCache->isCanLoadAllCommentToCache( $postId ) ;
       
        //get child comment list
        if( $parentCommentId ){
            if( $isCommentCanCache ){  //get child comment from cache
                $dataObj = $wpAPICommentCache->getChildCommentList( $parentCommentId , $postId , $currentPageNum , $perPage , $order ) ;
                if( !$dataObj ){
                    Response::sendSuccess( (object)array() ) ;  //empty object {}
                }else{
                    Response::sendSuccess( $dataObj ) ;
                }   
            }else{  //get child comment from database
               return $this->getChildCommentList( $parentCommentId , $postId , $currentPageNum , $perPage , $order ) ;
            }
        }
        //end and return get chilid comment list
        
        //get parent comment from cache and return 
        if( $isCommentCanCache ){
            $dataObj = $wpAPICommentCache->getParentCommentList($postId, $currentPageNum , $perPage , $order) ;
            if( !$dataObj ){
                Response::sendSuccess( (object)array() ) ;  //empty object {}
            }else{
                Response::sendSuccess( $dataObj ) ;
            }    
        }
        
        //get parent comment from database and return 
        $commentModel = new WPAPICommentModel() ;
        $parentCommentCountObj = $commentModel->getParentCommentCount( $postId ) ;
        if( !$parentCommentCountObj || !$parentCommentCountObj->parentCount ){
            Response::sendSuccess( (object)array() ) ;  //empty object {}
        }
            
        $offsetLimitArr = PageUtil::formatOffsetLimit( $parentCommentCountObj->parentCount, $currentPageNum, $perPage ) ;
        $parentCommentObj = $commentModel->getParentCommentList( $postId , $offsetLimitArr['offset'] , $offsetLimitArr['limit'] , $order) ;
        $parentCommentObj = WPAPICommentFilter::formatMultipleCommentObjByRules( $parentCommentObj , WPAPICommentFilter::COMMON_RULES_DEFAULT_NO_GZ ) ;
        
        if( !$parentCommentObj ){
            Response::sendSuccess( (object)array() ) ;  //empty object {} 
        }else{
            $dataObj = (object)array() ;
            $dataObj->parentCount = $parentCommentCountObj->parentCount ;

            foreach( $parentCommentObj as $key=>$value ){
                $childCommentObj      = $commentModel->getChildCommentList( $value->id , $postId , 0 , self::VALID_CHILD_COMMENT_NUM_DEFAULT , self::VALID_ORDER_DEFAULT ) ;
                $childCommentObj = WPAPICommentFilter::formatMultipleCommentObjByRules( $childCommentObj , WPAPICommentFilter::COMMON_RULES_DEFAULT_NO_GZ ) ;
                $childCommentCountObj = $commentModel->getChildCommentCount( $value->id , $postId ) ;
                $parentCommentObj[$key]->childCount = $childCommentCountObj->childCount ;
                if( $childCommentObj ){
                    $parentCommentObj[$key]->child = $childCommentObj ;
                }else{
                    $parentCommentObj[$key]->child = array() ;
                }
            }
            
            $dataObj->listData = $parentCommentObj ;
            Response::sendSuccess( $dataObj ) ;
        }
    }
    
  

    public function getChildCommentList( $parentCommentId , $postId , $currentPageNum , $perPage , $order ){
        $commentModel = new WPAPICommentModel() ;
        $parentCommentSingleObj = $commentModel->getCommentById( $parentCommentId ) ;
        $parentCommentSingleObj = WPAPICommentFilter::formatSingleCommentObjByRules( $parentCommentSingleObj , WPAPICommentFilter::COMMON_RULES_DEFAULT_NO_GZ ) ;
        if( !$parentCommentSingleObj ){
            Response::sendSuccess( (object)array() ) ;  //empty object {} 
        }else{
            $dataObj = (object)array() ;
            $dataObj->parentCount = 1 ;
            
            $childCommentCountObj = $commentModel->getChildCommentCount( $parentCommentId , $postId ) ;
            if( !$childCommentCountObj || !$childCommentCountObj->childCount ){
                $parentCommentSingleObj->childCount = 0 ;
                $parentCommentSingleObj->child = array() ;
            }else{
                $offsetLimitArr = PageUtil::formatOffsetLimit( $childCommentCountObj->childCount, $currentPageNum, $perPage ) ;
                $childCommentObj = $commentModel->getChildCommentList( $parentCommentId , $postId , $offsetLimitArr['offset'] , $offsetLimitArr['limit'] , $order) ;
                $childCommentObj = WPAPICommentFilter::formatMultipleCommentObjByRules( $childCommentObj , WPAPICommentFilter::COMMON_RULES_DEFAULT_NO_GZ ) ;
                $parentCommentSingleObj->childCount = $childCommentCountObj->childCount ;
                $parentCommentSingleObj->child = $childCommentObj ;
            }
        
            $dataObj->listData = $parentCommentSingleObj ;
            Response::sendSuccess( $dataObj ) ;
        }
    }
    
   
    
}