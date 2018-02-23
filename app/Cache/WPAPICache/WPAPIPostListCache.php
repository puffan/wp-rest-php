<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/3
 * Time: 14:15
 */

namespace App\Cache\WPAPICache;
use App\Utils\PageUtil;
use Cache;


class WPAPIPostListCache
{
    const rKeyPostList = 'post_list_' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;
    const VALID_ORDER_DEFAULT = 'desc' ;
  
    //add by chenyiwei on 20180205
    public function getPostList( $termId , $currentPageNum , $perPage , $order ){
        $wpAPICategoryCacheObj = new WPAPICategoryCache() ;
        $postCache = new WPAPIPostCache() ;
        
        $categorySPostIdList =  $wpAPICategoryCacheObj->getCategorySPostIdByTermId( $termId ) ; 
        if( !$categorySPostIdList ){
            return false ;
        }
        
        if( $order && $order != self::VALID_ORDER_DEFAULT ){
            $categorySPostIdList = array_reverse( $categorySPostIdList ) ;
        }
        
        $startEndArr = PageUtil::formatStartLength( $currentPageNum , $perPage ) ;
        $start = $startEndArr['start'] ;
        $length = $startEndArr['length'] ; 
        $pagePostIdList = array_slice( $categorySPostIdList ,  $start , $length ) ;
        if( !$pagePostIdList ){
            return false ;
        }
        
        $pagePostDetailList = $postCache->getPostDetailBatch( $pagePostIdList ) ;
        

        $dataArr = array() ;
        $postDetailObj = (object)array() ;
      
        if( !$pagePostDetailList ){
            //do nothing
        }else{
            $index = 0 ;
            foreach( $pagePostDetailList as $key=>$value ){
                $postDetailObj = (object)array() ;
                $postDetailObj->id = $pagePostDetailList[$key]->id ;
                $postDetailObj->content = $pagePostDetailList[$key]->content ;
                $postDetailObj->welink_title = $pagePostDetailList[$key]->welink_title ;
                $postDetailObj->welink_createTime = $pagePostDetailList[$key]->welink_createTime ;
                $postDetailObj->welink_nameCn = $pagePostDetailList[$key]->welink_nameCn ;
                $postDetailObj->welink_imgData = $pagePostDetailList[$key]->welink_imgData ;
                $dataArr[$index] = $postDetailObj ;
                unset( $postDetailObj ) ;
                ++$index ;
            }
        }

        unset( $pagePostDetailList ) ;
        
        return $dataArr ;
    }
    //end by chenyiwei 

}
