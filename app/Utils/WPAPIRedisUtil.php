<?php
namespace App\Utils ;

/**
 * 
 * @author chenyiwei on 20180124
 *
 */
class WPAPIRedisUtil {
    
    const VALID_REDIS_SWITCH_OPEN_VALUE = 'on' ;
    
    public static function isRedisOK(){
        
        if( config( 'redis.switch' ) &&  
            ( config( 'redis.switch' ) == self::VALID_REDIS_SWITCH_OPEN_VALUE )  ) {
            return true ;
        }else {
            return false ;
        }
    }
}