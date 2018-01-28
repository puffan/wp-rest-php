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
    
    const VALID_MAX_RECORD_NUM = 5000 ; // 
    
    public function getParentCommentList( $postId , $offset , $limit , $order ){
        $postId = intval( $postId ) ;
        $offset = intval( $offset ) ;
        $limit = intval( $limit ) ; 
        $sql = 'select comment_ID as id, comment_post_ID as post, comment_parent as parent, user_id as author, comment_author as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, comment_author as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
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
        $postId = intval( $postId ) ;  //reject sql inject
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
        $commentId = intval( $commentId ) ;
        $sql = 'select comment_ID as id, comment_post_ID as post, comment_parent as parent, user_id as author, comment_author as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, comment_author as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
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
        $parentCommentId = intval( $parentCommentId ) ;
        $postId = intval( $postId ) ;
        $offset = intval( $offset ) ;
        $limit = intval( $limit ) ;
        $sql = 'select comment_ID as id, comment_post_ID as post, comment_parent as parent, user_id as author, comment_author as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, comment_author as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
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
        $parentCommentId = intval( $parentCommentId ) ;
        $postId = intval( $postId ) ;
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
    
    public function getAllCommentList( $postId , $order , $limit=self::VALID_MAX_RECORD_NUM ){
        $postId = intval( $postId ) ;
        $limit  = intval( $limit ) ;
        if( $limit > self::VALID_MAX_RECORD_NUM ){  //to protect database , can't not fetch record more than self::VALID_MAX_RECORD_NUM 
           return false ;    
        }
        
        $sql = 'select comment_ID as id, comment_post_ID as post, comment_parent as parent, user_id as author, comment_author as author_name, comment_date_gmt as date, comment_content as content, comment_approved as status, comment_author as accountid  from '.WPAPISiteUtil::getSiteTableName('wp_%_comments').' '.
            'where comment_approved=\'1\' and comment_post_id='.$postId.' and comment_type=\'\' '.
            'order by comment_date_gmt '.$order.', comment_id '.$order.' limit '.$limit.' ; ' ;
        $rs = DB::select( $sql ) ;
        if( !$rs ){
            return false ;
        }else{
            return $rs ;
        }
    }
    
}