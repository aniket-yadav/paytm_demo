<?php
/*
* import checksum generation utility
* You can get this utility from https://developer.paytm.com/docs/checksum/
*/
require_once("PaytmChecksum.php");
include 'paytm_credentials.php';
//  you can get this from from paytm decumentation
// also  , this is in php there are other also
//  i have just made some change here for good response

$amount = $_POST["amount"]; // amount is coming from frontend


$paytmParams = array();
$orderId = "ORDERID_".mt_rand(); // generate order id
$paytmParams["body"] = array(
 "requestType"  => "Payment",
 "mid"  =>  $merchantId, // this you will get from dashboard of paytm
 "websiteName"  => "WEBSITE",
 "orderId"  => $orderId,
 "callbackUrl"  => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderId,
 "txnAmount"  => array(
 "value" => $amount,
 "currency" => "INR",
 ),
 "userInfo" => array(
 "custId" => "CUST_".mt_rand(), // customer id

 ),
); // this is body 

/*
* Generate checksum by parameters we have in body
* Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
*/
$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $marchantKey);
//  this is to create checksum
$paytmParams["head"] = array(
 "signature" => $checksum // checksum is signature 
);

$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

/* for Staging */
$url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=$merchantId&orderId=$orderId";

// this url is to generate txtoken , you have to generate checksum and pass it in header and body 

/* for Production */
// $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=YOUR_MID_HERE&orderId=ORDERID_98765";

//  use curl to call api and get reponse
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
$res = curl_exec($ch);
$res_json = json_decode($res,true); // json decode
if($res_json["body"]["resultInfo"]["resultCode"] == "0000"){ // 0000 means success
    //  on txtoken generation return mid, txtoken,orderid

    $response = array();
    $response["mid"] = $merchantId;
    $response["txToken"]= $res_json["body"]["txnToken"];
    $response["orderId"] = $orderId;
echo json_encode($response);

}else{
//  on failure return message and status code 
http_response_code($res_json["body"]["resultInfo"]["resultCode"]);
    echo $res_json["body"]["resultInfo"]["resultMsg"];
}
?>