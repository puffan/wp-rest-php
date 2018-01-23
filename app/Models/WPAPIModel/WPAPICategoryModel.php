<?php
namespace App\Models\WPAPIModel ;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query ;

/**
 *
 * @author chenyiwei on 20180123
 *
 */
class WPAPICategoryModel{
    
    public function getCategoryList(){
        $sql = 'select t.term_id as id, tt.count as count, t.name as name, tt.taxonomy as taxonomy, tt.parent as parent from wp_2_terms as t inner join wp_2_term_taxonomy as tt on t.term_id=tt.term_id where tt.taxonomy in (\'category\') order by t.name ASC' ;
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
        
        $sql = 'select term_id, meta_key, meta_value from wp_2_termmeta where term_id'.$inSqlStr.'order by meta_id asc; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
    
}