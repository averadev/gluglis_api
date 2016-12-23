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
class Upload extends CI_Controller {

	 public function __construct(){
        parent::__construct();
        $this->load->helper('url');
		$this->load->database('default');
        $this->load->model('api_db');
    }

	public function index(){
		
    }
	
	public function uploadImage(){
		
		
		if (!function_exists('http_response_code')){
			function http_response_code($newcode = NULL){
				static $code = 200;
				if($newcode !== NULL){
					header('X-PHP-Response-Code: '.$newcode, true, $newcode);
					if(!headers_sent())
						$code = $newcode;
				}       
				return $code;
			}
		}
		//
		// This is an arbitary limit.  Your PHP server has it's own limits, which may be more or 
		// less than this limit.  Consider this an exercise in learning more about how your PHP
		// server is configured.   If it allows less, then your script will fail.
		//
		// See: http://stackoverflow.com/questions/2184513/php-change-the-maximum-upload-file-size
		// for more information on file size limits.
		//
		$MAX_FILESIZE = 5 * 1024 * 1024;  // 5 megabyte limit -- arbitrary value based on your needs
	
		if ((isset($_SERVER["HTTP_FILENAME"])) && (isset($_SERVER["CONTENT_TYPE"])) && (isset($_SERVER["CONTENT_LENGTH"]))) {
			$filesize = $_SERVER["CONTENT_LENGTH"];
			// get the base name of the file.  This should remove any path information, but like anything
			// that writes to your file server, you may need to take extra steps to harden this to make sure
			// there are no path remnants in the file name.
			//
			$filename = basename($_SERVER["HTTP_FILENAME"]);
			$filetype = $_SERVER["CONTENT_TYPE"];
			
			//$aa = basename($_SERVER["HTTP_FILENAME"]);
 
			//
			// enforce the arbirary file size limits here
			// 
			if ($filesize > $MAX_FILESIZE) {
				http_response_code(413);
				echo json_encode("File too large");
				exit;
			}
			//
			// Make sure the filename is unique.
			// This will cause files after 100 of the same name to overwrite each other.
			// And it won't notify you.  Don't depend on this logic for production.
			// You should code this to fit your needs.
			//
			//$aa = 0;
			if (file_exists("upload/" . $filename)) {
				//echo("duplicate filename");
				$i = 1;
				$path_parts = pathinfo($filename);
				
				
         
				$filename = $path_parts['filename'] . "_" . $i . "." . $path_parts['extension'];
				while(file_exists("upload/" . $filename))  {
					$i++;
					if ($i > 100) {
						break;
					}
					//$aa = $path_parts['filename'];
					$filename = $path_parts['filename'] . "_" . $i . "." . $path_parts['extension'];
				}
			}
 
			/* PUT data comes in on the stdin stream */
			$putdata = fopen("php://input", "r");
 
			if ($putdata) {
				/* Open a file for writing */
				$tmpfname = tempnam("assets/img/avatar", "RCV");
				$fp = fopen($tmpfname, "w");
				if ($fp) {
					/* Read the data 1 KB at a time and write to the file */
					while ($data = fread($putdata, 1024)) {
						fwrite($fp, $data);
					}
					/* Close the streams */
					fclose($fp);
					fclose($putdata);
					$result = rename($tmpfname, "assets/img/avatar/" . $filename);  
					if ($result) {
						http_response_code(201);
						chmod("assets/img/avatar/" . $filename, 0644);
						
						/*$update = array(
							'ID'			=> 1183,
							'imagen'		=> $aa,
						);
						$this->api_db->updateProfile($update);*/
						
						echo json_encode("File Created " . $filename);
					} else {
						http_response_code(403);
						echo json_encode("Renaming file to upload/" . $filename . " failed.");
					}          
				} else {
					http_response_code(403);
					echo json_encode("Could not open tmp file " . $tmpfname);
				}
			} else {
				http_response_code(403);
				echo json_encode("Could not read upload stream.");
			}
		} else {
			http_response_code(500);
			echo json_encode("Malformed Request");
		}		
		//echo json_encode('aa');
	}
    
}