<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
Class api_db extends CI_MODEL
{
 
    public function __construct(){
        parent::__construct();
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
	 * obtenemos el usuario del mensaje
	 */
	public function getUserChat($channelId,$id){
		$this->db->select('wp_bp_chat_channel_users.status');
		$this->db->select('wp_users.id as idUSer, wp_users.display_name, wp_users.playerId');
		$this->db->from('wp_bp_chat_channel_users');
		$this->db->join('wp_users', 'wp_users.id = wp_bp_chat_channel_users.user_id');
		$this->db->where('wp_bp_chat_channel_users.channel_id = ', $channelId);
		$this->db->where('wp_users.id != ', $id);
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
	 * inserta el mensaje del chat
	 */
	function InsertMessageOfChat($data){
		$this->db->insert('wp_bp_chat_messages', $data);
		$id = $this->db->insert_id();
		
		$this->db->select('wp_bp_chat_messages.id, wp_bp_chat_messages.channel_id, wp_bp_chat_messages.sender_id, wp_bp_chat_messages.message');
		$this->db->select('wp_bp_chat_messages.sent_at, date(wp_bp_chat_messages.sent_at) as date');
		$this->db->from('wp_bp_chat_messages');
		$this->db->where('wp_bp_chat_messages.id = ', $id);
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
    
    
    /************** Pantalla HOME ******************/
	
	/**
	 * Obtiene los usuarios por ciudad
	 */
	function getUsersByCity($idCity){
        $this->db->from('users');
		return $this->db->get()->result();
	}
    
    
    
}
//end model



