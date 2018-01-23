<?php

namespace App\Utils ;

use App\Models\WPAPIModel\WPAPIModel;

class WPAPISiteUtil{
    
    const VALID_TENANTID_DEFAULT = '' ;
    const VALID_SITEID_DEFAULT = 1 ;
    
    protected static $siteId = '' ;
    
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
        $sitePath = self::getSitePath() ;
        $wpapiModel = new WPAPIModel() ;
        $siteId = $wpapiModel->getSiteId( $sitePath ) ;
        if( !$siteId ){
            return false ;
        }else{
            self::$siteId = $siteId ;
            return self::$siteId ;
        }
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