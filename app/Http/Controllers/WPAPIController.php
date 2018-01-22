<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WPAPIModel\WPAPIModel;
use App\Components\Response;


/**
 * 
 * @author chenyiwei on 20180120
 *
 */
class WPAPIController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getPostDetail( Request $req ){
        $a = new WPAPIModel() ;
        $rs = $a->getPostDetail() ;
        $aaa = $rs->post_content ;
        
        Response::sendCreated( $aaa , 'location=good' ) ;
    }
    
    
    public function getCategoriesCount(){
        $a = new WPAPIModel() ;
        $countObj = $a->getTotalCountTerms() ;
        if( !$countObj ){
            Response::sendError(500) ;
        }else{
            Response::sendCreated( $countObj->total_terms , 'location=good' ) ;
        }
    }
    
    public function getTermsInnjerjoinTaxonomy(){
        $a = new WPAPIModel() ;
        $termsTaxonomyObj = $a->getTermsInnjerjoinTaxonomy() ;
        if( !$termsTaxonomyObj ){
            Response::sendError(500) ;
        }else{
            //Response::sendCreated( $termsTaxonomyObj , 'location=good' ) ;
            Response::sendResult( $termsTaxonomyObj , 200 , 0 ) ;
        }
    }
    
    public function getTermmeta(){
        $a = new WPAPIModel() ;
        $termmeta = $a->getTermmeta() ;
        if( !$termmeta ){
            Response::sendError(500) ;
        }else{
            Response::sendCreated( $termmeta , 'location=good' ) ;
        }
    }
    
    public function getAllCategoriesByTenant( Request $req ){
        $a = new WPAPIModel() ;
        $siteIdObj = $a->getSiteId() ;
        if( !$siteIdObj ){
            Response::sendError(500) ;
        }else{
            Response::sendCreated( $siteIdObj->blog_id , 'location=good' ) ;
        }
        
    }
    
    private function getTenantIdFromHeader(){
        return 'welink' ;
    }
}
