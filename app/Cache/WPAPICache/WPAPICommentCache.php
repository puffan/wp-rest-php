<?php
namespace App\Cache\WPAPICache ;
use Cache ;
use App\Models\WPAPIModel\WPAPICommentModel;
use App\Utils\Filters\WPAPICommentFilter;
use App\Utils\PageUtil;

class WPAPICommentCache{
    
    const rKeyAllCommentList = 'all_comment_list_' ;
    const VALID_ORDER_DEFAULT = 'desc' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;  //1440 minutes = 24 hours
    const VALID_CAN_LOAD_TO_CACHE_LIMIT = 1000 ;  //
    
    private function getAllCommentListCache( $postId ){
        $rKeyFullAllCommentList = self::rKeyAllCommentList.$postId ;
        $allCommentList = Cache::get( $rKeyFullAllCommentList ) ;
        if( !$allCommentList ){
            return false ;
        }else{
            return $allCommentList ;
        }
    }
    
    private function setAllCommentListCache( $postId , $allCommentList ){
        $rKeyFullAllCommentList = self::rKeyAllCommentList.$postId ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyFullAllCommentList , $allCommentList , $expireMinutes  ) ;
    }

    private function initAllCommentListToCache( $postId ){
        $commentModel = new WPAPICommentModel() ;
        $allCommentList = $commentModel->getAllCommentList($postId, self::VALID_ORDER_DEFAULT ) ;
        if( !$allCommentList ){
           return false ;
        }
        
        $rstArr = array() ;
        $resultArr = array() ;
        $rstIndex = 0 ;
        
        foreach( $allCommentList as $key=>$value ){  //order : desc
            $valueClone = clone $value ;
            if( $valueClone->parent == 0 ){   // parent comment
                $arrTemp = array() ;
                $rstK = $valueClone->id ;
                $arrTemp[0] = WPAPICommentFilter::formatSingleCommentObjByRules( $value , WPAPICommentFilter::COMMON_RULES_DEFAULT_AND_GZ )  ;                    // $arrTemp[0] = parent comment
                if( isset(  $rstArr[$rstK] ) && isset( $rstArr[$rstK][1] ) ){                            // $arrTemp[1] = childCommentListArr
                    $arrTemp[1] = $rstArr[$rstK][1] ;
                }
                $rstArr[$rstK] = $arrTemp ;
                unset( $arrTemp ) ;
            }else{   //child comment
                $rstK = $valueClone->parent ;
                if( !isset( $rstArr[$rstK] ) ) {  //child comment appear first , parent not appear yet
                    $arrTemp = array() ;
                    $arrTemp[0] = (object)array()  ;  //parent object place there first
                    $childArrTemp[0] = WPAPICommentFilter::formatSingleCommentObjByRules( $value , WPAPICommentFilter::COMMON_RULES_DEFAULT_AND_GZ )    ;
                    $arrTemp[1] =  $childArrTemp ;
                    $rstArr[$rstK] = $arrTemp ;
                    unset( $arrTemp ) ;
                }else if( !isset(  $rstArr[$rstK][1] ) ){ // parent already appear , first child appear
                    $childArrTemp = array() ;
                    $childArrTemp[0] = WPAPICommentFilter::formatSingleCommentObjByRules( $value , WPAPICommentFilter::COMMON_RULES_DEFAULT_AND_GZ ) ;
                    $rstArr[$rstK][1] = $childArrTemp ;
                    unset( $childArrTemp ) ;
                }else{                                             //parent appear , and second , third... child appear
                    $childArrTemp = array() ;
                    $childArrTemp = $rstArr[$rstK][1] ;
                    $size = sizeof( $childArrTemp ) ;
                    $childArrTemp[ $size ] = WPAPICommentFilter::formatSingleCommentObjByRules( $value , WPAPICommentFilter::COMMON_RULES_DEFAULT_AND_GZ ) ;
                    $rstArr[$rstK][1] = $childArrTemp ;
                    unset( $childArrTemp ) ;
                }
            }
        }
        //end foreach
        
        $this->setAllCommentListCache( $postId , $rstArr ) ;
        return $rstArr ;
    }
    
    
    /**
     * 
     * @param unknown $postId
     * @param unknown $currentPageNum  1 first page
     * @param unknown $perPage
     * @param unknown $order
     * @return StdClass
     */
    public function getParentCommentList( $postId, $currentPageNum , $perPage , $order ){

        $postId = intval( $postId ) ;
       
        $startEndArr = PageUtil::formatStartEnd( $currentPageNum , $perPage ) ;
        $start = $startEndArr['start'] ; 
        $end = $startEndArr['end'] ; 
        
        $rstArr = $this->getAllCommentListCache( $postId ) ;
        if( !$rstArr ){
            $rstArr = $this->initAllCommentListToCache( $postId ) ;
        }
        //fort parent list what to be want
        $isOrderDefault = true ;
        if( strtolower( $order ) != self::VALID_ORDER_DEFAULT ){
            $allCommentList = array_reverse( $allCommentList ) ;  //reverse array
            $isOrderDefault = false ;
        }
        //end
        
        
        $resultArr = array_slice( $rstArr ,  $start , $end ) ;
        $dataObj = (object)array() ;
        
        $listData = array() ;
        $listDataObj = (object)array() ;
        $child = array() ;
        
        $index = 0 ;
        foreach( $resultArr as $key => $value ){
            $listDataObj =  WPAPICommentFilter::formatSingleCommentObjByRules( $value[0] , WPAPICommentFilter::COMMON_RULES_ONLY_GZUNCOMPRESS ) ;
            if( isset( $value[1] ) ){  // has child comment
                $listDataObj->childCount = sizeof( $value[1] ) ;
                if( !$isOrderDefault ){
                    $value[1] = array_reverse( $value[1] ) ;//reverse array
                }
                if( sizeof( $value[1] ) <= 3 ){
                    //$value[1] = WPAPICommentFilter::formatMultipleCommentObjOnlyGzcompress( $value[1] , false ) ;
                    $value[1] = WPAPICommentFilter::formatMultipleCommentObjByRules( $value[1] , WPAPICommentFilter::COMMON_RULES_ONLY_GZUNCOMPRESS ) ;
                    $listDataObj->child = $value[1] ;
                }else{
                    $aslice = array_slice( $value[1] ,  0 , 3 ) ;
                    //$aslice = WPAPICommentFilter::formatMultipleCommentObjOnlyGzcompress( $aslice , false ) ;
                    $aslice = WPAPICommentFilter::formatMultipleCommentObjByRules( $aslice , WPAPICommentFilter::COMMON_RULES_ONLY_GZUNCOMPRESS ) ;
                    $listDataObj->child = $aslice ;
                }
            }else{          //no child comment
                $listDataObj->childCount = 0 ;
                $listDataObj->child = array() ;
            }
            
            $listData[$index++] = $listDataObj ;
        }
        
        $dataObj->parentCount = sizeof( $rstArr ) ; 
        $dataObj->listData = $listData ;
         
        return $dataObj ;
      
    }
    
    
    
    
    public function getChildCommentList( $parentCommentId , $postId , $currentPageNum , $perPage , $order ) {
        
        $parentCommentId = intval( $parentCommentId ) ;
        $postId = intval( $postId ) ;

        $startEndArr = PageUtil::formatStartEnd( $currentPageNum , $perPage ) ;
        $start = $startEndArr['start'] ;
        $end = $startEndArr['end'] ;
        
        $isOrderDefault = true ;
        if( strtolower( $order ) != self::VALID_ORDER_DEFAULT ){
            $isOrderDefault = false ;
        }
        
        $rstArr = $this->getAllCommentListCache( $postId ) ;
        if( !$rstArr ){
            $rstArr = $this->initAllCommentListToCache( $postId ) ;
        }

        
        $dataObj = (object)array() ;
        $listData = array() ;
        $listDataObj = (object)array() ;
        $child = array() ;
        
        $resultArr = array() ;
        if( isset( $rstArr[$parentCommentId] )  && isset( $rstArr[$parentCommentId][1] )  ){
            $listDataObj =  WPAPICommentFilter::formatSingleCommentObjByRules( $rstArr[$parentCommentId][0] , WPAPICommentFilter::COMMON_RULES_ONLY_GZUNCOMPRESS ) ;
            if( $isOrderDefault ){
                $resultArr = array_slice( $rstArr[$parentCommentId][1] ,  $start , $end ) ;
            }else{
                $resultArr = array_slice( array_reverse ( $rstArr[$parentCommentId][1] ) ,  $start , $end ) ;
            }
            $resultArr = WPAPICommentFilter::formatMultipleCommentObjByRules( $resultArr , WPAPICommentFilter::COMMON_RULES_ONLY_GZUNCOMPRESS ) ;
        }
        
        if( !$resultArr ){
            $listDataObj->childCount = 0 ;
            $listDataObj->child = array() ;
            $dataObj->parentCount = 1 ;
            $dataObj->listData = $listDataObj ;
        }else{
            $listDataObj->childCount = sizeof( $resultArr ) ;
            $listDataObj->child = $resultArr ;
            $dataObj->parentCount = 1 ;
            $dataObj->listData = $listDataObj ;
        }
        
        return $dataObj ;
        
    }
    
    
    public function isCanLoadAllCommentToCache( $postId ){
        $commentCount = 0 ;
        $wpAPIPostCache = new WPAPIPostCache() ;
        $postDetailObj = $wpAPIPostCache->getPostDetail($postId) ;
        if( !$postDetailObj ){
            return false ;
        }else{
            $commentCount = intval( $postDetailObj->comment_count ) ;
            if( $commentCount > self::VALID_CAN_LOAD_TO_CACHE_LIMIT ){
                return false ;
            }else{
                return true ;
            }
        }
    }
    
}