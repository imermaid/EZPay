<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of ezdb30041
 * Extend Applications so each client can process their own version of an Application
 *
 * @author STHOMAS
 */
require( $_SERVER['DOCUMENT_ROOT']. '/application/controllers/Applications.php');

class Ez30041_Application extends Applications{
     public function __construct()
        {
         parent::__construct();
         //Need is_logged_in() for auth_*** to work in a method
       $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          $this->passdb = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdowns from the correct databases
          $this->db2use = $this->load->database($this->auth_client_id, true);
         //  $this->db2use = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdowns from the correct databases
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url', 'directory');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE); 
         $this->load->model('address_model', '', TRUE);
         $this->load->model('applications_model', '', TRUE);
         
        }
    //put your code here
}
