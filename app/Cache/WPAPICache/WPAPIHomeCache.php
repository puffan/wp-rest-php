<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/1
 * Time: 10:55
 */

namespace App\Cache\WPAPICache;
use App\Models\WPAPIModel\WPAPIModel;
use App\Models\WPAPIModel\WPAPICategoryModel;
use Cache;
use APP\Utils\WPAPIRedisUtil;
use App\Utils\WPAPISiteUtil;
use App\Utils\Filters\WPAPIHomeFilter;

class WPAPIHomeCache
{
    const rKeyHomeList = 'home_list' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;

    public function getHomeList(  ){
        $postDetail = $this->getHomeListCache() ;
        if( !$postDetail ){
            $postDetail = $this->initHomeListToCache() ;
        }
        if( !$postDetail ){
            return false ;
        }else{
            return $postDetail ;
        }
    }

    private function setHomeListCache($postList ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyHomeList = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyHomeList ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyHomeList , $postList , $expireMinutes  ) ;
    }


    private function getHomeListCache(  ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rHomeList = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyHomeList ;
        $homeList = Cache::get( $rHomeList ) ;
        if( !$homeList ){
            return false ;
        }else{
            return $homeList ;
        }
    }

    private function initHomeListToCache(  )
    {
        $categoryModel = new WPAPICategoryModel();
        $categoryMultipleObj = $categoryModel->getCategoryList();
        $categoryMultipleObj = WPAPIHomeFilter::formatMultipleCategoryObj($categoryMultipleObj);
        if (!$categoryMultipleObj) {
            return false;
        }
        $categoryIds = WPAPIHomeFilter::getCategoryIds($categoryMultipleObj);
        $term_taxonomy_ids = $categoryModel->getTaxonomyIds($categoryIds);
        if (!$term_taxonomy_ids) {
            return false;
        }
        $postModel = new WPAPIModel();
        $homeList = $postModel->getPostListByTerm($term_taxonomy_ids);
        if (!$homeList) {
            return false;
        } else {
            $homeList = WPAPIHomeFilter::getDataJsonArr($categoryMultipleObj, $homeList);
            //$homeList = WPAPIHomeFilter::mappingTermidToKey($homeList);
            if (WPAPIRedisUtil::isRedisOK()) {
                $this->setHomeListCache($homeList);
            }
            return $homeList;
        }

    }

}