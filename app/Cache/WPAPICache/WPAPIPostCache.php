<?php
namespace App\Cache\WPAPICache ;

use App\Models\WPAPIModel\WPAPIPostModel;
use Cache;
use App\Utils\WPAPIRedisUtil;
use App\Utils\WPAPISiteUtil;
use App\Utils\Filters\WPAPIPostFilter;

class WPAPIPostCache{
    
    const rKeyPostDetail = 'post_detail_' ;
    const rKeyCommentCount = 'comment_count_' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;
    
    //add by chenyiwei on 20180202
    public function getPostDetailBatch( $postIdArr ){
        $postDetailArr = array() ;
        foreach( $postIdArr as $key=>$value ){
            $value = intval($value) ;
            $postDetail = $this->getPostDetail( $value ) ;
            $postDetailArr[$value] = $postDetail ;   //[0] = detail 9
       }
       
       return $postDetailArr ;
    }
    //end
    
    public function getPostDetail( $postId ){
       $postDetail = $this->getPostDetailCache($postId) ;
       if( !$postDetail ){
           $postDetail = $this->initPostDetailToCache($postId) ;
       }else{
           $postDetail = $this->refreshPostDetailCacheSCategory( $postDetail ) ;
       }
       if( !$postDetail ){
           return false ;
       }else{
           return $postDetail ;
       }
    }
    
    private function setPostDetailCache( $postId , $postDetail ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyPostDetail = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyPostDetail.$postId ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyPostDetail , $postDetail , $expireMinutes  ) ;
    }
    

    private function getPostDetailCache( $postId ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyPostDetail = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyPostDetail.$postId ;
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
            if( WPAPIRedisUtil::isRedisOK() ){
                $this->setPostDetailCache($postId, $postDetail) ;
            }
            return $postDetail ;
        }
    }

    
    //add by chenyiwei on 20180208
    private function refreshPostDetailCacheSCategory( $postDetail ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return $postDetail ;
        }
        $wpAPICache = new WPAPICategoryCache() ;
        $categoryList = $wpAPICache->getCategoryList() ;
        $categoryPostDetail = $postDetail->categories ;
        if( !$categoryPostDetail || sizeof( $categoryPostDetail ) == 0 ){
            return $postDetail ;
        } 
        
        $refreshedTermArr = array() ;
        
        foreach( $categoryPostDetail as $key=>$value ){
            $termId = $value->term_id ;
            $termName = $value->term_name ;
            $termStillExist = false ;
            foreach( $categoryList as $keyCate=>$valueCate ){
                if( $valueCate->id == $termId ){
                    $termName = $valueCate->name ;
                    $termStillExist = true ;
                    break ;
                }
            }
            if( $termStillExist ){
                $termObj = new \stdClass() ;
                $termObj->term_id = $termId ;
                $termObj->term_name = $termName ;
                $refreshedTermArr[$termId] = $termObj ;
            }
            
        }
        
        if( $refreshedTermArr ){
            $refreshedTermArr = array_values( $refreshedTermArr ) ; //reset index from 0 , 1 , 2 ..........
        }
        
        $postDetail->categories = $refreshedTermArr ;
        
        
        return $postDetail ;
        
    }
    
    //
    
    public function getCommentCount( $postId ){
        $commentCount = $this->getCommentCountCache($postId) ;
        if( !$commentCount && 0 !== $commentCount ){
            $commentCount = $this->initCommentCountToCache($postId) ;
        }
        
        $commentCount = intval( $commentCount ) ;
        if( $commentCount < 0 ){
            $commentCount = 0 ;
        }
        return $commentCount ;
    }
    
    
    private function setCommentCountCache( $postId , $commentCount ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyCommentCount = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyCommentCount.$postId ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyCommentCount , $commentCount , $expireMinutes  ) ;
    }
    
    
    private function getCommentCountCache( $postId ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyCommentCount = WPAPISiteUtil::getWPAPICacheRedisKeyCommonPrefix().self::rKeyCommentCount.$postId ;
        $commentCount = Cache::get( $rKeyCommentCount ) ;
        if( 0 === $commentCount ){
            return 0 ;
        }else if( !$commentCount ){
            return false ;
        }else{
            return intval( $commentCount ) ;
        }
    }
    
    
    /**
     * Always init 0 value of comment_count to redis cache
     * 
     * @param unknown $postId
     * @return number|unknown
     */
    private function initCommentCountToCache( $postId ){
        $commentCount = 0 ;
        $wpAPIPostModel = new WPAPIPostModel() ;
        $postCommentCountObj = $wpAPIPostModel->getCommentCountByPostId($postId) ;
        if( !$postCommentCountObj ){
            $commentCount = 0 ; 
        }else{
            $commentCount = intval( $postCommentCountObj->comment_count ) ;// 
        }
        if( WPAPIRedisUtil::isRedisOK() ){
            $this->setCommentCountCache($postId, $commentCount) ;
        }
        return $commentCount ;
    }
    
    
}