<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Clients
 *
 * @author Steve
 */
class Clients extends MY_Controller {

    private $limit = 10;
    protected $passdb;
    
    public function __construct()
        {
         parent::__construct();
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
         $this->passdb = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdows from the correct databases                
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url', 'dropdown_helper');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE);
         $this->load->model('products_model', '', TRUE);
         
        }
    
    
    public function index($offset = 0, $order_column = 'client_id', $order_type = 'asc')
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
            if( $this->require_min_level(9) )  
            {
          // Git the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'id'; 
              if (empty($order_type)) $order_type = 'asc'; 
//TODO: check for valid column 
// load data 
  $Clients = $this->clients_model->get_paged_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
            $config['base_url'] = site_url('clients/index/'); 
            $config['total_rows'] = $this->clients_model->count_all(); 
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
              //    '#', 
                    secure_anchor('clients/index/'.$offset.'/client_id/'.$new_order, 'ID'),
                    secure_anchor('clients/index/'.$offset.'/client_name/'.$new_order, 'Client Name'), 
                    secure_anchor('clients/index/'.$offset.'/contact_name/'.$new_order, 'Contact Name'), 
                    secure_anchor('clients/index/'.$offset.'/contact_phone/'.$new_order, 'Phone'),
                    secure_anchor('clients/index/'.$offset.'/status/'.$new_order, 'Status'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Clients as $Client){ 
            $this->table->add_row(
            //  ++$i,
                $Client->client_id,
                $Client->client_name, 
                $Client->contact_name, 
                $Client->contact_phone, 
                $Client->status,    
                secure_anchor(
                        'clients/view/'.$Client->client_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'clients/update/'.$Client->client_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'clients/delete/'.$Client->client_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this Client?')")) 
                ); 
                
            }
            
            $data['table'] = $this->table->generate(); 
            if ($this->uri->segment(3)=='delete_success') {
                $data['message'] = 'The Data was successfully deleted'; 
            }else if ($this->uri->segment(3)=='add_success') {
                $data['message'] = 'The Data has been successfully added'; 
            }else{ 
                $data['message'] = ''; 
            }
            
// load view 
            $data['title'] = 'Client List';
            $this->template->load('client', 'clients/client_list', $data);
            
            
            
            }  
	}


        
        /*****************************************************************
         * Add()
         */
    function add(){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
        
        
     // set common properties 
            $today = date('Y-m-d');
            $data['title'] = 'Add New Client'; 
            $data['action'] = site_url('clients/add'); 
            $data['link_back'] = secure_anchor(
                    'clients/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Clients',
                    array('class'=>'back')); 
            $this->load->library('form_validation'); 
            $this->_set_rules(); 
     // run validation 
         if ($this->form_validation->run() === FALSE){ 
            
     // set common properties 
            $data['title']                      = 'Add new Client'; 
            $data['message']                    = ''; 
            $data['Client']['client_id']        =''; 
            $data['Client']['client_name']      =''; 
            $data['Client']['contact_name']     =''; 
            $data['Client']['contact_phone']    =''; 
            $data['Client']['conf_id']          =''; 
            $data['Client']['status']           =''; 
            $data['Client']['client_street']    = '';
            $data['Client']['client_unit']      = '';
            $data['Client']['client_city']      = '';
            $data['Client']['client_state']     = '';
            $data['Client']['client_zipcode']   = '';
            $data['Client']['default_product']  = '';

            
     //pass the listData the parms we need to pull from the states table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Prods'] = listData($this->passdb,'products','prod_name', 'prod_name');
            
            $data['link_back'] = secure_anchor(
                    'clients/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Clients',
                    array('class'=>'back')); 
     // use the edit form       
            $this->template->load('client', 'clients/client_edit', $data);
 

         }else{ 
            // If validation success, save data 
            $Clients = array(
                'client_name' => $this->input->post('client_name'), 
                'contact_name' => $this->input->post('contact_name'), 
                'contact_phone' => $this->input->post('contact_phone'),
                'created' => $today,
                'status' => 'Active',
                'client_street'    => $this->input->post('client_street'), 
                'client_unit'      => $this->input->post('client_unit'), 
                'client_city'      => $this->input->post('client_city'),
                'client_state'     => $this->input->post('client_state'),
                'client_zipcode'   => $this->input->post('client_zipcode'),
                'default_product'  => $this->input->post('default_product')
                ); 
            $this->clients_model->save($Clients); 
            $id = $this->insert_id(); // get last id inserted to use for redirect
            
             redirect('clients/view/'.$id); 

            } 

            } 
            
            

            /**********************************
             *  View function
             */
    function view(){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Git the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
        
        
        
     // set common properties 
            $data['title'] = 'Client Details'; 
            
     // get Client details 
            $data['Client'] = $this->clients_model->get_by_id($this->auth_client_id)->row(); 
            $data['titlec'] = 'User List';
     // get so can pass product data to turn id into prod_name for view.       
                       $rec = $this->clients_model->get_by_id($this->auth_client_id)->row();
              $data['Prod'] = $this->products_model->default_processing($this->auth_client_id)->row(); 
              
              
              
     
           // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'user_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
//TODO: check for valid column 
// load data 
  $Users = $this->users_model->get_paged_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('clients/view'); 
                $config['total_rows'] = $this->users_model->count_all(); 
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
              //    '#', 
                    secure_anchor('users/index/'.$offset.'/user_id/'.$new_order, 'ID'),
                    secure_anchor('users/index/'.$offset.'/user_name/'.$new_order, 'Username'), 
                    secure_anchor('users/index/'.$offset.'/user_email/'.$new_order, 'User Email'), 
                    secure_anchor('users/index/'.$offset.'/user_level/'.$new_order, 'User Level'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Users as $User){ 
            $this->table->add_row(
            //  ++$i,
                $User->user_id,
                $User->user_name, 
                $User->user_email, 
                $User->user_level, 
                secure_anchor(
                        'users/view/'.$User->user_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'users/update/'.$User->user_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'users/delete/'.$User->user_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this User?')")) 
                ); 
                
            }
            
            $data['table'] = $this->table->generate();   
            
     // load view 
            $this->template->load('client', 'clients/client_view', $data);

            } 
            
            /******************************************************
             * Update
             */
    function update($id){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
     // set common properties 
            $data['title'] = 'Update Client'; 
            $this->load->library('form_validation'); 
     // set validation properties 
            $this->_set_rules(); 
            $data['action'] = ('clients/update/'.$id); 
     // run validation, if false, display the form. 
         if ($this->form_validation->run() === FALSE){ 
                $data['message'] = ''; 
                $data['Client']  = (array)$this->clients_model->get_by_id($id)->row(); 
                
                $this->load->helper(array('dropdown_helper','form')); 
                $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
                $data['Prods'] = listData($this->passdb,'products','prod_id', 'prod_name'); 

         }else{ 
            // save data from form
          //  $id = $this->input->post('client_id'); 
            $Client = array(
                'client_name'      => $this->input->post('client_name'), 
                'contact_name'     => $this->input->post('contact_name'), 
                'contact_phone'    => $this->input->post('contact_phone'), 
                'conf_id'          => $this->input->post('conf_id'),
                'created'          => date('Y-m-d H:i:s'),
                'status'           => $this->input->post('status'),
                'client_street'    => $this->input->post('client_street'), 
                'client_unit'      => $this->input->post('client_unit'), 
                'client_city'      => $this->input->post('client_city'),
                'client_state'     => $this->input->post('client_state'),
                'client_zipcode'   => $this->input->post('client_zipcode'),
                'default_product'  => $this->input->post('default_product')
  
                );
            

            $this->clients_model->update($id,$Client); 
            $data['Client'] = (array)$this->clients_model->get_by_id($id)->row();          
            
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['Prods'] = listData($this->passdb,'products','prod_id', 'prod_name'); 
            // set user message 
            $data['message'] = 'Update client success'; 

            } 
            $data['link_back'] = secure_anchor(
                    'clients/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Clients',
                    array('class'=>'back')); 
      // load view 

            $this->template->load('client', 'clients/client_edit', $data);
            } 
            
            
            /************************************************
             * Delete
             */
    function delete($id){ 
     // delete Student 
            $this->clients_model->delete($id); 
     // redirect to Client list page 
            redirect('Clients/index/delete_success','refresh'); 

            } 
     // validation rules 
    function _set_rules(){ 
                $this->form_validation->set_rules('client_name', 'Client Name', 'required|trim'); 
                $this->form_validation->set_rules('contact_name', 'Contact Name', 'required|trim'); 
                $this->form_validation->set_rules('contact_phone', 'Phone Number', 'required|trim'); 
 

            } 
            // date_validation callback 

    
  public function user_by_clients($offset = 0, $order_column = 'user_id', $client_id, $order_type = 'asc')
	{
            $this->is_logged_in();
            
           // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'id'; 
              if (empty($order_type)) $order_type = 'asc'; 

// load data 
  $Userclient = $this->users_model->get_client_list($this->limit, $offset, $client_id, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('clients/view/'); 
                $config['total_rows'] = $this->users_model->count_all(); 
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
              //    '#', 
                    secure_anchor('users/index/'.$offset.'/user_id/'.$new_order, 'ID'),
                    secure_anchor('users/index/'.$offset.'/user_name/'.$new_order, 'Username'), 
                    secure_anchor('users/index/'.$offset.'/user_email/'.$new_order, 'User Email'), 
                    secure_anchor('users/index/'.$offset.'/user_level/'.$new_order, 'User Level'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Userclient as $Userc){ 
            $this->table->add_row(
            //  ++$i,
                $Userc->user_id,
                $Userc->user_name, 
                $Userc->user_email, 
                $Userc->user_level, 
                secure_anchor(
                        'users/view/'.$Userc->user_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'users/update/'.$Userc->user_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'users/delete/'.$Userc->user_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this User?')")) 
                ); 
                
            }
            
            $data['table'] = $this->table->generate(); 
            if ($this->uri->segment(3)=='delete_success') {
                $data['message'] = 'The Data was successfully deleted'; 
            }else if ($this->uri->segment(3)=='add_success') {
                $data['message'] = 'The Data has been successfully added'; 
            }else{ 
                $data['message'] = ''; 
            }
            
// load view 
          //  $this->template->load('client', 'clients/client_view', $data);
            
              
	}
    
    
    
    
  
    
    } 


