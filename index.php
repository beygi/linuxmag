<?php
error_reporting(0);
require_once('classes/mysql.php');
require_once('classes/mailer/class.phpmailer.php');
//create db instance
//insert your config here
$config['dbhost'] = "localhost";
$config['dbuser'] = "root";
$config['dbpass'] = "yourpassword";
$config['dbname'] = "linuxmag";                
                
$dbase = new dbase_wraper($config["dbhost"], $config["dbuser"], $config["dbpass"], $config["dbname"]);
$dbase->query("SET NAMES 'utf8'");

//check for any payment
if($_POST['InputEmail'] and $_POST['amount'])
{
    //valididy check
    require_once('classes/parsian.php');
    $error=0;

    if(((int)$_POST['amount'])<240000 or ((int)$_POST['amount'])!=$_POST['amount'])$error=1;
    $mail_preg='/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/';
    if  (!preg_match($mail_preg, $_POST['InputEmail'])) $error=1;
    if  ( !preg_match("/^0?\d{10}?$/", $_POST['phone']) and $_POST['phone']!="" ) $error=1;
    if ($_POST['InputEmail']=="") $error=1;
    //if  ($_POST['phone']=="") $error=1;
    
    if (!$error)
    {
        //insert payment data to database and get order id
        $_POST['amount']=(int)$_POST['amount'];
        $dbase->query("INSERT INTO `payments` (`id`, `date`, `email`, `mobile`, `amount`, `authority`, `status`) VALUES ('', NOW(), '{$_POST['InputEmail']}', '{$_POST['phone']}', '{$_POST['amount']}', '', '0');");
        
        $result=gotoParsian($_POST['amount'],$dbase->insert_id);
        
        if($result['error']==false and $result['url']!='')
        {
            //update our database with authority code based on orderID
            $dbase->query("UPDATE `payments` SET `authority` = '{$result['url']}' WHERE `id` ='{$dbase->insert_id}'");
            
            //redirect to Parsian Bank
            ;
            header("Location: "."https://www.pecco24.com/pecpaymentgateway/?au=" . $result['url']);
            die();
        }else
        {
            $payment = file_get_contents('tpls/block_error_bank.tpl');
        }     
    }else
    {
            $payment = file_get_contents('tpls/block_error.tpl');
    }
}
else
{
    $payment = file_get_contents('tpls/block_normal.tpl');
}


//check for any payment aproval
if($_GET['au']!='')
{
    
  $authority = (int)$_REQUEST['au'];
  $status = (int)$_REQUEST['rs'];

  // here we update our database JUST if status is 0 in database
  $record=$dbase->query("SELECT id from `payments` WHERE `authority` ='$authority' and status=0 ");
  $record=$record[0];
  if($record['id']>0)
  {
        //it is a new payment   
            if ($status!=0) {
                $dbase->query("UPDATE `payments` SET `status` = '2' WHERE `authority` ='$authority'");
                $payment = file_get_contents('tpls/block_error_bank.tpl');
            }else
            {
                $dbase->query("UPDATE `payments` SET `status` = '1' WHERE `authority` ='$authority'");
                //check for payment valididy
                require_once('classes/parsian.php');
                $result=check_Payment_Parsian($authority);
                if($result['ok']==true)
                {
                    //register is compeleted Now , lets update our status in dataBase
                    
                    //SEND MAIL

                    //fetch email
                    $email = $dbase->query("select email,amount from payments where `authority` ='$authority' ");
                    $amount=$email[0]['amount'];
                    $email=$email[0]['email'];
                    
                    $f = fopen ("email.html", "r");
                    while (!feof ($f)) {
            		$email_text.= fgets ($f);
                    }
                    
                    $email_text=str_replace("amount",$amount,$email_text);
                    $email_text=str_replace("code",$authority,$email_text);
                    
                    
                    $mail = new PHPMailer(); // defaults to using php "mail()"
                    
                    $mail->AddReplyTo("info@linuxmag.ir", "لینوکس‌مگ");
                    $mail->SetFrom("info@linuxmag.ir", "لینوکس‌مگ");
    
                    $mail->AddAddress($email, "<$email>");
                    $mail->Subject = "اشتراک در ماهنامه‌ی لینوکس مگ";
                    $mail->CharSet="utf-8";
                    $mail->MsgHTML($email_text);
    
                    @$mail->Send();                  
                    
                    $dbase->query("UPDATE `payments` SET `status` = '3' WHERE `authority` ='$authority'");
                    $payment = file_get_contents('tpls/block_thanks.tpl');
                }else
                {
                    $payment = file_get_contents('tpls/block_error_bank.tpl');
                }
            }
  } else
  {
    $payment = file_get_contents('tpls/block_error_bank.tpl');
  }

}


//render page

//read index template
$homepage = file_get_contents('tpls/index.tpl');



//put extra blocks

//progress
$progress = file_get_contents('tpls/progress.tpl');

//calculate progress from databse data

$total = $dbase->query("select sum(amount) as total from payments where status=3");
$total = $total[0]['total'];

//total goal is 240000000 rial
$total = round ((($total*100)/240000000),2);


$progress=str_replace("۶۰",num2fa($total),$progress);
$progress=str_replace("60",$total,$progress);

$homepage=str_replace("<div class='progress_block'>DO NOT REMOVE THIS DIVISION</div>",$progress,$homepage);

//payments
$homepage=str_replace("<div class='payment_block'>DO NOT REMOVE THIS DIVISION</div>",$payment,$homepage);
if($_GET['now']==1){ echo num2fa($total);die();}
echo $homepage ; die();

function code2utf($num){
	if($num<128)return chr($num);
	if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
	if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128). chr(($num&63)+128);
	return '';
}

function num2fa($str) {
  $num = strval($str);
  $res = '';
  for ($i=0; $i<strlen($num); $i++) {
    if (ord($num{$i})>=0x30 && ord($num{$i})<0x3A) {
      $res .= code2utf(0x6F0/*0x660*/+$num{$i});
    } else $res .= $num{$i};
  }
  return $res;
}
?>
