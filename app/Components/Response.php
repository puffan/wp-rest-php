<?php
namespace App\Components ;

class Response{
    
   /* public static function send( $data=[] , $statusCode=200 ){
        $resp=[
            'api_version' => config( 'app.version' ) ,
        ];
        $resp = array_merge( $resp , $data ) ;
        header( 'HTTP/1.0 '.$statusCode ) ;
        header( 'Content-Type: application/json' ) ;
        die( json_encode( $resp ) ) ;
    }*/
    
    public static function send( $data=[] , $statusCode=200, $statusId=0 ){
        $resp=[
            'status' => $statusId ,
            'code'   => $statusCode ,
        ];
        $resp = array_merge( $resp , $data ) ;
        header( 'HTTP/1.0 '.$statusCode ) ;
        header( 'Content-Type: application/json' ) ;
        die( json_encode( $resp ) ) ;
    }
    
    public static function sendResult( $result='' , $statusCode = 200 , $statusId = 0 ){
        $resp=[
            'data'=> $result,
        ] ;
        self::send( $resp , $statusCode , $statusId ) ;
    }
    
    public static function sendCreated( $result='' , $location='' ){
        if( $location ){ 
            header( 'Location: ' , $location ) ;
        }
        self::sendResult( $result , 201 ) ;
    }
    
    public static function sendError( $statusCode , $msg='' , $code=100 ){
        $resp = [
            'error' => [
                'code'=>$code ,
                'message' => $msg ,
            ],
        ] ;
        self::send( $resp , $statusCode ) ;
    }
    
}