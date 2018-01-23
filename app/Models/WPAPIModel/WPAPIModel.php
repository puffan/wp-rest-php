<?php
namespace App\Models\WPAPIModel ;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query ;

/**
 * 
 * @author chenyiwei on 20180122
 *
 */
class WPAPIModel{
   
   //20180122
   public function getSiteId( $sitePath ){
       $rs = DB::select( 'select blog_id from wp_blogs where path in(\''.$sitePath.'\') limit 1 ;' ) ;
       if( $rs && $rs[0] ){
           return $rs[0]->blog_id ;
       }else{
           return false ;
       }
   }
   
}