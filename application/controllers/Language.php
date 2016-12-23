<?php
/**
 * Portable PHP password hashing framework.
 * @package phpass
 * @since 2.5.0
 * @version 0.3 / WordPress
 * @link http://www.openwall.com/phpass/
 */

 
class Language {
	
	
	
	function Language(){
		
	}
	
	function selectLanguage($leng){
		if( $leng == "es" ){
			return $this->spanish();
		}else if( $leng == "en" ){
			return $this->english();
		}else if( $leng == "it" ){
			return $this->italian();
		}else if( $leng == "de" ){
			return $this->german();
		}else if( $leng == "zh" ){
			return $this->chinese();
		}else if( $leng == "he" ){
			return $this->hebrew();
		}else{
			return $this->english();
		}
	}
	
	function spanish(){
		$language = array(
			'registeredUser' => 'Usuario registrado',
			'existingUser' => 'Usuario existente, intente con otro correo',
			'userNotFound' => 'Usuario no encontrado',
			'correctUser' => 'Usuario correcto',
			'chatBlocked' => 'chat bloqueado',
			'readMessages' => 'mensajes leidos',
			'NoUserWasFound' => 'No se encontraron usuarios',
			'yourSessionClosed' => 'Sesion cerrada con exito',
			'changesProfileSaved' => 'Los cambios a tu perfil han sido almacenados',
			'nowRegisteredUser' => ' ahora es un usuario registrado',
		);
		return $language;
	}
	
	function english(){
		$language = array(
			'registeredUser' => 'Registered user',
			'existingUser' => 'Existing user, try with another e-mail',
			'userNotFound' => 'User not found',
			'correctUser' => 'Correct user',
			'chatBlocked' => 'Chat blocked',
			'readMessages' => 'Read messages',
			'NoUserWasFound' => 'No user was found',
			'yourSessionClosed' => 'Your session was successfully closed',
			'changesProfileSaved' => 'Changes to your profile have been saved',
			'nowRegisteredUser' => ' s now a registered user',
		);
		return $language;
	}
	
	function italian(){
		$language = array(
			'registeredUser' => 'Utente registrato',
			'existingUser' => 'Utente esistente, utente con un’altra email',
			'userNotFound' => 'Utente non trovato',
			'correctUser' => 'Utente corretto',
			'chatBlocked' => 'chat bloccata',
			'readMessages' => 'Messaggi letti',
			'NoUserWasFound' => 'Utenti non trovati',
			'yourSessionClosed' => 'Sessione chiusa con successo',
			'changesProfileSaved' => 'Le modifiche sul tuo profilo si sono salvate',
			'nowRegisteredUser' => ' ora sei registrato',
		);
		return $language;
	}
	
	function german(){
		$language = array(
			'registeredUser' => 'Angemeldeter Nutzer',
			'existingUser' => 'Nutzer existiert bereits, verwenden Sie eine andere Email',
			'userNotFound' => 'Nutzer wurde nicht gefunden',
			'correctUser' => 'Richtiger Nutzer',
			'chatBlocked' => 'Chat blockiert',
			'readMessages' => 'Gelesene Nachrichten',
			'NoUserWasFound' => 'Es wurden keine Nutzer gefunden',
			'yourSessionClosed' => 'Sitzung erfolgreich beendet',
			'changesProfileSaved' => 'Die Profiländerungen wurden gespeichert',
			'nowRegisteredUser' => ' ist jetzt angemeldet',
		);
		return $language;
	}
	
	function chinese(){
		$language = array(
			'registeredUser' => '登记用户',
			'existingUser' => '现有的用户，尝试另一种电子邮件',
			'userNotFound' => '用户未找到',
			'correctUser' => '正确的用户',
			'chatBlocked' => '阻止了聊天功能',
			'readMessages' => '阅读邮件',
			'NoUserWasFound' => '没有成员',
			'yourSessionClosed' => '成功闭门会议',
			'changesProfileSaved' => '个人资料更改已保存。',
			'nowRegisteredUser' => ' 现在是一个注册的用户',
		);
		return $language;
	}
	
	function hebrew(){
		$language = array(
			'registeredUser' => 'משתמש רשום',
			'existingUser' => 'משתמש קיים,נסה בעזרת דואר אלקטרוני אחר',
			'userNotFound' => 'לא נמצא משתמש',
			'correctUser' => 'משתמש נכון',
			'chatBlocked' => "צא'ט חסום",
			'readMessages' => 'הודעות שנקראו',
			'NoUserWasFound' => 'לא נמצא משתמש',
			'yourSessionClosed' => 'חשבון נסגר בהצלחה',
			'changesProfileSaved' => 'השינויים בפרופיל שלך נשמרו',
			'nowRegisteredUser' => ' עכשיו הינך משתמש רשום',
		);
		return $language;
	}
	
	function selectMonths($leng){
		if( $leng == "es" ){
			return $this->monthsEs();
		}else if( $leng == "en" ){
			return $this->monthsEn();
		}else if( $leng == "it" ){
			return $this->monthsIt();
		}else if( $leng == "de" ){
			return $this->monthsDe();
		}else if( $leng == "zh" ){
			return $this->monthsZh();
		}else if( $leng == "he" ){
			return $this->monthsHe();
		}else{
			return $this->monthsEn();
		}
	}
	
	function monthsEs(){
		$months = array('', 'Enero','Febrero','Marzo','Abril','Mayo','Junio',
			'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
		return $months;
	}
	
	function monthsEn(){
		$months = array('', 'January','February','March','April','May','June',
			'July','August','September','October','November','December');
		return $months;
	}
	
	function monthsIt(){
		$months = array('', 'Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
			'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');
		return $months;
	}
	
	function monthsDe(){
		$months = array('', 'Januar','Februar','März','April','Mai','Juni',
			'Juli','August','September','Oktober','November','Dezember');
		return $months;
	}
	function monthsZh(){
		$months = array('', '一月','二月','三月','四月','五月','六月',
			'七月','八月','九月','十月','十一月','十二月');
		return $months;
	}
	
	function monthsHe(){
		$months = array('', 'ינואר','פברואר','מרץ','אפריל','מאי','יוני',
			'יולי','אוגוסט','ספטמבר','אוקטובר','נובמבר','דצמבר');
		return $months;
	}
	
}

?>
