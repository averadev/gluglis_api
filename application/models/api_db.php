<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class api_db extends CI_MODEL
{
 
    public function __construct(){
        parent::__construct();
    }
	
	/************** Pantalla LOGIN ******************/
	
	/**
	 * valida si existe el usuario
	 */
	public function getUser($email,$pass){
		$this->db->select('wp_users.Id as id');
		$this->db->from('wp_users');
		$this->db->where('wp_users.user_email = ', $email);
        return $this->db->get()->result();
	}
	
	/**
	 * valida si existe el usuario
	 */
	public function getXprofileData($id,$field){
		$this->db->select('wp_bp_xprofile_data.id');
		$this->db->from('wp_bp_xprofile_data');
		$this->db->where('wp_bp_xprofile_data.user_id = ', $id);
		$this->db->where('wp_bp_xprofile_data.field_id = ', $field);
        return $this->db->get()->result();
	}
	
	/**
	 * valida al usuario por correo o password
	 */
	public function validateUser($email, $pass){
		$this->db->select('wp_users.ID as id, wp_users.user_email, wp_users.display_name');
		$this->db->from('wp_users');
		$this->db->where('wp_users.user_email = ', $email);
		$this->db->where('wp_users.user_pass = ', $pass);
        return $this->db->get()->result();
	}
	
	/**
	 * inserta un nuevo usuario
	 */
	public function insertUser($data){
		$this->db->insert('wp_users', $data);
		return $this->db->insert_id();
	}
	
	/**
	 * inserta el identificador social
	 */
	public function insertSocialUser($data){
		$this->db->insert('wp_social_users', $data);
	}
	
	/**
	 * inserta los datos del usuario
	 */
	public function insertXProfileData($data){
		$this->db->insert('wp_bp_xprofile_data', $data);
	}
	
	/**
	 * actualiza el token de notificaciones del usuario
	 */
	public function updatePlayerId($id, $playerId){
		$this->db->set('wp_users.playerId', $playerId); 
		$this->db->where('wp_users.ID = ', $id);
        $this->db->update('wp_users');
	}
	
	/************** Pantalla MESSAGES ******************/
	
	/**
	 * obtenemos la lista de mensajes chats del usuario
	 */
	public function getChannelsById($id){
		$this->db->select('wp_bp_chat_channel_users.channel_id, wp_bp_chat_channel_users.status');
		$this->db->from('wp_bp_chat_channel_users');
		$this->db->where('wp_bp_chat_channel_users.user_id = ', $id);
        return $this->db->get()->result();
	}
	
	/**
	 * obtenemos la lista de mensajes chats del usuario
	 */
	public function getListMessageChat($channelId,$id){
		$this->db->select('wp_bp_chat_messages.id as idMessage, wp_bp_chat_messages.channel_id, wp_bp_chat_messages.message, wp_bp_chat_messages.status_message');
		$this->db->select('wp_bp_chat_messages.sender_id, wp_bp_chat_messages.sent_at, wp_bp_chat_messages.sent_at');
		$this->db->select('(select count(*) from wp_bp_chat_messages where wp_bp_chat_messages.channel_id = ' . 
			$channelId . ' and wp_bp_chat_messages.status_message = 0 and wp_bp_chat_messages.sender_id != ' . $id . ' ) as NoRead ');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.channel_id = ', $channelId);
		$this->db->order_by('wp_bp_chat_messages.sent_at', 'desc');
        return $this->db->get()->result();
	}
	
	/**
	 * obtenemos el usuario del mensaje de la lista de chats
	 */
	public function getUserChat($channelId,$id){
		$this->db->select('wp_bp_chat_channel_users.status');
		$this->db->select('wp_users.id as idUSer, wp_users.display_name, wp_users.playerId');
		$this->db->select('wp_social_users.type, wp_social_users.identifier');
		$this->db->from('wp_bp_chat_channel_users');
		$this->db->join('wp_users', 'wp_users.id = wp_bp_chat_channel_users.user_id');
		$this->db->join('wp_social_users', 'wp_social_users.ID = wp_users.id','left');
		$this->db->where('wp_bp_chat_channel_users.channel_id = ', $channelId);
		$this->db->where('wp_users.id != ', $id);
		$this->db->limit(1);
        return $this->db->get()->result();
	}
	
	/**
	 * obtenemos la info del usuario del canal por id
	 */
	public function getUserChatById($channelId,$id){
		$this->db->select('wp_bp_chat_channel_users.status');
		$this->db->select('wp_users.id as idUSer, wp_users.display_name, wp_users.playerId');
		$this->db->from('wp_bp_chat_channel_users');
		$this->db->join('wp_users', 'wp_users.id = wp_bp_chat_channel_users.user_id');
		$this->db->where('wp_bp_chat_channel_users.channel_id = ', $channelId);
		$this->db->where('wp_users.id = ', $id);
		$this->db->limit(1);
        return $this->db->get()->result();
	}
	
	/**
	 * Obtiene los mensajes no leidos por id
	 */
	function getChatNoReadById($channelId, $id){
		$this->db->select('count(*) as NoRead');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.channel_id = ', $channelId);
		$this->db->where('wp_bp_chat_messages.status_message = 0');
		$this->db->where('wp_bp_chat_messages.sender_id != ', $id);
        return $this->db->get()->result();
	}
	
	/************** Pantalla MESSAGE ******************/
	
	/**
	 * obtenemos la lista de mensajes del canal
	 */
	public function getMessagesByChannel($channelId){
		$this->db->select('wp_bp_chat_messages.id, wp_bp_chat_messages.channel_id, wp_bp_chat_messages.sender_id');
		$this->db->select('wp_bp_chat_messages.message, wp_bp_chat_messages.status_message');
		$this->db->select('wp_bp_chat_messages.sent_at, date(wp_bp_chat_messages.sent_at) as dateOnly');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.channel_id = ', $channelId);
		$this->db->order_by('wp_bp_chat_messages.sent_at', 'asc');
        return $this->db->get()->result();
	}
	
	/**
	 * obtenemos la lista de mensajes del canal no leidos
	 */
	public function getMessagesByChannelNotRead($channelId, $idApp){
		$this->db->select('wp_bp_chat_messages.id, wp_bp_chat_messages.channel_id, wp_bp_chat_messages.sender_id');
		$this->db->select('wp_bp_chat_messages.message, wp_bp_chat_messages.status_message');
		$this->db->select('wp_bp_chat_messages.sent_at, date(wp_bp_chat_messages.sent_at) as dateOnly');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.channel_id = ', $channelId);
		$this->db->where('wp_bp_chat_messages.status_message = ', 0);
		$this->db->where('wp_bp_chat_messages.sender_id != ', $idApp);
		$this->db->order_by('wp_bp_chat_messages.sent_at', 'asc');
        return $this->db->get()->result();
	}
	
	/**
	 * obtenemos la lista de mensajes del canal no leidos
	 */
	public function getLastMessageRead($channelId, $idApp){
		$this->db->select('wp_bp_chat_messages.id');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.channel_id = ', $channelId);
		$this->db->where('wp_bp_chat_messages.status_message = ', 1);
		$this->db->where('wp_bp_chat_messages.sender_id = ', $idApp);
		$this->db->order_by('wp_bp_chat_messages.sent_at', 'DESC');
		$this->db->limit(1);
        return $this->db->get()->result();
	}
	
	/**
	 * inserta el mensaje del chat
	 */
	function InsertMessageOfChat($data){
		$this->db->insert('wp_bp_chat_messages', $data);
		$id = $this->db->insert_id();
		
		$this->db->select('wp_bp_chat_messages.id as idMessage, wp_bp_chat_messages.channel_id, wp_bp_chat_messages.sender_id, wp_bp_chat_messages.message');
		$this->db->select('wp_bp_chat_messages.sent_at, date(wp_bp_chat_messages.sent_at) as date');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.id = ', $id);
        return $this->db->get()->result();
	}
	
	/**
	 *
	 */
	public function getMessageRead($idMessage){
		$this->db->select('wp_bp_chat_messages.id');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.id = ', $idMessage);
		$this->db->where('wp_bp_chat_messages.status_message = ', 0);
        return $this->db->get()->result();
	}
	
	/**
	 * bloquea el chat
	 */
	function updateStatusChat($data){
		$this->db->where('channel_id', $data['channel_id']);
		$this->db->where('user_id', $data['user_id']);
        $this->db->update('wp_bp_chat_channel_users', $data);
	}
	
	/**
	 * cambia es status de los mensajes del chats
	 */
	function changeStatusMessages($data){
		$this->db->set('status_message', 1); 
		$this->db->where('channel_id = ', $data['channel_id']);
		$this->db->where('sender_id != ', $data['sender_id']);
		$this->db->where('id <= ', $data['id']);
        $this->db->update('wp_bp_chat_messages');
	}
	
	/**
	 * actualiza la fecha del ultimo mensaje enviado en canal
	 */
	function updateLastMessage($date, $channelId){
		$this->db->set('last_message_time', $date); 
		$this->db->where('id', $channelId);
        $this->db->update('wp_bp_chat_channels');
	}
    
    /************** Pantalla HOME ******************/
	
	/**
	 * Obtiene la informacion del usuario por id
	 */
	public function getUsersById($idApp){
		$this->db->from('users');
		$this->db->where('users.id = ', $idApp);
		return $this->db->get()->result();
	}
	
	/**
	 * Obtiene los usuarios por ciudad
	 */
	function getUsersByCity($idCity,$idApp){
        $this->db->from('users');
		$this->db->where('users.id != ', $idApp);
		return $this->db->get()->result();
	}
	
	/**
	 * Obtiene los usuarios por filtro
	 */
	function getUsersByFilter($idApp,$data){
        $this->db->from('users');
		//condiciones
		if($data['city'] != "0"){
			//$this->db->where('users.residencia = ', "Cancún, Mexico");
			$this->db->where('users.residencia = ', $data['city']);
		}
		if($data['iniDate'] != "0000-00-00"){
			//$this->db->where('users.residencia = ', $data['iniDate']);
		}
		if($data['genH'] == 1 && $data['genM'] == 0 ){
			$this->db->where('users.genero = ', "Hombre");
		}else if($data['genM'] == 1 && $data['genH'] == 0 ){
			$this->db->where('users.genero = ', "Mujer");
		}
		if($data['accommodation'] == "Sí" ){
			$this->db->where('(users.alojamiento = "Sí" or users.alojamiento is null)');
		}else{
			$this->db->where('(users.alojamiento = "No" or users.alojamiento is null)');
		}
		$this->db->where("(users.edad is null or (users.edad >=" . $data['iniAge'] . " and users.edad <= " . $data['endAge'] . "))");
		//$this->db->where('users.edad <= ', $data['endAge']);
		//$this->db->where('users.edad <= ', $data['endAge']);
		$this->db->where('users.id != ', $idApp);
		
		return $this->db->get()->result();
	}
	
	/************** Pantalla PROFILE ******************/
	
	/**
	 * obtiene la lista de hobbies
	 */
	public function getHobbies(){
		$this->db->select('wp_bp_xprofile_fields.id, wp_bp_xprofile_fields.name');
		$this->db->from('wp_bp_xprofile_fields');
		$this->db->where('wp_bp_xprofile_fields.parent_id = 322 ');
        return $this->db->get()->result();
	}
	
	/**
	 * obtiene la lista de idiomas
	 */
	public function getLanguage(){
		$this->db->select('wp_bp_xprofile_fields.id, wp_bp_xprofile_fields.name');
		$this->db->from('wp_bp_xprofile_fields');
		$this->db->where('wp_bp_xprofile_fields.parent_id = 137 ');
        return $this->db->get()->result();
	}
	
	/**
	 * obtiene la lista de deportes
	 */
	public function getSport(){
		$this->db->select('wp_bp_xprofile_fields.id, wp_bp_xprofile_fields.name');
		$this->db->from('wp_bp_xprofile_fields');
		$this->db->where('wp_bp_xprofile_fields.parent_id = 353 ');
        return $this->db->get()->result();
	}
	
	/**
	 * obtiene la lista de deportes
	 */
	public function getResidenceTime(){
		$this->db->select('wp_bp_xprofile_fields.id, wp_bp_xprofile_fields.name');
		$this->db->from('wp_bp_xprofile_fields');
		$this->db->where('wp_bp_xprofile_fields.parent_id = 14 ');
        return $this->db->get()->result();
	}
	
	/**
	 * obtiene la lista de deportes
	 */
	public function getRace(){
		$this->db->select('wp_bp_xprofile_fields.id, wp_bp_xprofile_fields.name');
		$this->db->from('wp_bp_xprofile_fields');
		$this->db->where('wp_bp_xprofile_fields.parent_id = 125 ');
        return $this->db->get()->result();
	}
	
	/**
	 * obtiene la lista de deportes
	 */
	public function getWorkArea(){
		$this->db->select('wp_bp_xprofile_fields.id, wp_bp_xprofile_fields.name');
		$this->db->from('wp_bp_xprofile_fields');
		$this->db->where('wp_bp_xprofile_fields.parent_id = 214 ');
        return $this->db->get()->result();
	}
	
	/**
	 * Verifica si existe el canal
	 */
	public function startConversation($idApp){
		$this->db->select('wp_bp_chat_channel_users.channel_id');
		$this->db->from('wp_bp_chat_channel_users');
		$this->db->where('wp_bp_chat_channel_users.user_id = ', $idApp);
        return $this->db->get()->result();
	}
	
	/**
	 * obtenemos la lista de mensajes chats del usuario
	 */
	public function getChannelChat($channelId,$id){
		$this->db->select('wp_bp_chat_messages.id, wp_bp_chat_messages.channel_id, wp_bp_chat_messages.message, wp_bp_chat_messages.status_message');
		$this->db->select('wp_bp_chat_messages.sender_id, wp_bp_chat_messages.sent_at, wp_bp_chat_messages.sent_at');
		$this->db->select('(select count(*) from wp_bp_chat_messages where wp_bp_chat_messages.channel_id = ' . 
			$channelId . ' and wp_bp_chat_messages.status_message = 0 and wp_bp_chat_messages.sender_id != ' . $id . ' ) as NoRead ');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.channel_id = ', $channelId);
		$this->db->order_by('wp_bp_chat_messages.sent_at', 'desc');
        return $this->db->get()->result();
	}
	
	/**
	 * crea un nuevo canal de chat
	 */
	public function createChannel($data){
		$this->db->insert('wp_bp_chat_channels', $data);
		return $this->db->insert_id();
	}
	
    /**
	 * inserta los usuarios al canal
	 */
	public function createChannelUser($data){
		$this->db->insert_batch('wp_bp_chat_channel_users', $data); 
	}
    
	/**
	 * actualiza los datos generales del usuario
	 */
	public function updateProfile($data){
		$this->db->where('wp_users.ID = ', $data['ID']);
		$this->db->update('wp_users', $data);
	}
	
	/**
	 * actualiza los datos opcionales
	 */
	public function updateXProfileData($data){
		$this->db->where('wp_bp_xprofile_data.field_id = ', $data['field_id']);
		$this->db->where('wp_bp_xprofile_data.user_id = ', $data['user_id']);
		$this->db->update('wp_bp_xprofile_data', $data);
	}
    
}
//end model



