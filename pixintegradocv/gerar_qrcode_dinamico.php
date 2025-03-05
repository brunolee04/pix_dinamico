<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__.'/vendor/autoload.php';

use \App\Pix\Api;
use \App\Pix\Payload;
use Mpdf\QrCode\QrCode;
use Mpdf\QrCode\Output;

/*
	sandbox_base_url = api.itau.com.br/sandbox
	sandbox_token_url = api.itau.com.br/sandbox/api/oauth/token
*/


$base_url = "https://api.itau.com.br/sandbox";

$client_id = ""; // Fornecido pelo banco
$client_secret = "";// Fornecido pelo banco

$obApiPix = new Api($base_url,
					$client_id,
					$client_secret,
					'');


$request = [
	'calendario'=>[
		'expiracao'=>3600
	],
	'devedor'=>[
		'cpf' => '00000000000', // Informe aqui o CPF
		'nome' => 'Bruno Lee' // Informe o nome aqui
	],
	'valor'=>[
		'original'=> '10.00'
	],
	'chave'=>'chave@pix.com.br', //Chave PIX
	'solicitacaoPagador'=> "Pagamento do pedido tal"
];

$txId = 'abcde1234567891234567890001'; // Id Aleatório para identificação
$response = $obApiPix->createCob($txId,$request);

if(!isset($response['location'])){
	echo "problemas ao gerar PIX dinamico";
	echo "<pre>";
	print_r($response);
	echo "</pre>";
}


//instancia do payload
$objPayload = (new Payload)->setMerchantName("Bruno Lee")
						   ->setMerchantCity("Ribeirao das Neves")
						   ->setAmount($response['valor']['original'])
						   ->setTxId($response['txid'])
						   ->setUrl($response['location'])
						   ->setUniquePayment(true);


//código do pagamento
$payloadQrcode = $objPayload->getPayload();

$objQrCode = new QrCode($payloadQrcode);

//imagem do qrcode
$image = (new Output\Png)->output($objQrCode,400);


?>

<h1>Qrcode Dinâmico PIX</h1>
<br>
<img src="data:image/png;base64, <?=base64_encode($image)?>">

<br><br>
Código pix:<br>
<strong><?=$payloadQrcode;?></strong>