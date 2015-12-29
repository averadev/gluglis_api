<?php
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';


/**
 * Gluglis
 * Author: Alberto Vera Espitia
 * GeekBucket 2015
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
					$result[0]->idUSer = $user[0]->idUSer;
					$result[0]->display_name = $user[0]->display_name;
					$result[0]->blockYour = $user[0]->status;
					$result[0]->blockMe = $item->status;
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
            $chat = $this->api_db->InsertMessageOfChat($insert);
			$user = $this->api_db->getUserChat($this->get('channelId'),$this->get('idApp'));
			$user2 = $this->api_db->getUserChatById($this->get('channelId') ,$this->get('idApp'));
			$noRead = $this->api_db->getChatNoReadById($this->get('channelId'),$this->get('idApp'));
		
			foreach($chat as $item){
				$date = date_create($item->sent_at);
				$item->hora = date_format($date, 'g:i A');
				$item->fechaFormat = date('d', strtotime($item->sent_at)) . ' de ' . 
					$months[date('n', strtotime($item->sent_at))] . ' del ' . 
					date('Y', strtotime($item->sent_at));
				$item->idUSer = $user[0]->idUSer;
				$item->display_name = $user[0]->display_name;
				$item->NoRead = $noRead[0]->NoRead;
				$item->blockYour = $user[0]->status;
				$item->blockMe = $user2[0]->status;
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
	 * Obtiene los usuarios por ciudad
	 */
	public function getUsersByCity_get(){
		$items = $this->api_db->getUsersByCity($this->get('idCity'));
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
			//$user[0]->idUSer = $user[0]->idUSer;
			/*$result[0]->display_name = $user[0]->display_name;*/
			$user[0]->blockYour = $user[0]->status;
			$user[0]->blockMe = $result[0]->status;
			
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