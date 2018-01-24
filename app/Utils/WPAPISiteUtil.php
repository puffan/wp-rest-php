<?php

namespace App\Utils ;

use App\Models\WPAPIModel\WPAPIModel;
use Cache ;

class WPAPISiteUtil{
    
    const VALID_TENANTID_DEFAULT = '' ;
    const VALID_SITEID_DEFAULT = 1 ;
    
    const VALID_REDIS_EXPIRE_MINUTES_DEFAULT = 1440 ;  //1440 minutes = 24 hours
    
    protected static $siteId = 0 ;
    
    public function __construct(){}
    
    
    /**
     *
     * @param  $tableName wp_%_posts
     */
    public static function getSiteTableName( $tableName ){
        if( strpos( $tableName , '%') === false ){
            return $tableName ;
        }else{
            if( self::getSiteId() == self::VALID_SITEID_DEFAULT ){
                return str_replace( '_%' , ''  , $tableName );   // wp_posts
            }else{
                return str_replace( '%' , self::getSiteId()  , $tableName );  // wp_3_posts
            }
        }
    }
    
    public static function getSiteId(){
        if( self::$siteId ){
            return self::$siteId ;
        }
        $sitePath = self::getSitePath() ;
        $wpapiModel = new WPAPIModel() ;
        
        //getSiteId from redis 20180124
        $siteId = self::getSiteIdCache( $sitePath ) ;
        if( !$siteId ){
            $siteId = $wpapiModel->getSiteId( $sitePath ) ;
            if( !$siteId ){
                return false ;
            }else{
                self::setSiteIdCache( $sitePath , $siteId ) ;
                self::$siteId = $siteId ;
                return self::$siteId ;
            }
        }else{
            self::$siteId = $siteId ;
            return self::$siteId ;
        }
        //end
        
    }
       
    //getSiteId from redis 20180124
    private static function getSiteIdCache( $sitePath ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $siteRKey = 'site_id_'.$sitePath ;
        $siteRValue = Cache::get( $siteRKey ) ;
        if( !$siteRValue ){
            return false ;
        }else{
            return $siteRValue ;
        }
    }
    
    private static function setSiteIdCache( $sitePath , $siteId ){
        if( !WPAPIRedisUtil::isRedisOK() ){
            return false ;
        }
        $siteRKey = 'site_id_'.$sitePath ;
        $expireMinutes = self::VALID_REDIS_EXPIRE_MINUTES_DEFAULT ;
        if( intval( config( 'cache.default.time' ) ) ){
            $expireMinutes = intval( config( 'cache.default.time' ) ) ;
        }
        Cache::put( $siteRKey , $siteId , $expireMinutes  ) ;
    }
    
    
    private static function getSitePath(){
        
        $tenantId = self::getTenantIdFromHeader() ;
        
        if( !$tenantId ){
            $tenantId = self::getTenantIdFromUrl() ;
        }
        
        $pathCurrentSiteTemp = '' ;
        
        if( !config( 'PATH_CURRENT_SITE' ) || config( 'PATH_CURRENT_SITE' ) === '/' ){
            $pathCurrentSiteTemp = '/' ;
        }else{
            $pathCurrentSiteTemp = config( 'PATH_CURRENT_SITE' ) ;
            
            if( strpos( $pathCurrentSiteTemp , '/') !== 0 ) {  //not / start
                $pathCurrentSiteTemp = '/'.$pathCurrentSiteTemp ;
            }
            if( substr( $pathCurrentSiteTemp , strlen( $pathCurrentSiteTemp ) -1 , 1 ) !== '/' ){  // not / end
                $pathCurrentSiteTemp = $pathCurrentSiteTemp.'/' ;
            }
        }
        
        if( !$tenantId){
            return $pathCurrentSiteTemp ;
        }else{
            return $pathCurrentSiteTemp.$tenantId.'/' ;
        }
    }
    
    
    private static function getTenantIdFromHeader(){
        $tenantIdHeader = self::VALID_TENANTID_DEFAULT ;
        if( isset( $_SERVER['HTTP_TENANTID'] ) && $_SERVER['HTTP_TENANTID'] && trim( $_SERVER['HTTP_TENANTID'] ) ) {
            $tenantIdHeader = strtolower( substr( trim(  $_SERVER['HTTP_TENANTID'] ) , 0 , 1000 ) ) ;
            if( !$tenantIdHeader ){
                $tenantIdHeader = self::VALID_TENANTID_DEFAULT ;
            }
        }
        
        return $tenantIdHeader ;
    }
    
    private static function getTenantIdFromUrl(){
        $tenantIdUrl = self::VALID_TENANTID_DEFAULT ;
        if( isset( $_GET['tenantid'] ) && $_GET['tenantid'] && trim( $_GET['tenantid'] ) ){
            $tenantIdUrl = strtolower( substr( trim(  $_GET['tenantid'] ) , 0 , 1000 ) ) ;
            if( !$tenantIdUrl ){
                $tenantIdUrl = self::VALID_TENANTID_DEFAULT ;
            }
        }
        return $tenantIdUrl ;
    }
}