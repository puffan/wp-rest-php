<?php
namespace App\Cache\WPAPICache ;

use App\Models\WPAPIModel\WPAPICategoryModel;
use Cache;
use App\Utils\Filters\WPAPICategoryFilter;
use App\Utils\WPAPIRedisUtil;
use App\Utils\WPAPISiteUtil ;

class WPAPICategoryCache{
    
    const rKeyCategoryList         = 'category_list' ;
    const rKeyCategorySPostIdList  = 'category_s_postid_list_' ;
    
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;
    
   
    /**
     * Add by chenyiwei on 20180205
     * Set every one category's all post_ids to a zset collection 
     * 
     * @param unknown $termId
     * @return boolean|boolean|array
     */
    public function getCategorySPostIdByTermId( $termId ){
        $categorySPostIdList = $this->getCategorySPostIdListCache( $termId ) ;
        if(  !$categorySPostIdList ){
            $categorySPostIdList = $this->initCategorySPostIdListToCache( $termId ) ;
        }
        
        if( !$categorySPostIdList ){
            return false ;
        }else{
            return $categorySPostIdList ;
        }
    }
    
    private function initCategorySPostIdListToCache( $termId ){
        $categoryModel = new WPAPICategoryModel() ;
        $categorySPostIdList = $categoryModel->getCategorySPostIdByTermId( $termId ) ;
        
        if( !$categorySPostIdList ){
            return false ;
        }else{
            $arrayTemp = array() ; 
            foreach( $categorySPostIdList as $key=>$value ){
                $arrayTemp[$value->post_ID] = $value->post_ID ;
            }
            $categorySPostIdList = $arrayTemp ;
            if( WPAPIRedisUtil::isRedisOK() ){
                $this->setCategorySPostIdListCache( $termId , $categorySPostIdList ) ;
            }
            unset( $arrayTemp ) ;
            return array_values ( $categorySPostIdList ) ;  //reset array index to keep continue, from 0,1,2......
        }
    }
    
    
    private function setCategorySPostIdListCache( $termId , $categorySPostIdList ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        
        $rKeyCategorySPostIdList = self::getRKeyCategorySPostIdList( $termId ) ;
        
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        
        if( !$categorySPostIdList ){
            return false ;
        }

        Cache::connection()->zadd(  $rKeyCategorySPostIdList , $categorySPostIdList ) ;

    }
    
    private function getCategorySPostIdListCache( $termId ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyCategorySPostIdList = self::getRKeyCategorySPostIdList( $termId ) ;

        //$categorySPostIdList = Cache::connection()->ZRANGEBYSCORE(  $rKeyCategorySPostIdList , '-inf' , '+inf' ) ;
        $categorySPostIdList = Cache::connection()->ZREVRANGEBYSCORE (  $rKeyCategorySPostIdList , '+inf' , '-inf' ) ;

        if( !$categorySPostIdList ){
            return false ;
        }else{
            return $categorySPostIdList ;
        }
    }
    
    private function getRKeyCategorySPostIdList( $termId ){
        $rKeyCategorySPostIdList = WPAPIRedisUtil::getWPAPICacheRedisKeyCommonPrefix().':'.WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyCategorySPostIdList.$termId ;
        return $rKeyCategorySPostIdList ;
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
    public function initCategoryListToCache(){
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