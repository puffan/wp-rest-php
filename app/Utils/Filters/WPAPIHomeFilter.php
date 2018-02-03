<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/1
 * Time: 11:09
 */

namespace App\Utils\Filters;

use App\Models\WPAPIModel\WPAPICategoryModel;
use App\Models\WPAPIModel\WPAPIModel;
use App\Utils\WPAPIUserUtil;


class WPAPIHomeFilter
{
    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;
    public static function formatMultipleCategoryObj( $categoryMultipleObj ){

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

    public static function mappingTermidToKey($homeList){
        $mappingArray = array();
        foreach ($homeList as $key=>$value){
            $mappingArray[$value['categoryId']] = $value;
        }
        return $mappingArray;
    }

    private static function formatParentSingleCategoryObj( $categorySingleObj ){
        $categorySingleObj->parent = 0 ;
        return $categorySingleObj ;
    }

    public static function getCategoryIds($categoryMultipleObj){
        $categoryIdsArr = array();
        foreach ($categoryMultipleObj as $key => $value){
            $categoryIdsArr[$key] = $value->id;
        }
        return implode(',',$categoryIdsArr);
    }

    public static function getDataJsonArr($categoryMultipleObj,$postList){
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
        $userUtil = new WPAPIUserUtil();
        foreach ($postListObj as $key=> $value){
            $listDataArr[$key]['welink_createTime'] = $value->post_date;
            $listDataArr[$key]['welink_imgData'] = $postModel->getPostImgData($value->ID);
            $listDataArr[$key]['welink_nameCn'] = $userUtil->getWPSingleUserById($value->post_author)->user_login;
            $listDataArr[$key]['id'] = $value->ID;
            $listDataArr[$key]['welink_title'] = $value->post_title;
        }
        return $listDataArr;
    }

}