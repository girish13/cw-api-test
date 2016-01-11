<?php

/*
Storing all the globals in one place for making it easier to update.
The file will contain all environment level variables.
*/

return [
'api_path' => '/api/v001',
'error_msg' => 'Internal Server Error. Please contact CaterWow customer support to report the error',
'time_zone' => 'Asia/Calcutta',
'storage_endpoint' => 'https://s3-us-west-2.amazonaws.com/cwresources/',
'list_page_size'=>20,

'restaurant_img_path'=>'Resources/images/RestaurantImages/',
'default_logo'=>'cw_default/logo.png',
'default_mast'=>'cw_default/default_mast_head.jpg',

/* operating time parameters */

'first_order_time'=>'10:00 am',
'last_order_time'=>'10:00 pm',

];