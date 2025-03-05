<?php
 /**
  * 
  */
 class Api{


 	/**
	   * base urls
	   * @var string
	   */
 	const SANDBOX_BASE_URL = 'https://api.itau.com.br/sandbox';
	// const SANDBOX_BASE_URL = 'https://devportal.itau.com.br/sandboxapi/sispag_ext/v1';

 	const LIVE_BASE_URL    = 'https://secure.api.itau';

    const LIVE_TOKEN_URL   = 'https://sts.itau.com.br';

	const SANDBOX_TOKEN_URL = 'https://oauthd.itau/identity/connect/token';

	

 	/**
	   * Chave Pix
	   * @var string
	   */
 	private $base_url;

 	/**
	   * Client ID do oAuth2 do PSP
	   * @var string
	   */
 	private $client_id;

 	/**
	   * Client Secret do oAuth2 do PSP
	   * @var string
	   */
 	private $client_secret;

 	/**
	   * Caminho absoluto do arquivo do certificado
	   * @var string
	   */
 	private $certificate_file;


 	/**
	   * Caminho absoluto do arquivo chave do certificado
	   * @var string
	   */
 	private $key_file;

 	/**
	   * Identificador único da transação – gerado pela aplicação do Parceiro
	   * @var string
	   */
 	private $x_itau_correlationID;

 	/**
	   * Identificador único da funcionalidade – gerado pela aplicação do Parceiro
	   * @var string
	   */
 	private $x_itau_flowID;

 	
 	/**
	   * Define se as chamadas serão sandbox ou produtivas
	   * @var string
	   */
 	private $sandbox;

 	

 	/**
	   * Método responsável por retornar o valor completo de um objeto do payload
	   * @param string $base_url
	   * @param string $client_id
	   * @param string $client_secret
	   * @param string $certificate
	   */
 	public function __construct($sandbox,$base_url,$client_id,$client_secret,$certificate_file,$key_file,$x_itau_correlationID,$x_itau_flowID){
 		$this->sandbox = $sandbox;
 		$this->base_url = $base_url;
 		$this->client_id = $client_id;
 		$this->client_secret = $client_secret;
 		$this->certificate_file = $certificate_file;
 		$this->key_file = $key_file;
 		$this->x_itau_correlationID = $x_itau_correlationID;
 		$this->x_itau_flowID = $x_itau_flowID;
 	}
 	/**
	   * Método responsável por constular uma cobrança
	   * @param string $txId
	   * @return array
	   */
 	public function getOrderStatus($txId){
 		$api_url = '/pix_recebimentos/v2/cobv/';
 		return $this->send('GET',$api_url.$txId);
 	}
	
	 /**
	   * Método responsável por constular uma lista de cobranças
	   * @param string $queryData
	   * @return array
	   */
	 public function getOrdersStatus($query_first_order_date_added,$query_last_order_date_added){
		$api_url = '/pix_recebimentos/v2/cob?'.$query_first_order_date_added.'&'.$query_last_order_date_added;
		return $this->send('GET',$api_url);
	 }

 	/**
	   * Método responsável por criar uma cobrança Imediata
	   * @param string $txId
	   * @param array $request
	   * @return array
	   */
 	public function createCob($txId,$request){
 		$api_url = '/pix_recebimentos/v2/cob/';
 		return $this->send('PUT',$api_url.$txId,$request);
 	}


 	/**
	   * Método responsável por obter o token de acesso das apis PIX
	   * @return string
	*/
 	private function getAccessToken($devMode=false){

 		
 		//headers
 		if($this->sandbox){
 			//Endpoint Completo Sandbox
 			$endpoint = 'https://devportal.itau.com.br/api/jwt';//self::SANDBOX_BASE_URL.'/api/oauth/token';
			//$endpoint = 'https://oauthd.itau/identity/connect/token';
 			$headers =[
 				'Content-Type: application/x-www-form-urlencoded',
 			];
 		}
 		else{
 			//Endpoint Completo Sandbox
 			$endpoint = self::LIVE_TOKEN_URL.'/api/oauth/token';
			if($devMode)$endpoint = 'https://devportal.itau.com.br/api/jwt';

 			$headers =[
 				'Content-Type: application/x-www-form-urlencoded',
 				"x-itau-correlationID: ".$this->x_itau_correlationID,
				"x-itau-flowID: ".$this->x_itau_flowID
 			];
 		}


 		
 		$ch = curl_init();

 		if($this->sandbox){
 			//Configuração do CURL SANDBOX
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_PORT , 443);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials&client_id='.$this->client_id.'&client_secret='.$this->client_secret);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 		}
 		else{
 	
 			//Configuração do CURL PRODUÇÃO
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_PORT , 443);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSLCERT, $this->certificate_file);
			curl_setopt($ch, CURLOPT_SSLKEY, $this->key_file);
			curl_setopt($ch, CURLOPT_CAINFO, $this->certificate_file);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPAUTH, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials&client_id='.$this->client_id.'&client_secret='.$this->client_secret);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
 		}

 		//Executa o curl
 		$response = curl_exec($ch);
		//var_dump($response);
 		$info =curl_errno($ch)>0 ? array("curl_error_".curl_errno($ch)=>curl_error($ch)) : curl_getinfo($ch);
		//var_dump($info);
 		curl_close($ch);

 		$responseArray = json_decode($response,true);

 		//Retorna o Access Token
 		return isset($responseArray['access_token'])?$responseArray['access_token']:''; 		

 	}

 	/**
	   * Método responsável por enviar requisições para o PSP
	   * @param string $method
	   * @param string $resource
	   * @param array $request
	   * @return array
	   */
 	private function send($method,$resource,$request =[],$debug=false,$devMode=false){
 		//Endpoint Completo
 		$endpoint = $this->base_url.$resource;
		if($devMode)$endpoint = "https://api.itau.com.br/sispag/v1/transferencias";
		
 		//Headers
 		$headers = [
 			'Cache-Control: no-cache',
 			'Content-Type: application/json',
 			'x-itau-correlationID: '.$this->x_itau_correlationID,
			'x-itau-flowID: '.$this->x_itau_flowID,
 			'Authorization: Bearer '.$this->getAccessToken($devMode)
 		];


 		if($this->sandbox){
 			//Configuração do CURL SANDBOX
			 $headers = [
				'Cache-Control: no-cache',
				'Content-Type: application/json',
				'x-itau-correlationID: '.$this->x_itau_correlationID,
			    'x-itau-flowID: '.$this->x_itau_flowID,
			    'Accept: application/json',
				'Authorization: Bearer '.$this->getAccessToken()
			];

	 		$curl = curl_init();
	 		curl_setopt_array($curl, [
	 			CURLOPT_URL =>$endpoint,
	 			CURLOPT_SSL_VERIFYPEER=>false,
	 			CURLOPT_RETURNTRANSFER=>true,
	 			CURLOPT_CUSTOMREQUEST=>$method,
	 			CURLOPT_PORT =>443,
	 			CURLOPT_HTTPHEADER=> $headers
	 		]);
 		}
 		else{
 			//Configuração do CURL PRODUÇÃO
	 		$curl = curl_init();
	 		curl_setopt_array($curl, [
	 			CURLOPT_URL =>$endpoint,
	 			CURLOPT_SSL_VERIFYPEER=>false,
	 			CURLOPT_RETURNTRANSFER=>true,
	 			CURLOPT_SSLCERT =>$this->certificate_file,
				CURLOPT_SSLKEY =>$this->key_file,
	 			CURLOPT_CUSTOMREQUEST=>$method,
	 			CURLOPT_PORT =>443,
	 			CURLOPT_HTTPHEADER=> $headers
	 		]);
 		}
 		

 		
 		
 		switch ($method) {
 			case 'POST':
 			case 'PUT':
 				curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($request));
 				break;
 		}

		

 		//Executa o curl
 		$response = curl_exec($curl);
		$info =curl_errno($curl)>0 ? array("curl_error_".curl_errno($curl)=>curl_error($curl)) : curl_getinfo($curl);
		 curl_close($curl);
		if($debug){
			
			$data = array($endpoint,$headers,$response,$info);
	
			return $data;
		}
		else return json_decode($response,true);

 		

 	}
 }
?>