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
        $postId = intval( $postId ) ;
        $sql = 'select ID as id, post_content as content, post_author as author, comment_status, 0 as categories, \'\' as tags, '. 
           'post_title as welink_title, post_date as welink_createTime, \'\' as welink_nameCn, \'\' as welink_imgData, \'\' as welink_accountid '.
          // 'comment_count '.
           'from '.WPAPISiteUtil::getSiteTableName('wp_%_posts').' where ID='.$postId.' and post_status=\'publish\' limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
    
    //add by chenyiwei on 20180202
    public function getPostDetailByIdBatch( $postIdArr ){
        if( !$postIdArr || sizeof( $postIdArr ) == 0 ){
            return false ;
        }
        
        $postIdStr = implode( ',' , $postIdArr ) ;

        $sql = 'select ID as id, post_content as content, post_author as author, comment_status, 0 as categories, \'\' as tags, '.
            'post_title as welink_title, post_date as welink_createTime, \'\' as welink_nameCn, \'\' as welink_imgData, \'\' as welink_accountid '.
            // 'comment_count '.
        'from '.WPAPISiteUtil::getSiteTableName('wp_%_posts').' where ID in ('.$postIdStr.') and post_status=\'publish\' limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs ;
        }
    }
    //end
    
    
    public function getCommentCountByPostId( $postId ){
        $postId = intval( $postId ) ;
        $sql = 'select comment_count '.
            'from '.WPAPISiteUtil::getSiteTableName('wp_%_posts').' where ID='.$postId.' limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
}