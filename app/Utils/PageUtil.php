<?php
namespace App\Utils ;
class PageUtil{
    
    const VALID_PER_PAGE_DEFAULT  = 20 ;
    const VALID_PER_PAGE_MAX  = 50 ;
    
    /**
     *
     *
     Example:
     
     5/2  = 2 ( pages=2+1=3)
     
     0
     1
     
     2
     3
     
     4
     *
     * @param  $totalCount
     * @param  $currentPageNum
     * @param  $perPage
     * @return |number|string|
     */
    public static function formatOffsetLimit( $totalCount , $currentPageNum , $perPage ){
        
        if( $perPage > self::VALID_PER_PAGE_MAX ){
            $perPage = self::VALID_PER_PAGE_MAX ;
        }
        
        if( $perPage >= $totalCount ){  //only one page
            $resultArr[ 'offset' ] = 0 ;
            $resultArr[ 'limit' ] = $totalCount ;
            return $resultArr ;
        }
        
        $offset = 0 ;
        $limit = self::VALID_PER_PAGE_DEFAULT ;
        
        if( $totalCount%$perPage == 0 ){ //
            $totalPages = $totalCount/$perPage ;
        }else{
            $totalPages =  $totalCount/$perPage + 1 ;
        }
        
        if( $currentPageNum == $totalPages ){  //the last page
            $offset = ( $currentPageNum - 1 ) * $perPage ;
            $limit = $totalCount - ( $currentPageNum - 1 ) * $perPage ;
        }else{   //not the last page
            $offset = ( $currentPageNum - 1 ) * $perPage ;
            $limit = $perPage ;
        }
        
        $resultArr[ 'offset' ] = $offset ;
        $resultArr[ 'limit' ] = $limit ;
        
        return $resultArr ;
        
    }
    
    /**
     * 
   0 1  2  3  4  5  6  7  8   9  10
 
   perpage = 3
  
  
   pagenum = 1 
   result = 0 1 2
   
   index_start = (pagenum-1)* perpage  =  0
   index_end   = index_start + perpage - 1 =  2
   length = 3 
   



   pagenum 2 
   result = 3 4 5

   index_start = (pagenum-1)* perpage  = 3
   index_end   = index_start + perpage - 1 = 6
   length = 3 
   
     * @param unknown $currentPageNum
     * @param unknown $perPage
     */
    public static function formatStartLength(  $currentPageNum , $perPage ){
        $currentPageNum = intval( $currentPageNum ) ;
        if( $currentPageNum <= 0 ){
            $currentPageNum = 1 ;
        }
        
        $perPage = intval( $perPage ) ;
        if( $perPage <= 0 ){
            $perPage = self::VALID_PER_PAGE_MAX ;
        }
        
        $resultArr[ 'start' ] = ($currentPageNum-1) * $perPage ;
        $resultArr[ 'length' ] = $perPage ; //  $resultArr[ 'start' ] + $perPage - 1 ;
        
        return $resultArr ;
    }
    
}