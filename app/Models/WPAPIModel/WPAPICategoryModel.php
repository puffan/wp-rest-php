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
