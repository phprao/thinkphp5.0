<?php
 header('Content-type:text/html;charset=utf-8');
 $code = isset($_GET['code']) ? $_GET['code'] : '';
 if(empty($code)){
     die('微信授权错误，请重新登录');
 }
 header('Location:http://jd.product.com/v1/index/userinfo?code='. $code);


// if (isset($_GET['myCallback'])) {
//     $myCallback = $_GET['myCallback'];
//     unset($_GET['myCallback']);
//     $params = [];
//     foreach ($_GET as $key => $value) {
//         $params[] = $key . '=' . urlencode($value);
//     }
//     if (strpos($myCallback, '?') !== false) {
//         $myCallback .= '&' . implode('&', $params);
//     } else {
//         $myCallback .= '?' . implode('&', $params);
//     }
////     var_dump($myCallback);exit;
//     header('Location:' . $myCallback);
// }