<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Loans
 * 3/5/2016
 * @author Steve
 * Methods List
 * index, view, 
 */
class Loans extends MY_Controller {

    private $limit = 10;
    public $passdb;
    protected $db2use;



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
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url', 'directory');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE); 
         $this->load->model('applications_model', '', TRUE);
         $this->load->model('products_model', '', TRUE);
         $this->load->model('loans_model', '', TRUE);
        }
    
    
    public function index($offset = 0, $order_column = 'loa_id', $order_type = 'asc')
	{
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}         
          
//          if( $sql = $this->clients_model->view_record())
//           {
//            $data['cli_rec'] = $sql;
//           }    
          
                $data['title'] = 'EZ Pay Loan List';
                $data['tab'] = 'List All Loans';
                $data['user'] = $this->auth_user_id;
                $data['client'] = $this->auth_client_id; 
                
                /*****************************************************************************************
                 *    Pagination
                 */
                 if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'loa_id'; 
              if (empty($order_type)) $order_type = 'desc'; 
//TODO: check for valid column 
// load data 
   
  $LoanListings = $this->loans_model->get_paged_loan_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('loans/'); 
                $config['total_rows'] = $this->loans_model->count_all(); 
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
                    secure_anchor('loans/index/'.$offset.'/loa_id/'.$new_order, 'ID', array('class'=>'h_row', 'title' => 'Loan ID')),
                    secure_anchor('loans/index/'.$offset.'/loa_type/'.$new_order, 'Name', array('class'=>'h_row', 'title' => 'Bank Name')),
                    secure_anchor('loans/index/'.$offset.'/bank_account/'.$new_order, 'Account', array('class'=>'h_row', 'title' => 'Bank Account Number')),
                    '<i class="h_row">Action</i>' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($LoanListings as $BL){ 
            $this->table->add_row(
                       ++$i,
                    secure_anchor( 'banks/view/'.$BL->bank_id, 
                         '<i class="fa txt-color-darken">'.$BL->bank_id. '</i>', 
                            array('class'=>'view', 'title' => 'View Bank Account')), 
                    
                    secure_anchor( 'banks/view/'.$BL->bank_id, 
                         '<i class="fa txt-color-darken">'.$BL->bank_name. '</i>', 
                            array('class'=>'view', 'title' => 'View Bank Account')),  
                    
                    secure_anchor( 'banks/view/'.$BL->bank_id, 
                         '<i class="fa txt-color-darken">'.$BL->bank_account. '</i>', 
                            array('class'=>'view', 'title' => 'View Bank Account')),
                    
                secure_anchor( 'banks/view/'.$BL->bank_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                    
                secure_anchor('banks/update/'.$BL->bank_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update'))  
                    ); 
                
            }
            
            $data['table'] = $this->table->generate(); 
            
            /*******************************************************************************************
             * End Pagination
             */
                
           
           
            $this->template->load('client', 'banks/banks_list', $data);
            // If not admin, redirect
   
	}
    
        
        function add($id = '') {
         {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
//          if( $sql = $this->clients_model->view_record())
//           { $data['cli_rec'] = $sql; }
           
           if(!empty($this->input->get('id')) && $this->input->get('id') != '') {
               $id = $this->input->get('id');
           } elseif ($this->input->post('id')) {
               $id  = $this->input->post('id');
       }
           

            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'App ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'loa_status',
				'label' => 'Loan Status',
				'rules' => 'trim|max_length[15]'
			),
                        array(
				'field' => 'loa_requestdate',
				'label' => 'Loan Request Date',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'loa_activedate',
				'label' => 'Active Loan Date',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'loa_amount',
				'label' => 'Loan Amount',
				'rules' => 'trim|max_length[9]'
			),
                        array(
				'field' => 'loa_type',
				'label' => 'Loan Type',
				'rules' => 'trim|max_length[35]'
			),
                         array(
				'field' => 'loa_terms',
				'label' => 'Loan Terms',
				'rules' => 'trim|max_length[3]|numeric'
			),
                        array(
				'field' => 'risk_discount',
				'label' => 'Risk Discount',
				'rules' => 'trim|max_length[3]|numeric'
			),
                        array(
				'field' => 'down_payment',
				'label' => 'Down Payment',
				'rules' => 'trim|max_length[10]'
			),
                         array(
				'field' => 'approval_amount',
				'label' => 'Approval Amount',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'approval_term',
				'label' => 'Approval Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'bid_percent',
				'label' => 'Bid Percent',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'esign',
				'label' => 'Esign',
				'rules' => 'trim|max_length[20]'
			),
                        array(
				'field' => 'interest_rate',
				'label' => 'Interest Rate',
				'rules' => 'trim|max_length[8]'
			)
                       
                        
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form

            $data['action']     = site_url('loans/add/'.$id); 
             $data['title']     = 'New Loan'; 
                $data['id']     = $id;
            $data['link_app'] = secure_anchor(
                    'applications/view/'.$id,
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Application',
                    array('class'=>'back')); 
            
            $data['link_update'] = secure_anchor('loans/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Update Loan',array('class'=>'back'));
            
             $data['link_add'] = secure_anchor('loans/add/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Add A Loan',array('class'=>'back'));
             
             $data['link_view'] = secure_anchor('loans/view/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> View Loans',array('class'=>'back'));
            
            $data['link_dash'] = secure_anchor(
                    'dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dash',
                    array('class'=>'back'));
            //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value, third arg is what you want OrderBy to sort on. If blank, $value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name'); 
            $data['LoanTerm'] = listData($this->passdb,'loan_terms','term_id', 'term', '', 'term_id');
          $data['LoanStatus'] = listData($this->passdb,'loan_status','loan_status', 'loan_status');
            $this->template->load('client', 'loans/loan_add', $data);
    } else {
        // If form submitted and validated, 
            
                          $loan_data['app_id']  = $id;
                      $loan_data['loa_status']  = $this->input->post('loa_status');
                 $loan_data['loa_requestdate']  = date('Y-m-d');
                  $loan_data['loa_activedate']  = '0000-00-00';
                      $loan_data['loa_amount']  = $this->input->post('loa_amount');
                        $loan_data['loa_type']  = $this->input->post('loa_type');
                       $loan_data['loa_terms']  = $this->input->post('loa_terms');
                   $loan_data['risk_discount']  = $this->input->post('risk_discount');
                    $loan_data['down_payment']  = $this->input->post('down_payment');
                 $loan_data['approval_amount']  = $this->input->post('approval_amount');
                   $loan_data['approval_term']  = $this->input->post('approval_term');
                     $loan_data['bid_percent']  = $this->input->post('bid_percent');
                           $loan_data['esign']  = $this->input->post('esign');
                   $loan_data['interest_rate']  = $this->input->post('interest_rate');
         
           
                // Insert application into database
            $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($loan_data)
				->insert('loans');

                 // set common properties 

             $data['title']    = 'New Loan Saved'; 
            $data['action']    = site_url('loans/add/'.$id);
                $data['id']    = $id;
           $data['link_app'] = secure_anchor(
                    'applications/view/'.$id,
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Application',
                    array('class'=>'back')); 
            
            $data['link_update'] = secure_anchor('loans/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Update Loan',array('class'=>'back'));
            
             $data['link_add'] = secure_anchor('loans/add/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Add A Loan',array('class'=>'back'));
             
             $data['link_view'] = secure_anchor('loans/view/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> View Loans',array('class'=>'back'));
            
            $data['link_dash'] = secure_anchor(
                    'dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dash',
                    array('class'=>'back'));
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name');  
            $data['LoanTerm'] = listData($this->passdb,'loan_terms','term_id', 'term', '', 'term_id');
          $data['LoanStatus'] = listData($this->passdb,'loan_status','loan_status', 'loan_status');
            $this->template->load('client', 'loans/loan_add', $data);
                  
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

            // get user details 
            $Loan = (array)$this->loans_model->get_by_id($id)->row(); 
            
            if(empty($Loan)) {
                              $data['id']  = $id;
                          $data['app_id']  = '';
                      $data['loa_status']  = '';
                 $data['loa_requestdate']  = '';
                  $data['loa_activedate']  = '';
                      $data['loa_amount']  = '';
                        $data['loa_type']  = '';
                       $data['loa_terms']  = '';
                   $data['risk_discount']  = '';
                    $data['down_payment']  = '';
                 $data['approval_amount']  = '';
                   $data['approval_term']  = '';
                     $data['bid_percent']  = '';
                           $data['esign']  = '';
                   $data['interest_rate']  = '';
            }else {
                $data['Loan'] = (array)$this->loans_model->get_by_id($id)->row(); 
            }
         
            // set common properties 
            $data['title'] = 'Loan Details'; 
             $data['link_app'] = secure_anchor(
                    'applications/view/'.$Loan['app_id'],
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Application',
                    array('class'=>'back')); 
            
            $data['link_update'] = secure_anchor('loans/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Update Loan',array('class'=>'back'));
            
             $data['link_add'] = secure_anchor('loans/add/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Add A Loan',array('class'=>'back'));
            
            $data['link_dash'] = secure_anchor(
                    'dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dash',
                    array('class'=>'back'));
            
            // load view 
            $this->template->load('client', 'loans/loan_view', $data);
           

            } 
            
            
            
            
    function update($id = ''){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
                         
//          if( $sql = $this->clients_model->view_record())
//           { $data['cli_rec'] = $sql; } 
           
           if(!empty($this->input->get('id')) && $this->input->get('id') != '') {
               $id = $this->input->get('id');
           } elseif ($this->input->post('id')) {
               $id  = $this->input->post('id');
       }
       $appl = $this->loans_model->get_by_id($id)->row();
       $data['Appl'] = $appl;
                
               $this->load->library('form_validation');

        $validation_rules = array(
                      array(
				'field' => 'app_id',
				'label' => 'App ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'loa_status',
				'label' => 'Loan Status',
				'rules' => 'trim|max_length[15]'
			),
                        array(
				'field' => 'loa_requestdate',
				'label' => 'Loan Request Date',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'loa_activedate',
				'label' => 'Active Loan Date',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'loa_amount',
				'label' => 'Loan Amount',
				'rules' => 'trim|max_length[9]'
			),
                        array(
				'field' => 'loa_type',
				'label' => 'Loan Type',
				'rules' => 'trim|max_length[35]'
			),
                         array(
				'field' => 'loa_terms',
				'label' => 'Loan Terms',
				'rules' => 'trim|max_length[3]|numeric'
			),
                        array(
				'field' => 'risk_discount',
				'label' => 'Risk Discount',
				'rules' => 'trim|max_length[3]|numeric'
			),
                        array(
				'field' => 'down_payment',
				'label' => 'Down Payment',
				'rules' => 'trim|max_length[10]'
			),
                         array(
				'field' => 'approval_amount',
				'label' => 'Approval Amount',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'approval_term',
				'label' => 'Approval Term',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'bid_percent',
				'label' => 'Bid Percent',
				'rules' => 'trim|max_length[3]'
			),
                        array(
				'field' => 'esign',
				'label' => 'Esign',
				'rules' => 'trim|max_length[20]'
			),
                        array(
				'field' => 'interest_rate',
				'label' => 'Interest Rate',
				'rules' => 'trim|max_length[8]'
			)
                       
		);

		$this->form_validation->set_rules( $validation_rules );

           if ( $this->form_validation->run() == FALSE )
		{
 
                // set common properties 
                            $data['title'] = 'Update Loan'; 
                           $data['action'] = ('loans/update/'.$id);
                               $data['id'] = $id;
                          $data['app_id']  = $appl->app_id;
                      $data['loa_status']  = '';
                 $data['loa_requestdate']  = '';
                  $data['loa_activedate']  = '';
                      $data['loa_amount']  = '';
                        $data['loa_type']  = '';
                       $data['loa_terms']  = '';
                   $data['risk_discount']  = '';
                    $data['down_payment']  = '';
                 $data['approval_amount']  = '';
                   $data['approval_term']  = '';
                     $data['bid_percent']  = '';
                           $data['esign']  = '';
                   $data['interest_rate']  = '';
                   
                        $appl = $this->loans_model->get_by_id($id)->row();         
            $data['link_app'] = secure_anchor(
                    'applications/view/'.$appl->app_id,
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Application',
                    array('class'=>'back')); 
            
            $data['link_view'] = secure_anchor('loans/view/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> View Loan',array('class'=>'back'));
            
            $data['link_dash'] = secure_anchor(
                    'dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dash',
                    array('class'=>'back'));
                        
                $data['Appl'] = $appl;
                $data['Loan'] = $this->loans_model->get_by_id($id)->row(); 
              $this->load->helper(array('dropdown_helper','form')); 
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name');
            $data['LoanTerm'] = listData($this->passdb,'loan_terms','term_id', 'term', '', 'term_id');
          $data['LoanStatus'] = listData($this->passdb,'loan_status','loan_status', 'loan_status');
           $this->template->load('client', 'loans/loan_edit', $data);
           
  } else {
                     // set common properties 
                                 $id    = $this->input->post('id');
                      $data['title']    = 'Update Loan Information'; 
                     $data['action']    = ('loans/update/'.$id);
                        // array of items to pass to form validation

                          $loan_data['app_id']  = $this->input->post('app_id');
                      $loan_data['loa_status']  = $this->input->post('loa_status');
                 $loan_data['loa_requestdate']  = date('Y-m-d');
                  $loan_data['loa_activedate']  = '0000-00-00';
                      $loan_data['loa_amount']  = $this->input->post('loa_amount');
                        $loan_data['loa_type']  = $this->input->post('loa_type');
                       $loan_data['loa_terms']  = $this->input->post('loa_terms');
                   $loan_data['risk_discount']  = $this->input->post('risk_discount');
                    $loan_data['down_payment']  = $this->input->post('down_payment');
                 $loan_data['approval_amount']  = $this->input->post('approval_amount');
                   $loan_data['approval_term']  = $this->input->post('approval_term');
                     $loan_data['bid_percent']  = $this->input->post('bid_percent');
                           $loan_data['esign']  = $this->input->post('esign');
                   $loan_data['interest_rate']  = $this->input->post('interest_rate');
            
        // update and then get the record by id
            $this->loans_model->update($id,$loan_data); 
            $data['Loan'] = $this->loans_model->get_by_id($id)->row();
                    $appl = $this->loans_model->get_app_by_id($id)->row();
            $data['Appl'] = $appl;
            $this->load->helper(array('dropdown_helper','form')); 
            //pass the listData the parms we need to pull from the loan_type table - tablename,keyid,value 
            $data['LoanType'] = listData($this->passdb,'loan_type','loan_type_name', 'loan_type_name');
            $data['LoanTerm'] = listData($this->passdb,'loan_terms','term_id', 'term', '', 'term_id');
          $data['LoanStatus'] = listData($this->passdb,'loan_status','loan_status', 'loan_status');
                $data['id'] = $id;    
          $data['link_app'] = secure_anchor(
                    'applications/view/'.$appl->app_id,
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Application',
                    array('class'=>'back')); 
        $data['link_view'] = secure_anchor('loans/view/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> View Loan',array('class'=>'back'));
          $data['link_dash'] = secure_anchor(
                    'dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dash',
                    array('class'=>'back'));
      // load view 

            $this->template->load('client', 'loans/loan_edit', $data);
                    
            }   
                    
                
    }    
                

} // END CLASS
