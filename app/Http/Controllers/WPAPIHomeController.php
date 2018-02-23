<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/1/23
 * Time: 11:40
 */

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Components\Response;
use App\Cache\WPAPICache\WPAPICategoryCache;
use App\Cache\WPAPICache\WPAPIPostCache;



class WPAPIHomeController extends WPAPIBaseController
{

    const VALID_TERMMETA_META_KEY_HIDE =   'hide_category' ;
    const VALID_TERMMETA_META_VALUE_FALSE = 'false' ;
    const VALID_TERMMETA_META_VALUE_YES = 'yes' ;

    public function __construct(){
        parent::__construct() ;  //add by chenyiwei on 20180205
    }

 
    /**
     * Refactor by chenyiwei on 20180205 
     * Add cache
     * 
     * @param Request $req
     */
    public function getHomeList( Request $req ){
        $categoryCache = new WPAPICategoryCache() ;
        $postCache = new WPAPIPostCache() ;
        $categoryList = $categoryCache->getCategoryList() ;
      
        if( !$categoryList ){
            Response::sendSuccess( array() ) ;
        }
        
        $firstThreePostIds = array() ;

        $dataArr = array() ;
        $dataObj = (object)array() ;
        
        $postDetailArr = array() ;
        $postDetailObj = (object)array() ;
        
        $index = 0 ;
        foreach( $categoryList as $key=>$value ){
            $categorySPostId = $categoryCache->getCategorySPostIdByTermId( $value->id ) ;
            
            //add by chenyiwei on 20180205O
            if( !$categorySPostId ){
                $dataObj = (object)array() ;
                $dataObj->categoryId = intval( $value->id ) ;
                $dataObj->categoryName = $value->name;
                $dataObj->listData = array() ; ;
                $dataArr[$index] = $dataObj ;
                ++$index ;
                unset( $dataObj ) ;
                continue ;
            }
            
            $firstThreePostIds = array_slice( $categorySPostId , 0 , 3 ) ;
            $postDetailListOfOneCategory = $postCache->getPostDetailBatch( $firstThreePostIds ) ;

            $dataObj = (object)array() ;
            $postDetailArr = array() ;
            $postDetailObj = (object)array() ;
            $dataObj->categoryId = intval( $value->id ) ; 
            $dataObj->categoryName = $value->name;
            
            if( !$postDetailListOfOneCategory ){
                //do nothing
            }else{
                $index2 = 0 ;
                foreach( $postDetailListOfOneCategory as $key=>$value ){
                    $postDetailObj = (object)array() ;
                    $postDetailObj->createTime = $postDetailListOfOneCategory[$key]->welink_createTime ;
                    $postDetailObj->imgData = $postDetailListOfOneCategory[$key]->welink_imgData ; 
                    $postDetailObj->nameCn = $postDetailListOfOneCategory[$key]->welink_nameCn ; 
                    $postDetailObj->id = $postDetailListOfOneCategory[$key]->id ; 
                    $postDetailObj->title = $postDetailListOfOneCategory[$key]->welink_title ; 
                    $postDetailArr[$index2] = $postDetailObj ;
                    unset( $postDetailObj ) ;
                    ++$index2 ;
                }
            }
            
            $dataObj->listData = $postDetailArr ;
            $dataArr[$index] = $dataObj ;
            ++$index ;
            unset( $dataObj ) ;
            unset( $postDetailObj ) ;
            unset( $postDetailArr ) ;
            unset( $postDetailListOfOneCategory ) ; 
        } 
        
        if( !$dataArr ){
            Response::sendSuccess( array() ) ;
        }else{
            Response::sendSuccess( $dataArr ) ;
        }
    }

}
