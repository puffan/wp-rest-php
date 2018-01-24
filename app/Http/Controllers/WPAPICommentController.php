<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WPAPIModel\WPAPICommentModel;
use App\Components\Response;
use App\Utils\WPAPIUserUtil ;

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
        //Get child comment list
        if( $parentCommentId ){
            return $this->getChildCommentList( $parentCommentId , $postId , $currentPageNum , $perPage , $order ) ;
        }
        //end get chilid comment list
        
        $commentModel = new WPAPICommentModel() ;
        $parentCommentCountObj = $commentModel->getParentCommentCount( $postId ) ;
        if( !$parentCommentCountObj || !$parentCommentCountObj->parentCount ){
            Response::sendSuccess( (object)array() ) ;  //empty object {}
        }
            
        $offsetLimitArr = self::formatOffsetLimit( $parentCommentCountObj->parentCount, $currentPageNum, $perPage ) ;
        $parentCommentObj = $commentModel->getParentCommentList( $postId , $offsetLimitArr['offset'] , $offsetLimitArr['limit'] , $order) ;
        $parentCommentObj = self::formatMultipleCommentObj( $parentCommentObj ) ;
        
        if( !$parentCommentObj ){
            Response::sendSuccess( (object)array() ) ;  //empty object {} 
        }else{
            $dataObj = (object)array() ;
            $dataObj->parentCount = $parentCommentCountObj->parentCount ;

            foreach( $parentCommentObj as $key=>$value ){
                $childCommentObj      = $commentModel->getChildCommentList( $value->id , $postId , 0 , self::VALID_CHILD_COMMENT_NUM_DEFAULT , self::VALID_ORDER_DEFAULT ) ;
                $childCommentObj = self::formatMultipleCommentObj( $childCommentObj ) ;
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
        $parentCommentSingleObj = self::formatSingleCommentObj( $parentCommentSingleObj ) ;
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
                $offsetLimitArr = self::formatOffsetLimit( $childCommentCountObj->childCount, $currentPageNum, $perPage ) ;
                $childCommentObj = $commentModel->getChildCommentList( $parentCommentId , $postId , $offsetLimitArr['offset'] , $offsetLimitArr['limit'] , $order) ;
                $childCommentObj = self::formatMultipleCommentObj( $childCommentObj ) ;
                $parentCommentSingleObj->childCount = $childCommentCountObj->childCount ;
                $parentCommentSingleObj->child = $childCommentObj ;
            }
        
            $dataObj->listData = $parentCommentSingleObj ;
            Response::sendSuccess( $dataObj ) ;
        }
    }
    
    
    private static function formatSingleCommentObj( $singleCommentObj ){
        if( !$singleCommentObj ){
            return  $singleCommentObj ;
        }
        
        $singleCommentObj = self::formatCommentObjStatus( $singleCommentObj ) ; 
        $singleCommentObj = self::formatCommentObjUser( $singleCommentObj )  ; 
        $singleCommentObj = self::formatCommentObjParent( $singleCommentObj )  ;
        
        return $singleCommentObj ;
    }
    
    private static function formatMultipleCommentObj( $mutipleCommentObj ){
        if( !$mutipleCommentObj ){
            return $mutipleCommentObj ;
        }   
        $resultArr = array() ;
        foreach ( $mutipleCommentObj as $key=> $value ){
            $resultArr[$key] = self::formatSingleCommentObj( $value ) ;
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
        $wpSingleUserMeta = WPAPIUserUtil::getWPSingleUserMetaById( $singleCommentObj->author ) ;
        if( !$wpSingleUserMeta || !$wpSingleUserMeta->meta_value ){
            $singleCommentObj->accountid = '' ; // not find accountid , set accountid to emtpy
            return $singleCommentObj ;
        }else{
            $singleCommentObj->accountid = $wpSingleUserMeta->meta_value ;
            return $singleCommentObj ;
        }
    }
    
    
    /**
     *
     * 
        Example:
        
        5/2  = 2 ( pages=2+1=3)
        
        0
        1
        
        2
        3
        
        4
     * 
     * @param  $totalCount
     * @param  $currentPageNum
     * @param  $perPage
     * @return |number|string|
     */
    private static function formatOffsetLimit( $totalCount , $currentPageNum , $perPage ){
   
        if( $perPage > self::VALID_PER_PAGE_MAX ){
            $perPage = self::VALID_PER_PAGE_MAX ;
        }
        
        if( $perPage >= $totalCount ){  //only one page
            $resultArr[ 'offset' ] = 0 ;
            $resultArr[ 'limit' ] = $totalCount ; 
            return $resultArr ;
        }
        
        $offset = 0 ;
        $limit = self::VALID_PER_PAGE_DEFAULT ;
        
        if( $totalCount%$perPage == 0 ){ //
            $totalPages = $totalCount/$perPage ;
        }else{
            $totalPages =  $totalCount/$perPage + 1 ;
        }
        
        if( $currentPageNum == $totalPages ){  //the last page
            $offset = ( $currentPageNum - 1 ) * $perPage ;
            $limit = $totalCount - ( $currentPageNum - 1 ) * $perPage ;
        }else{   //not the last page
            $offset = ( $currentPageNum - 1 ) * $perPage ;
            $limit = $perPage ;
        }
        
        $resultArr[ 'offset' ] = $offset ;
        $resultArr[ 'limit' ] = $limit ; 
        
        return $resultArr ;
         
    }
    
    
    
}