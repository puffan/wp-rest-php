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
class WPAPICommentModel{
    
    public function getParentCommentList( $postId , $offset , $limit , $order ){
        $sql = 'select comment_ID as id, comment_post_ID as post, 0 as parent, user_id as author, \'author_name\' as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, \'test1\' as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
                   'where comment_approved=\'1\' and comment_post_id='.$postId.' and comment_type=\'\' and comment_parent=0 '.
                   'order by comment_date_gmt '.$order.', comment_id '.$order.' limit '.$offset.','.$limit.' ; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
    public function getParentCommentCount( $postId ){
        $sql = 'select count(1) as parentCount  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
            'where comment_approved=\'1\' and comment_post_id='.$postId.' and comment_type=\'\' and comment_parent=0 '.
            'limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
    
    public function  getCommentById( $commentId ){
        $sql = 'select comment_ID as id, comment_post_ID as post, 0 as parent, user_id as author, \'author_name\' as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, \'test1\' as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
            'where comment_approved=\'1\' and comment_ID='.$commentId.' and comment_type=\'\' '.
            '; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
    
    public function getChildCommentList( $parentCommentId , $postId , $offset , $limit , $order ){
        $sql = 'select comment_ID as id, comment_post_ID as post, 0 as parent, user_id as author, \'author_name\' as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, \'test1\' as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
            'where comment_approved=\'1\' and comment_post_id='.$postId.' and comment_type=\'\' and comment_parent='.$parentCommentId.' '.
            'order by comment_date_gmt '.$order.', comment_id '.$order.' limit '.$offset.','.$limit.' ; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
    public function getChildCommentCount( $parentCommentId , $postId ){
        $sql = 'select count(1) as childCount  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
            'where comment_approved=\'1\' and comment_post_id='.$postId.' and comment_type=\'\' and comment_parent='.$parentCommentId.' '.
            'limit 1; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs || !$rs[0] ){
            return false ;
        }else{
            return $rs[0] ;
        }
    }
    
}