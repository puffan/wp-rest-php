<?php
namespace App\Models\WPAPIModel ;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query ;
use App\Utils\WPAPISiteUtil ;

/**
 *
 * @author chenyiwei on 20180122
 *
 */
class WPAPIUserModel{
    public function getWPSingleUserById( $userId ){
        $userId = intval( $userId ) ;
        $sql = 'select user_login, user_nicename, display_name, user_email, deleted from wp_users where id='.$userId.' limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
    
    
    public function getWPSingleUserMetaById( $userId  ){
        
        $userId = intval( $userId ) ;
        $sql = 'select meta_value from wp_usermeta where user_id='.$userId.' and meta_key=\'accountid\' limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
    
}