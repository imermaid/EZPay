<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Banking
 *
 * @author Steve
 */

class Banks extends MY_Controller {
    
    protected $passdb;
     private $limit = 5;
     protected $db2use;

    public function __construct()
        {
         parent::__construct();
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          $this->passdb = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdowns from the correct databases
          $this->db2use = $this->load->database($this->auth_client_id, true);
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url', 'directory');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE); 
         $this->load->model('banks_model', '', TRUE);
        }
    
    
    public function index($offset = 0, $order_column = 'bank_id', $order_type = 'asc')
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}         
          
          if( $sql = $this->clients_model->view_record())
           {
            $data['cli_rec'] = $sql;
           }    
          
                $data['title'] = 'Bank List';
                $data['tab'] = 'List All Banks';
                $data['user'] = $this->auth_user_id;
                $data['client'] = $this->auth_client_id; 
                
                /*****************************************************************************************
                 *    Pagination
                 */
                 if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'bank_create'; 
              if (empty($order_type)) $order_type = 'desc'; 
//TODO: check for valid column 
// load data 
   
  $BankListings = $this->banks_model->get_paged_bank_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('banks/'); 
                $config['total_rows'] = $this->banks_model->count_all(); 
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
                     '<i class="h_row">#</i>',
                    secure_anchor('banks/index/'.$offset.'/bank_id/'.$new_order, 'ID', array('class'=>'h_row', 'title' => 'Bank ID')),
                    secure_anchor('banks/index/'.$offset.'/bank_name/'.$new_order, 'Name', array('class'=>'h_row', 'title' => 'Bank Name')),
                    secure_anchor('banks/index/'.$offset.'/bank_account/'.$new_order, 'Account', array('class'=>'h_row', 'title' => 'Bank Account Number')),
                    secure_anchor('banks/index/'.$offset.'/bank_status/'.$new_order, 'Account', array('class'=>'h_row', 'title' => 'Bank Status')),
                    '<i class="h_row">Action</i>' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($BankListings as $BL){ 
            $this->table->add_row(
                       ++$i,
                    $BL->bank_id, 
                    
                    $BL->bank_name,  
                    
                    $BL->bank_account,
                    $BL->bank_status,
                    
                secure_anchor( 'banks/view/'.$BL->bank_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '.
                    
                secure_anchor('banks/update/'.$BL->bank_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '.  
                    
                secure_anchor('banks/delete/'.$BL->bank_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this Bank?')")) 
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
            
            /*******************************************************************************************
             * End Pagination
             */
            $this->template->load('client', 'banks/bank_list', $data);
	}
    
        
        function add() {
         {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; }
           
//           if( ! empty($this->input->get('id')) ) {
//               $id = $this->input->get('id');
//           } elseif ($this->input->post('id')) {
//               $id  = $this->input->post('id');
//       }
//           

            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
//			array(
//				'field' => 'bank_id',
//				'label' => 'Bank ID',
//				'rules' => 'trim|max_length[11]'
//			),
                        array(
				'field' => 'bank_name',
				'label' => 'Bank Name',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_branch',
				'label' => 'Branch Name',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_account',
				'label' => 'Account Number',
				'rules' => 'trim|max_length[20]|numeric'
			),
                        array(
				'field' => 'bank_routing',
				'label' => 'Routing Number',
				'rules' => 'trim|max_length[9]|numeric'
			),
                        array(
				'field' => 'bank_address',
				'label' => 'Bank Street Address',
				'rules' => 'trim|max_length[65]'
			),
                         array(
				'field' => 'bank_city',
				'label' => 'City',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_state',
				'label' => 'State',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_zip',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[5]|numeric'
			),
                         array(
				'field' => 'bank_purpose',
				'label' => 'Account Purpose',
				'rules' => 'trim|max_length[25]'
			),
                        array(
				'field' => 'bank_trans_type',
				'label' => 'Account Trans Type',
				'rules' => 'trim|max_length[25]'
			),
                        array(
				'field' => 'bank_status',
				'label' => 'Status',
				'rules' => 'trim|max_length[12]'
			)
                       
                        
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form

            $data['action']     = site_url('banks/add/'); 
             $data['title']     = 'New Bank'; 
           //     $data['id']     = $id;
           
            //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Bank_Purpose'] = listData($this->passdb,'bank_purpose','bank_purpose', 'bank_purpose'); 
            $data['Bank_Trans_Type'] = listData($this->passdb,'bank_trans_type','bank_trans_type', 'bank_trans_type');
            $this->template->load('client', 'banks/bank_add', $data);
    } else {
        // If form submitted and validated, 
            
                        //  $bank_data['app_id']  = $id;
                       $bank_data['bank_name']  = ucwords($this->input->post('bank_name'));
                     $bank_data['bank_branch']  = ucwords($this->input->post('bank_branch'));
                    $bank_data['bank_account']  = $this->input->post('bank_account');
                    $bank_data['bank_routing']  = $this->input->post('bank_routing');
                    $bank_data['bank_address']  = ucwords($this->input->post('bank_address'));
                       $bank_data['bank_city']  = ucwords($this->input->post('bank_city'));
                      $bank_data['bank_state']  = $this->input->post('bank_state');
                        $bank_data['bank_zip']  = $this->input->post('bank_zip');
                    $bank_data['bank_purpose']  = $this->input->post('bank_purpose');
                 $bank_data['bank_trans_type']  = $this->input->post('bank_trans_type');
                     $bank_data['bank_status']  = $this->input->post('bank_status');
                     $bank_data['bank_create']  = date('Y-m-d H:i:s');
                         $bank_data['bank_by']  = $this->auth_user_id;
         
           
                // Insert application into database
            $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($bank_data)
				->insert('banks');

                 // set common properties 

             $data['title']    = 'New Bank Saved'; 
            $data['action']    = site_url('banks/add/');
               // $data['id']    = $id;
           
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['Bank_Purpose'] = listData($this->passdb,'bank_purpose','bank_purpose', 'bank_purpose'); 
            $data['Bank_Trans_Type'] = listData($this->passdb,'bank_trans_type','bank_trans_type', 'bank_trans_type');
            $this->template->load('client', 'banks/bank_add', $data);
                  
 }
    
    }
    // -----------------------------------------------------------------------
    
    
    
}



   /**********************************
             *  View function
             */
    function view($id){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
          

            // set common properties 
            $data['title'] = 'Banking Details'; 
             
            
            // get user details 
            $Bank = (array)$this->banks_model->get_by_id($id)->row(); 
            
            if(empty($Bank)) {
                               $data['id'] = $id;
                       $data['bank_name']  = '';
                     $data['bank_branch']  = '';
                    $data['bank_account']  = '';
                    $data['bank_routing']  = '';
                    $data['bank_address']  = '';
                       $data['bank_city']  = '';
                      $data['bank_state']  = '';
                        $data['bank_zip']  = '';
                    $data['bank_purpose']  = '';
                 $data['bank_trans_type']  = '';
                     $data['bank_status']  = '';
                     $data['bank_create']  = '';
                         $data['bank_by']  = '';
            }else {
                $data['Bank'] = (array)$this->banks_model->get_by_id($id)->row(); 
            }
         
            // load view 
            $this->template->load('client', 'banks/bank_view', $data);
           

            } 
            
            
            
            
    function update($id = ''){ 
        $this->is_logged_in();
        // if not loged in...
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
                         
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
           if( ! empty($this->input->get('id')) ) {
               $id = $this->input->get('id');
           } elseif ($this->input->post('id')) {
               $id  = $this->input->post('id');
       }
       // make sure the bank exists. If someone changes the GET bank_id to a non existant id, logout. Otherwise, it will pull their own bank up.
      $Bank = (array)$this->banks_model->get_by_id($id)->row(); 
            if(empty($Bank)) {
                redirect( secure_site_url('users/logout') );
            }
         //*******************************************************************************************       
               $this->load->library('form_validation');

        $validation_rules = array(
                        array(
				'field' => 'bank_id',
				'label' => 'Bank ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'bank_name',
				'label' => 'Bank Name',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_branch',
				'label' => 'Branch Name',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_account',
				'label' => 'Account Number',
				'rules' => 'trim|max_length[20]|numeric'
			),
                        array(
				'field' => 'bank_routing',
				'label' => 'Routing Number',
				'rules' => 'trim|max_length[9]|numeric'
			),
                        array(
				'field' => 'bank_address',
				'label' => 'Bank Street Address',
				'rules' => 'trim|max_length[65]'
			),
                         array(
				'field' => 'bank_city',
				'label' => 'City',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_state',
				'label' => 'State',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'bank_zip',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[5]|numeric'
			),
                         array(
				'field' => 'bank_purpose',
				'label' => 'Account Purpose',
				'rules' => 'trim|max_length[26]'
			),
                        array(
				'field' => 'bank_trans_type',
				'label' => 'Account Trans Type',
				'rules' => 'trim|max_length[26]'
			),
                        array(
				'field' => 'bank_status',
				'label' => 'Status',
				'rules' => 'trim|max_length[12]'
			)
                       
		);

		$this->form_validation->set_rules( $validation_rules );

           if ( $this->form_validation->run() == FALSE )
		{
 
                // set common properties 
                            $data['title'] = 'Update Bank Account'; 
                           $data['action'] = ('banks/update/'.$id);
                               $data['id'] = $id;
                       $data['bank_name']  = '';
                     $data['bank_branch']  = '';
                    $data['bank_account']  = '';
                    $data['bank_routing']  = '';
                    $data['bank_address']  = '';
                       $data['bank_city']  = '';
                      $data['bank_state']  = '';
                        $data['bank_zip']  = '';
                    $data['bank_purpose']  = '';
                 $data['bank_trans_type']  = '';
                     $data['bank_status']  = '';
                     $data['bank_create']  = '';
                         $data['bank_by']  = '';
                         
            

              $data['Bank'] = $this->banks_model->get_by_id($id)->row();
             // $data['Bank'] = (array)$this->banks_model->get_by_id($id); 
              $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['Bank_Purpose'] = listData($this->passdb,'bank_purpose','bank_purpose', 'bank_purpose'); 
            $data['Bank_Trans_Type'] = listData($this->passdb,'bank_trans_type','bank_trans_type', 'bank_trans_type');
           $this->template->load('client', 'banks/bank_edit', $data);
           
  } else {
                     // set common properties 
                              //   $id    = $this->input->post('id');
                                    
                      $data['title']    = 'Update Banking Information'; 
                     $data['action']    = ('banks/update/'.$id);
                        // array of items to pass to form validation

                       $bank_data['bank_name']  = ucwords($this->input->post('bank_name'));
                     $bank_data['bank_branch']  = ucwords($this->input->post('bank_branch'));
                    $bank_data['bank_account']  = $this->input->post('bank_account');
                    $bank_data['bank_routing']  = $this->input->post('bank_routing');
                    $bank_data['bank_address']  = ucwords($this->input->post('bank_address'));
                       $bank_data['bank_city']  = ucwords($this->input->post('bank_city'));
                      $bank_data['bank_state']  = $this->input->post('bank_state');
                        $bank_data['bank_zip']  = $this->input->post('bank_zip');
                    $bank_data['bank_purpose']  = $this->input->post('bank_purpose');
                 $bank_data['bank_trans_type']  = $this->input->post('bank_trans_type');
                     $bank_data['bank_status']  = $this->input->post('bank_status');
                     $bank_data['bank_create']  = date('Y-m-d H:i:s');
                         $bank_data['bank_by']  = $this->auth_user_id;
            
        // update and then get the record by id
            $this->banks_model->update($id,$bank_data); 
            $data['Bank'] = $this->banks_model->get_by_id($id)->row();

            $this->load->helper(array('dropdown_helper','form')); 
            //pass the listData the parms we need to pull from the states table - tablename,keyid,value 
            $data['States'] = listData($this->passdb,'states','state_abbrev','state_name'); 
            $data['Bank_Purpose'] = listData($this->passdb,'bank_purpose','bank_purpose', 'bank_purpose'); 
            $data['Bank_Trans_Type'] = listData($this->passdb,'bank_trans_type','bank_trans_type', 'bank_trans_type');
                $data['id'] = $id;    
         
      // load view 

            $this->template->load('client', 'banks/bank_edit', $data);
                    
            }   
                    
                
    }    
    
                /************************************************
             * Delete
             */
    function delete($id){ 
     // delete bank 
            $this->banks_model->delete($id); 
     // redirect to Client list page 
            redirect('banks/index/delete_success','refresh'); 

            } 
                

} // END CLASS
