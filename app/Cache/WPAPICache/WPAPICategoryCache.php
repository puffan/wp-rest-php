<?php
namespace App\Cache\WPAPICache ;

use App\Models\WPAPIModel\WPAPICategoryModel;
use App\Models\WPAPIModel\WPAPIPostModel;
use Cache;
use App\Utils\Filters\WPAPICategoryFilter;
use App\Utils\Filters\WPAPIPostFilter;
use App\Utils\WPAPIRedisUtil;
use App\Utils\WPAPISiteUtil ;

class WPAPICategoryCache{
    
    const rKeyCategoryList         = 'category_list' ;
    const rKeyCategorySPostIdList  = 'category_s_postid_list_' ;
   
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;
    
    public function setCategorySPostIdListCache( $termId  , $postId ){
        $termId = intval( $termId ) ;
        $postId = intval( $postId ) ;
        
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyCategorySPostIdList = WPAPIRedisUtil::getWPAPICacheRedisKeyCommonPrefix().':'.WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyCategorySPostIdList.$termId ;
        Cache::connection()->sadd(  $rKeyCategorySPostIdList , $postId ) ;
    }
    
    public function getCategoryList(){
        $categoryList = $this->getCategoryListCache() ;
        if(  !$categoryList ){
            $categoryList = $this->initCategoryListToCache() ;
        }
        
        if( !$categoryList ){
            return false ;
        }else{
            return $categoryList ;
        }
       
    }
    
 
    private function setCategoryListCache( $categoryList ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyCategoryList = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyCategoryList ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyCategoryList , $categoryList , $expireMinutes  ) ;
    }
    
    
    private function getCategoryListCache(){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyCategoryList = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyCategoryList ;
        $categoryList = Cache::get( $rKeyCategoryList ) ;
        if( !$categoryList ){
            return false ;
        }else{
            return $categoryList ;
        }
    }
    
    
    /**
     * Always init 0 value of comment_count to redis cache
     *
     * @param unknown $postId
     * @return number|unknown
     */
    private function initCategoryListToCache(){
        $categoryModel = new WPAPICategoryModel() ;
        $categoryMultipleObj = $categoryModel->getCategoryList() ;
        $categoryMultipleObj = WPAPICategoryFilter::formatMultipleCategoryObjByRules( $categoryMultipleObj , WPAPICategoryFilter::COMMON_RULES_DEFAULT ) ;
        if( !$categoryMultipleObj ){
            return false ;
        }else{
            if( WPAPIRedisUtil::isRedisOK() ){
                $this->setCategoryListCache( $categoryMultipleObj ) ;
            }
            return $categoryMultipleObj ;
        }
    }

}