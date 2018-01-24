<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/1/23
 * Time: 11:40
 */

namespace App\Http\Controllers;

use App\Models\WPAPIModel\WPAPIModel;
use Illuminate\Http\Request;
use App\Components\Response;
use App\Models\WPAPIModel\WPAPICategoryModel;



class WPAPIHomeController extends Controller
{

    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;

    public function __construct(){
    }

    public function getHomeList( Request $req ){
        $categoryModel = new WPAPICategoryModel() ;
        $categoryMultipleObj = $categoryModel->getCategoryList() ;
        $categoryMultipleObj = self::formatMultipleCategoryObj( $categoryMultipleObj ) ;
        if( !$categoryMultipleObj ){
            Response::sendSuccess( array() ) ;
        }
        $categoryIds = self::getCategoryIds($categoryMultipleObj);
        $term_taxonomy_ids = $categoryModel->getTaxonomyIds($categoryIds);

        $postModel = new WPAPIModel();
        $postList = $postModel->getPostListByTerm($term_taxonomy_ids);

        $dataJsonArr = self::getDataJsonArr($categoryMultipleObj,$postList);

        if(!$dataJsonArr){
            Response::sendSuccess( array() ) ;
        }else{
            Response::sendSuccess( $dataJsonArr , 200 , 0 ) ;
        }
    }

    private static function getDataJsonArr($categoryMultipleObj,$postList){
        $dataJsonArr = array();
        for($i=0;$i<sizeof($categoryMultipleObj);$i++){
            $dataJsonArr[$i]['categoryId'] = $categoryMultipleObj[$i]->id;
            $dataJsonArr[$i]['categoryName'] = $categoryMultipleObj[$i]->name;
            $dataJsonArr[$i]['listData'] = array();
            $dataJsonArr[$i]['listData'] = self::formatListData($postList[$i]);
        }
        return $dataJsonArr;
    }

    private static function formatListData($postListObj){
        $listDataArr = array();
        $postModel = new WPAPIModel();
       foreach ($postListObj as $key=> $value){
           $listDataArr[$key]['createTime'] = $value->post_date;
           $listDataArr[$key]['imgData'] = $postModel->getPostImgData($value->ID);
           $listDataArr[$key]['nameCn'] = $value->post_author;
           $listDataArr[$key]['id'] = $value->ID;
           $listDataArr[$key]['title'] = $value->post_title;
       }
       return $listDataArr;
    }

    private static function getCategoryIds($categoryMultipleObj){
        $categoryIdsArr = array();
        foreach ($categoryMultipleObj as $key => $value){
            $categoryIdsArr[$key] = $value->id;
        }
        return implode(',',$categoryIdsArr);
    }

    private static function formatMultipleCategoryObj( $categoryMultipleObj ){

        if( !$categoryMultipleObj ){
            return false ;
        }
        $termIdArr = array() ;
        $termIdKeyArr = array() ;
        foreach( $categoryMultipleObj as $key=>$value ){
            $termIdArr[$key] = $value->id ;
            $termIdKeyArr[$value->id] = self::formatParentSingleCategoryObj( $value ) ; //change parent to be 0
        }

        $categoryModel = new WPAPICategoryModel() ;
        $termmetaMultipleObj = $categoryModel->getTermmeta( $termIdArr ) ;

        if( !$termmetaMultipleObj ){
            return $categoryMultipleObj ; //return do nothing
        }

        foreach( $termmetaMultipleObj as $key=>$value ){
            if( array_key_exists( $value->term_id , $termIdKeyArr ) ){
                if( $value->meta_key == self::VALID_TERMMETA_META_KEY_HIDE && $value->meta_value == self::VALID_TERMMETA_META_VALUE_YES ) { // hide , not show
                    unset( $termIdKeyArr[$value->term_id] ) ;
                }
            }
        }

        if( !$termIdKeyArr ){
            return false ;
        }else{
            return array_values( $termIdKeyArr ) ;  //reset array index to keep continue, from 0,1,2.......
        }
    }

    private static function formatParentSingleCategoryObj( $categorySingleObj ){
        $categorySingleObj->parent = 0 ;
        return $categorySingleObj ;
    }


}
