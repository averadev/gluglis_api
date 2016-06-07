<?php
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';


/**
 * Gluglis
 * Author: Alfredo Zum
 * Gluglis 2015
 *
 */
class Api extends REST_Controller {

	public function __construct() {
        parent::__construct();
		error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
        $this->load->database('default');
        $this->load->model('api_db');
		date_default_timezone_set('Etc/GMT0');
    }

	public function index_get(){
		/*$hoy = getdate();
		$strHoy = $hoy["year"]."-".$hoy["mon"]."-".$hoy["mday"] . " " . $hoy["hours"] . ":" . $hoy["minutes"] . ":" . $hoy["seconds"];
		echo $strHoy . "</br>";*/
		
    }
	
	/************** Pantalla LOGIN ******************/
	
	/**
	 * crea un nuevo usuario
	 */
	public function createUser_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			$id = 0;
			$hoy = getdate();
			$strHoy = $hoy["year"]."-".$hoy["mon"]."-".$hoy["mday"] . " " . $hoy["hours"] . ":" . $hoy["minutes"] . ":" . $hoy["seconds"];
			//verifica si existe o no el usuario
			$result = $this->api_db->getUser($this->get('email'),$this->get('pass'));
			if(count($result) == 0){
				$password = $this->decryptPass($this->get('pass'));
				$password = $this->wp_hash_password($password);
				$nameUser = "";
				//verifica si existe la variableo le asigna vacio
				if($this->get('name')){
					$nameUser = $this->get('name');
				}
				$insert = array(
					'user_login' 			=> $this->get('userLogin'),
					'user_pass' 			=> $password,
					'user_nicename' 		=> $this->get('userLogin'),
					'user_email' 			=> $this->get('email'),
					'user_url' 				=> '',
					'user_registered' 		=> $strHoy,
					'user_activation_key' 	=> '',
					'user_status' 			=> '0',
					'display_name' 			=> $nameUser,
					'playerId'				=> $this->get('playerId'),
				);
				//inserta los datos de usuario normales
				$id = $this->api_db->insertUser($insert);
				
				$insertAct = array();
				$activityType = array("last_activity", "new_member");
				$activityAction= array("", '<a href="http://www.gluglis.travel/members/"'. $this->get('userLogin') . '/" title="'.$nameUser.'">'.$nameUser.'</a> ahora es un usuario registrado');
				$activityLink = array("", "http://www.gluglis.travel/members/" . $this->get('userLogin'));
				for($i=0;$i<2;$i++){
					$dataInsert = array(
						'user_id' 				=> $id,
						'component' 			=> $activityType[$i],
						'type' 					=> $activityAction[$i],
						'action' 				=> $activityLink[$i],
						'content' 				=> "",
						'primary_link' 			=> "",
						'item_id'			 	=> '0',
						'secondary_item_id' 	=> '0',
						'date_recorded' 		=> $strHoy
					);
					array_push($insertAct,$dataInsert);
				}
				$this->api_db->insertActivity($insertAct);

				
				//verifica si existe el parametro de genero
				if($this->get('gender')){
					$gen = "";
					if($this->get('gender') == "Male" || $this->get('gender') == "male"){
						$gen = "Hombre";
					}else if($this->get('gender') == "Female" || $this->get('gender') == "female"){
						$gen = "Mujer";
					}
					if($gen != ""){
						$gender = array(
							'field_id' 			=> 3,
							'user_id' 			=> $id,
							'value' 			=> $gen,
							'last_updated' 		=> $strHoy,
						);
						//inserta el genero del usuario
						$this->api_db->insertXProfileData($gender);
					}
				}
				
				if($this->get('birthday')){
					$birthday = array(
						'field_id' 			=> 25,
						'user_id' 			=> $id,
						'value' 			=> $this->get('birthday'),
						'last_updated' 		=> $strHoy,
					);
					//inserta la fecha de nacimiento del usuario
					$this->api_db->insertXProfileData($birthday);
				}
				
				if($this->get('location')){
					$location = array(
						'field_id' 			=> 11,
						'user_id' 			=> $id,
						'value' 			=> $this->get('location'),
						'last_updated' 		=> $strHoy,
					);
					//inserta la fecha de nacimiento del usuario
					$this->api_db->insertXProfileData($location);
				}
				
				//verifica si se loqueo mediante face
				if($this->get('facebookId')){
					$insert2 = array(
						'ID' 			=> $id,
						'type' 			=> "fb",
						'identifier' 	=> $this->get('facebookId'),
					);
					//inserta los datos de facebook
					$this->api_db->insertSocialUser($insert2);
				}
				$message = array('success' => true, 'message' => "Usuario registrado", 'idApp' => $id );
			}else{
				$id = $result[0]->id;
				if($this->get('facebookId')){
					if($this->get('birthday')){
						//verifica si existe los datos
						$result1 = $this->api_db->getXprofileData($id, 25);
						if(count($result1) == 0 ){
							$birthday = array(
								'field_id' 			=> 25,
								'user_id' 			=> $id,
								'value' 			=> $this->get('birthday'),
								'last_updated' 		=> $strHoy,
							);
							//inserta la fecha de nacimiento del usuario
							$this->api_db->insertXProfileData($birthday);
						}
					}
					if($this->get('location')){
						//verifica si existe los datos
						$result1 = $this->api_db->getXprofileData($id, 11);
						if(count($result1) == 0 ){
							$location = array(
								'field_id' 			=> 11,
								'user_id' 			=> $id,
								'value' 			=> $this->get('location'),
								'last_updated' 		=> $strHoy,
							);
							//inserta la fecha de nacimiento del usuario
							$this->api_db->insertXProfileData($location);
						}
					}
					$this->api_db->updatePlayerId($id, $this->get('playerId'));
					$message = array('success' => true, 'message' => "Usuario registrado", 'idApp' => $id );
				}else{
					$message = array('success' => false, 'message' => "Usuario existente, intente con otro correo", 'idApp' => $id );
				}
			}
        }
        $this->response($message, 200);
	}
	
	//
	public function validatePass_get($source){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			//verifica si existe o no el usuario
			$pass = 'key';
			$method = 'aes-256-cbc';
			$decrypted = openssl_decrypt ( html_entity_decode($this->get('source')), $method, $pass );
			if($decrypted != false){
				$message = array( 'success' => true, 'message' => $decrypted );
			}else{
				$message = array( 'success' => false, 'message' => $decrypted );
			}
        }
        $this->response($message, 200);
	}
	
	
	/**
	 * valida el inicio de secion
	 */
	public function validateUser_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			//verifica si existe o no el usuario
			$password = $this->decryptPass($this->get('password'));
			if($password != false){
				if( $this->get('email') && $this->get('password') ){
					require_once( 'class-phpass.php');
					$result = $this->api_db->validateUser($this->get('email'),$this->get('password'));
					$wp_hasher = new PasswordHash(8, TRUE);
					if(count($result) > 0){
						if($wp_hasher->CheckPassword($password, $result[0]->user_pass)) {
							$this->api_db->updatePlayerId($result[0]->id, $this->get('playerId'));
							$message = array( 'success' => true, 'message' => "Usuario correcto", 'item' => $result );
						} else {
							$message = array( 'success' => false, 'message' => "Usuario no encontrado" );
						}
					}else{
						$message = array( 'success' => false, 'message' => "Usuario no encontrado" );
					}
				}else{
					$message = array( 'success' => false, 'message' => "Usuario no encontrado" );
				}
			}else{
				$message = array( 'success' => false, 'message' => "Usuario no encontrado" );
			}
        }
        $this->response($message, 200);
	}
	
	//
	private function decryptPass($source){
		//verifica si existe o no el usuario
		$pass = 'key';
		$method = 'aes-256-cbc';
		$decrypted = openssl_decrypt ( html_entity_decode($source), $method, $pass );
		if($decrypted != false){
			return $decrypted;
		}else{
			return false;
		}
	}
	
	//
	private function wp_hash_password($password) {
		global $wp_hasher;
 
		if ( empty($wp_hasher) ) {
			require_once( 'class-phpass.php');
			// By default, use the portable hash from phpass
			$wp_hasher = new PasswordHash(8, true);
		}
 
		return $wp_hasher->HashPassword( trim( $password ) );
	}
	
	/************** Pantalla MESSAGES ******************/
	
	/**
	 * Obtiene la lista de chats del usuario
	 */
	public function getListMessageChat_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			if($this->get('timeZone')<0){
				$timeZone = substr($this->get('timeZone'), 0, 3);
			}else{
				if(substr($this->get('timeZone'),0,1) == "+"){
					$timeZone = substr($this->get('timeZone'), 1, 2);
				}else{
					$timeZone = substr($this->get('timeZone'), 0, 2);
				}
			}
			$timeZone = intval($timeZone);
            $channel = $this->api_db->getChannelsById($this->get('idApp'));
			$chats = array();
			foreach($channel as $item){
				$result = $this->api_db->getListMessageChat($item->channel_id,$this->get('idApp'),$timeZone);
				if(count($result) > 0){
					$user = $this->api_db->getUserChat($item->channel_id,$this->get('idApp'));
					if(count($user) > 0){
						$result[0]->id = $user[0]->idUSer;
						$result[0]->display_name = $user[0]->display_name;
						$result[0]->blockYour = $user[0]->status;
						$result[0]->blockMe = $item->status;
						$result[0]->image = $result[0]->id . ".png";
						$result[0]->type = $user[0]->type;
						$result[0]->identifier = $user[0]->identifier;
						$result[0]->image2 = $result[0]->image;
						$imgAvatar = get_avatar( $result[0]->id );
						$avatar = $this->extraerSRC($imgAvatar);
						if($avatar){
							//$item->image2 = $avatar;
							$file = $avatar;
							$file_headers = @get_headers($file);
							if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
								$result[0]->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
							}else {
								$mystring = $avatar;
								$findme   = 'www.gravatar.com';
								$pos = strpos($mystring, $findme);
								if ($pos === false) {
									$result[0]->image2 = $avatar;
								}else{
									$result[0]->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
								}
								
							}
						}else{
							$result[0]->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
						}
						$array2 = json_decode(json_encode($result[0]),true);
						array_push($chats, $array2);
					}
				}
			}
			//date_default_timezone_set('America/Los_Angeles');

			usort($chats, array($this, "ordenar")); 
			$message = array('success' => true, 'items' => $chats);
        }
        $this->response($message, 200);
	}
	
	public function ordenar($a, $b) {
		return ($b['sent_at_unix']) - ($a['sent_at_unix']);
	}
	
	/************** Pantalla MESSAGE ******************/
	
	/**
	 * Obtiene los mensajes por channel
	 */
	public function getChatMessages_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			$months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
			'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
			$dias = array('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
			if($this->get('timeZone')<0){
				$timeZone = substr($this->get('timeZone'), 0, 3);
			}else{
				if(substr($this->get('timeZone'),0,1) == "+"){
					$timeZone = substr($this->get('timeZone'), 1, 2);
				}else{
					$timeZone = substr($this->get('timeZone'), 0, 2);
				}
			}
			$timeZone = intval($timeZone);
            $messages = $this->api_db->getMessagesByChannel($this->get('channelId'),$timeZone);
			$dateBefore = "";
			foreach($messages as $item){
				if($item->sender_id == $this->get('idApp')){
					$item->isMe = true;
				}else{
					$item->isMe = false;
				}
				$item->dia = $dias[date('N',($item->sent_at_unix)) - 1];
				$item->fechaFormat = date('d', ($item->sent_at_unix)) . ' de ' . 
					$months[date('n', ($item->sent_at_unix))] . ' del ' . 
					date('Y', ($item->sent_at_unix));
				$date = date_create($item->sent_at);
				$item->hora = date_format($date, 'g:i A');
				if($dateBefore != $item->dateOnly){
					$item->changeDate = 1;
				}else{
					$item->changeDate = 0;
				}
				$dateBefore = $item->dateOnly;
			}
			$message = array('success' => true, 'items' => $messages );
        }
        $this->response($message, 200);
	}
	
	/**
	 * Guarda los chats
	 */
	public function saveChat_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			if($this->get('timeZone')<0){
				$timeZone = substr($this->get('timeZone'), 0, 3);
			}else{
				if(substr($this->get('timeZone'),0,1) == "+"){
					$timeZone = substr($this->get('timeZone'), 1, 2);
				}else{
					$timeZone = substr($this->get('timeZone'), 0, 2);
				}
			}
			$timeZone = intval($timeZone);
			$months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
				'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
			$dias = array('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
			$hoy = getdate();
			$strHoy = $hoy["year"]."-".$hoy["mon"]."-".$hoy["mday"] . " " . $hoy["hours"] . ":" . $hoy["minutes"] . ":" . $hoy["seconds"];
			$insert = array(
				'sender_id' 	=> $this->get('idApp'),
				'channel_id' 	=> $this->get('channelId'),
				'message' 		=> html_entity_decode($this->get('message')),
				'sent_at'			=> $strHoy
			);
            $chat = $this->api_db->InsertMessageOfChat($insert,$timeZone); //inserta el mensaje del chats
			$user = $this->api_db->getUserChat($this->get('channelId'),$this->get('idApp')); //obtiene los datos del otro usuario
			$user2 = $this->api_db->getUserChatById($this->get('channelId') ,$this->get('idApp')); // obtiene el status del usuario
			$noRead = $this->api_db->getChatNoReadById($this->get('channelId'),$this->get('idApp')); //obtiene los mensajes no leidos
			
			$this->api_db->updateLastMessage($chat[0]->sent_at,$chat[0]->channel_id); //actualiza el tiempo del ultimo mensaje enviado en canal
		
			foreach($chat as $item){
				$date = date_create($item->sent_at);
				$item->hora = date_format($date, 'g:i A');
				$item->fechaFormat = date('d', ($item->sent_at_unix)) . ' de ' . 
					$months[date('n', ($item->sent_at_unix))] . ' del ' . 
					date('Y', ($item->sent_at_unix));
				$item->id = $user[0]->idUSer;
				$item->display_name = $user[0]->display_name;
				$item->NoRead = $noRead[0]->NoRead;
				$item->blockYour = $user[0]->status;
				$item->blockMe = $user2[0]->status;
				$item->image = $item->id . ".png";
				$item->type = $user[0]->type;
				$item->identifier = $user[0]->identifier;
				$total = strlen($this->get('message'));
				$messa = html_entity_decode($this->get('message'));
				if($total > 35){
					$messa = substr($messa, 0, 35) . "..."; 
				}
				$item->image2 = $item->image;
				$imgAvatar = get_avatar( $item->id );
				$avatar = $this->extraerSRC($imgAvatar);
				if($avatar){
					//$item->image2 = $avatar;
					$file = $avatar;
					$file_headers = @get_headers($file);
					if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
						$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
					}else {
						$mystring = $avatar;
						$findme   = 'www.gravatar.com';
						$pos = strpos($mystring, $findme);
						if ($pos === false) {
							$item->image2 = $avatar;
						}else{
							$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
						}
						
					}
				}else{
					$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
				}
				usleep(100000);
				$isRead = $this->api_db->getMessageRead($chat[0]->idMessage); //comprueba que el mensaje no este leido
				if($isRead > 0){
					$messa = $user2[0]->display_name . " : " . $messa;
					if($user[0]->playerId != '0' || $user[0]->playerId != 0){
						$this->SendNotificationPush($user[0]->playerId,json_encode($chat),"1", $messa);
					}
				}
			}
			//$timeZone = intval($this->get('timeZone'));
			$message = array('success' => true, 'items' => $chat );
        }
        $this->response($message, 200);
	} 
	
	public function getMessagesByChannel_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			$months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
			'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
			$dias = array('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
			if($this->get('timeZone')<0){
				$timeZone = substr($this->get('timeZone'), 0, 3);
			}else{
				if(substr($this->get('timeZone'),0,1) == "+"){
					$timeZone = substr($this->get('timeZone'), 1, 2);
				}else{
					$timeZone = substr($this->get('timeZone'), 0, 2);
				}
			}
			$timeZone = intval($timeZone);
            $messages = $this->api_db->getMessagesByChannelNotRead($this->get('channelId'),$this->get('idApp'),$timeZone);
			$lastRead = $this->api_db->getLastMessageRead($this->get('channelId'),$this->get('idApp'));
			if(count($lastRead) > 0){
				$lastRead = $lastRead[0]->id;
			}else{
				$lastRead = 0;
			}
			$dateBefore = "";
			foreach($messages as $item){
				if($item->sender_id == $this->get('idApp')){
					$item->isMe = true;
				}else{
					$item->isMe = false;
				}
				$fechaD = $dias[date('N', ($item->sent_at_unix)) - 1];
				$item->dia = $fechaD;
				$item->fechaFormat = date('d', ($item->sent_at_unix)) . ' de ' . 
					$months[date('n', ($item->sent_at_unix))] . ' del ' . 
					date('Y', ($item->sent_at_unix));
				$date = date_create($item->sent_at);
				$item->hora = date_format($date, 'g:i A');
				if($dateBefore != $item->dateOnly){
					$item->changeDate = 1;
				}else{
					$item->changeDate = 0;
				}
				$dateBefore = $item->dateOnly;
			}
			$message = array('success' => true, 'items' => $messages, 'lastRead' => $lastRead );
        }
        $this->response($message, 200);
	}
	
	/**
	 * bloquea o desbloquea los chats
	 */
	public function blokedChat_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			$status = "closed";
			if($this->get('status') == "closed"){
				$status = "open";
			}
			$update = array(
				'channel_id'	=> $this->get('channelId'),
				'user_id'		=> $this->get('idApp'),
				'status' 		=> $status
			);
			$this->api_db->updateStatusChat($update);
			$message = array('success' => true, 'message' => "chat bloqueado", 'status' => $status );
        }
        $this->response($message, 200);
	}
    
	/**
	 * Cambia los status de los mensajes
	 */
    public function changeStatusMessages_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			$update = array(
				'id' 			=> $this->get('idMessage'),
				'channel_id'	=> $this->get('channelId'),
				'sender_id'		=> $this->get('idApp')
			);
			$this->api_db->changeStatusMessages($update);
			$message = array('success' => true, 'message' => "mensajes leidos" );
        }
        $this->response($message, 200);
	}
    
    /************** Pantalla HOME ******************/
	
	 /**
	 * Obtiene los datos del usuario por id
	 */
	public function getUsersById_get(){
		$items = $this->api_db->getUsersById($this->get('idApp'));
        foreach($items as $item){
            $item->idiomas = unserialize($item->idiomas);
            $item->hobbies = unserialize($item->hobbies);
			$item->deportes = unserialize($item->deportes);
			$item->image2 = $item->image;
			$imgAvatar = get_avatar( $item->id );
			$avatar = $this->extraerSRC($imgAvatar);
			if($avatar){
					//$item->image2 = $avatar;
					$file = $avatar;
					$file_headers = @get_headers($file);
					if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
						$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
					}else {
						$mystring = $avatar;
						$findme   = 'www.gravatar.com';
						$pos = strpos($mystring, $findme);
						if ($pos === false) {
							$item->image2 = $avatar;
						}else{
							$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
						}
						
					}
				}else{
					$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
				}
				//$item->patch = base_url();
			}
        $message = array('success' => true, 'items' => $items );
        $this->response($message, 200);
	}
    
    /**
	 * Obtiene los usuarios por ciudad
	 */
	public function getUsersByCity_get(){
		$data = array(
			'city' 				=> $this->get('city'),
		);
		$items = $this->api_db->getUsersByCity($this->get('idApp'),$data,$this->get('limit'));
		
        foreach($items as $item){
            $item->idiomas = unserialize($item->idiomas);
            $item->hobbies = unserialize($item->hobbies);
			$item->deportes = unserialize($item->deportes);
			$item->cuentaPropia = unserialize($item->cuentaPropia);
			$item->image2 = $item->image;
			$imgAvatar = get_avatar( $item->id );
			$avatar = $this->extraerSRC($imgAvatar);
			if($avatar){
				//$item->image2 = $avatar;
				$file = $avatar;
				$file_headers = @get_headers($file);
				if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
					$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
				}else {
					$mystring = $avatar;
					$findme   = 'www.gravatar.com';
					$pos = strpos($mystring, $findme);
					if ($pos === false) {
						$item->image2 = $avatar;
					}else{
						$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
					}
				}
			}else{
				$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
			}
        }
		if(count($items) > 0){
			$message = array('success' => true, 'items' => $items );
		}else{
			$message = array('success' => false, 'message' => "No se encontraron usuarios" );
		}
        
        $this->response($message, 200);
	}
	
	/**
	 * Obtiene los usuarios filtrados
	 */
	public function getUsersByFilter_get(){
		$data = array(
			'city' 				=> $this->get('city'),
			'iniDate'			=> $this->get('iniDate'),
			'endDate'			=> $this->get('endDate'),
			'genH'				=> $this->get('genH'),
			'genM'				=> $this->get('genM'),
			'iniAge'			=> $this->get('iniAge'),
			'endAge'			=> $this->get('endAge'),
			'accommodation'		=> $this->get('accommodation')
		);
		$items = $this->api_db->getUsersByFilter($this->get('idApp'),$data,$this->get('limit'));
		
        foreach($items as $item){
            $item->idiomas = unserialize($item->idiomas);
            $item->hobbies = unserialize($item->hobbies);
			$item->deportes = unserialize($item->deportes);
			$item->cuentaPropia = unserialize($item->cuentaPropia);
			$item->image2 = $item->image;
			$imgAvatar = get_avatar( $item->id );
			$avatar = $this->extraerSRC($imgAvatar);
			if($avatar){
				//$item->image2 = $avatar;
				$file = $avatar;
				$file_headers = @get_headers($file);
				if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
					$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
				}else {
					$mystring = $avatar;
					$findme   = 'www.gravatar.com';
					$pos = strpos($mystring, $findme);
					if ($pos === false) {
						$item->image2 = $avatar;
					}else{
						$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
					}
					
				}
			}else{
				$item->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
			}
        }
		if(count($items) > 0){
			$message = array('success' => true, 'items' => $items );
		}else{
			$message = array('success' => false, 'message' => "No se encontraron usuarios" );
		}
        
        $this->response($message, 200);
	}
	
	 /**
	 * limpia los datos del usuario(playerId)
	 */
	public function clearUser_get(){
		$items = $this->api_db->updatePlayerId($this->get('idApp'), '0');
        $message = array('success' => true, 'message' => 'Sesion cerrada con exito' );
        $this->response($message, 200);
	}
	
	/**
	 * actualiza el playerId cada vez que entra a la app
	 */
	public function updatePlayerId_get(){
		$items = $this->api_db->updatePlayerId($this->get('idApp'), $this->get('playerId'));
        $message = array('success' => true);
        $this->response($message, 200);
	}
	
	
	
	/************** Pantalla PROFILE ******************/
	
	/**
	 * Obtiene la lista de hobbies
	 */
	public function getHobbies_get(){
		$items = $this->api_db->getHobbies();
		$items2 = $this->api_db->getLanguage();
		$items3 = $this->api_db->getSport();
		$items4 = $this->api_db->getResidenceTime();
		$items5 = $this->api_db->getRace();
		$items6 = $this->api_db->getWorkArea();
        $message = array('success' => true, 'hobbies' => $items, 'language' => $items2, 'sport' => $items3, 'residenceTime' => $items4, 'race' => $items5, 'workArea' => $items6 );
        $this->response($message, 200);
	}
    
    /**
	 * Obtiene los usuarios por ciudad
	 */
	public function startConversation_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			//obtenemos la lista de canales
			$channelId1 = $this->api_db->getChannelsById($this->get('idApp'));
			$channelId2 = $this->api_db->getChannelsById($this->get('idUser'));
			$channelId = 0;
			$thereChannel = false;
			//comprueba si el chat no existe
			for ($i = 0; $i<count($channelId1); $i++) {
				for ($j = 0; $j<count($channelId2); $j++) {
					if($channelId1[$i]->channel_id == $channelId2[$j]->channel_id){
						$channelId = $channelId1[$i]->channel_id;
					}
				}
			}
			
			//verificamos si existe
			if($channelId != 0 ){
				$thereChannel = true;
			
			}else{
				//en caso de no existir creamos un nuevo canal
				$thereChannel = false;
				$hoy = getdate();
				$strHoy = $hoy["year"]."-".$hoy["mon"]."-".$hoy["mday"] . " " . $hoy["hours"] . ":" . $hoy["minutes"] . ":" . $hoy["seconds"];
				$insertChannel = array(
					'time_created'		=> $strHoy,
					'status'			=> 1,
					'is_multichat'		=> 0,
					'is_open'			=> 1
				);
				$channelId = $this->api_db->createChannel($insertChannel);
				
				//se crea los usuarios del canal
				$insertChannelUsers = array(
					array(
						'channel_id' 	=> $channelId,
						'user_id' 		=> $this->get('idApp'),
						'status' 		=> 'open' ,
						'has_initiated' => '0'
					),
					array(
						'channel_id' 	=> $channelId,
						'user_id' 		=> $this->get('idUser'),
						'status' 		=> 'open' ,
						'has_initiated' => '0'
					)
				);
				$this->api_db->createChannelUser($insertChannelUsers);
			}
			
			$result = $this->api_db->getUserChatById($channelId ,$this->get('idApp'));
			$user = $this->api_db->getUserChat($channelId,$this->get('idApp'));
			$user[0]->channel_id = $channelId;
			$user[0]->blockYour = $user[0]->status;
			$user[0]->blockMe = $result[0]->status;
			$user[0]->image = $user[0]->idUSer . ".png";
			$user[0]->id = $user[0]->idUSer;
			$user[0]->image2 = $user[0]->image;
			$imgAvatar = get_avatar( $user[0]->id );
			$avatar = $this->extraerSRC($imgAvatar);
			if($avatar){
				//$item->image2 = $avatar;
				$file = $avatar;
				$file_headers = @get_headers($file);
				if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
					$user[0]->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
				}else {
					$mystring = $avatar;
					$findme   = 'www.gravatar.com';
					$pos = strpos($mystring, $findme);
					if ($pos === false) {
						$user[0]->image2 = $avatar;
					}else{
						$user[0]->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
					}
					
				}
			}else{
				$user[0]->image2 = "http://gluglis.travel/gluglis_api/assets/img/avatar/avatar.png";
			}
			
			$message = array('success' => true, 'thereChannel' => $thereChannel, 'item' => $user[0]);
        }
        $this->response($message, 200);
	}
	
	/**
	 * guarda los datos del usuario
	 */
	public function saveProfile_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			//obtiene la fecha actual
			$hoy = getdate();
			$strHoy = $hoy["year"]."-".$hoy["mon"]."-".$hoy["mday"] . " " . $hoy["hours"] . ":" . $hoy["minutes"] . ":" . $hoy["seconds"];
			
			//verifica si existe la variable de nombre o asigna un vacio
			$display_name = "";
			if($this->get('UserName')){
				$display_name = $this->get('UserName');
			}
			//actualiza el nombre del usuario
			$update = array(
				'ID'				=> $this->get('idApp'),
				'display_name'		=> $display_name,
			);
			$this->api_db->updateProfile($update);
			
			//name
			$name = "";
			if($this->get('name')){
				$name = $this->get('name');
			}	
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 2,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $name,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 2);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//apellidos
			$lastName = "";
			if($this->get('lastName')){
				$lastName = $this->get('lastName');
			}	
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 24,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $lastName,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 24);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//genero
			$updateXdata = array(
				'field_id' 			=> 3,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('gender'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 3);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//residencia
			$residence = "";
			if($this->get('residence')){
				$residence = $this->get('residence');
			}	
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 11,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $residence,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 11);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//residencia
			$originCountry = "";
			if($this->get('originCountry')){
				$originCountry = $this->get('originCountry');
			}	
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 8,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $originCountry,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 8);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//tiempo de residencia
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 14,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('residenceTime'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 14);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//email
			$emailContact = "";
			if($this->get('emailContact')){
				$emailContact = $this->get('emailContact');
			}	
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 29,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $emailContact,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 29);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//disponibilidad
			$updateXdata = array(
				'field_id' 			=> 1316,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('availability'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 1316);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//alojamiento
			$updateXdata = array(
				'field_id' 			=> 33,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('accommodation'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 33);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//vehicle
			$updateXdata = array(
				'field_id' 			=> 109,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('vehicle'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 109);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//food
			$updateXdata = array(
				'field_id' 			=> 36,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('food'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 36);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//race
			$updateXdata = array(
				'field_id' 			=> 125,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('race'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 125);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//workArea
			$updateXdata = array(
				'field_id' 			=> 214,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('workArea'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 214);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//ownAccount
			$updateXdata = array(
				'field_id' 			=> 319,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('ownAccount'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 319);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//pet
			$pet = "No";
			if($this->get('pet')){
				$pet = "Sí";
			}
			
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 121,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $pet,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 121);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			if($pet == "Sí"){
				//carga los datos de la residencia
				$updateXdata = array(
					'field_id' 			=> 124,
					'user_id' 			=> $this->get('idApp'),
					'value' 			=> $this->get('pet'),
					'last_updated' 		=> $strHoy,
				);
				//verifica si existe ya el campo en la bd
				$result = $this->api_db->getXprofileData($this->get('idApp'), 124);
				//inserta o actualiza los datos dependiendo si existe o no
				if(count($result) > 0){
					$this->api_db->updateXProfileData($updateXdata);
				}else{
					$this->api_db->insertXProfileData($updateXdata);
				}
			}
			
			//smoke
			$updateXdata = array(
				'field_id' 			=> 112,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('smoke'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 112);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//drink
			$updateXdata = array(
				'field_id' 			=> 115,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('drink'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 115);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//drink
			$updateXdata = array(
				'field_id' 			=> 118,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $this->get('psychrotrophic'),
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 118);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			//hobbies
			if($this->get('hobbies')){
				$hobbies = json_decode($this->get('hobbies'));
				if(count($hobbies) > 0){
					for($i=0;$i<count($hobbies);$i++){
						$remplaza = str_replace("...", "/", $hobbies[$i]);
						$hobbies[$i] = $remplaza;
					}
					$hobbies = serialize($hobbies);
				}else{
					$hobbies = "";
				}
				$updateXdata = array(
					'field_id' 			=> 322,
					'user_id' 			=> $this->get('idApp'),
					'value' 			=> $hobbies,
					'last_updated' 		=> $strHoy,
				);
				$result = $this->api_db->getXprofileData($this->get('idApp'), 322);
				if(count($result) > 0){
					$this->api_db->updateXProfileData($updateXdata);
				}else{
					$this->api_db->insertXProfileData($updateXdata);
				}
			}
			
			//language
			if($this->get('language')){
				$language = json_decode($this->get('language'));
				if(count($language) > 0){
					for($i=0;$i<count($language);$i++){
						$remplaza = str_replace("...", "/", $language[$i]);
						$language[$i] = $remplaza;
					}
					$language = serialize($language);
				}else{
					$language = "";
				}
				$updateXdata = array(
					'field_id' 			=> 137,
					'user_id' 			=> $this->get('idApp'),
					'value' 			=> $language,
					'last_updated' 		=> $strHoy,
				);
				$result = $this->api_db->getXprofileData($this->get('idApp'), 137);
				if(count($result) > 0){
					$this->api_db->updateXProfileData($updateXdata);
				}else{
					$this->api_db->insertXProfileData($updateXdata);
				}
			}
			
			//pet
			$sport = "No";
			if($this->get('pet')){
				$sport = "Sí";
			}
			
			//carga los datos de la residencia
			$updateXdata = array(
				'field_id' 			=> 350,
				'user_id' 			=> $this->get('idApp'),
				'value' 			=> $sport,
				'last_updated' 		=> $strHoy,
			);
			//verifica si existe ya el campo en la bd
			$result = $this->api_db->getXprofileData($this->get('idApp'), 350);
			//inserta o actualiza los datos dependiendo si existe o no
			if(count($result) > 0){
				$this->api_db->updateXProfileData($updateXdata);
			}else{
				$this->api_db->insertXProfileData($updateXdata);
			}
			
			if($sport == "Sí"){
				$sport = json_decode($this->get('sport'));
				if(count($sport) > 0){
					for($i=0;$i<count($sport);$i++){
						$remplaza = str_replace("...", "/", $sport[$i]);
						$sport[$i] = $remplaza;
					}
					$sport = serialize($sport);
				}else{
					$sport = "";
				}
				$updateXdata = array(
					'field_id' 			=> 353,
					'user_id' 			=> $this->get('idApp'),
					'value' 			=> $sport,
					'last_updated' 		=> $strHoy,
				);
				$result = $this->api_db->getXprofileData($this->get('idApp'), 353);
				if(count($result) > 0){
					$this->api_db->updateXProfileData($updateXdata);
				}else{
					$this->api_db->insertXProfileData($updateXdata);
				}
			}
			
			$message = array('success' => true, 'message' => "Los cambios a tu perfil han sido almacenados." );
        }
        $this->response($message, 200);
	}
	
	/************** metodo generico ******************/
	
	/**
     * Verificamos si las variables obligatorias fueron enviadas
     */
    private function verifyIsSet($params){
    	foreach ($params as &$value) {
		    if ($this->get($value) ==  '')
		    	return array('success' => false, 'message' => 'El parametro '.$value.' es obligatorio');
		}
		return null;
    }
	
	/**
     *  obtiene solamente la dirrecion de la imagen
     */
	private function extraerSRC($cadena) {
		preg_match('@src="([^"]+)"@', $cadena, $array);
		$src = array_pop($array);
		return $src;
	}
	
	/**
	 * Funcion para enviar notificaciones
	 */
	public function SendNotificationPush($playerId, $items, $type, $messa){
		
		$userID = [$playerId]; 
		if($type == 1){
			$massage = $messa;
		}
		$content = array(
			"en" => $massage
		);
		$fields = array(
		'app_id' => "b7f8ee34-cf02-4671-8826-75d45b3aaa07",
		'include_player_ids' => $userID,
		'data' => array("type" => $type, "item" => $items),
		'isAndroid' => true,
		'isIos' => true,
		'contents' => $content,
		);
    
		$fields = json_encode($fields);
		
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                           'Authorization: Basic NGEwMGZmMjItY2NkNy0xMWUzLTk5ZDUtMDAwYzI5NDBlNjJj'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		$return["allresponses"] = $response;
		$return = json_encode($return);
	
		curl_close($ch);
	}
	
}