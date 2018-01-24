<?php
namespace App\Components ;

class Response{
    
    const STATUS_ID_0 = 0 ;  //success
    const STATUS_ID_1 = 1 ; //empty object
    const STATUS_ID_MINUS_1 = -1  ;   //unknown error
    
    const HTTP_CODE_200 = 200 ; //OK
    const HTTP_CODE_401 = 401 ;   //401 unauthorized
    const HTTP_CODE_403 = 403 ;  //403 forbidden
    const HTTP_CODE_404 = 404 ; //404 not found
    const HTTP_CODE_500 = 500 ; //500 internal server error
    const HTTP_CODE_503 = 503 ; //503 service unavailable
    
    const MSG_COMMON = 'something wrong in wordpress api service' ;
    const MSG_SITE_NOT_FOUND = 'site not found' ;
    const MSG_PARAMETER_ERROR = 'parameter error:' ;
    
    public static function send( $data=[] , $statusCode=self::HTTP_CODE_200 , $statusId=self::STATUS_ID_0 ){
        $resp=[
            'status' => $statusId ,
            'code'   => $statusCode ,
        ];
        $resp = array_merge( $resp , $data ) ;
        header( 'HTTP/1.0 '.$statusCode ) ;
        header( 'Content-Type: application/json' ) ;
        die( json_encode( $resp ) ) ;
    }
    
    /*public static function sendResult( $result='' , $statusCode = 200 , $statusId = 0 ){
        $resp=[
            'data'=> $result,
        ] ;
        self::send( $resp , $statusCode , $statusId ) ;
    }*/
    
    
    public static function sendSuccess( $result='' , $statusCode = self::HTTP_CODE_200 , $statusId = self::STATUS_ID_0 ){
        $resp=[
            'data'=> $result,
        ] ;
        self::send( $resp , $statusCode , $statusId ) ;
    }
    
  
    
    public static function sendError( $msg=self::MSG_COMMON ,  $statusCode=self::HTTP_CODE_404, $statusId=self::STATUS_ID_MINUS_1  ){
 
       $resp=[
           'info'=> $msg,
       ] ;
       self::send( $resp , $statusCode , $statusId ) ;
    }
    
}