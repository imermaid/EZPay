<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collections
 *
 * @author Steve
 */

class Collections extends MY_Controller {
    
    protected $passdb;
     private $limit = 20;

    public function __construct()
        {
         parent::__construct();
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          $this->passdb = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdowns from the correct databases
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE); 
         $this->load->model('applications_model', '', TRUE);
        }
    
    
    public function index($offset = 0, $order_column = 'app_id', $order_type = 'asc')
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}         
          
          if( $sql = $this->clients_model->view_record())
           {
            $data['cli_rec'] = $sql;
           }    
          
                $data['title'] = 'EZ Pay Dashboard';
                $data['tab'] = 'Recent Applications';
                $data['action_sbi']    = site_url('dashboard/search_by_id');
                $data['action_sbfl']    = site_url('dashboard/search_by_first_last');
                $data['user'] = $this->auth_user_id;
                $data['client'] = $this->auth_client_id; 
                $data['Client_count']  = $this->clients_model->count_all();
                $data['User_count']  = $this->users_model->count_all_by_client();
                $this->load->helper(array('dropdown_helper','form')); 
                $data['Search_by'] = listData($this->passdb,'search_by_id_type','id_name', 'description');
                
                /*****************************************************************************************
                 *    Pagination
                 */
                 if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_created'; 
              if (empty($order_type)) $order_type = 'desc'; 
//TODO: check for valid column 
// load data 
   
  $AppListings = $this->applications_model->get_paged_recentapp_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('dashboard/index/'); 
                $config['total_rows'] = $this->applications_model->count_all(); 
                  $config['per_page'] = $this->limit; 
               $config['uri_segment'] = 3; 
                 $config['next_link'] = 'Next &gt;';
                 $config['prev_link'] = '&lt; Previous';
             $config['full_tag_open'] = '<div id="pagination">';
            $config['full_tag_close'] = '</div>';
            $this->pagination->initialize($config); 
            $data['pagination'] = $this->pagination->create_links();               
                 
 // generate table data 
            $this->load->library('table'); 
            $this->table->set_empty(""); 
            $new_order = ($order_type == 'asc' ? 'desc' : 'asc'); 
            $this->table->set_heading( 
                     '#',
                    secure_anchor('applications/index/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/index/'.$offset.'/app_fname/'.$new_order, 'First Name'),
                    secure_anchor('applications/index/'.$offset.'/app_lname/'.$new_order, 'Last Name'),
                    secure_anchor('applications/index/'.$offset.'/app_email/'.$new_order, 'Email'), 
                    secure_anchor('applications/index/'.$offset.'/app_status/'.$new_order, 'Status'),
                    secure_anchor('applications/index/'.$offset.'/app_created/'.$new_order, 'File Date'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($AppListings as $AL){ 
                $lesstime = substr($AL->app_created,0,10); // get rid of the time aspect
            $this->table->add_row(
                       ++$i,
                $AL->app_id,
                $AL->app_fname, 
                $AL->app_lname,
                $AL->app_email, 
                $AL->app_status, 
                $lesstime,
                secure_anchor(
                        'applications/view/'.$AL->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$AL->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update'))  
                    ); 
                
            }
            
            $data['table'] = $this->table->generate(); 
            
            /*******************************************************************************************
             * End Pagination
             */
                
           
           
            $this->template->load('client', 'collections/collection_view', $data);
            // If not admin, redirect
   
	}
    
    
    
}
