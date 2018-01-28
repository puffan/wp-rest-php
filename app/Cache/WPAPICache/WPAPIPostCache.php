<?php
namespace App\Cache\WPAPICache ;

use App\Models\WPAPIModel\WPAPIPostModel;
use Cache;
use App\Utils\Filters\WPAPIPostFilter;

class WPAPIPostCache{
    
    const rKeyPostDetail = 'post_detail_' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;
    
    public function getPostDetail( $postId ){
       $postDetail = $this->getPostDetailCache($postId) ;
       if( !$postDetail ){
           $postDetail = $this->initPostDetailToCache($postId) ;
       }
       if( !$postDetail ){
           return false ;
       }else{
           return $postDetail ;
       }
    }
    
    private function setPostDetailCache( $postId , $postDetail ){
        $rKeyPostDetail = self::rKeyPostDetail.$postId ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyPostDetail , $postDetail , $expireMinutes  ) ;
    }
    
    
    private function getPostDetailCache( $postId ){
        $rKeyPostDetail = self::rKeyPostDetail.$postId ;
        $postDetail = Cache::get( $rKeyPostDetail ) ;
        if( !$postDetail ){
            return false ;
        }else{
            return $postDetail ;
        }
    }
    
    private function initPostDetailToCache( $postId ){
        $wpAPIPostModel = new WPAPIPostModel() ;
        $postDetail = $wpAPIPostModel->getPostDetailById($postId) ;
        if( !$postDetail ){
            return false ;
        }else{
            $postDetail = WPAPIPostFilter::formatSinglePostObjByRules( $postDetail , WPAPIPostFilter::COMMON_RULES_DEFAULT_AND_GZ ) ;
            $this->setPostDetailCache($postId, $postDetail) ;
            return $postDetail ;
        }
    }
    
}