<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Clients
 *
 * @author Steve
 */
class Admin extends MY_Controller {

    
    public function __construct()
        {
         parent::__construct();
       //  $this->load->library('session');
          
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE); 
        }
    
    
    public function index()
	{
            $this->is_logged_in();
            if( $this->require_min_level(9) )
    {
    // Users level 9 and up see this ...
                        
            $data = array();

            if( $http_user_cookie_contents = $this->input->cookie( config_item('http_user_cookie_name') ) )
            {
                $http_user_cookie_contents = unserialize( $http_user_cookie_contents );
              // get the client info from the clients table 
          if( $sql = $this->clients_model->view_record())
           {
            $data['cli_rec'] = $sql;
           }    
           // get the user info from the users table
           if( $query = $this->users_model->view_record())
            {
                $data['records'] = $query;
                $data['title'] = 'EZPAY Administrator';
                $data['user'] = $this->auth_user_id;
                $data['client'] = $this->auth_client_id; 
                $data['Client_count']  = $this->clients_model->count_all();
                $data['User_count']  = $this->users_model->count_all_by_client();
            }    
           }
            $this->template->load('client', 'admin/admin_main_view', $data);
            // If not admin, redirect
    }  else {
    redirect( secure_site_url('pages/') );         
			}
	}

            // -----------------------------------------------------------------------
 
        
 
    
    
}

