<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

setlocale(LC_ALL,"es_ES@euro","es_ES","esp");


/**
 * The Saving coupon
 * Author: Alberto Vera Espitia
 * GeekBucket 2014
 *
 */
class Home extends CI_Controller {

	public function __construct() {
        parent::__construct();
    }

	public function index(){
		
		echo "hola";
		
		/*if ($this->session->userdata('type') == 1) {
			$data['page'] = 'home';
            $this->load->view('web/vwHome',$data);
		}else if($this->session->userdata('type') == 2) {
			redirect('dashboard');
            $this->load->view('web/vwHome',$data);
        } else {
            redirect('login');
        }*/
    }
    
}