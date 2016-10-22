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

class Products extends MY_Controller {
    
    protected $passdb;
     private $limit = 20;
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
         $this->load->model('products_model', '', TRUE);
        }
    
    
    public function index($offset = 0, $order_column = 'prod_id', $order_type = 'asc')
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}         
          
          if( $sql = $this->clients_model->view_record())
           {
            $data['cli_rec'] = $sql;
           }    
          
                $data['title'] = 'Products List';
                $data['tab'] = 'List Products';
                $data['user'] = $this->auth_user_id;
                $data['client'] = $this->auth_client_id; 
                
                /*****************************************************************************************
                 *    Pagination
                 */
                 if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'prod_created'; 
              if (empty($order_type)) $order_type = 'desc'; 
//TODO: check for valid column 
// load data 
   
  $ProdListings = $this->products_model->get_paged_product_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('products/'); 
                $config['total_rows'] = $this->products_model->count_all(); 
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
                    secure_anchor('products/index/'.$offset.'/MODEL_ID/'.$new_order, 'ID', array('class'=>'h_row', 'title' => 'Product ID')),
                    secure_anchor('products/index/'.$offset.'/MODEL_NAME/'.$new_order, 'Name', array('class'=>'h_row', 'title' => 'Product Name')),
                    '<i class="h_row">Action</i>' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($ProdListings as $PL){ 
            $this->table->add_row(
                       ++$i,
                    secure_anchor( 'products/view/'.$PL->prod_id, 
                         '<i class="fa txt-color-darken">'.$PL->prod_id. '</i>', 
                            array('class'=>'update', 'title' => 'Update')), 
                    
                    secure_anchor( 'products/view/'.$PL->prod_id, 
                         '<i class="fa txt-color-darken">'.$PL->model_name. '</i>', 
                            array('class'=>'update', 'title' => 'Update')),  
                    
                secure_anchor( 'products/view/'.$PL->prod_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')) 
                    
//                secure_anchor('products/update/'.$PL->MODEL_ID,
//                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
//                        array('class'=>'update', 'title' => 'Update'))  
                    ); 
                
            }
            
            $data['table'] = $this->table->generate(); 
            
            /*******************************************************************************************
             * End Pagination
             */
                
           
           
            $this->template->load('client', 'products/products_list', $data);
            // If not admin, redirect
   
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

            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'MODEL_ID',
				'label' => 'Product ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'MODEL_NAME',
				'label' => 'Product Name',
				'rules' => 'trim|max_length[35]'
			),
                        array(
				'field' => 'prod_loan_type',
				'label' => 'Loan Type',
				'rules' => 'trim|max_length[15]'
			),
                        array(
				'field' => 'prod_min_term',
				'label' => 'Minimum Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'prod_max_term',
				'label' => 'Maximum Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'prod_default_term',
				'label' => 'Default Term',
				'rules' => 'trim|max_length[3]'
			),
                         array(
				'field' => 'prod_min_rate',
				'label' => 'Minimum Rate',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_max_rate',
				'label' => 'Maximum Rate',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_default_rate',
				'label' => 'Default Rate',
				'rules' => 'trim|max_length[8]'
			),
                         array(
				'field' => 'prod_min_amount',
				'label' => 'Minimum Amount',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_max_amount',
				'label' => 'Maximum Amount',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_default_amount',
				'label' => 'Default Amount',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_default_freq',
				'label' => 'Default Frequency',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'prod_decision_model',
				'label' => 'Decision Model',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'prod_signature_type',
				'label' => 'Signature Type',
				'rules' => 'trim|max_length[20]'
			),
                        array(
				'field' => 'state',
				'label' => 'State',
				'rules' => 'trim|max_length[35]'
			),
                        array(
				'field' => 'prod_int_abatement_term',
				'label' => 'abatement Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'prod_status',
				'label' => 'Status',
				'rules' => 'trim|max_length[14]'
			)
                        
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form

            $data['action']    = site_url('products/add'); 
            $data['title']     = 'New Product'; 
            $data['link_back'] = secure_anchor(
                    'products/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Model List',
                    array('class'=>'back'));
            //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name');  
            $data['PayCycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');
            $this->template->load('client', 'products/product_add', $data);
    } else {
        // If form submitted and validated, 
            

            $prod_data['MODEL_NAME']               = ucfirst($this->input->post('MODEL_NAME'));
            $prod_data['prod_loan_type']          = $this->input->post('prod_loan_type');
            $prod_data['prod_min_term']           = $this->input->post('prod_min_term');
            $prod_data['prod_max_term']           = $this->input->post('prod_max_term');
            $prod_data['prod_default_term']       = $this->input->post('prod_default_term');
            $prod_data['prod_min_rate']           = $this->input->post('prod_min_rate');
            $prod_data['prod_max_rate']           = $this->input->post('prod_max_rate');
            $prod_data['prod_default_rate']       = $this->input->post('prod_default_rate');
            $prod_data['prod_min_amount']         = $this->input->post('prod_min_amount');
            $prod_data['prod_max_amount']         = $this->input->post('prod_max_amount');
            $prod_data['prod_default_amount']     = $this->input->post('prod_default_amount');
            $prod_data['prod_decision_model']     = $this->input->post('prod_decision_model');
            $prod_data['prod_signature_type']     = $this->input->post('prod_signature_type');
            $prod_data['prod_int_abatement_term'] = $this->input->post('prod_int_abatement_term');
            $prod_data['prod_state']              = $this->input->post('prod_state');
            $prod_data['prod_status']             = $this->input->post('prod_status');
            $prod_data['prod_create']             = date('Y-m-d H:i:s');
            $prod_data['prod_created_by']         = $this->auth_user_id;
            
           
                // Insert application into database
            $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($prod_data)
				->insert('products');

                 // set common properties 

             $data['title']    = 'New Product Saved'; 
            $data['action']    = site_url('products/add');
            $data['link_back'] = secure_anchor(
                    'products/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Model List',
                    array('class'=>'back')); 
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name'); 
            $data['PayCycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');
            $this->template->load('client', 'products/product_add', $data);
                  
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
            $data['title'] = 'Model Details'; 
            $data['link_back'] = secure_anchor(
                    'products/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Model List',
                    array('class'=>'back')); 
            // get user details 
            $data['Prod'] = $this->products_model->get_by_id($id)->row(); 
            
         
            // load view 
            $this->template->load('client', 'products/product_view', $data);
           

            } 
            
            
            
            
    function update($id){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
                         
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
                
               $this->load->library('form_validation');

        $validation_rules = array(
                        array(
				'field' => 'MODEL_ID',
				'label' => 'Product ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'MODEL_NAME',
				'label' => 'Product Name',
				'rules' => 'trim|max_length[35]'
			),
                        array(
				'field' => 'prod_loan_type',
				'label' => 'Loan Type',
				'rules' => 'trim|max_length[15]'
			),
                        array(
				'field' => 'prod_min_term',
				'label' => 'Minimum Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'prod_max_term',
				'label' => 'Maximum Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'prod_default_term',
				'label' => 'Default Term',
				'rules' => 'trim|max_length[3]'
			),
                         array(
				'field' => 'prod_min_rate',
				'label' => 'Minimum Rate',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_max_rate',
				'label' => 'Maximum Rate',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_default_rate',
				'label' => 'Default Rate',
				'rules' => 'trim|max_length[8]'
			),
                         array(
				'field' => 'prod_min_amount',
				'label' => 'Minimum Amount',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_max_amount',
				'label' => 'Maximum Amount',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_default_amount',
				'label' => 'Default Amount',
				'rules' => 'trim|max_length[8]'
			),
                        array(
				'field' => 'prod_default_freq',
				'label' => 'Default Frequency',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'prod_decision_model',
				'label' => 'Decision Model',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'prod_signature_type',
				'label' => 'Signature Type',
				'rules' => 'trim|max_length[20]'
			),
                        array(
				'field' => 'state',
				'label' => 'State',
				'rules' => 'trim|max_length[35]'
			),
                        array(
				'field' => 'prod_int_abatement_term',
				'label' => 'abatement Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'prod_status',
				'label' => 'Status',
				'rules' => 'trim|max_length[14]'
			)
		);

		$this->form_validation->set_rules( $validation_rules );

           if ( $this->form_validation->run() == FALSE )
		{
 
                // set common properties 
            $data['title'] = 'Update Model'; 
            $data['action']    = site_url('products/update/'.$id);
            $data['link_back'] = secure_anchor(
                    'products/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Products List',
                    array('class'=>'back')); 
              $data['Prod']  = $this->products_model->get_by_id($id)->row();
              $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
             $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name');
             $data['PayCycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');
           $this->template->load('client', 'products/product_edit', $data);
           
  } else {
                     // set common properties 
                      $data['title']    = 'Update Product Information'; 
                     $data['action']    = site_url('products/update/'.$id);
                   //  $data['action']    = $id;
                        // array of items to pass to form validation
          
          
                    

          
                         $prod_data['MODEL_NAME'] = ucfirst($this->input->post('MODEL_NAME'));
                     $prod_data['prod_loan_type'] = $this->input->post('prod_loan_type');
                      $prod_data['prod_min_term'] = $this->input->post('prod_min_term');
                      $prod_data['prod_max_term'] = $this->input->post('prod_max_term');
                  $prod_data['prod_default_term'] = $this->input->post('prod_default_term');
                      $prod_data['prod_min_rate'] = $this->input->post('prod_min_rate');
                      $prod_data['prod_max_rate'] = $this->input->post('prod_max_rate');
                  $prod_data['prod_default_rate'] = $this->input->post('prod_default_rate');
                    $prod_data['prod_min_amount'] = $this->input->post('prod_min_amount');
                    $prod_data['prod_max_amount'] = $this->input->post('prod_max_amount');
                $prod_data['prod_default_amount'] = $this->input->post('prod_default_amount');
                  $prod_data['prod_default_freq'] = $this->input->post('prod_default_freq');
                $prod_data['prod_decision_model'] = $this->input->post('prod_decision_model');
                $prod_data['prod_signature_type'] = $this->input->post('prod_signature_type');
            $prod_data['prod_int_abatement_term'] = $this->input->post('prod_int_abatement_term');
                         $prod_data['prod_state'] = $this->input->post('prod_state');
                        $prod_data['prod_status'] = $this->input->post('prod_status');
                        $prod_data['prod_create'] = date('Y-m-d H:i:s');
                    $prod_data['prod_created_by'] = $this->auth_user_id;
            
        // update and then get the record by id
            $this->products_model->update($id,$prod_data); 
            $data['Prod'] = $this->products_model->get_by_id($id)->row(); 
           
            // Get state/level dropdown list
            $this->load->helper(array('dropdown_helper','form')); 
            //pass the listData the parms we need to pull from the states table - tablename,keyid,value 
            $data['States'] = listData($this->passdb,'states','state_abbrev','state_name'); 
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name');
            $data['PayCycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');
                
            $data['link_back'] = secure_anchor(
                    'products/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Products List',
                    array('class'=>'back')); 
      // load view 

            $this->template->load('client', 'products/product_edit', $data);
                    
            }   
                    
                
    }    
                

} // END CLASS
