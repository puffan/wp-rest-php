<?php

/**
 * Create by chenyiwei on 20180120
 */


config([
   'app.api.rootname'=>'wpapi',
   'app.version'=>'v1',
   'redis.switch' => 'closed' , //open : redis is ok    closed : redis is not ok  
   'cache.default.time'=>'1440',  //redis default cache time , minutes , 1440 minutes = 24 hours
   'static.version'=>'11',
   'PATH_CURRENT_SITE' => '/cms/' ,
]);