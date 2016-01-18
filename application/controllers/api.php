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
        $this->load->database('default');
        $this->load->model('api_db');
    }

	public function index_get(){
       // $this->load->view('web/vwApi');
	   echo "";
    }
	/************** Pantalla LOGIN ******************/
	
	/**
	 * crea un nuevo usuario
	 */
	public function createUser_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
			$id = 0;
			//verifica si existe o no el usuario
			$result = $this->api_db->getUser($this->get('email'),$this->get('pass'));
			if(count($result) == 0){
				$nameUser = "";
				//verifica si existe la variableo le asigna vacio
				if($this->get('name')){
					$nameUser = $this->get('name');
				}
				$hoy = getdate();
				$strHoy = $hoy["year"]."-".$hoy["mon"]."-".$hoy["mday"] . " " . $hoy["hours"] . ":" . $hoy["minutes"] . ":" . $hoy["seconds"];
				$insert = array(
					'user_login' 			=> $nameUser,
					'user_pass' 			=> $this->get('pass'),
					'user_nicename' 		=> $nameUser,
					'user_email' 			=> $this->get('email'),
					'user_url' 				=> '',
					'user_registered' 		=> $strHoy,
					'user_activation_key' 	=> '',
					'user_status' 			=> '2',
					'display_name' 			=> $nameUser,
					'playerId'				=> $this->get('playerId'),
				);
				//inserta los datos de usuario normales
				$id = $this->api_db->insertUser($insert);
				//verifica si existe el parametro de genero
				if($this->get('gender')){
					$gen = "";
					if($this->get('gender') == "Male" || $this->get('gender') == "male"){
						$gen = "Hombre";
					}else{
						$gen = "Mujer";
					}
					$gender = array(
						'field_id' 			=> 3,
						'user_id' 			=> $id,
						'value' 			=> $gen,
						'last_updated' 		=> $strHoy,
					);
					//inserta el genero del usuario
					$this->api_db->insertXProfileData($gender);
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
					$message = array('success' => true, 'message' => "Usuario registrado", 'idApp' => $id );
				}else{
					$message = array('success' => false, 'message' => "Usuario existente, intente con otra contraseña", 'idApp' => $id );
				}
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
			
			if( $this->get('email') && $this->get('password') ){
				$result = $this->api_db->validateUser($this->get('email'),$this->get('password'));
				if(count($result) > 0){
				//$this->api_db->updatePlayerId($result[0]->id, $this->get('playerId'));
					$message = array( 'success' => true, 'message' => "Usuario correcto", 'item' => $result );
				}else{
					$message = array( 'success' => false, 'message' => "Usuario no encontrado" );
				}
			}else{
				$message = array( 'success' => false, 'message' => "Usuario no encontrado" );
			}
			
			/**/
			
        }
        $this->response($message, 200);
	}
	
	/************** Pantalla MESSAGES ******************/
	
	/**
	 * Obtiene la lista de chats del usuario
	 */
	public function getListMessageChat_get(){
		$message = $this->verifyIsSet(array('idApp'));
		if ($message == null) {
            $channel = $this->api_db->getChannelsById($this->get('idApp'));
			$chats = array();
			foreach($channel as $item){
				$result = $this->api_db->getListMessageChat($item->channel_id,$this->get('idApp'));
				if(count($result) > 0){
					$user = $this->api_db->getUserChat($item->channel_id,$this->get('idApp'));
					$result[0]->id = $user[0]->idUSer;
					$result[0]->display_name = $user[0]->display_name;
					$result[0]->blockYour = $user[0]->status;
					$result[0]->blockMe = $item->status;
					$result[0]->image = $result[0]->id . ".png";
					$result[0]->type = $user[0]->type;
					$result[0]->identifier = $user[0]->identifier;
					$array2 = json_decode(json_encode($result[0]),true);
					array_push($chats, $array2);
				}
			}
			usort($chats, array($this, "ordenar")); 
			$message = array('success' => true, 'items' => $chats );
        }
        $this->response($message, 200);
	}
	
	public function ordenar($a, $b) {
		return strtotime($b['sent_at']) - strtotime($a['sent_at']);
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
            $messages = $this->api_db->getMessagesByChannel($this->get('channelId'));
			$dateBefore = "";
			foreach($messages as $item){
				if($item->sender_id == $this->get('idApp')){
					$item->isMe = true;
				}else{
					$item->isMe = false;
				}
				$fechaD = $dias[date('N', strtotime($item->sent_at)) - 1];
				$item->dia = $fechaD;
				$item->fechaFormat = date('d', strtotime($item->sent_at)) . ' de ' . 
					$months[date('n', strtotime($item->sent_at))] . ' del ' . 
					date('Y', strtotime($item->sent_at));
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
			$months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
				'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
			$dias = array('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado','Domingo');
			$insert = array(
				'sender_id' 	=> $this->get('idApp'),
				'channel_id' 	=> $this->get('channelId'),
				'message' 		=> $this->get('message')
				//'sent_at'			=> $this->get('dateM')
			);
            $chat = $this->api_db->InsertMessageOfChat($insert); //inserta el mensaje del chats
			$user = $this->api_db->getUserChat($this->get('channelId'),$this->get('idApp')); //obtiene los datos del otro usuario
			$user2 = $this->api_db->getUserChatById($this->get('channelId') ,$this->get('idApp')); // obtiene el status del usuario
			$noRead = $this->api_db->getChatNoReadById($this->get('channelId'),$this->get('idApp')); //obtiene los mensajes no leidos
			
			$this->api_db->updateLastMessage($chat[0]->sent_at,$chat[0]->channel_id); //actualiza el tiempo del ultimo mensaje enviado en canal
		
			foreach($chat as $item){
				$date = date_create($item->sent_at);
				$item->hora = date_format($date, 'g:i A');
				$item->fechaFormat = date('d', strtotime($item->sent_at)) . ' de ' . 
					$months[date('n', strtotime($item->sent_at))] . ' del ' . 
					date('Y', strtotime($item->sent_at));
				$item->id = $user[0]->idUSer;
				$item->display_name = $user[0]->display_name;
				$item->NoRead = $noRead[0]->NoRead;
				$item->blockYour = $user[0]->status;
				$item->blockMe = $user2[0]->status;
				$item->image = $item->id . ".png";
				$item->type = $user[0]->type;
				$item->identifier = $user[0]->identifier;
				if($user[0]->playerId != '0' || $user[0]->playerId != 0){
					$this->SendNotificationPush($user[0]->playerId,json_encode($chat),"1");
				}
			}
			
			$message = array('success' => true, 'items' => $chat );
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
        }
        $message = array('success' => true, 'items' => $items );
        $this->response($message, 200);
	}
    
    /**
	 * Obtiene los usuarios por ciudad
	 */
	public function getUsersByCity_get(){
		$items = $this->api_db->getUsersByCity($this->get('idCity'),$this->get('idApp'));
        foreach($items as $item){
            $item->idiomas = unserialize($item->idiomas);
            $item->hobbies = unserialize($item->hobbies);
        }
        $message = array('success' => true, 'items' => $items );
        $this->response($message, 200);
	}
	
	/**
	 * Obtiene los usuarios filtrados
	 */
	public function getUsersByFilter_get(){
		$data = array(
			'city' 			=> $this->get('city'),
			'iniDate'		=> $this->get('iniDate'),
			'endDate'		=> $this->get('endDate'),
			'genH'			=> $this->get('genH'),
			'genM'			=> $this->get('genM'),
			'iniAge'		=> $this->get('iniAge'),
			'endAge'		=> $this->get('endAge')
		);
		$items = $this->api_db->getUsersByFilter($this->get('idApp'),$data);
        foreach($items as $item){
            $item->idiomas = unserialize($item->idiomas);
            $item->hobbies = unserialize($item->hobbies);
        }
        $message = array('success' => true, 'items' => $items );
        $this->response($message, 200);
	}
	
	/************** Pantalla PROFILE ******************/
    
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
			
			$message = array('success' => true, 'thereChannel' => $thereChannel, 'item' => $user[0]);
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
	 * Funcion para enviar notificaciones
	 */
	public function SendNotificationPush($playerId, $items, $type){
		
		$userID = [$playerId]; 
		if($type == 1){
			$massage = "Nuevo mensaje";
		}
		$content = array(
			"en" => $massage
		);
		$fields = array(
		'app_id' => "b7f8ee34-cf02-4671-8826-75d45b3aaa07",
		'include_player_ids' => $userID,
		'data' => array("type" => $type, "item" => $items),
		'isAndroid' => true,
		'contents' => $content
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