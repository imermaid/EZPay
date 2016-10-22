<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Dashboard
 * Main menu page for navagating to the application functions/reports
 *
 * @author Steve
 */
class Dashboard extends MY_Controller {
    

    public function __construct()
        {
         parent::__construct();
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
        
         $this->load->helper('form', 'url');
         $this->load->model('clients_model', '', TRUE);
         $this->load->model('users_model', '', TRUE);
         $this->load->model('applications_model', '', TRUE);

        }
    
    
    public function index()
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}         
          
          
                            $data['title'] = 'EZ Pay Dashboard';
                      $data['count_users'] = $this->applications_model->count_users(); 
                         $data['headline'] = 'Main Dashboard';
 

            $this->template->load('client', 'dash/dash_view', $data);
            
   
	}

            // -----------------------------------------------------------------------

   
 
   
 }
               
               
             
	
      
    
  

             

