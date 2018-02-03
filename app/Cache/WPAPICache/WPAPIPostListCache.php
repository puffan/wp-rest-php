<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/3
 * Time: 14:15
 */

namespace App\Cache\WPAPICache;
use App\Models\WPAPIModel\WPAPICategoryModel;
use APP\Utils\WPAPIRedisUtil;
use App\Utils\WPAPISiteUtil;
use App\Utils\WPAPIUserUtil;
use Cache;
use App\Models\WPAPIModel\WPAPIModel;

class WPAPIPostListCache
{
    const rKeyPostList = 'post_list_' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;

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
    }

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
}
