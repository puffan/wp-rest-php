<?php
namespace App\Models\WPAPIModel ;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query ;
use App\Utils\WPAPISiteUtil ;

/**
 *
 * @author chenyiwei on 20180123
 *
 */
class WPAPICategoryModel{
    
    public function getCategoryList(){
        $sql = 'select t.term_id as id, tt.count as count, t.name as name, tt.taxonomy as taxonomy, tt.parent as parent from '.WPAPISiteUtil::getSiteTableName('wp_%_terms').' as t inner join '.WPAPISiteUtil::getSiteTableName('wp_%_term_taxonomy').' as tt on t.term_id=tt.term_id where tt.taxonomy in (\'category\') order by t.name ASC' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
    //20180123
    public function getTermmeta( $termIdArr ){
        if( !$termIdArr ){
            return false ;
        }
        
        $arrStr = implode( ',' , $termIdArr );
        $inSqlStr = ' in( '.$arrStr.' ) ' ;
        
        $sql = 'select term_id, meta_key, meta_value from '.WPAPISiteUtil::getSiteTableName('wp_%_termmeta').' where term_id'.$inSqlStr.'order by meta_id asc; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
    //by chenyiwei on 20180123
    public function getPostTermByPostId( $postId ){
        $postId = intval( $postId ) ;
        $sql = 'select te.term_id, te.name as term_name from '.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').' as tr inner join '.WPAPISiteUtil::getSiteTableName('wp_%_term_taxonomy').' as tt on tr.term_taxonomy_id=tt.term_taxonomy_id '.
                   'inner join '.WPAPISiteUtil::getSiteTableName('wp_%_terms').' as te on tt.term_id=te.term_id '.
                   'where tr.object_id='.$postId.' and tt.taxonomy=\'category\' '.
                   'order by te.name asc';
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
     //added by liuhongqiang 20180123
    public function getTaxonomyIds($categoryIds){
        IF(!$categoryIds){
            return false;
        }

        $inSqlStr = ' in( '.$categoryIds.' ) ' ;

        $sql = 'SELECT term_taxonomy_id from '.WPAPISiteUtil::getSiteTableName('wp_%_term_taxonomy').' where taxonomy = \'category\' AND term_id'.$inSqlStr;

        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    

    
}
