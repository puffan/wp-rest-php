<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/1/23
 * Time: 16:46
 */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Components\Response;
use App\Cache\WPAPICache\WPAPIPostListCache;



class WPAPIPostListController extends WPAPIBaseController
{
    const VALID_ORDER_VALUE = array( 'desc'=>'desc' , 'asc'=>'asc' ) ;
    const VALID_ORDER_DEFAULT = 'desc' ;
    const VALID_PER_PAGE_DEFAULT  = 10 ;
    const VALID_PER_PAGE_MAX = 50;
    const VALID_CURRENT_PAGE_NUM_DEFAULT = 1 ;

    public function __construct(){
        parent::__construct();
    }

    /**
     * Refactor by chenyiwei on 20180205 
     * Add cache
     * 
     * @param Request $req
     */
    public function getPostList( Request $req ){
        $termId = intval( $req->input( 'categories' ) ) ;
        if( empty( $termId ) ){
            Response::sendError( Response::MSG_PARAMETER_ERROR.'categories is empty' ) ;
        }
        $currentPageNum = empty( intval( $req->input( 'page' ) ) ) ? self::VALID_CURRENT_PAGE_NUM_DEFAULT : intval( $req->input( 'page' ) ) ;
        $perPage = empty( intval( $req->input( 'per_page' ) ) ) ? self::VALID_PER_PAGE_DEFAULT : intval( $req->input( 'per_page' ) ) ;
        
        if( $perPage > self::VALID_PER_PAGE_MAX ){
            $perPage = self::VALID_PER_PAGE_MAX ;
        }
        $order = strtolower( trim( $req->input( 'order' ) ) ) ;
        
        if( !array_key_exists( $order , self::VALID_ORDER_VALUE ) ) {
            $order = self::VALID_ORDER_DEFAULT ;
        }
       
        $wpAPIHomeCache = new WPAPIPostListCache() ;
        $postListArr = $wpAPIHomeCache->getPostList( $termId, $currentPageNum , $perPage , $order ) ;
        
        if( !$postListArr ){
            Response::sendSuccess(array());  //empty []
        }else{
            Response::sendSuccess($postListArr);
        }
    }
}
