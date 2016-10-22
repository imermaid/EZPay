<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of Applications
 * 12/14/2015
 * @author Steve
 * Methods List
 * index, view, create_appl, create_coappl, create_ref, create_empl, update, ref_update, emp_update, co_update, dob_month, bday, completed_appls, incompleted_appls, applied_list
 */
class Applications extends MY_Controller {

    private $limit = 20;
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
         //  $this->db2use = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdowns from the correct databases
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url', 'directory', 'file');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE); 
         $this->load->model('applications_model', '', TRUE);
         //$this->load->model('products_model', '', TRUE);
         $this->load->model('loans_model', '', TRUE);
         $this->load->model('banks_model', '', TRUE);
        }
    
    
    public function index()
	{
            $this->is_logged_in();
            if( $this->require_min_level(1) )
    {
    // Appls level 1 and up see this ...
            redirect( secure_site_url('login') );
             
			} 
                        
	}
        

 // -----------------------------------------------------------------------
        
        public function view($id) {
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Git the individual client to list as the heading               
//          if( $sql = $this->clients_model->view_record())
//           { $data['cli_rec'] = $sql; } 
 
             // get user details 
            $data['Applicant'] = $this->applications_model->get_by_applicant($id)->row(); 
                 $Bank_Details = $this->banks_model->get_by_app_id($id)->row();
                 $Loan_Details = $this->loans_model->get_by_app_id($id)->row();
                 $data['Loan'] = $Loan_Details;
                 $data['Bank'] = $Bank_Details;
                 
     // set common properties 
            $data['title'] = 'Applicant Details'; 
            $data['action']    = site_url('applications/view/'.$id);
            $data['link_edit'] = secure_anchor(
                    'applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Update Application',
                    array('class'=>'back')); 
            if ( ! empty($Bank_Details) && $Bank_Details->bank_id > 0 ) {   // If cust has bank, show with (GET) bank_id. If not, add with (GET) app_id 
            $data['link_bank'] = secure_anchor('banks/view/'.$Bank_Details->bank_id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> View Bank',array('class'=>'back'));
            }else{
                $data['link_bank'] = secure_anchor('banks/add/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Add Banking',array('class'=>'back'));
            }
            if ( ! empty($Loan_Details) && $Loan_Details->loa_id > 0 ) {   // If cust has loan, show with (GET) loa_id. If not, add with (GET) app_id 
                $data['link_loan'] = secure_anchor('loans/view/'.$Loan_Details->loa_id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> View Loan',array('class'=>'back'));
            }else{
                $data['link_loan'] = secure_anchor('loans/add/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Add Loan',array('class'=>'back'));
            }
            
            $data['link_back'] = secure_anchor(
                    'dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dash',
                    array('class'=>'back'));
     
            
          if($this->input->post('denied') == 'denied') {
               $the_data = 'Denied';
               $the_id = $this->input->post('loa');
               $this->loans_model->update_status($the_id,$the_data);
           }
           if($this->input->post('pending') == 'pending') {
               $the_data = 'Pending';
               $the_id = $this->input->post('loa');
               $this->loans_model->update_status($the_id,$the_data);
           }
           if($this->input->post('approved') == 'approved') {
               $the_data = 'Approved';
               $the_id = $this->input->post('loa');
               $this->loans_model->update_status($the_id,$the_data);
           }
      
         
     // load view 
            $this->template->load('client', 'applications/applicants_view', $data);
           

            }
        


/**************************************************************************************************************
 * Function Create_appl
 */
        public function create_appl()
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
//          if( $sql = $this->clients_model->view_record())
//           { $data['cli_rec'] = $sql; } 
           
          /************************************************************************
           * get the client_config file by the client_id - right now it's who ever is logged in client_id
           * extract the field appl_path out. It holds the application name used for this client
           * build the path for the view to load
           */ 

        $client_app = 'applications/'.$this->passdb.'/default';
 
           /**************************************************************************************/
      
        // check to see if post NewApplID from the nav for the forms was used to get around. If so, load minimal and }else{ the validation and data input.
      if($this->input->post('NewApplID') === '' && $this->input->post('appl') === 'appl') {
                  $data['action']    = site_url('applications/create_appl'); 
                   $data['title']    = 'Start New Application'; 
               $data['NewApplID']    = '';
                $data['CoApplID']    = '';
               $data['EmpApplID']    = '';
               $data['RefApplID']    = '';
             

            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $this->template->load('client', $client_app, $data);
            
      } elseif($this->input->post('NewApplID') > 0 && $this->input->post('appl') == 'appl') {
           $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
                $data['NewApplID']  = $this->input->post('NewApplID');
              $data['Appl_Record']  = $this->applications_model->get_by_id($this->input->post('NewApplID'))->row(); // this sends appl data to show what was saved
                   $data['action']  = site_url('applications/create_appl'); 
                    $data['title']  = 'Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
                 $data['CoApplID']  = $this->input->post('CoApplID');
                $data['EmpApplID']  = $this->input->post('EmpApplID');
                $data['RefApplID']  = $this->input->post('RefApplID');
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
           //$id = $this->input->post('NewApplID');
            $this->template->load('client', $client_app, $data);
      } else {
         
            $this->load->library('form_validation');
         
            // create array to pass all the rules into the set_rules()
                $validation_rules = array(
                    array(
				'field' => 'app_primary_ssn',
				'label' => 'Primary Social Security Number/Tax ID',
				'rules' => 'trim|max_length[11]|alpha_dash'
			),
                    array(
				'field' => 'app_fname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_mname',
				'label' => 'Middle Initial',
				'rules' => 'trim|max_length[4]'
			),
                    array(
				'field' => 'app_lname',
				'label' => 'Last Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_suffix',
				'label' => 'Suffix',
				'rules' => 'trim|max_length[4]'
			),
                    array(
				'field' => 'app_phone',
				'label' => 'Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
                    array(
				'field' => 'app_altphone',
				'label' => 'Alternate Phone',
				'rules' => 'trim|max_length[14]'
			),
                    array(
				'field' => 'app_dob',
				'label' => 'Date of Birth',
				'rules' => 'max_length[10]'
			),
                    array(
				'field' => 'app_marital',
				'label' => 'Marital Status',
				'rules' => 'max_length[11]'
			),
                    array(
				'field' => 'app_gender',
				'label' => 'Gender',
				'rules' => 'max_length[10]'
			),
                    array(
				'field' => 'app_spouse_fname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_spouse_lname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_spouse_ssn',
				'label' => 'Spouse Social Security Number/Tax ID',
				'rules' => 'trim|max_length[11]|alpha_dash'
			),
                    array(
				'field' => 'app_spouse_dob',
				'label' => 'Date of Birth',
				'rules' => 'max_length[10]'
			),
                    array(
				'field' => 'app_email',
				'label' => 'Email Address',
				'rules' => 'trim|max_length[80]|valid_email'
			),
                    array(
				'field' => 'app_picture_id',
				'label' => 'Picture ID',
				'rules' => 'max_length[25]'
			),
                    array(
				'field' => 'app_id_type',
				'label' => 'Type of ID',
				'rules' => 'max_length[25]'
			),
                    array(
				'field' => 'app_state_issue',
				'label' => 'State of Issue',
				'rules' => 'max_length[65]'
			),
                    array(
				'field' => 'app_id_expire',
				'label' => 'Expiration Date',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'app_street',
				'label' => 'Street Address',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'app_unit',
				'label' => 'Unit',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'app_city',
				'label' => 'City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'app_state',
				'label' => 'State',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'app_zipcode',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[5]'
			),
                        array(
				'field' => 'app_mailing_address',
				'label' => 'Mailing Address',
				'rules' => 'trim|max_length[85]'
			),
                        array(
				'field' => 'app_mailing_city',
				'label' => 'Mailing City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'app_mailing_state',
				'label' => 'Mailing State',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'app_mailing_zipcode',
				'label' => 'Mailing Zipcode',
				'rules' => 'trim|max_length[5]'
			),
                        array(
				'field' => 'app_possession',
				'label' => 'Possession',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'app_payment',
				'label' => 'Monthly Payment',
				'rules' => 'trim|numeric|max_length[6]'
			),
                        array(
				'field' => 'app_since',
				'label' => 'Address Since',
				'rules' => 'trim|max_length[10]'
			)
		);
 
		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
          
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
                                 $data['action']    = site_url('applications/create_appl'); 
                                  $data['title']    = 'Start New Application'; 
                                $data['message']    = ''; 
                         $data['Appl']['app_id']    =''; 
                      $data['Appl']['client_id']    ='';
                $data['Appl']['app_internal_id']    ='';
                      $data['Appl']['app_fname']    =''; 
                      $data['Appl']['app_mname']    ='';
                      $data['Appl']['app_lname']    =''; 
                     $data['Appl']['app_suffix']    =''; 
                      $data['Appl']['app_phone']    =''; 
                   $data['Appl']['app_altphone']    =''; 
                        $data['Appl']['app_dob']    ='';
                $data['Appl']['app_primary_ssn']    ='';
                    $data['Appl']['app_marital']    ='';  
                     $data['Appl']['app_gender']    ='';
               $data['Appl']['app_spouse_fname']    ='';
               $data['Appl']['app_spouse_lname']    ='';
                 $data['Appl']['app_spouse_ssn']    ='';
                 $data['Appl']['app_spouse_dob']    ='';
                      $data['Appl']['app_email']    ='';
                 $data['Appl']['app_picture_id']    ='';
                    $data['Appl']['app_id_type']    ='';
                $data['Appl']['app_state_issue']    ='';
                  $data['Appl']['app_id_expire']    ='';
                       $data['Appl']['app_type']    = '';
                     $data['Appl']['app_street']    = '';
                       $data['Appl']['app_unit']    = '';
                       $data['Appl']['app_city']    = '';
                      $data['Appl']['app_state']    = '';
                    $data['Appl']['app_zipcode']    = '';
            $data['Appl']['app_mailing_address']    ='';
               $data['Appl']['app_mailing_city']    ='';
              $data['Appl']['app_mailing_state']    ='';
            $data['Appl']['app_mailing_zipcode']    ='';
                 $data['Appl']['app_possession']    = '';
                    $data['Appl']['app_payment']    = '';
                      $data['Appl']['app_since']    = '';
            
            //The only reason it makes it to here is because it's brand new, without a app_id.
                              $data['NewApplID']    = '';
                               $data['CoApplID']    = '';
                              $data['RefApplID']    = '';
                              $data['EmpApplID']    = '';

            
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 

            // load the app according to the client
            $this->template->load('client', $client_app, $data);
    } else {
        // If form submitted and validated, 
     

            // put $app_data in database 
            $app_data['client_id']           = $this->auth_client_id;
            $app_data['app_status']          = 'Incomplete';
            $app_data['app_internal_id']     = $this->input->post('app_internal_id');
            $app_data['app_fname']           = ucfirst($this->input->post('app_fname'));
            $app_data['app_mname']           = ucfirst($this->input->post('app_mname'));
            $app_data['app_lname']           = ucfirst($this->input->post('app_lname'));
            $app_data['app_suffix']          = ucfirst($this->input->post('suffix'));
            $app_data['app_phone']           = $this->input->post('app_phone');
            $app_data['app_altphone']        = $this->input->post('app_altphone');
            $app_data['app_dob']             = $this->input->post('app_dob');
            $app_data['app_primary_ssn']     = $this->input->post('app_primary_ssn');
            $app_data['app_marital']         = $this->input->post('app_marital');
            $app_data['app_gender']          = $this->input->post('app_gender');
            $app_data['app_spouse_fname']    = ucfirst($this->input->post('app_spouse_fname'));
            $app_data['app_spouse_lname']    = ucfirst($this->input->post('app_spouse_lname'));
            $app_data['app_spouse_ssn']      = $this->input->post('app_spouse_ssn');
            $app_data['app_spouse_dob']      = $this->input->post('app_spouse_dob');
            $app_data['app_email']           = $this->input->post('app_email');
            $app_data['app_picture_id']      = $this->input->post('app_picture_id');
            $app_data['app_id_type']         = ucfirst($this->input->post('app_id_type'));
            $app_data['app_state_issue']     = $this->input->post('app_state_issue');
            $app_data['app_id_expire']       = $this->input->post('app_id_expire');
            $app_data['app_type']            = $this->input->post('app_type');
            $app_data['app_street']          = ucfirst($this->input->post('app_street'));
            $app_data['app_unit']            = ucfirst($this->input->post('app_unit'));
            $app_data['app_city']            = ucfirst($this->input->post('app_city'));
            $app_data['app_state']           = $this->input->post('app_state');
            $app_data['app_zipcode']         = $this->input->post('app_zipcode');
            $app_data['app_mailing_address'] = ucfirst($this->input->post('app_mailing_address'));
            $app_data['app_mailing_city']    = ucfirst($this->input->post('app_mailing_city'));
            $app_data['app_mailing_state']   = $this->input->post('app_mailing_state');
            $app_data['app_mailing_zipcode'] = $this->input->post('app_mailing_zipcode');
            $app_data['app_possession']      = $this->input->post('app_possession');
            $app_data['app_payment']         = $this->input->post('app_payment');
            $app_data['app_since']           = $this->input->post('app_since');
            $app_data['app_created']         = date('Y-m-d H:i:s');
            $app_data['app_by']              = $this->auth_user_id;
            

                // Insert application into database
            $db1 = $this->load->database($this->auth_client_id, true);
           
			$db1->set($app_data)
				->insert('applicant');
                        $NewApplID = $db1->insert_id(); // get last id inserted to use for navigation
                        
               $rec = $this->clients_model->get_by_id($this->auth_client_id)->row();
              $prod = $this->products_model->get_by_id($rec->default_product)->row();         
            
              $loa_data['app_id']  = $NewApplID;
              $loa_data['loa_status'] = 'Applied';
              $loa_data['loa_requestdate'] = date('Y-m-d H:i:s');
              $loa_data['loa_amount'] = $prod->prod_default_amount;
              $loa_data['loa_type'] = $prod->prod_loan_type;
              $loa_data['loa_terms'] = $prod->prod_default_term;
              $loa_data['risk_discount'] = '0';
              $loa_data['down_payment'] = '0';
              $loa_data['approval_amount'] = $prod->prod_default_amount;
              $loa_data['approval_term'] = $prod->prod_default_term;
              $loa_data['bid_percent'] = '0';
              $loa_data['esign'] = $prod->prod_signature_type;
              
              
              $db1->set($loa_data)
				->insert('loans');
              
// set common properties 
                  $Appl_rec = (array)$this->applications_model->get_by_id($NewApplID)->row();
             $data['title'] = 'Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
            $data['action'] = site_url('applications/create_appl');
         $data['NewApplID'] = $NewApplID;
          $data['CoApplID'] = '';
         $data['EmpApplID'] = '';
         $data['RefApplID'] = '';

                 //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
           $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['Appl_Record']  = $this->applications_model->get_by_id($NewApplID)->row();   

             $this->template->load('client', $client_app, $data);
           //  redirect('applications/view/'.$id); 
 }
    }
    }
    
                // -----------------------------------------------------------------------
 
          
   public function create_coappl()
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
          /************************************************************************
           * get the client_config file by the client_id - right now it's who ever is logged in client_id
           * extract the field appl_path out. It holds the application name used for this client
           * build the path for the view to load
           */ 
        $client_app = 'applications/'.$this->passdb.'/coborrower'; //Template load page
           /**************************************************************************************/
           // check to see if post NewApplID from the nav for the forms was used to get around. If so, load minimal and }else{ the validation and data input.
    if($this->input->post('NewApplID') === '' && $this->input->post('coappl') === 'coappl') {
                       $data['action'] = site_url('applications/create_coappl'); 
                        $data['title'] = 'New Co-Application'; 
                    $data['NewApplID'] = '';
                     $data['CoApplID'] = '';
                    $data['EmpApplID'] = '';
                    $data['RefApplID'] = '';
                    $data['id']    = '';
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['Coapp_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');
            $this->template->load('client', $client_app, $data);
    } elseif($this->input->post('NewApplID') > 0 && $this->input->post('coappl') == 'coappl') {
                         $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
                  $data['action']  = site_url('applications/create_coappl'); 
                   $data['title']  = 'Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
               $data['NewApplID']  = $this->input->post('NewApplID');
           $data['Coappl_Record']  = $this->applications_model->get_by_coid($this->input->post('CoApplID'))->row();
                $data['CoApplID']  = ($this->input->post('CoApplID')>0)?$this->input->post('CoApplID'):'';
               $data['EmpApplID']  = $this->input->post('EmpApplID');
               $data['RefApplID']  = $this->input->post('RefApplID');
               $data['id']    = '';
            $this->load->helper(array('dropdown_helper','form')); 
                   $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
           $data['Coapp_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');
            $this->template->load('client', $client_app, $data);
    } else { 
            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'Primary Applicant ID',
				'rules' => 'trim|numeric|max_length[11]'
			),
                        array(
				'field' => 'coapp_relation',
				'label' => 'Relation to Applicant',
				'rules' => 'trim|max_length[35]'
			),
                        
                        array(
				'field' => 'coapp_fname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'coapp_mname',
				'label' => 'Middle Initial',
				'rules' => 'trim|max_length[4]'
			),
                        array(
				'field' => 'coapp_lname',
				'label' => 'Last Name',
				'rules' => 'trim|max_length[80]'
			),
			array(
				'field' => 'coapp_suffix',
				'label' => 'Suffix',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'coapp_phone',
				'label' => 'Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
                        array(
				'field' => 'coapp_altphone',
				'label' => 'Alternate Phone',
				'rules' => 'trim|max_length[14]'
			),
                        array(
				'field' => 'coapp_dob',
				'label' => 'Date of Birth',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'coapp_ssn',
				'label' => 'Co Social Security Number/Tax ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'coapp_marital',
				'label' => 'Marital Status',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'coapp_gender',
				'label' => 'Gender',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'coapp_email',
				'label' => 'Email Address',
				'rules' => 'trim|valid_email'
			),
                         array(
				'field' => 'coapp_street',
				'label' => 'Street Address',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'coapp_unit',
				'label' => 'Unit',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'coapp_city',
				'label' => 'City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'coapp_state',
				'label' => 'State',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'coapp_zipcode',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[11]'
			),
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
                            $data['action']   = site_url('applications/create_coappl'); 
                             $data['title']   = 'New Coborrower Application';  
                    $data['Appl']['app_id']   =''; 
                 $data['Appl']['client_id']   ='';
            $data['Appl']['coapp_relation']   ='';
               $data['Appl']['coapp_fname']   =''; 
               $data['Appl']['coapp_mname']   ='';
               $data['Appl']['coapp_lname']   =''; 
              $data['Appl']['coapp_suffix']   =''; 
               $data['Appl']['coapp_phone']   =''; 
            $data['Appl']['coapp_altphone']   ='';  
                 $data['Appl']['coapp_dob']   ='';
                 $data['Appl']['coapp_ssn']   ='';
             $data['Appl']['coapp_marital']   =''; 
              $data['Appl']['coapp_gender']   ='';
               $data['Appl']['coapp_email']   ='';
              $data['Appl']['coapp_street']   = '';
                $data['Appl']['coapp_unit']   = '';
                $data['Appl']['coapp_city']   = '';
               $data['Appl']['coapp_state']   = '';
             $data['Appl']['coapp_zipcode']   = '';

            
            
           $data['NewApplID'] = $this->input->post('NewApplID');
           $data['EmpApplID'] = $this->input->post('EmpApplID');
           $data['RefApplID'] = $this->input->post('RefApplID'); 

            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
                    $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Coapp_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');

              
            // load the app according to the client
            $this->template->load('client', $client_app, $data);
    } else {
        // If form submitted and validated, 
     // set common properties 
            
                    $coapp_data['app_id']   = $this->input->post('app_id');
            $coapp_data['coapp_relation']   = $this->input->post('coapp_relation');
               $coapp_data['coapp_fname']   = ucfirst($this->input->post('coapp_fname'));
               $coapp_data['coapp_mname']   = ucfirst($this->input->post('coapp_mname'));
               $coapp_data['coapp_lname']   = ucfirst($this->input->post('coapp_lname'));
              $coapp_data['coapp_suffix']   = ucfirst($this->input->post('coapp_suffix'));
               $coapp_data['coapp_phone']   = $this->input->post('coapp_phone');
            $coapp_data['coapp_altphone']   = $this->input->post('coapp_altphone');
                 $coapp_data['coapp_dob']   = $this->input->post('coapp_dob');
                 $coapp_data['coapp_ssn']   = $this->input->post('coapp_ssn');
             $coapp_data['coapp_marital']   = $this->input->post('coapp_marital');
              $coapp_data['coapp_gender']   = $this->input->post('coapp_gender');
               $coapp_data['coapp_email']   = $this->input->post('coapp_email');
              $coapp_data['coapp_street']   = ucfirst($this->input->post('coapp_street'));
                $coapp_data['coapp_unit']   = ucfirst($this->input->post('coapp_unit'));
                $coapp_data['coapp_city']   = ucfirst($this->input->post('coapp_city'));
               $coapp_data['coapp_state']   = ucfirst($this->input->post('coapp_state'));
             $coapp_data['coapp_zipcode']   = $this->input->post('coapp_zipcode');
             $coapp_data['coapp_created']   = date('Y-m-d H:i:s');
                  $coapp_data['coapp_by']   = $this->auth_user_id;
                // Insert application into database
                  $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($coapp_data)
				->insert('coapplicant');
                        $CoApplID = $db1->insert_id(); // get last id inserted to use for navigation/submit button

                 // set common properties 
                                  $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
                             $data['title'] = 'New Co-Application ' .$CoApplID. ' Saved for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
                            $data['action'] = site_url('applications/create_coappl');
                         $data['NewApplID'] = $this->input->post('NewApplID');
                          $data['CoApplID'] = $CoApplID;
                         $data['EmpApplID'] = $this->input->post('EmpApplID');
                         $data['RefApplID'] = $this->input->post('RefApplID');
                 //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
                    $this->load->helper(array('dropdown_helper','form')); 
                    $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
                    $data['Coapp_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');
                    $data['Coappl_Record']  = $this->applications_model->get_by_coid($CoApplID)->row();  
             $this->template->load('client', $client_app, $data);
                 
                 //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
         //   $this->load->helper(array('dropdown_helper','form')); 
          //  $data['States'] = listData('states','state_abbrev', 'state_name');
                 
           //  $this->template->load('client', $client_app, $data);
         //    redirect('applications/view/'.$id); 
 }
    }
    }
    // -----------------------------------------------------------------------
    
    // -----------------------------------------------------------------------
                    // -----------------------------------------------------------------------
 
          
   public function create_ref()
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
          /************************************************************************
           * get the client_config file by the client_id - right now it's who ever is logged in client_id
           * extract the field appl_path out. It holds the application name used for this client
           * build the path for the view to load
           */ 

        $client_app = 'applications/'.$this->passdb.'/reference'; //Template load page

           /**************************************************************************************/
           // check to see if post NewApplID from the nav for the forms was used to get around. If so, load minimal and }else{ the validation and data input.
  if($this->input->post('NewApplID') == '' && $this->input->post('ref') === 'ref') {
                     $data['action'] = site_url('applications/create_ref'); 
                      $data['title'] = 'New Reference'; 
                  $data['NewApplID'] = '';
                   $data['CoApplID'] = '';
                  $data['EmpApplID'] = '';
                  $data['RefApplID'] = '';
                  $data['id']    = '';
            //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['Ref_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation'); 
            $this->template->load('client', $client_app, $data);
  } elseif($this->input->post('NewApplID') > 0 && $this->input->post('ref') === 'ref') {
           $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
           $disp_name = $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
                  $data['action']  = site_url('applications/create_ref'); 
                   $data['title']  = 'Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
               $data['NewApplID']  = $this->input->post('NewApplID');
               $data['Ref_Record']  = $this->applications_model->get_by_refid($this->input->post('RefApplID'))->row();
                $data['CoApplID']  = $this->input->post('CoApplID');
               $data['EmpApplID']  = $this->input->post('EmpApplID');
               $data['RefApplID']  = ($this->input->post('RefApplID')>0) ? $this->input->post('RefApplID') : '';
               $data['id']    = '';
            $this->load->helper(array('dropdown_helper','form')); 
            $data['Ref_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');  
            $this->template->load('client', $client_app, $data);
   } else {
            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'Primary Applicant ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'ref1_fname',
				'label' => 'Reference One First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref1_lname',
				'label' => 'Reference One Last Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref1_phone',
				'label' => 'Reference One Phone',
				'rules' => 'trim|max_length[14]'
			),
                        array(
				'field' => 'ref1_relation',
				'label' => 'Reference One Relationship',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'ref2_fname',
				'label' => 'Reference Two First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref2_lname',
				'label' => 'Reference Two Last Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref2_phone',
				'label' => 'Reference Two Phone',
				'rules' => 'trim|max_length[14]'
			),
			array(
				'field' => 'ref2_relation',
				'label' => 'Reference Two Relationship',
				'rules' => 'max_length[65]'
			),
                       
                        array(
				'field' => 'ref3_fname',
				'label' => 'Reference Three First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref3_lname',
				'label' => 'Reference Three Last Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref3_phone',
				'label' => 'Reference Three Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
			array(
				'field' => 'ref3_relation',
				'label' => 'Reference Three Relationship',
				'rules' => 'max_length[65]'
			)
                        
                        
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
            $data['action']                         = site_url('applications/create_ref'); 
            $data['title']                          = 'New References'; 
            $data['message']                        = ''; 
            $data['Appl']['app_id']                 =''; 
            $data['Appl']['client_id']              ='';
            $data['Appl']['ref1_fname']             =''; 
            $data['Appl']['ref1_lname']             =''; 
            $data['Appl']['ref1_phone']             ='';  
            $data['Appl']['ref1_relation']          ='';
            
            $data['Appl']['ref2_fname']             =''; 
            $data['Appl']['ref2_lname']             =''; 
            $data['Appl']['ref2_phone']             ='';  
            $data['Appl']['ref2_relation']          ='';
            
            $data['Appl']['ref3_fname']             =''; 
            $data['Appl']['ref3_lname']             =''; 
            $data['Appl']['ref3_phone']             ='';  
            $data['Appl']['ref3_relation']          ='';
            
            $data['NewApplID'] = $this->input->post('NewApplID');
             $data['CoApplID'] = $this->input->post('CoApplID');
            $data['EmpApplID'] = $this->input->post('EmpApplID');
            
            //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['Ref_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation'); 
              
            // load the app according to the client
            $this->template->load('client', $client_app, $data);
    } else {
        // If form submitted and validated, 
            
            $ref_data['app_id']           = $this->input->post('app_id'); 
            $ref_data['ref1_fname']       = ucfirst($this->input->post('ref1_fname'));
            $ref_data['ref1_lname']       = ucfirst($this->input->post('ref1_lname'));
            $ref_data['ref1_phone']       = $this->input->post('ref1_phone');
            $ref_data['ref1_relation']    = $this->input->post('ref1_relation');
            
            $ref_data['ref2_fname']       = ucfirst($this->input->post('ref2_fname'));
            $ref_data['ref2_lname']       = ucfirst($this->input->post('ref2_lname'));
            $ref_data['ref2_phone']       = $this->input->post('ref2_phone');
            $ref_data['ref2_relation']    = $this->input->post('ref2_relation');
            
            $ref_data['ref3_fname']       = ucfirst($this->input->post('ref3_fname'));
            $ref_data['ref3_lname']       = ucfirst($this->input->post('ref3_lname'));
            $ref_data['ref3_phone']       = $this->input->post('ref3_phone');
            $ref_data['ref3_relation']    = $this->input->post('ref3_relation');
            
            $ref_data['ref_created']          = date('Y-m-d H:i:s');
            $ref_data['ref_created_by']       = $this->auth_user_id;
                // Insert application into database
            $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($ref_data)
				->insert('references');
                        $RefApplID = $db1->insert_id(); // get last id inserted to use for navigation/submit button
                        
                        // After references insert, below will set the status of the application to Complete or Incomplete
                  if($RefApplID > 0) {   //If ref(above) insert a success, check if emp has been completed for this application. If yes, change app_status in applicant to Complete 
                      if($this->input->post('EmpApplID') > 0) {
                         $this->db2use->where('app_id', $this->input->post('NewApplID'));
                         $this->db2use->set('app_status', 'Complete');
                         $this->db2use->update('applicant');
                         // if Complete, move loan status to Pending
                         $this->db2use->set('loa_status', 'Pending');
                         $this->db2use->where('app_id', $this->input->post('NewApplID'));
                         $this->db2use->update('loans');
                     }else {
                         $this->db2use->set('app_status', 'Incomplete');
                         $this->db2use->where('app_id', $this->input->post('NewApplID'));
                         $this->db2use->update('applicant');
                     }
                  } 
                        
                 // set common properties 
                  $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
             $data['title'] = 'New Reference Saved for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
            $data['action'] = site_url('applications/create_ref');
         $data['NewApplID'] = $this->input->post('NewApplID');
         $data['RefApplID'] = $RefApplID;
          $data['CoApplID'] = $this->input->post('CoApplID');
         $data['EmpApplID'] = $this->input->post('EmpApplID');
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['Ref_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation'); 
             $data['Ref_Record']  = $this->applications_model->get_by_refid($RefApplID)->row();   
             $this->template->load('client', $client_app, $data);
                  
 }
    }
    }
    // -----------------------------------------------------------------------
    
    // ----------------------------------------------------------------------- 
    
    
                    // -----------------------------------------------------------------------
 
          
   public function create_empl()
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
          /************************************************************************
           * get the client_config file by the client_id - right now it's who ever is logged in client_id
           * extract the field appl_path out. It holds the application name used for this client
           * build the path for the view to load
           */ 

        $client_app = 'applications/'.$this->passdb.'/employer'; //Template load page

           /**************************************************************************************/
           // check to see if post NewApplID from the nav for the forms was used to get around. If so, load minimal and }else{ the validation and data input.
   if($this->input->post('NewApplID') == '' && $this->input->post('empl') === 'empl') {
                      $data['action'] = site_url('applications/create_empl'); 
                       $data['title'] = 'Income Source'; 
                   $data['NewApplID'] = '';
                    $data['CoApplID'] = '';
                   $data['EmpApplID'] = '';
                   $data['RefApplID'] = '';
                          $data['id'] = '';
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['ET'] = listData($this->passdb,'employment_type','et_type', 'et_type');
            $data['Cycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');
            // load the app according to the client
            $this->template->load('client', $client_app, $data);
   } elseif($this->input->post('NewApplID') > 0 && $this->input->post('empl') == 'empl') {
           $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
                  $data['action']  = site_url('applications/create_empl'); 
                   $data['title']  = 'Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
               $data['NewApplID']  = $this->input->post('NewApplID');
                $data['CoApplID']  = $this->input->post('CoApplID');
               $data['EmpApplID']  = ($this->input->post('EmpApplID')>0)? $this->input->post('EmpApplID') : '';
               $data['RefApplID']  = $this->input->post('RefApplID');
                      $data['id']  = '';
              $data['Emp_Record']  = $this->applications_model->get_by_empid($this->input->post('EmpApplID'))->row();
              
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['ET'] = listData($this->passdb,'employment_type','et_type', 'et_type');
            $data['Cycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');
            $this->template->load('client', $client_app, $data);
   } else {
            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'Primary Applicant ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'emp_company',
				'label' => 'Company Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'emp_supervisor',
				'label' => 'Supervisor Name',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'emp_phone',
				'label' => 'Employer Phone',
				'rules' => 'max_length[14]'
			),
                        array(
				'field' => 'emp_position',
				'label' => 'Position',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'emp_hiredate',
				'label' => 'Date Hired',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'emp_type',
				'label' => 'Employment Type',
				'rules' => 'max_length[45]'
			),
                        array(
				'field' => 'emp_frequency',
				'label' => 'Pay Cycle',
				'rules' => 'trim|max_length[25]'
			),
                        array(
				'field' => 'emp_annual_salary',
				'label' => 'Annual Salary',
				'rules' => 'trim|max_length[7]|numeric'
			),
                        array(
				'field' => 'emp_other_source',
				'label' => 'Other Source of Income',
				'rules' => 'trim|max_length[25]'
			),
                        array(
				'field' => 'emp_other_amount',
				'label' => 'Amount of Other Income',
				'rules' => 'trim|max_length[7]|numeric'
			),
                        array(
				'field' => 'emp_other_frequency',
				'label' => 'Other Pay Cycle',
				'rules' => 'max_length[15]'
			),
                        array(
				'field' => 'emp_url',
				'label' => 'Employer Website',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'emp_address',
				'label' => 'Employer Street Address',
				'rules' => 'max_length[85]'
			),
                        array(
				'field' => 'emp_unit',
				'label' => 'Employer Address Unit',
				'rules' => 'max_length[85]'
			),
                        array(
				'field' => 'emp_city',
				'label' => 'Employer City',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'emp_state',
				'label' => 'Employer State',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'emp_zipcode',
				'label' => 'Employer Zipcode',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'emp_direct_deposit',
				'label' => 'Direct Deposit',
				'rules' => 'trim|in_list[Yes,No]'
			)       
		);
             // this will allow validation to work for next payday or semi monthly pay dates.           
           if($this->input->post('emp_frequency') !== 'Semi-Monthly') {
               $validation_rules2 = array(              
                        array(
				'field' => 'emp_next_payday',
				'label' => 'Next Payday',
				'rules' => 'trim|max_length[10]'
			));
              $this->form_validation->set_rules( $validation_rules2 ); // set rules to check for 
            } else {
                $validation_rules3 = array(
                        array(
				'field' => 'emp_semi_first',
				'label' => 'First Next Payday',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'emp_semi_second',
				'label' => 'Second Next Payday',
				'rules' => 'trim|max_length[10]'
			));
               $this->form_validation->set_rules( $validation_rules3 ); // set rules to check for 
           }
            
            
            $this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
            $data['action']                         = site_url('applications/create_empl'); 
            $data['title']                          = 'Income Source';  
            $data['Appl']['app_id']                 =''; 
            $data['Appl']['emp_company']            ='';
            $data['Appl']['emp_supervisor']         ='';
            $data['Appl']['emp_phone']              ='';
            $data['Appl']['emp_position']           ='';
            $data['Appl']['emp_hiredate']           ='';
            $data['Appl']['emp_type']               ='';
            $data['Appl']['emp_frequency']          ='';
            $data['Appl']['emp_next_payday']        ='';
            $data['Appl']['emp_semi_first']         ='';
            $data['Appl']['emp_semi_second']        ='';
            $data['Appl']['emp_annual_salary']      ='';
            $data['Appl']['emp_other_source']       ='';
            $data['Appl']['emp_other_amount']       ='';
            $data['Appl']['emp_other_frequency']    ='';
            $data['Appl']['emp_url']                ='';
            $data['Appl']['emp_address']            = '';
            $data['Appl']['emp_street']             = '';
            $data['Appl']['emp_unit']               = '';
            $data['Appl']['emp_city']               = '';
            $data['Appl']['emp_state']              = '';
            $data['Appl']['emp_zipcode']            = '';
            $data['Appl']['emp_direct_deposit']     ='';
            $data['Appl']['emp_created']            ='';
            $data['Appl']['emp_created_by']         ='';
            
            $data['NewApplID'] = $this->input->post('NewApplID');
             $data['CoApplID'] = $this->input->post('CoApplID');
            $data['RefApplID'] = $this->input->post('RefApplID');
            $data['EmpApplID'] = '';
            
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
                $data['ET'] = listData($this->passdb,'employment_type','et_type', 'et_type'); 
             $data['Cycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');  
            // load the app according to the client
            $this->template->load('client', $client_app, $data);
    } else {
        // If form submitted and validated, 
     // set common properties 
            $data['title'] = 'Income Source Added'; 
            $data['action'] = site_url('applications/create_empl');

            $app_data['app_id']              = $this->input->post('NewApplID'); 
            $app_data['emp_company']         = ucfirst($this->input->post('emp_company'));
            $app_data['emp_supervisor']      = ucfirst($this->input->post('emp_supervisor'));
            $app_data['emp_phone']           = $this->input->post('emp_phone');
            $app_data['emp_position']        = ucfirst($this->input->post('emp_position'));
            $app_data['emp_hiredate']        = $this->input->post('emp_hiredate');
            $app_data['emp_type']            = $this->input->post('emp_type');
            $app_data['emp_frequency']       = $this->input->post('emp_frequency');
            $app_data['emp_next_payday']     = $this->input->post('emp_next_payday');
            $app_data['emp_semi_first']      = $this->input->post('emp_semi_first');
            $app_data['emp_semi_second']     = $this->input->post('emp_semi_second');
            $app_data['emp_annual_salary']   = $this->input->post('emp_annual_salary');
            $app_data['emp_other_source']    = $this->input->post('emp_other_source');
            $app_data['emp_other_amount']    = $this->input->post('emp_other_amount');
            $app_data['emp_other_frequency'] = $this->input->post('emp_other_frequency');
            $app_data['emp_url']             = $this->input->post('emp_url');
            $app_data['emp_address']         = ucfirst($this->input->post('emp_address'));
            $app_data['emp_unit']            = ucfirst($this->input->post('emp_unit'));
            $app_data['emp_city']            = ucfirst($this->input->post('emp_city'));
            $app_data['emp_state']           = $this->input->post('emp_state');
            $app_data['emp_zipcode']         = $this->input->post('emp_zipcode');
            $app_data['emp_direct_deposit']  = $this->input->post('emp_direct_deposit');
            $app_data['emp_created']         = date('Y-m-d H:i:s');
            $app_data['emp_created_by']      = $this->auth_user_id;
                // Insert application into database
            $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($app_data)
				->insert('employers');
                     $EmpApplID = $db1->insert_id(); // get last id inserted to use for navigation/submit button  
                     
                     // After employer insert, below will set the status of the application to Complete or Incomplete
                  if($EmpApplID > 0) {   //If emp(above) insert a success, check if ref has been completed for this application. If yes, change app_status in applicant to Complete 
               //      $this->db2use->where('app_id', $this->input->post('NewApplID'));
               //      $this->db2use->from('references');
               //      $check_ref = $this->db2use->count_all_results();
                //     if($check_ref > 0) {
                      if($this->input->post('RefApplID') > 0 || $this->input->post('RefApplID') != '')
                         $this->db2use->where('app_id', $this->input->post('NewApplID')); 
                         $this->db2use->set('app_status', 'Complete');
                         $this->db2use->update('applicant');
                         // if Complete, move loan status to Pending
                         $this->db2use->set('loa_status', 'Pending');
                         $this->db2use->where('app_id', $this->input->post('NewApplID'));
                         $this->db2use->update('loans');
                     }else {
                         $this->db2use->set('app_status', 'Incomplete');
                         $this->db2use->where('app_id', $this->input->post('NewApplID'));
                         $this->db2use->update('applicant');
                 //    }
                  } 
            }
                 // set common properties 
                  $Appl_rec = (array)$this->applications_model->get_by_id($this->input->post('NewApplID'))->row();
             $data['title'] = 'Income Source Saved for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
            $data['action'] = site_url('applications/create_empl');
         $data['NewApplID'] = $this->input->post('NewApplID');
         $data['EmpApplID'] = ($EmpApplID > 0) ? $EmpApplID : 0;
          $data['CoApplID'] = $this->input->post('CoApplID');
         $data['RefApplID'] = $this->input->post('RefApplID');
                 //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
           $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
                $data['ET'] = listData($this->passdb,'employment_type','et_type', 'et_type'); 
             $data['Cycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle');   
       $data['Emp_Record']  = $this->applications_model->get_by_empid($EmpApplID)->row();
             $this->template->load('client', $client_app, $data);
                 
 }
    
}
    // -----------------------------------------------------------------------
    
    // -----------------------------------------------------------------------
    
    /**************************************************************************************************************
 * Function Edit
 */
public function update($id)
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
 
           // show form by client
        $client_forms = 'applications/'.$this->passdb.'/applicants_edit';

       
         
            $this->load->library('form_validation');
         
            // create array to pass all the rules into the set_rules()
                $validation_rules = array(
                    array(
				'field' => 'app_primary_ssn',
				'label' => 'Primary Social Security Number/Tax ID',
				'rules' => 'trim|max_length[11]|alpha_dash'
			),
                    array(
				'field' => 'app_status',
				'label' => 'Status',
				'rules' => 'trim|max_length[12]'
			),
                    array(
				'field' => 'app_fname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_mname',
				'label' => 'Middle Initial',
				'rules' => 'trim|max_length[4]'
			),
                    array(
				'field' => 'app_lname',
				'label' => 'Last Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_suffix',
				'label' => 'Suffix',
				'rules' => 'trim|max_length[4]'
			),
                    array(
				'field' => 'app_phone',
				'label' => 'Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
                    array(
				'field' => 'app_altphone',
				'label' => 'Alternate Phone',
				'rules' => 'trim|max_length[14]'
			),
                    array(
				'field' => 'app_dob',
				'label' => 'Date of Birth',
				'rules' => 'max_length[10]'
			),
                    array(
				'field' => 'app_marital',
				'label' => 'Marital Status',
				'rules' => 'max_length[11]'
			),
                    array(
				'field' => 'app_gender',
				'label' => 'Gender',
				'rules' => 'max_length[10]'
			),
                    array(
				'field' => 'app_spouse_fname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_spouse_lname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[45]'
			),
                    array(
				'field' => 'app_spouse_ssn',
				'label' => 'Spouse Social Security Number/Tax ID',
				'rules' => 'trim|max_length[11]|alpha_dash'
			),
                    array(
				'field' => 'app_spouse_dob',
				'label' => 'Date of Birth',
				'rules' => 'trim|max_length[10]'
			),
                    array(
				'field' => 'app_email',
				'label' => 'Email Address',
				'rules' => 'trim|valid_email'
			),
                    array(
				'field' => 'app_picture_id',
				'label' => 'Picture ID',
				'rules' => 'max_length[25]'
			),
                    array(
				'field' => 'app_id_type',
				'label' => 'Type of ID',
				'rules' => 'max_length[25]'
			),
                    array(
				'field' => 'app_state_issue',
				'label' => 'State of Issue',
				'rules' => 'max_length[55]'
			),
                    array(
				'field' => 'app_id_expire',
				'label' => 'Expiration Date',
				'rules' => 'max_length[10]'
			),
                    array(
				'field' => 'app_type',
				'label' => 'Address Type',
				'rules' => 'trim'
			),
                        array(
				'field' => 'app_street',
				'label' => 'Street Address',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'app_unit',
				'label' => 'Unit',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'app_city',
				'label' => 'City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'app_state',
				'label' => 'State',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'app_zipcode',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'app_mailing_address',
				'label' => 'Mailing Address',
				'rules' => 'trim|max_length[85]'
			),
                        array(
				'field' => 'app_mailing_city',
				'label' => 'Mailing City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'app_mailing_state',
				'label' => 'Mailing State',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'app_mailing_zipcode',
				'label' => 'Mailing Zipcode',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'app_possession',
				'label' => 'Possession',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'app_payment',
				'label' => 'Address Payment',
				'rules' => 'trim|numeric'
			),
                        array(
				'field' => 'app_since',
				'label' => 'Address Since',
				'rules' => 'trim|max_length[10]'
			),
                        
		);
 
		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
          
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
               $data['action'] = site_url('applications/update/'.$id); 
                     $Appl_rec = (array)$this->applications_model->get_by_id($id)->row();
                $data['title'] = 'Update Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
                   $data['id'] = $id;
            $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Applicant'] = $this->applications_model->get_by_applicant($id)->row();
              
            // load the app according to the client
            $this->template->load('client', $client_forms, $data);
    } else {
        // If form submitted and validated, 
     

            // put $app_data in database 
           
            $app_data['app_status']          = $this->input->post('app_status');
            $app_data['app_internal_id']     = $this->input->post('app_internal_id');
            $app_data['app_fname']           = ucfirst($this->input->post('app_fname'));
            $app_data['app_mname']           = ucfirst($this->input->post('app_mname'));
            $app_data['app_lname']           = ucfirst($this->input->post('app_lname'));
            $app_data['app_suffix']          = ucfirst($this->input->post('suffix'));
            $app_data['app_phone']           = $this->input->post('app_phone');
            $app_data['app_altphone']        = $this->input->post('app_altphone');
            $app_data['app_dob']             = $this->input->post('app_dob');
            $app_data['app_primary_ssn']     = $this->input->post('app_primary_ssn');
            $app_data['app_marital']         = $this->input->post('app_marital');
            $app_data['app_gender']          = $this->input->post('app_gender');
            $app_data['app_spouse_fname']    = ucfirst($this->input->post('app_spouse_fname'));
            $app_data['app_spouse_lname']    = ucfirst($this->input->post('app_spouse_lname'));
            $app_data['app_spouse_ssn']      = $this->input->post('app_spouse_ssn');
            $app_data['app_spouse_dob']      = $this->input->post('app_spouse_dob');
            $app_data['app_email']           = $this->input->post('app_email');
            $app_data['app_picture_id']      = $this->input->post('app_picture_id');
            $app_data['app_id_type']         = ucfirst($this->input->post('app_id_type'));
            $app_data['app_state_issue']     = $this->input->post('app_state_issue');
            $app_data['app_id_expire']       = $this->input->post('app_id_expire');
            $app_data['app_type']            = $this->input->post('app_type');
            $app_data['app_street']          = ucfirst($this->input->post('app_street'));
            $app_data['app_unit']            = ucfirst($this->input->post('app_unit'));
            $app_data['app_city']            = ucfirst($this->input->post('app_city'));
            $app_data['app_state']           = $this->input->post('app_state');
            $app_data['app_zipcode']         = $this->input->post('app_zipcode');
            $app_data['app_mailing_address'] = ucfirst($this->input->post('app_mailing_address'));
            $app_data['app_mailing_city']    = ucfirst($this->input->post('app_mailing_city'));
            $app_data['app_mailing_state']   = $this->input->post('app_mailing_state');
            $app_data['app_mailing_zipcode'] = $this->input->post('app_mailing_zipcode');
            $app_data['app_possession']      = $this->input->post('app_possession');
            $app_data['app_payment']         = $this->input->post('app_payment');
            $app_data['app_since']           = $this->input->post('app_since');
            $app_data['app_modified']         = date('Y-m-d H:i:s');
            $app_data['app_mod_by']              = $this->auth_user_id;
            
         
                // Insert application into database
          
            $this->applications_model->update($id,$app_data);
           
				
                        

              
// set common properties 
                  $Appl_rec = (array)$this->applications_model->get_by_id($id)->row();
             $data['title'] = 'Application for ' . $Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
            $data['action'] = site_url('applications/update/'.$id);
            $data['id']     = $id;
          $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
                 //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
           $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
            $data['Applicant'] = $this->applications_model->get_by_applicant($id)->row();
    
             $this->template->load('client', $client_forms, $data);
           //  redirect('applications/view/'.$id); 
 }
    
    }
    /***************************************************************************************************************************
     * Function Reference Update
     ****************************/
public function ref_update($id)
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
         
        $client_forms = 'applications/'.$this->passdb.'/references_edit';
         
            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'Primary Applicant ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'ref1_fname',
				'label' => 'Reference One First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref1_lname',
				'label' => 'Reference One Last Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref1_phone',
				'label' => 'Reference One Phone',
				'rules' => 'trim|max_length[14]'
			),
                        array(
				'field' => 'ref1_relation',
				'label' => 'Reference One Relationship',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'ref2_fname',
				'label' => 'Reference Two First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref2_lname',
				'label' => 'Reference Two Last Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref2_phone',
				'label' => 'Reference Two Phone',
				'rules' => 'trim|max_length[14]'
			),
			array(
				'field' => 'ref2_relation',
				'label' => 'Reference Two Relationship',
				'rules' => 'max_length[65]'
			),
                       
                        array(
				'field' => 'ref3_fname',
				'label' => 'Reference Three First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref3_lname',
				'label' => 'Reference Three Last Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'ref3_phone',
				'label' => 'Reference Three Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
			array(
				'field' => 'ref3_relation',
				'label' => 'Reference Three Relationship',
				'rules' => 'max_length[65]'
			)
                        
                        
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
               $data['action'] = site_url('applications/ref_update/'.$id); 
                   $data['id'] = $id;
                     $Appl_rec = (array)$this->applications_model->get_by_id($id)->row();
                $data['title'] = 'Edit Reference for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
            $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['Ref_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');
            $data['References'] = $this->applications_model->get_ref_by_app_id($id)->row();
               
            // load the app according to the client
            $this->template->load('client', $client_forms, $data);
    } else {
        // If form submitted and validated, 
            
            $ref_data['app_id']           = $this->input->post('app_id'); 
            $ref_data['ref1_fname']       = ucfirst($this->input->post('ref1_fname'));
            $ref_data['ref1_lname']       = ucfirst($this->input->post('ref1_lname'));
            $ref_data['ref1_phone']       = $this->input->post('ref1_phone');
            $ref_data['ref1_relation']    = $this->input->post('ref1_relation');
            
            $ref_data['ref2_fname']       = ucfirst($this->input->post('ref2_fname'));
            $ref_data['ref2_lname']       = ucfirst($this->input->post('ref2_lname'));
            $ref_data['ref2_phone']       = $this->input->post('ref2_phone');
            $ref_data['ref2_relation']    = $this->input->post('ref2_relation');
            
            $ref_data['ref3_fname']       = ucfirst($this->input->post('ref3_fname'));
            $ref_data['ref3_lname']       = ucfirst($this->input->post('ref3_lname'));
            $ref_data['ref3_phone']       = $this->input->post('ref3_phone');
            $ref_data['ref3_relation']    = $this->input->post('ref3_relation');
            // If no references, insert, but if yes, then update.
            if($this->input->post('ref_form') === 'NEW') {
                $ref_data['ref_created']     = date('Y-m-d H:i:s');
                $ref_data['ref_created_by']       = $this->auth_user_id;
                      // Insert references
                $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($ref_data)
				->insert('references'); 
            } else {
                $ref_data['ref_modified']     = date('Y-m-d H:i:s');
                $ref_data['ref_mod_by']       = $this->auth_user_id;
                
                       // Update references 
                $this->applications_model->ref_update($id,$ref_data);   
            }
                // whether update or new, change application status if already has emp filled out.
                       $this->db2use->where('app_id', $id);
                     $this->db2use->from('employers');
                     $check_emp = $this->db2use->count_all_results();
                     if($check_emp > 0) {
                        $this->db2use->set('app_status', 'Complete');
                         $this->db2use->where('app_id', $id);
                         $this->db2use->update('applicant');
                     }else {
                         $this->db2use->set('app_status', 'Incomplete');
                         $this->db2use->where('app_id', $id);
                         $this->db2use->update('applicant');
                     }
                  
                 // set common properties 
                  $Appl_rec    = (array)$this->applications_model->get_by_id($id)->row();
             $data['title']    = 'New Reference Saved for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
             $data['id']       = $id;
            $data['action']    = site_url('applications/references_edit/'.$id);
            $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['Ref_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation'); 
            $data['References'] = $this->applications_model->get_ref_by_app_id($id)->row();
              
             $this->template->load('client', $client_forms, $data);
                  
 }
    }
   
 /***************************************************************************************************************************
     * Function Employee Update
     ****************************/
public function emp_update($id)
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
         
      //  $appl_used = (array)$this->applications_model->get_by_client_id($this->auth_client_id)->row();
       
        $client_forms = 'applications/'.$this->passdb.'/employers_edit';
         
            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'Primary Applicant ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'emp_company',
				'label' => 'Company Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'emp_supervisor',
				'label' => 'Supervisor Name',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'emp_phone',
				'label' => 'Employer Phone',
				'rules' => 'max_length[14]'
			),
                        array(
				'field' => 'emp_position',
				'label' => 'Position',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'emp_hiredate',
				'label' => 'Date Hired',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'emp_type',
				'label' => 'Employment Type',
				'rules' => 'max_length[45]'
			),
                        array(
				'field' => 'emp_frequency',
				'label' => 'Pay Cycle',
				'rules' => 'trim|max_length[25]'
			),
                        array(
				'field' => 'emp_annual_salary',
				'label' => 'Annual Salary',
				'rules' => 'trim|max_length[7]|numeric'
			),
                        array(
				'field' => 'emp_other_source',
				'label' => 'Other Source of Income',
				'rules' => 'trim|max_length[25]'
			),
                        array(
				'field' => 'emp_other_amount',
				'label' => 'Amount of Other Income',
				'rules' => 'trim|max_length[7]|numeric'
			),
                        array(
				'field' => 'emp_other_frequency',
				'label' => 'Other Pay Cycle',
				'rules' => 'max_length[25]'
			),
                        array(
				'field' => 'emp_url',
				'label' => 'Employer Website',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'emp_address',
				'label' => 'Employer Street Address',
				'rules' => 'max_length[85]'
			),
                        array(
				'field' => 'emp_unit',
				'label' => 'Employer Address Unit',
				'rules' => 'max_length[85]'
			),
                        array(
				'field' => 'emp_city',
				'label' => 'Employer City',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'emp_state',
				'label' => 'Employer State',
				'rules' => 'max_length[65]'
			),
                        array(
				'field' => 'emp_zipcode',
				'label' => 'Employer Zipcode',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'emp_direct_deposit',
				'label' => 'Direct Deposit',
				'rules' => 'trim|in_list[Yes,No]'
			)       
		);
             // this will allow validation to work for next payday or semi monthly pay dates.           
           if($this->input->post('emp_frequency') !== 'Semi-Monthly') {
               $validation_rules2 = array(              
                        array(
				'field' => 'emp_next_payday',
				'label' => 'Next Payday',
				'rules' => 'trim|max_length[10]'
			));
              $this->form_validation->set_rules( $validation_rules2 ); // set rules to check for 
            } else {
                $validation_rules3 = array(
                        array(
				'field' => 'emp_semi_first',
				'label' => 'First Next Payday',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'emp_semi_second',
				'label' => 'Second Next Payday',
				'rules' => 'trim|max_length[10]'
			));
               $this->form_validation->set_rules( $validation_rules3 ); // set rules to check for 
           }
            
            
            $this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
               $data['action'] = site_url('applications/emp_update/'.$id); 
                   $data['id'] = $id;
                     $Appl_rec = (array)$this->applications_model->get_by_id($id)->row();
                $data['title'] = 'Edit Employer for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
            $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
             $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
                 $data['ET'] = listData($this->passdb,'employment_type','et_type', 'et_type'); 
              $data['Cycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle'); 
          $data['Employers'] = $this->applications_model->get_emp_by_app_id($id)->row();
            $this->template->load('client', $client_forms, $data);
    } else {
        // If form submitted and validated, 
            
             $emp_data['app_id']              = $this->input->post('app_id'); 
            $emp_data['emp_company']         = ucfirst($this->input->post('emp_company'));
            $emp_data['emp_supervisor']      = ucfirst($this->input->post('emp_supervisor'));
            $emp_data['emp_phone']           = $this->input->post('emp_phone');
            $emp_data['emp_position']        = ucfirst($this->input->post('emp_position'));
            $emp_data['emp_hiredate']        = $this->input->post('emp_hiredate');
            $emp_data['emp_type']            = $this->input->post('emp_type');
            $emp_data['emp_frequency']       = $this->input->post('emp_frequency');
            $emp_data['emp_next_payday']     = $this->input->post('emp_next_payday');
            $emp_data['emp_semi_first']      = $this->input->post('emp_semi_first');
            $emp_data['emp_semi_second']     = $this->input->post('emp_semi_second');
            $emp_data['emp_annual_salary']   = $this->input->post('emp_annual_salary');
            $emp_data['emp_other_source']    = $this->input->post('emp_other_source');
            $emp_data['emp_other_amount']    = $this->input->post('emp_other_amount');
            $emp_data['emp_other_frequency'] = $this->input->post('emp_other_frequency');
            $emp_data['emp_url']             = $this->input->post('emp_url');
            $emp_data['emp_address']         = ucfirst($this->input->post('emp_address'));
            $emp_data['emp_unit']            = ucfirst($this->input->post('emp_unit'));
            $emp_data['emp_city']            = ucfirst($this->input->post('emp_city'));
            $emp_data['emp_state']           = $this->input->post('emp_state');
            $emp_data['emp_zipcode']         = $this->input->post('emp_zipcode');
            $emp_data['emp_direct_deposit']  = $this->input->post('emp_direct_deposit');
                
            // If no references, insert, but if yes, then update.
            if($this->input->post('emp_form') === 'NEW') {
                $emp_data['emp_created']     = date('Y-m-d H:i:s');
                $emp_data['emp_created_by']       = $this->auth_user_id;
                      // Insert references
                $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($emp_data)
				->insert('employers'); 
                        $EmpApplID = $db1->insert_id(); // get last id inserted to use for navigation/submit button
            } else {
                $emp_data['emp_modified']     = date('Y-m-d H:i:s');
                $emp_data['emp_mod_by']       = $this->auth_user_id;
                
                       // Update employers 
                $this->applications_model->emp_update($id,$emp_data); 
            }
            // whether update or new, change application status if already has ref filled out.
                       $this->db2use->where('app_id', $id);
                     $this->db2use->from('references');
                     $check_ref = $this->db2use->count_all_results();
                     if($check_ref > 0) {
                        $this->db2use->set('app_status', 'Complete');
                         $this->db2use->where('app_id', $id);
                         $this->db2use->update('applicant');
                     }else {
                         $this->db2use->set('app_status', 'Incomplete');
                         $this->db2use->where('app_id', $id);
                         $this->db2use->update('applicant');
                     }
                  
                 // set common properties 
                     $Appl_rec = (array)$this->applications_model->get_by_id($id)->row();
                $data['title'] = 'New Employer Saved for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
                   $data['id'] = $id;
               $data['action'] = site_url('applications/employers_edit/'.$id);
           $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
               $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
                   $data['ET'] = listData($this->passdb,'employment_type','et_type', 'et_type'); 
                $data['Cycle'] = listData($this->passdb,'pay_cycle','pay_cycle', 'pay_cycle'); 
            $data['Employers'] = $this->applications_model->get_emp_by_app_id($id)->row(); 
             $this->template->load('client', $client_forms, $data);
                  
 }
    }
    
 /***************************************************************************************************************************
     * Function Employee Update
     ****************************/
public function co_update($id)
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
         
   //     $appl_used = (array)$this->applications_model->get_by_client_id($this->auth_client_id)->row();
       
        $client_forms = 'applications/'.$this->passdb.'/coapplication_edit';
         
            $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
      $validation_rules = array(
			array(
				'field' => 'app_id',
				'label' => 'Primary Applicant ID',
				'rules' => 'trim|numeric'
			),
                        array(
				'field' => 'coapp_relation',
				'label' => 'Relation to Applicant',
				'rules' => 'trim|max_length[35]'
			),
                        
                        array(
				'field' => 'coapp_fname',
				'label' => 'First Name',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'coapp_mname',
				'label' => 'Middle Initial',
				'rules' => 'trim|max_length[4]'
			),
                        array(
				'field' => 'coapp_lname',
				'label' => 'Last Name',
				'rules' => 'trim|max_length[80]'
			),
			array(
				'field' => 'coapp_suffix',
				'label' => 'Suffix',
				'rules' => 'trim|max_length[12]'
			),
                        array(
				'field' => 'coapp_phone',
				'label' => 'Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
                        array(
				'field' => 'coapp_altphone',
				'label' => 'Alternate Phone',
				'rules' => 'trim|max_length[14]'
			),
                        array(
				'field' => 'coapp_dob',
				'label' => 'Date of Birth',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'coapp_ssn',
				'label' => 'Co Social Security Number/Tax ID',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'coapp_marital',
				'label' => 'Marital Status',
				'rules' => 'trim|max_length[11]'
			),
                        array(
				'field' => 'coapp_gender',
				'label' => 'Gender',
				'rules' => 'max_length[10]'
			),
                        array(
				'field' => 'coapp_email',
				'label' => 'Email Address',
				'rules' => 'trim|valid_email'
			),
                         array(
				'field' => 'coapp_street',
				'label' => 'Street Address',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'coapp_unit',
				'label' => 'Unit',
				'rules' => 'trim|max_length[80]'
			),
                        array(
				'field' => 'coapp_city',
				'label' => 'City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'coapp_state',
				'label' => 'State',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'coapp_zipcode',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[5]'
			),
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for
           
           
           
           
        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
               $data['action'] = site_url('applications/co_update/'.$id); 
                   $data['id'] = $id;
                     $Appl_rec = (array)$this->applications_model->get_by_id($id)->row();
                $data['title'] = 'Edit Co-Applicant for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname'];
            $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
                    $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Coapp_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');
               $data['Coapplicant'] = $this->applications_model->get_co_by_app_id($id)->row();
            $this->template->load('client', $client_forms, $data);
    } else {
        // If form submitted and validated, 
            
                    $coapp_data['app_id']   = $this->input->post('app_id');
            $coapp_data['coapp_relation']   = $this->input->post('coapp_relation');
               $coapp_data['coapp_fname']   = ucfirst($this->input->post('coapp_fname'));
               $coapp_data['coapp_mname']   = ucfirst($this->input->post('coapp_mname'));
               $coapp_data['coapp_lname']   = ucfirst($this->input->post('coapp_lname'));
              $coapp_data['coapp_suffix']   = ucfirst($this->input->post('coapp_suffix'));
               $coapp_data['coapp_phone']   = $this->input->post('coapp_phone');
            $coapp_data['coapp_altphone']   = $this->input->post('coapp_altphone');
                 $coapp_data['coapp_dob']   = $this->input->post('coapp_dob');
                 $coapp_data['coapp_ssn']   = $this->input->post('coapp_ssn');
             $coapp_data['coapp_marital']   = $this->input->post('coapp_marital');
              $coapp_data['coapp_gender']   = $this->input->post('coapp_gender');
               $coapp_data['coapp_email']   = $this->input->post('coapp_email');
              $coapp_data['coapp_street']   = ucfirst($this->input->post('coapp_street'));
                $coapp_data['coapp_unit']   = ucfirst($this->input->post('coapp_unit'));
                $coapp_data['coapp_city']   = ucfirst($this->input->post('coapp_city'));
               $coapp_data['coapp_state']   = ucfirst($this->input->post('coapp_state'));
             $coapp_data['coapp_zipcode']   = $this->input->post('coapp_zipcode');
             
               
                
            // If no references, insert, but if yes, then update.
            if($this->input->post('co_form') === 'NEW') {
                $emp_data['coapp_created']   = date('Y-m-d H:i:s');
                     $emp_data['coapp_by']   = $this->auth_user_id;
                      // Insert references
                $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($coapp_data)
				->insert('coapplicant');     
            } else {
                $emp_data['coapp_modified']     = date('Y-m-d H:i:s');
                $emp_data['coapp_mod_by']       = $this->auth_user_id;
                
                       // Update employers 
                $this->applications_model->co_update($id,$emp_data); 
                        
            }
                 // set common properties 
                  $Appl_rec    = (array)$this->applications_model->get_by_id($id)->row();
             $data['title']    = 'New Co-Applicant Saved for '.$Appl_rec['app_fname'] . " " . $Appl_rec['app_lname']; 
             $data['id']       = $id;
            $data['action']    = site_url('applications/co_update/'.$id);
            $data['link_back'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             $data['link_app'] = secure_anchor('applications/update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-pencil-square-o txt-color-blue"></i> Applicant',array('class'=>'back'));
             $data['link_ref'] = secure_anchor('applications/ref_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-users txt-color-blue"></i> Reference',array('class'=>'back'));
             $data['link_emp'] = secure_anchor('applications/emp_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-institution txt-color-blue"></i> Employer',array('class'=>'back'));
              $data['link_co'] = secure_anchor('applications/co_update/'.$id,
                    '<i class="fa fa-lg fa-fw fa-plus-circle txt-color-blue"></i> Co-Applicant',array('class'=>'back'));
              $data['link_back_r'] = secure_anchor('dashboard/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-right txt-color-blue"></i> Back to Dashboard',array('class'=>'back'));
             //pass the listData the parms we need to pull from the reference_relation table - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
                    $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Coapp_Relation'] = listData($this->passdb,'reference_relation','relation', 'relation');
            $data['Coapplicant'] = $this->applications_model->get_co_by_app_id($id)->row(); 
             $this->template->load('client', $client_forms, $data);
                  
 }
    }
    

    
    
                public function dob_month(){

                 //       If (empty(set_select('app_bmonth'))) {
                          $options = array(
                            '01' => 'January',
                            '02' => 'February',
                            '03' => 'March',
                            '04' => 'April',
                            '05' => 'May',
                            '06' => 'June',
                            '07' => 'July',
                            '08' => 'August',
                            '09' => 'September',
                            '10' => 'October',
                            '11' => 'November',
                            '12' => 'December'
                        );  
 
                            return $options;
        
    }
     // -----------------------------------------------------------------------            
            
     public function bday() {
         for($i = '1'; $i <= '31'; $i++)
         {
            $days[] =  sprintf('%02d', $i);  //the sprintf('%02d puts the leading 0 on the number
         }

         return $days;
     }
     public function byear() {
         
         for($i = date('Y')-17; $i >= date('Y')-100; $i--)
         {
            $year[] =  $i; 
         }
        return $year;
     }
     
      /*
         * Both the completed_appls and the incompleted_appls  are obsolete
         * Now just check the applications status.
         */
     
 public function completed_appls($offset = 0, $order_column = 'app_id', $order_type = 'asc')
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
            //TODO: check for valid column 
            // load data 

              $Appls = $this->applications_model->get_paged_completed_list($this->limit, $offset, $order_column, $order_type)->result();

             // generate pagination 
            $this->load->library('pagination');  
                  $config['base_url'] = site_url('applications/completed_appls/');
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
                    secure_anchor('applications/completed_appls/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/completed_apple/'.$offset.'/app_fname/'.$new_order, 'Cardholder Name'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'Last 4'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Auth Amount'), 
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Approval Code'),
                    
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $Cappl){ 
            $this->table->add_row(

                $Cappl->app_id,
                $Cappl->app_fname,
                $Cappl->app_lname,    
                substr($Cappl->app_primary_ssn, 4), 
                 
                secure_anchor(
                        'applications/view/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$Cappl->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'applications/delete/'.$Cappl->app_id,
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
           
           
           
         $data['title'] = 'Completed Transactions';
         $this->template->load('client', 'applications/completed_appls', $data);
         
     }
     
     
     
          /**********************************************************
      * incompleted applications
      */
 public function incompleted_appls($offset = 0, $order_column = 'app_id', $order_type = 'asc') 
             {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
        //TODO: check for valid column 
        // load data 

          $Appls = $this->applications_model->get_paged_incompleted_list($this->limit, $offset, $order_column, $order_type)->result();

         // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('applications/incompleted_appls/'); 
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
              //    '#', 
                    secure_anchor('applications/incompleted_appls/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/incompleted_apple/'.$offset.'/app_fname/'.$new_order, 'Cardholder Name'), 
                    secure_anchor('applications/incompleted_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'Last 4'),
                    secure_anchor('applications/incompleted_appls/'.$offset.'/app_lname/'.$new_order, 'Amoumt'), 
                    secure_anchor('applications/incompleted_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'Ref #'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $Cappl){ 
            $this->table->add_row(
            //  ++$i,
                $Cappl->app_id,
                $Cappl->app_fname,
                $Cappl->app_lname,    
                substr($Cappl->app_primary_ssn, 4), 
                 
                secure_anchor(
                        'applications/view/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$Cappl->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'applications/delete/'.$Cappl->app_id,
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
           
           
           
         $data['title'] = 'NSF Transactions';
         $this->template->load('client', 'applications/incompleted_appls', $data);
         
     }
     
     
     /**********************************************************
      * incompleted applications
      */
     public function applied_list($offset = 0, $order_column = 'app_id', $order_type = 'asc')
     {
    
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
//TODO: check for valid column 
// load data 
          
  $Appls = $this->applications_model->get_applied_status_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('applications/applied_list/'); 
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
              //    '#', 
                    secure_anchor('applications/applied_status_appls/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/applied_status_appls/'.$offset.'/app_fname/'.$new_order, 'First Name'), 
                    secure_anchor('applications/applied_status_appls/'.$offset.'/app_lname/'.$new_order, 'Last Name'), 
                    secure_anchor('applications/applied_status_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'SSN Last 4'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $Applied){ 
            $this->table->add_row(
            //  ++$i,
                $Applied->app_id,
                $Applied->app_fname,
                $Applied->app_lname,    
                substr($Applied->app_primary_ssn, 5), 
                 
                secure_anchor(
                        'applications/view/'.$Applied->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$Applied->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'applications/delete/'.$Applied->app_id,
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
           
           
           
         $data['title'] = 'Applied Applications';
         $this->template->load('client', 'applications/applied_status_appls', $data);
         
     }
     
     public function settlement($offset=0, $order_column = 'FCC_ID', $order_type = 'desc')
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
                        
                        $this->load->library('dompdf_gen');
                        
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'FCC_ID'; 
              if (empty($order_type)) $order_type = 'desc'; 
            //TODO: check for valid column 
            // load data 

              $Appls = $this->applications_model->get_paged_settlement(10, $offset, $order_column, $order_type)->result();

             // generate pagination 
            $this->load->library('pagination');  
                  $config['base_url'] = site_url('applications/settlement');
                $config['total_rows'] = $this->applications_model->count_settlements(); 
                  $config['per_page'] = 10; 
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
                    secure_anchor('applications/settlement/'.$offset.'/FCC_ID/'.$new_order, 'ID'),
                    secure_anchor('applications/settlement/'.$offset.'/CUST_ID/'.$new_order, 'CUSTID'),
                    secure_anchor('applications/settlement/'.$offset.'/name_on_card/'.$new_order, 'Cardholder'),
                    secure_anchor('applications/settlement/'.$offset.'/CARD_NUM/'.$new_order, 'Card Number'),
                    secure_anchor('applications/settlement/'.$offset.'/AMOUNT/'.$new_order, 'Amount'), 
                    secure_anchor('applications/settlement/'.$offset.'/DATE/'.$new_order, 'Date'),
                    secure_anchor('applications/settlement/'.$offset.'/TIME/'.$new_order, 'Time'),
                    secure_anchor('applications/settlement/'.$offset.'/STATUS/'.$new_order, 'Status'),
                    secure_anchor('applications/settlement/'.$offset.'/AUTHID/'.$new_order, 'Auth ID'),
                    secure_anchor('applications/settlement/'.$offset.'/REFID/'.$new_order, 'Ref ID')

                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $val){ 
                $EZ = substr($val->CARD_NUM, 12);
                $EZ = '************'.$EZ;
                $AMT = '$'.number_format($val->AMOUNT, 2);
                
            $this->table->add_row(

                $val->FCC_ID,
                $val->CUST_ID,
                $val->name_on_card,    
                $EZ,    
                $AMT,
                $val->DATE,
                $val->TIME,
                $val->STATUS,
                $val->AUTHID,
                $val->REFID    
                 
                ); 
                
            }
            
            $data['table'] = $this->table->generate(); 
//            if ($this->uri->segment(3)=='delete_success') {
//                $data['message'] = 'The Data was successfully deleted'; 
//            }else if ($this->uri->segment(3)=='add_success') {
//                $data['message'] = 'The Data has been successfully added'; 
//            }else{ 
//                $data['message'] = ''; 
//            }
            
 
             // Load all views as normal          
           
         $data['title'] = 'Settlement';
         $this->template->load('client', 'applications/settlement', $data);

         
     }
     
     /**********************************************************************************************************/
     
     public function pdf_settlement()
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
                        
                        $this->load->library('dompdf_gen');
                        
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // load data 

              $Appls = $this->applications_model->get_pdf_settlement()->result();                             
 // generate table data 
                  
//                          $data['th'] = '<th>ID</th>'.
//                                  '<th>Cardholder</th>'.
//                                   '<th>Card Number</th>'.
//                                   '<th>Amount</th>'.
//                                  '<th>Date</th>'.
//                                   '<th>Time</th>';        
              
                    $i = 1;   //Start the counter   
            foreach ($Appls as $Cappl){         // get all the data that will be displayed on the pdf
                $EZ = substr($Cappl->CARD_NUM, 12);   // get only the last 4 numbers
                $EZ = '**'.$EZ;
                $AMT = '$'.number_format($Cappl->AMOUNT, 2);  // format the amount
                $count = $i;
                $cust = $Cappl->CUST_ID;
                
                $data['ret'][] = (object) array(    // pass it in an array (object)
                     'CUST_ID' => $cust,
                'name_on_card' => $Cappl->name_on_card,
                     'CNumber' => $EZ,
                         'AMT' => $AMT,
                       'CDate' => $Cappl->DATE,
                       'CTime' => $Cappl->TIME,
                      'Status' => $Cappl->STATUS
                );
               
               $data['total'] = $count;   // pass the number of records found
                         $i += 1;           
            }

            $data['title'] = 'Settlement Report';
		$this->load->view('pdf/pdf_settlement', $data);  // load it all in the page

		// Get output html
		$html = $this->output->get_output();
		$this->dompdf->set_paper('A4','potrait');
		// Load library
		$this->load->library('dompdf_gen');
		// Convert to PDF

		$this->dompdf->load_html($html);
		$this->dompdf->render();
//                $f;
//                $l;
//                if(headers_sent($f,$l))
//                {
//                    echo $f,'<br/>',$l,'<br/>';
//                    die('now detect line');
//                }
		$this->dompdf->stream("Settlement.pdf");
  
         
     }
     
     
     /***********************************************************************************************************/
     
     
     public function chargeback($offset = 0, $order_column = 'app_id', $order_type = 'asc')
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
            //TODO: check for valid column 
            // load data 

              $Appls = $this->applications_model->get_paged_chargeback($this->limit, $offset, $order_column, $order_type)->result();

             // generate pagination 
            $this->load->library('pagination');  
                  $config['base_url'] = site_url('applications/chargeback/');
             //   $config['total_rows'] = $this->applications_model->count_all(); 
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
                    secure_anchor('applications/completed_appls/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/completed_apple/'.$offset.'/app_fname/'.$new_order, 'Cardholder Name'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'Last 4'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Amount'), 
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Denial Code'),
                    
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $Cappl){ 
            $this->table->add_row(

                $Cappl->app_id,
                $Cappl->app_fname,
                $Cappl->app_lname,    
                substr($Cappl->app_primary_ssn, 4), 
                 
                secure_anchor(
                        'applications/view/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$Cappl->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'applications/delete/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this Record?')")) 
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
           
           
           
         $data['title'] = 'Chargeback';
         $this->template->load('client', 'applications/chargeback', $data);
         
     }
     
     public function holidays($offset = 0, $order_column = 'app_id', $order_type = 'asc')
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
  
           
         $data['title'] = 'Federal Holidays';
         $this->template->load('client', 'applications/holidays', $data);
         
     }
     
     /********************************************************************************************************************************/
     
     
          public function approvals($offset = 0, $order_column = 'app_id', $order_type = 'asc')
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
            //TODO: check for valid column 
            // load data 

              $Appls = $this->applications_model->get_paged_completed_list($this->limit, $offset, $order_column, $order_type)->result();

             // generate pagination 
            $this->load->library('pagination');  
                  $config['base_url'] = site_url('applications/approvals/');
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
                    secure_anchor('applications/completed_appls/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/completed_apple/'.$offset.'/app_fname/'.$new_order, 'Cardholder Name'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'Last 4'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Amount'), 
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Approval Code'),
                    
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $Cappl){ 
            $this->table->add_row(

                $Cappl->app_id,
                $Cappl->app_fname,
                $Cappl->app_lname,    
                substr($Cappl->app_primary_ssn, 4), 
                 
                secure_anchor(
                        'applications/view/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$Cappl->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'applications/delete/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this Record?')")) 
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
           
           
           
         $data['title'] = 'Approvals';
         $this->template->load('client', 'applications/approvals', $data);
         
     }
     
     
     /************************************************************************************************************************************/
     
     
          public function denials($offset = 0, $order_column = 'app_id', $order_type = 'asc')
             {
         
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
            // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'app_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
            //TODO: check for valid column 
            // load data 

              $Appls = $this->applications_model->get_paged_completed_list($this->limit, $offset, $order_column, $order_type)->result();

             // generate pagination 
            $this->load->library('pagination');  
                  $config['base_url'] = site_url('applications/denials/');
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
                    secure_anchor('applications/completed_appls/'.$offset.'/app_id/'.$new_order, 'ID'),
                    secure_anchor('applications/completed_apple/'.$offset.'/app_fname/'.$new_order, 'Cardholder Name'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_primary_ssn/'.$new_order, 'Last 4'),
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Amount'), 
                    secure_anchor('applications/completed_appls/'.$offset.'/app_lname/'.$new_order, 'Denial Code'),
                    
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Appls as $Cappl){ 
            $this->table->add_row(

                $Cappl->app_id,
                $Cappl->app_fname,
                $Cappl->app_lname,    
                substr($Cappl->app_primary_ssn, 4), 
                 
                secure_anchor(
                        'applications/view/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-eye txt-color-blue"></i>',
                        array('class'=>'view', 'title' => 'View')).' '. 
                secure_anchor(
                        'applications/update/'.$Cappl->app_id,
                         '<i class="fa fa-lg fa-pencil-square-o txt-color-blue"></i>',
                        array('class'=>'update', 'title' => 'Update')).' '. 
                secure_anchor(
                        'applications/delete/'.$Cappl->app_id,
                        '<i class="fa fa-lg fa-fw fa-eraser txt-color-blue"></i>',
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this Record?')")) 
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
           
           
           
         $data['title'] = 'Denials';
         $this->template->load('client', 'applications/denials', $data);
         
     }
     
     
     
}

