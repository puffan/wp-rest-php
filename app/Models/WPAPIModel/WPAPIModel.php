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
   
   //20180123 added by liuhongqiang
    public function getPostListByTerm($term_taxonomy_id_arr){
        $listData = array();

        foreach ($term_taxonomy_id_arr as $key => $value){
            $id = $value->term_taxonomy_id;
            $inSqlStr = ' in( '.$id.' ) ' ;
            $sql = 'SELECT  post_date,post_author,ID,post_title FROM '.WPAPISiteUtil::getSiteTableName('wp_%_posts').' LEFT JOIN '.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').' ON ('.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.ID = '.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').'.object_id) WHERE 1=1 AND (

'.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').'.term_taxonomy_id'.$inSqlStr.'

) AND '.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.post_type = \'post\' AND (('.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.post_status = \'publish\')) GROUP BY '.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.ID ORDER BY '.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.post_date DESC LIMIT 0, 3; ' ;
            $rs = DB::select( $sql ) ;
            if( !$rs ){
                $listData[$key] = array() ;
            }else{
                $listData[$key] = $rs ;
            }
        }

        return $listData;
    }

    //20180123 added by liuhongqiang
    public function getPostListBySingleTerm($term_taxonomy_id,$offset,$limit,$order){
        $id =$term_taxonomy_id;
        $inSqlStr = ' in( '.$id.' ) ' ;
        $sql = 'SELECT  ID,post_content,post_title,post_date,post_author FROM '.WPAPISiteUtil::getSiteTableName('wp_%_posts').' LEFT JOIN '.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').' ON ('.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.ID = '.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').'.object_id) WHERE 1=1 AND (

'.WPAPISiteUtil::getSiteTableName('wp_%_term_relationships').'.term_taxonomy_id'.$inSqlStr.'

) AND '.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.post_type = \'post\' AND (('.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.post_status = \'publish\')) GROUP BY '.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.ID ORDER BY '.WPAPISiteUtil::getSiteTableName('wp_%_posts').'.post_date '.$order.' limit '.$offset.','.$limit.'; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return array();
        }else {
            return $rs;
        }
    }


    public function getPostImgData($postId){
        $sql = 'select meta_value from '.WPAPISiteUtil::getSiteTableName('wp_%_postmeta').' where post_id = '.$postId.' and meta_key=\'_thumbnail_id\';';
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return "";
        }else{
            $meta_value = $rs[0]->meta_value;
        }
        $guid = self::getPostGuid($meta_value);
        $attachment = self::getAttachment($meta_value);
        $imgFileName = $attachment['sizes']['medium']['file'];
        $pos = strrpos($guid,'/');
        $guid = substr($guid,0,$pos+1).$imgFileName;
        return $guid;
    }

    public function getPostGuid($postId){
        $sql = 'select guid from '.WPAPISiteUtil::getSiteTableName('wp_%_posts'). ' where id='.$postId.'';
        $rs = DB::select( $sql ) ;
        if( $rs ){
            return $rs[0]->guid ;
        }
    }

    public function getAttachment($meta_value){
        $sql = 'select meta_value from '.WPAPISiteUtil::getSiteTableName('wp_%_postmeta').' where post_id = '.$meta_value.' and meta_key=\'_wp_attachment_metadata\';';
        $rs = DB::select( $sql ) ;
        if( $rs ){
            return unserialize($rs[0]->meta_value) ;
        }
       else
           return false;
    }
   
}
