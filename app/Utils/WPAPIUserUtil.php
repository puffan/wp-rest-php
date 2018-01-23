<?php

namespace App\Utils ;
use App\Models\WPAPIModel\WPAPIUserModel;

/**
 * 
 * @author chenyiwei on 20180123
 * 
 * todo ADD redis cache......................
 *
 */
class WPAPIUserUtil{

    public static function getWPSingleUserById( $userId ){
        $wpUserModel = new WPAPIUserModel() ;
        $wpSingleUserObj = $wpUserModel->getWPSingleUserById( $userId ) ;
        return $wpSingleUserObj ;
    }
    
    public static function getWPSingleUserMetaById( $userId ){
        $wpUserModel = new WPAPIUserModel() ;
        $wpSingleUserMetaObj = $wpUserModel->getWPSingleUserMetaById( $userId ) ;
        return $wpSingleUserMetaObj ;
    }
    
}