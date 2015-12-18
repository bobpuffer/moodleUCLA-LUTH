<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class morsle {
    
    public function __construct($username) {
        $service_name = 'drive';
//        $key = get_config('morsle','privatekey');
//        $this->privatekey = json_decode($key);
//        var_dump($key);
//        die;
        $this->client = new Google_Client();
	$this->client_id = '1016393342084-dqql4goj9s0l402sbf4dtnoq2tsk0hp8.apps.googleusercontent.com';
        $this->service_account_name = '1016393342084-dqql4goj9s0l402sbf4dtnoq2tsk0hp8@developer.gserviceaccount.com';
        $this->client->addScope("https://www.googleapis.com/auth/drive");
        $this->client->addScope("https://www.googleapis.com/auth/drive.file");
        $this->client->addScope('https://spreadsheets.google.com/feeds'
);
        $this->service = new Google_Service_Drive($this->client);
        $this->auth = service_account($this->client, $this->client_id, $this->service_account_name, $username);
        $this->revoke_token();
        $this->auth = service_account($this->client, $this->client_id, $this->service_account_name, $username);
    }
    
        function revoke_token() {
            $this->client->revokeToken($this->auth->access_token);
            unset($this->client->auth);
            unset($_SESSION['service_token']);
        }
}
