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
   public function getPostDetail(){
       $rs = DB::select( 'select post_content from wp_posts where id=22' ) ;
       if( $rs ){
           return $rs[0] ;
       }
   }
   
   //20180122
   public function getSiteId(){
       $aa = '/cms/welink/' ;
       $rs = DB::select( 'select blog_id from wp_blogs where path in(\''.$aa.'\') limit 1 ;' ) ;
       if( $rs ){
           return $rs[0] ;
       }else{
           return false ;
       }
   }
   
}