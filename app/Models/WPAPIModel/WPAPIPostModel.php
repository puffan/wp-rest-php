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
class WPAPIPostModel{
    public function getPostDetailById( $postId ){
        $sql = 'select ID as id, post_content as content, post_author as author, comment_status, 0 as categories, \'\' as tags, '. 
           'post_title as welink_title, post_date as welink_createTime, \'\' as welink_nameCn, \'\' as welink_imgData, \'\' as welink_accountid '.
           'from '.WPAPISiteUtil::getSiteTableName('wp_%_posts').' where ID='.$postId.' limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
}