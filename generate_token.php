<?php
require_once("PaytmChecksum.php");
include 'paytm_credentials.php';
$amount = $_POST["amount"]; 
// $amount = 20;
$paytmParams = array();
$orderId = "ORDERID_".mt_rand(); // generate order id
$paytmParams["body"] = array(
 "requestType"  => "Payment",
 "mid"  =>  $merchantId, // this you will get from  paytm dashboard
 "websiteName"  => "WEBSTAGING",
 "industryType" => "Retail",
 "orderId"  => $orderId,
 "callbackUrl"  => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderId,
 "txnAmount"  => array(
 "value" => $amount,
 "currency" => "INR",
 ),
 "userInfo" => array(
 "custId" => "CUST_".mt_rand(), // customer id
"mobile"=> "7777777777",
 ),
); // request body for txTOken generation 

$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchantKey);

$paytmParams["head"] = array(
 "signature" => $checksum // pass check in head , checksum in basically is signature.
);

$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

// this url is to generate txtoken , you have to generate checksum and pass it in header and body 
/* for Staging */
$url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=$merchantId&orderId=$orderId";
/* for Production */
// $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=$merchantId&orderId=$orderId";

//  use curl to call api and get reponse from paytm server
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
    $response["txnToken"]= $res_json["body"]["txnToken"];
    $response["orderId"] = $orderId;
//     $response["res"] = $res;
//     $response["paytm"]= $paytmParams;
//     $response["key"]=$merchantKey;
echo json_encode($response);

}else{
//  on failure return message and status code 
http_response_code($res_json["body"]["resultInfo"]["resultCode"]);
    echo $res_json["body"]["resultInfo"]["resultMsg"];
}
// var_dump($res);
?>
