<?php
/**
 * Created by chenyiwei
 * Date: 2018/2/23
 * Time: 14:04
 */

namespace App\Cache\WPAPICache;
use App\Models\WPAPIModel\WPAPIUserModel;
use Cache;
use App\Utils\WPAPIRedisUtil;


class WPAPIUserCache
{
    const RKEY_USER_PREFIX = 'user_' ;
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;

    public function getUser( $userId ){
        $singleUser = $this->getUserCache( $userId ) ;
        if( !$singleUser ){
            $singleUser = $this->initUserToCache( $userId ) ;
        }
        if( !$singleUser ){
            return false ;
        }else{
            return $singleUser ;
        }
    }

    private function setUserCache( $singleUser , $userId ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyUser = self::RKEY_USER_PREFIX.$userId ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $rKeyUser , $singleUser , $expireMinutes  ) ;
    }


    private function getUserCache( $userId ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $rKeyUser = self::RKEY_USER_PREFIX.$userId ;
        $singleUser = Cache::get( $rKeyUser ) ;
        if( !$singleUser ){
            return false ;
        }else{
            return $singleUser ;
        }
    }

    private function initUserToCache( $userId ){
        $wpUserModel = new WPAPIUserModel() ;
        $wpSingleUserObj = $wpUserModel->getWPSingleUserById( $userId ) ;
        $accountIdMeta = $wpUserModel->getWPSingleUserMetaById( $userId ) ;
        $accountIdStr = '' ;
        if( !$accountIdMeta || !$accountIdMeta->meta_value ){
            $accountIdStr = '' ;
        }else{
            $accountIdStr = (string)$accountIdMeta->meta_value ;
        }
        
        if( !$wpSingleUserObj ){
            return false ;
        }else{
            $wpSingleUserObj->accountid = $accountIdStr ;
            if( WPAPIRedisUtil::isRedisOK() ){
                $this->setUserCache( $wpSingleUserObj , $userId ) ;
            }
            return $wpSingleUserObj ; 
        }
     
    }

}