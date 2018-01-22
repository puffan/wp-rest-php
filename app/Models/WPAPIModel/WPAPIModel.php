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
   public function getTermsInnjerjoinTaxonomy(){
      // $sql = 'select t.*,tt.* from wp_2_terms as t inner join wp_2_term_taxonomy as tt on t.term_id=tt.term_id where tt.taxonomy in (\'category\') order by t.name ASC' ;
       $sql = 'select t.term_id as id, tt.count as count, t.name as name, tt.taxonomy as taxonomy,  0 as parent  from wp_2_terms as t inner join wp_2_term_taxonomy as tt on t.term_id=tt.term_id where tt.taxonomy in (\'category\') order by t.name ASC' ;
       $rs = DB::select( $sql ) ;
       if( !$rs ){
           return false ;
       }else{
           $rstemp = array() ;
           $rstemp[0] = $rs[0] ;
           //$rstemp[1] = $rs[1] ;
           return $rstemp ;
       }
   }
   
   //20180122
   public function getTermmeta(){
       $sql = 'select term_id, meta_key, meta_value from wp_2_termmeta where term_id in( 6 ,3 ,5 ,2 , 1 ) order by meta_id asc ' ;
       $rs = DB::select( $sql ) ;
       if( !$rs ){
           return false ;
       }else{
           return $rs ;
       }
   }
   
   public function getTotalCountTerms(){
       $sql = 'select count(1) as total_terms from wp_2_terms as t inner join wp_2_term_taxonomy as tt on t.term_id=tt.term_id where tt.taxonomy in(\'category\')' ;
       $rs = DB::select( $sql ) ;
       if( !$rs ){
           return false ;
       }else{
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