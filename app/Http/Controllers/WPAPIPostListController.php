<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/1/23
 * Time: 16:46
 */

namespace App\Http\Controllers;
use App\Models\WPAPIModel\WPAPIModel;
use App\Utils\WPAPIUserUtil;
use Illuminate\Http\Request;
use App\Components\Response;
use App\Models\WPAPIModel\WPAPICategoryModel;



class WPAPIPostListController extends WPAPIBaseController
{
    const VALID_ORDER_VALUE = array( 'desc'=>'desc' , 'asc'=>'asc' ) ;
    const VALID_ORDER_DEFAULT = 'desc' ;
    const VALID_PER_PAGE_DEFAULT  = 10 ;
    const VALID_CURRENT_PAGE_NUM_DEFAULT = 1 ;

    public function __construct(){
        parent::__construct() ;
    }

    public function getPostList( Request $req ){
         $termId = intval( $req->input( 'categories' ) ) ;
        if( empty( $termId ) ){
            Response::sendError( Response::MSG_PARAMETER_ERROR.'categoreis is empty' ) ;
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
        $categoryModel = new WPAPICategoryModel() ;
        $offset = ( $currentPageNum - 1 ) * $perPage ;
        $limit = $perPage;
        $term_taxonomy_obj = $categoryModel->getTaxonomyIds($termId);
        if(empty($term_taxonomy_obj)){
            Response::sendSuccess( array() ) ;
        }
        $term_taxonomy_id = $term_taxonomy_obj[0]->term_taxonomy_id;
        $postModel = new WPAPIModel();

        $postList = $postModel->getPostListBySingleTerm($term_taxonomy_id,$offset,$limit,$order);

        $dataJsonArr = self::getDataJsonArr($postList);

        Response::sendSuccess( $dataJsonArr ) ;

    }

    private static function getDataJsonArr($postList){
        $dataJsonArr = array();
        $postModel = new WPAPIModel();
        $userUtil = new WPAPIUserUtil();
        for($i=0;$i<sizeof($postList);$i++){
            $dataJsonArr[$i]['id'] = $postList[$i]->ID;
            $dataJsonArr[$i]['content'] = $postList[$i]->post_content;
            $dataJsonArr[$i]['welink_title'] = $postList[$i]->post_title;
            $dataJsonArr[$i]['welink_createTime'] = $postList[$i]->post_date;
            $userObj = $userUtil->getWPSingleUserById($postList[$i]->post_author);
            $dataJsonArr[$i]['welink_nameCn'] = $userObj->user_login;
            $dataJsonArr[$i]['welink_imgData'] = $postModel->getPostImgData($postList[$i]->ID);
        }
        return $dataJsonArr;
    }



}
