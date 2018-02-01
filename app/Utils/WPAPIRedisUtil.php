<?php
namespace App\Utils ;

/**
 * 
 * @author chenyiwei on 20180124
 *
 */
class WPAPIRedisUtil {
    
    const VALID_REDIS_SWITCH_OPEN_VALUE = 'on' ;
    const RKEY_COMMON_PREFIX  = 'km_wp' ;
    
    public static function isRedisOK(){
        
        if( config( 'redis.switch' ) &&  
            ( config( 'redis.switch' ) == self::VALID_REDIS_SWITCH_OPEN_VALUE )  ) {
            return true ;
        }else {
            return false ;
        }
    }
    
    public static function getWPAPICacheRedisKeyCommonPrefix(){
        $commonrRediKeyPrefix = env( 'CACHE_PREFIX' ) ;
        if( !$commonrRediKeyPrefix ){
            $commonrRediKeyPrefix = self::RKEY_COMMON_PREFIX ;
        }
        return $commonrRediKeyPrefix ;
    }
}