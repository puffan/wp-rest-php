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

    /*
    public function getPostList($termId,$offset,$limit,$order){
        $postList = $this->getPostListCache($termId,$offset,$limit,$order) ;
        if( !$postList ){
            $postList = $this->initPostListToCache($termId,$offset,$limit,$order) ;
        }
        if( !$postList ){
            return false ;
        }else{
            return $postList ;
        }
    }*/
    
    
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

    
    /*
    private function setPostListCache($postList,$termId,$offset,$limit,$order ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyPostList = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyPostList.$termId ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyPostList , $postList , $expireMinutes  ) ;
    }


    private function getPostListCache($termId,$offset,$limit,$order ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rPostList = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyPostList.$termId ;
        $postList = Cache::get( $rPostList ) ;
        if( !$postList ){
            return false ;
        }else{
            return $postList ;
        }
    }

    private function initPostListToCache($termId,$offset,$limit,$order)
    {
        $categoryModel = new WPAPICategoryModel() ;
        $term_taxonomy_obj = $categoryModel->getTaxonomyIds($termId);
        if(!$term_taxonomy_obj){
            return false;
        }
        $term_taxonomy_id = $term_taxonomy_obj[0]->term_taxonomy_id;
        $postModel = new WPAPIModel();
        $postList = $postModel->getPostListBySingleTerm($term_taxonomy_id,$offset,$limit,$order);
        if(!$postList){
            return false;
        }else{
            $postList = self::getDataJsonArr($postList);
            if (WPAPIRedisUtil::isRedisOK()) {
                $this->setPostListCache($postList,$termId,$offset,$limit,$order );
            }
            return $postList;
        }
    }

    private static function getDataJsonArr($postList){
        $dataJsonArr = array();
        $postModel = new WPAPIModel();
        $userUtil = new WPAPIUserUtil();
        for($i=0;$i<sizeof($postList);$i++){
            $dataJsonArr[$i]['id'] = $postList[$i]->ID;
            $dataJsonArr[$i]['content'] = $postList[$i]->post_content;
            $dataJsonArr[$i]['welink_title'] = $postList[$i]->post_title;
            $dataJsonArr[$i]['welink_createTime'] = $postList[$i]->post_date;
            $userObj = $userUtil->getWPSingleUserById($postList[$i]->post_author);
            $dataJsonArr[$i]['welink_nameCn'] = $userObj->user_login;
            $dataJsonArr[$i]['welink_imgData'] = $postModel->getPostImgData($postList[$i]->ID);
        }
        return $dataJsonArr;
    } 
    */
}
