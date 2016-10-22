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
class Dashboard extends MY_Controller {
    
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
         $this->load->model('loans_model', '', TRUE);
        }
    
    
    public function index($offset = 0, $order_column = 'app_id', $order_type = 'desc')
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}         
          
//          if( $sql = $this->clients_model->view_record())
//           {
//            $data['cli_rec'] = $sql;
//           }    
          
                            $data['title'] = 'EZ Pay Dashboard';
                              $data['tab'] = 'Recent';
                       $data['action_sbi'] = site_url('dashboard/search_by_id');
                             $data['user'] = $this->auth_user_id;
                           $data['client'] = $this->auth_client_id; 
                        $data['All_count'] = $this->applications_model->count_all(); 
                            $data['title'] = 'Search Applications';
                         $data['headline'] = 'Search Applications <span class="badge pull-right inbox-badge">' .$data['All_count']. '</span>';
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
                    '<i class="h_row">'.$data['tab'].'</i>',
                    secure_anchor('applications/index/'.$offset.'/app_id/'.$new_order, 'ID', array('class'=>'h_row', 'title' => 'Application ID')),
                    secure_anchor('applications/index/'.$offset.'/loa_id/'.$new_order, 'Loan#', array('class'=>'h_row', 'title' => 'Loan Number')),
                    secure_anchor('applications/index/'.$offset.'/app_lname/'.$new_order, 'Name', array('class'=>'h_row', 'title' => 'Applicant Name')),
                    secure_anchor('applications/index/'.$offset.'/app_phone/'.$new_order, 'Phone', array('class'=>'h_row', 'title' => 'Phone')),
                    secure_anchor('applications/index/'.$offset.'/app_primary_ssn/'.$new_order, 'SSN', array('class'=>'h_row', 'title' => 'SSN/TIN')),
                    secure_anchor('applications/index/'.$offset.'/app_email/'.$new_order, 'Email', array('class'=>'h_row', 'title' => 'Email Address')), 
                    secure_anchor('applications/index/'.$offset.'/app_status/'.$new_order, 'Status', array('class'=>'h_row', 'title' => 'Status')),
                    secure_anchor('applications/index/'.$offset.'/app_created/'.$new_order, 'File Date', array('class'=>'h_row', 'title' => 'File Date'))

                    );        
              
            foreach ($AppListings as $AL){ 
                $lesstime = substr($AL->app_created,0,10); // get rid of the time aspect
                $disp_name = $AL->app_fname. ' ' .$AL->app_lname;
                $last_four_a = substr($AL->app_primary_ssn, 7);
                $last_four = "***-**-".$last_four_a;
            $this->table->add_row(
                    secure_anchor( 'applications/view/'.$AL->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-grayDark"></i>',
                        array('class'=>'view', 'title' => 'View Applicant')).' '. 
                    secure_anchor( 'applications/update/'.$AL->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-darken"></i>',
                        array('class'=>'update', 'title' => 'Update Applicant')).' '. 
                    secure_anchor( 'loans/view/'.$AL->loa_id,
                         '<i class="fa fa-lg fa-money txt-color-darken"></i>',
                        array('class'=>'view', 'title' => 'View Loan')),
                    
                    secure_anchor( 'applications/view/'.$AL->app_id, 
                         '<i class="fa txt-color-darken">'.$AL->app_id. '</i>', 
                            array('class'=>'view', 'title' => 'View Applicant')), 
                    
                    secure_anchor( 'loans/view/'.$AL->loa_id, 
                         '<i class="fa txt-color-darken">'.$AL->loa_id. '</i>', 
                            array('class'=>'view', 'title' => 'View Loan')), 
                    
                    secure_anchor( 'applications/view/'.$AL->app_id, 
                         '<i class="fa txt-color-darken">'.$disp_name. '</i>', 
                            array('class'=>'update', 'title' => 'Update Applicant')),
 
                $AL->app_phone, 
                    $last_four,
                $AL->app_email, 
                $AL->loa_status, 
                $lesstime 
                    ); 
                
            }
            
            $data['table'] = $this->table->generate(); 

            $this->template->load('client', 'dash/dash_view', $data);
   
	}

            // -----------------------------------------------------------------------

     public function search_by_id($offset = 0, $order_column = 'app_id', $order_type = 'desc') 
             {
     $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
             
//          if( $sql = $this->clients_model->view_record())
//           {
//            $data['cli_rec'] = $sql;
//           }    
 
           // get the user info from the users table
                
       $data['action_sbi'] = site_url('dashboard/search_by_id');
             $data['user'] = $this->auth_user_id;
           $data['client'] = $this->auth_client_id; 


                /*****************************************************************************************
                 *    Pagination
                 */
                 if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'desc'; 
//TODO: check for valid column 
// load data 
              
if ($this->input->post('dash_search_by') == 'app_id') {      
                 $data['tab'] = 'App ID';
                       $table = 'applicant';
                       $field = 'applicant.app_id'; //if missing table, query is ambiguous
                       $value = $this->input->post('app_id', TRUE); 
               $data['title'] = 'Search By ID';
        $data['count_search'] = $this->applications_model->count_search($table,$field,$value); // for the tab on quick search window       
            $data['headline'] = 'Search By ID';
                         $SBI = $this->applications_model->search_by_id($this->limit, $offset, $field, $value, $order_column, $order_type)->result();
  }
if ($this->input->post('dash_search_by') == 'app_phone') {
                $data['tab'] = 'Phone';
                      $table = 'applicant';
                      $field = 'applicant.app_phone'; //if missing table, query is ambiguous
                      $value = $this->input->post('app_phone', TRUE);
              $data['title'] = 'Search By Phone';
       $data['count_search'] = $this->applications_model->count_search($table,$field,$value); // for the tab on quick search window       
           $data['headline'] = 'Search By Phone';
                        $SBI = $this->applications_model->search_by_id($this->limit, $offset, $field, $value, $order_column, $order_type)->result();
  }
if ($this->input->post('dash_search_by') == 'app_internal_id') {
                $data['tab'] = 'Internal ID';
                      $table = 'applicant';
                      $field = 'app_internal_id';
                      $value = $this->input->post('app_internal_id', TRUE); 
              $data['title'] = 'Search By Internal ID';
       $data['count_search'] = $this->applications_model->count_search($table,$field,$value); // for the tab on quick search window       
           $data['headline'] = 'Search By Internal ID';
                        $SBI = $this->applications_model->search_by_id($this->limit, $offset, $field, $value, $order_column, $order_type)->result();
   }
if ($this->input->post('dash_search_by') == 'app_primary_ssn') {
                $data['tab'] = 'SSN';
                      $table = 'applicant';
                      $field = 'applicant.app_primary_ssn'; //if missing table, query is ambiguous
                      $value = $this->input->post('app_primary_ssn', TRUE);  
              $data['title'] = 'Search By SSN';
       $data['count_search'] = $this->applications_model->count_search($table,$field,$value); // for the tab on quick search window       
           $data['headline'] = 'Search By SSN/EIN';  
                        $SBI = $this->applications_model->search_by_id($this->limit, $offset, $field, $value, $order_column, $order_type)->result();  
    }
if ($this->input->post('dash_search_by') == 'Incomplete') {
                $data['tab'] = 'Incomplete';
                      $table = 'applicant';
                      $field = 'applicant.app_status'; //if missing table, query is ambiguous
                      $value = 'Incomplete';
              $data['title'] = 'Search Incomplete Applications';
       $data['count_search'] = $this->applications_model->count_search($table,$field,$value); // for the tab on quick search window        
           $data['headline'] = 'Search Incomplete Applications <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>'; 
                        $SBI = $this->applications_model->search_by_id($this->limit, $offset, $field, $value, $order_column, $order_type)->result();
     }
if ($this->input->post('dash_search_by') == 'List All') {
                $data['tab'] = 'All';
                      $table = 'applicant';
              $data['title'] = 'List All';
       $data['count_search'] = $this->applications_model->count_all(); // for the tab on quick search window    
           $data['headline'] = 'List All Applicants';
                        $SBI = $this->applications_model->get_paged_all_list($this->limit, $offset, $order_column, $order_type)->result(); 
     }
if ($this->input->post('dash_search_by') == 'Pending') {
                 $data['tab'] = 'Pending';
                       $table = 'loans';
                       $field = 'loans.loa_status';
                       $value = 'Pending';
               $data['title'] = 'Search Pending Applications';      
        $data['count_search'] = $this->applications_model->count_search($table, $field, $value); 
            $data['headline'] = 'Search Pending Applications <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>';
                         $SBI = $this->applications_model->get_paged_search_list($this->limit, $offset, $field, $value, $order_column, $order_type)->result(); 
     }     
if ($this->input->post('dash_search_by') == 'Denied') {
                 $data['tab'] = 'Denied';
                       $table = 'loans';
                       $field = 'loans.loa_status';
                       $value = 'Denied';
               $data['title'] = 'Search Denied Applications';
        $data['count_search'] = $this->loans_model->count_search($table,$field, $value); // for the tab on quick search window        
            $data['headline'] = 'Search Denied Applications <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>';   
                         $SBI = $this->applications_model->get_paged_search_list($this->limit, $offset, $field, $value, $order_column, $order_type)->result();  
     }
if ($this->input->post('dash_search_by') == 'Funded') {
                 $data['tab'] = 'Funded';
                       $table = 'loans';
                       $field = 'loans.loa_status';
                       $value = 'Funded';
               $data['title'] = 'Search Funded Applications';
        $data['count_search'] = $this->loans_model->count_search($table,$field, $value); // for the tab on quick search window        
            $data['headline'] = 'Search Funded Applications <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>';   
                         $SBI = $this->applications_model->get_paged_search_list($this->limit, $offset, $field, $value, $order_column, $order_type)->result();  
     }     
if ($this->input->post('dash_search_by') == 'Approved') {
                 $data['tab'] = 'Approved';
                       $table = 'loans';
                       $field = 'loans.loa_status';
                       $value = 'Approved';
               $data['title'] = 'Search Approved Applications';
        $data['count_search'] = $this->loans_model->count_search($table, $field, $value); 
            $data['headline'] = 'Search Approved Applications <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>';
                         $SBI = $this->loans_model->get_paged_search_list($this->limit, $offset, $field, $value, $order_column, $order_type)->result();
     }    
if ($this->input->post('dash_search_by') == 'Applied') {
                 $data['tab'] = 'Applied';
                       $table = 'loans';
                       $field = 'loans.loa_status';
                       $value = 'Applied';
               $data['title'] = 'Search Applied Applications';      
        $data['count_search'] = $this->applications_model->count_search($table, $field, $value); 
            $data['headline'] = 'Search Applied Applications <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>';
                         $SBI = $this->applications_model->get_paged_search_list($this->limit, $offset, $field, $value, $order_column, $order_type)->result(); 
     }
if ($this->input->post('dash_search_by') == 'app_name') {     
                 $data['tab'] = 'Name';
                       $table = 'applicant';
                       $fname = $this->input->post_get('app_fname', TRUE);
                       $lname = $this->input->post_get('app_lname', TRUE);
               $data['title'] = 'Search Applications By Name';       
        $data['count_search'] = $this->applications_model->count_search($table,'app_lname', $lname); 
            $data['headline'] = 'Search Applications By Name <span class="badge pull-right inbox-badge">' .$data['count_search']. '</span>';
          if($fname == '') {
        $SBI = $this->applications_model->get_paged_search_like_last($this->limit, $offset, $lname, $order_column, $order_type)->result();          
          } else {     
        $SBI = $this->applications_model->get_paged_search_first_last($this->limit, $offset, $fname, $lname, $order_column, $order_type)->result();
          }
}
     
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('dashboard/search_by_id'); 
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
                    '<i class="h_row">'.$data['tab'].'</i>',
                    secure_anchor('applications/index/'.$offset.'/app_id/'.$new_order, 'ID', array('class'=>'h_row', 'title' => 'Application ID')),
                    secure_anchor('applications/index/'.$offset.'/loa_id/'.$new_order, 'Loan#', array('class'=>'h_row', 'title' => 'Loan Number')),
                    secure_anchor('applications/index/'.$offset.'/app_lname/'.$new_order, 'Name', array('class'=>'h_row', 'title' => 'Applicant Name')),
                    secure_anchor('applications/index/'.$offset.'/app_phone/'.$new_order, 'Phone', array('class'=>'h_row', 'title' => 'Phone')),
                    secure_anchor('applications/index/'.$offset.'/app_primary_ssn/'.$new_order, 'SSN', array('class'=>'h_row', 'title' => 'SSN/TIN')),
                    secure_anchor('applications/index/'.$offset.'/app_email/'.$new_order, 'Email', array('class'=>'h_row', 'title' => 'Email Address')), 
                    secure_anchor('applications/index/'.$offset.'/app_status/'.$new_order, 'Status', array('class'=>'h_row', 'title' => 'Status')),
                    secure_anchor('applications/index/'.$offset.'/app_created/'.$new_order, 'File Date', array('class'=>'h_row', 'title' => 'File Date'))

                    );         

            foreach ($SBI as $SBIs){ 
                 $lesstime = substr($SBIs->app_created,0,10); // get rid of the time aspect
                $disp_name = $SBIs->app_fname. ' ' .$SBIs->app_lname;
                $last_four_a = substr($SBIs->app_primary_ssn, 7);
                $last_four = "***-**-".$last_four_a;
             $this->table->add_row(
                    secure_anchor( 'applications/view/'.$SBIs->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-grayDark"></i>',
                        array('class'=>'view', 'title' => 'View Application')).' '. 
                    secure_anchor( 'applications/update/'.$SBIs->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-darken"></i>',
                        array('class'=>'update', 'title' => 'Update Application')).' '. 
                    secure_anchor( 'loans/view/'.$SBIs->loa_id,
                         '<i class="fa fa-lg fa-money txt-color-darken"></i>',
                        array('class'=>'update', 'title' => 'View Loan')), 
                    
                    secure_anchor( 'applications/view/'.$SBIs->app_id, 
                         '<i class="fa txt-color-darken">'.$SBIs->app_id. '</i>', 
                            array('class'=>'update', 'title' => 'Update')), 
                     
                    secure_anchor( 'loans/view/'.$SBIs->loa_id, 
                         '<i class="fa txt-color-darken">'.$SBIs->loa_id. '</i>', 
                            array('class'=>'view', 'title' => 'View Loan')),  
                    
                    secure_anchor( 'applications/view/'.$SBIs->app_id, 
                         '<i class="fa txt-color-darken">'.$disp_name. '</i>', 
                            array('class'=>'update', 'title' => 'Update Applicant')),
              $SBIs->app_phone, 
                    $last_four,
              $SBIs->app_email, 
             $SBIs->app_status, 
                     $lesstime
               );
            }
            
            $data['table'] = $this->table->generate(); 
           
            /*******************************************************************************************
             * End Pagination
             */
               $this->template->load('client', 'dash/dash_view', $data);
           
            }
 
   
 }
               
               
             
	
      
    
  

             

