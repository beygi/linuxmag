<?php
function gotoParsian($amount,$orderId) {
  require_once('nusoap/nusoap.php');
  $soapclient = new nusoap_client('https://www.pecco24.com:27635/pecpaymentgateway/eshopservice.asmx?wsdl','wsdl');
  
  if (!$err = $soapclient->getError())
   $soapProxy = $soapclient->getProxy() ;

   
  if ( (!$soapclient) OR ($err = $soapclient->getError()) ) {
        //$error .= $err . "<br />" ;
        $result['error']="خطا در برقراری ارتباط با بانک پارسیان";
  } else {
	$authority = 0 ;  // default authority
	$status = 1 ;	// default status
        $callbackUrl = "payment/paid_parsian/" ; // site call back Url
        $callbackUrl="http://linuxmag.ir/";

    $params = array(
	 	'pin' => "thisIsYourPinNumber" ,  // this is our PIN NUMBER
                'amount' => $amount,
                'orderId' => $orderId,
		'callbackUrl' => $callbackUrl,
		'authority' => $authority,
		'status' => $status
              );
    
	$sendParams = array($params) ;
        $res = $soapclient->call('PinPaymentRequest', $sendParams);
	$authority = $res['authority'];
	$status = $res['status'];

    if ( ($authority) and ($status==0) )  {
	   // this is a succcessfull connection
           $result['url']=$authority ;
           $result['error']=false;
    } else {
        $result['error']="خطا در برقراری ارتباط با بانک پارسیان";
    }
  }
  return $result;
}



function check_Payment_Parsian($authority) {
  require_once('nusoap/nusoap.php');
    $soapclient = new nusoap_client('https://www.pecco24.com:27635/pecpaymentgateway/eshopservice.asmx?wsdl','wsdl');

	if ( (!$soapclient) OR ($err = $soapclient->getError()) ) {
	     $result['error']="خطا در برقراری ارتباط با بانک";
	} else {
	  $status = 1 ;   // default status
	  $params = array(
	            'pin' => "thisIsYourPinNumber" ,  // this is our PIN NUMBER
	 	    'authority' => $authority,
                    'status' => $status ) ; // to see if we can change it
	  $sendParams = array($params) ;
      $res = $soapclient->call('PinPaymentEnquiry', $sendParams);
	  $status = $res['status'];

	  if ($status==0) {
	   // this is a succcessfull payment
	   $result['ok']=true;
	  } else {
	     $result['error']="خطا در برقراری ارتباط با بانک";
	  }

	}
    return $result;
  }
?>
