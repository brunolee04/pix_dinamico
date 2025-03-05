<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/vendor/autoload.php';

use \App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

$myKey = 'chave@pix.com.br'; // Minha chave PIX

//instancia do payload
$objPayload = (new Payload)->setPixKey($myKey)
						   ->setDescription("pagamento do pedido 123")
						   ->setMerchantName("Bruno Lee")
						   ->setMerchantCity("Ribeirao das Neves")
						   ->setAmount(100.00)
						   ->setTxId('CODEVALEY123');


//código do pagamento
$payloadQrcode = $objPayload->getPayload();

$objQrCode = new QrCode($payloadQrcode);

//imagem do qrcode
$image = (new Output\Png)->output($objQrCode,400);


//echo $image;
// echo "<pre>";
// print_r($payloadQrcode);
// echo "</pre>";

?>

<h1>Qrcode Estático PIX</h1>
<br>
<img src="data:image/png;base64, <?=base64_encode($image)?>">

<br><br>
Código pix:<br>
<strong><?=$payloadQrcode;?></strong>