<?php
defined('BASEPATH') or exit('No direct script access allowed');



class Users extends MY_Controller
{

    private $limit = 5;
    protected $passdb;
    protected $dbMain;
    
    public function __construct()
        {
         parent::__construct();
        //Need is_logged_in() for auth_*** to work in a method
       $this->is_logged_in();
           
         $this->passdb = $this->auth_client_id; // This passes the client id to the dropdown helper to call the dynamic dropdowns from the correct databases     
         $this->dbMain = $this->load->database('main', true); // User authentication uses the "main" database 
         $this->load->library('table','form_validation');
         $this->load->helper('form', 'url');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE);
         $this->load->model('applications_model', '', TRUE);
         $this->load->model('states_model', '', TRUE);
         
        }
    

    // -----------------------------------------------------------------------

        public function index($offset = 0, $order_column = 'user_id', $order_type = 'asc')
	{
           // $this->load->database('30041');
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'user_id'; 
              if (empty($order_type)) $order_type = 'asc'; 
//TODO: check for valid column 
// load data 
  $Users = $this->users_model->get_paged_list($this->limit, $offset, $order_column, $order_type)->result();
            
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('users/index/'); 
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
                    secure_anchor('users/index/'.$offset.'/user_name/'.$new_order, 'Name'), 
                    secure_anchor('users/index/'.$offset.'/user_email/'.$new_order, 'User Email'), 
                    secure_anchor('users/index/'.$offset.'/user_level/'.$new_order, 'User Level'),
                    'Actions' 
                    );        
              
            $i = 0 + $offset; 
            foreach ($Users as $User){ 
                // create the name for the list
                $display_name = $User->user_fname. ' ' .$User->user_lname;
                // If the current user has a lower level, then they shouldn't be offered the links to edit the users. Hide them
                If($this->auth_level <= $User->user_level && $this->auth_level != 9){
                    $actionset = '';
                } else {
                    $actionset = secure_anchor(
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
                        array('class'=>'delete', 'title' => 'Delete', 'onclick'=>"return confirm('Are you sure you want to remove this User?')")); 
                
                }
            $this->table->add_row(
            //  ++$i,
                $User->user_id,
                $display_name, 
                $User->user_email, 
                $User->user_level, 
                $actionset 
                
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
            $data['title'] = 'User List';
            $this->template->load('client', 'users/user_list', $data);
              
	}
        
            /**********************************
             *  View function
             */
    function view($id){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Git the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
        
        
        
     // set common properties 
            $data['title'] = 'User Details'; 
            $data['link_back'] = secure_anchor(
                    'users/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Users',
                    array('class'=>'back')); 
     // get user details 
            $data['User'] = $this->users_model->get_by_id($id)->row(); 
            
         
     // load view 
            $this->template->load('client', 'users/user_view', $data);
           

            } 
            
            /******************************************************
             * Update
             */
    function update($id){ 
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
                         
          if( $sql = $this->clients_model->view_record())// Get the individual client to list as the heading
           { $data['cli_rec'] = $sql; } 
           // if this is pulling up the form for the first time, user_token will be '' 
                
               $this->load->library('form_validation');
        

//        $is_same_username = $this->users_model->check_for_match($id,'user_name',$user_vata['user_name']);
//                if($is_same_username == FALSE){
//                $this->form_validation->set_rules('user_name', 'Username', 'trim|required|is_unique[users.user_name]|max_length[22]|min_length[5]');
//                }
//        $is_same_email = $this->users_model->check_for_match($id,'user_email',$user_vata['user_email']);
//                if($is_same_email == FALSE){
//                $this->form_validation->set_rules('user_email', 'Email Address', 'required|trim|is_unique[users.user_email]');
//                }
        $validation_rules = array(
//			array(
//				'field' => 'user_name',
//				'label' => 'Username',
//				'rules' => 'trim|required|is_unique[users.user_name]|max_length[22]|min_length[5]'
//			),
                        array(
				'field' => 'user_fname',
				'label' => 'User First Name',
				'rules' => 'trim|required|max_length[45]'
			),
                        array(
				'field' => 'user_mname',
				'label' => 'User Middle Initial',
				'rules' => 'trim|max_length[4]'
			),
                        array(
				'field' => 'user_lname',
				'label' => 'User Last Name',
				'rules' => 'trim|required|max_length[45]'
			),
//			array(
//				'field' => 'user_pass',
//				'label' => 'Password',
//				'rules' => 'trim|required|external_callbacks[model,formval_callbacks,_check_password_strength,TRUE]'
//			),
//                        array(
//				'field' => 'passconf',
//				'label' => 'Password Confirm',
//				'rules' => 'trim|required|matches[user_pass]'
//			),
//			array(
//				'field' => 'user_email',
//				'label' => 'User Email',
//				'rules' => 'required|valid_email|is_unique[users.user_email]'
//			),
			array(
				'field' => 'user_level',
				'label' => 'User Level',
				'rules' => 'required'
			)
		);

		$this->form_validation->set_rules( $validation_rules );
            
            
            
            
           if ( $this->form_validation->run() == FALSE )
		{
 
                // set common properties 
            $data['title'] = 'Update User'; 
            $data['action'] = ('users/update/'.$id);
            $data['link_back'] = secure_anchor(
                    'users/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Users',
                    array('class'=>'back')); 
            $data['message'] = ''; 
              $data['User']  = (array)$this->users_model->get_by_id($id)->row();
              $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name');
             $data['Level'] = listLevels($this->passdb,'level','level_id', 'level_position',$this->auth_level);
              
          //    $this->load->helper(array('dd_helpers','form')); 
         //   $data['Clients'] = ddData($this->dbMain,'client','client_id', 'client_name');
         
           $this->template->load('client', 'users/user_edit', $data);
               
               
                
  } else {
                     // set common properties 
                      $data['title']    = 'Update User Information'; 
                     $data['action']    = ('users/update/'.$id);
                        // array of items to pass to form validation
          
          
                     $id = $this->input->post('user_id');

          
             $user_data['user_email']   = $this->input->post('user_email');
             $user_data['user_fname']   = $this->input->post('user_fname');
             $user_data['user_mname']   = $this->input->post('user_mname');
             $user_data['user_lname']   = $this->input->post('user_lname');
             $user_data['user_phone']   = $this->input->post('user_phone');
             $user_data['user_level']   = $this->input->post('user_level');
            $user_data['user_status']   = $this->input->post('user_status');
            $user_data['user_street']   = $this->input->post('user_street');
              $user_data['user_unit']   = $this->input->post('user_unit');
              $user_data['user_city']   = $this->input->post('user_city');
             $user_data['user_state']   = $this->input->post('user_state');
           $user_data['user_zipcode']   = $this->input->post('user_zipcode');
          $user_data['user_modified']   = date('Y-m-d H:i:s'); 
            
            
        // update and then get the record by id
            $this->users_model->update($id,$user_data); 
            $data['User'] = (array)$this->users_model->get_by_id($id)->row(); 
           
            // Get state/level dropdown list
            $this->load->helper(array('dropdown_helper','form')); 
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $data['States'] = listData($this->passdb,'states','state_abbrev','state_name'); 
             $data['Level'] = listLevels($this->passdb,'level','level_id', 'level_position', $this->auth_level);
            
            
                
            $data['link_back'] = secure_anchor(
                    'users/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Users',
                    array('class'=>'back')); 
      // load view 

            $this->template->load('client', 'users/user_edit', $data);
                    
            }   
                    
                
    }    
                
            
            /************************************************
             * Delete
             */
    function delete($id){ 
     // delete Student 
            $this->users_model->delete($id); 
     // redirect to User list page 
            redirect('Users/index/delete_success','refresh'); 

            } 
            

    function valid_date($str) { 
//             if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str)) { 
//                    $this->form_validation->set_message('valid_date', 'date format is not valid. yyyy-mm-dd'); 
//                    return false; 
//
//             } else { 
//                 return true; 
//                 
//             } 

    } 

    
    // -----------------------------------------------------------------------
public function login()
    {
   
     if( ! empty( $this->auth_role ) )
        {
        redirect( secure_site_url('dashboard/') );
     }
   // $this->session->sess_destroy();
        $data = array(
                    'title'       =>   'EZ Pay Login!',
                    'description' => 'Pagasys offers products that allow your customers to pay online from the convenience of any web devise.'
                    );
         //Method should not be directly accessible
        if( $this->uri->uri_string() == 'users/login')
        {
            redirect( secure_site_url('pages/') );
           // show_404();
        }
        // if sending data to the login page, must be already logged in.
        if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' )
        {
            $this->require_min_level(1);
        }

        $this->setup_login_form();
        $html = $this->template->load('default', 'users/form_login', $data);

        echo $html;
    }

    // --------------------------------------------------------------
    
       /**
     * Log out
     */
    public function logout()
    {
        $this->session->sess_destroy();
        $this->authentication->logout();

        redirect( secure_site_url( LOGIN_PAGE . '?logout=1') );
    }

    // --------------------------------------------------------------
    
    public function simple_verification()
    {
        $this->is_logged_in();

     //   echo $this->load->view('users/page_header', '', TRUE);

        echo '<p>';
        if( ! empty( $this->auth_role ) )
        {
            echo $this->auth_role . ' logged in!<br />
                User ID is ' . $this->auth_user_id . '<br />
                Auth level is ' . $this->auth_level . '<br />
                Username is ' . $this->auth_user_name;

            if( $http_user_cookie_contents = $this->input->cookie( config_item('http_user_cookie_name') ) )
            {
                $http_user_cookie_contents = unserialize( $http_user_cookie_contents );
                
                echo '<br />
                    <pre>';

                print_r( $http_user_cookie_contents );

                echo '</pre>';
            }
        }
        else
        {
            echo 'Nobody logged in.';
        }
        echo '</p>';

     //   echo $this->load->view('users/page_footer', '', TRUE);
    }
    

    // -----------------------------------------------------------------------
    /**  Create a User
     * The password used in the $user_data array needs to meet the
     * following default strength requirements:
     *   - Must be at least 8 characters long
     *   - Must have at least one digit
     *   - Must have at least one lower case letter
     *   - Must have at least one upper case letter
     *   - Must not have any space, tab, or other whitespace characters
     *   - No backslash, apostrophe or quote chars are allowed
     */
    public function create_user()
    {
         $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/login') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
        // if form not submitted, display form
        if(  empty($this->input->post('user_name')) && empty($this->input->post('client_id')) ) {
            // set common properties to blank for a fresh copy of the form
            $data['action'] = site_url('users/create_user'); 
            $data['title'] = 'Add new User'; 
            $data['message'] = ''; 
            $data['User']['user_id']=''; 
            $data['User']['user_level']='';
            $data['User']['user_name']=''; 
            $data['User']['user_email']='';
            $data['User']['user_fname']=''; 
            $data['User']['user_mname']=''; 
            $data['User']['user_lname']=''; 
            $data['User']['user_phone']='';  
            $data['User']['user_status']='';
            $data['User']['user_level']='';
            $data['User']['client_id']='';
            //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Level'] = listLevels($this->passdb,'level','level_id', 'level_position', $this->auth_level);
           // $data['Clients'] = listData($this->passdb,'client','client_id', 'client_name');
            $data['link_back'] = secure_anchor(
                    'users/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Users',
                    array('class'=>'back')); 
        // use the edit form       
            $this->template->load('client', 'users/user_add', $data);
        } 
 else {
     // set common properties 
            $today = date('Y-m-d');
            $data['title'] = 'Add New User'; 
            $data['action'] = site_url('users/create_user'); 
            $data['link_back'] = secure_anchor(
                    'users/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Users',
                    array('class'=>'back')); 
          
            
              $user_vata['user_name']   = $this->input->post('user_name'); 
              $user_vata['user_pass']   = $this->input->post('user_pass');
               $user_vata['passconf']   = $this->input->post('passconf');
             $user_vata['user_email']   = $this->input->post('user_email');
             $user_vata['user_fname']   = $this->input->post('user_fname');
             $user_vata['user_mname']   = $this->input->post('user_mname');
             $user_vata['user_lname']   = $this->input->post('user_lname');
             $user_vata['user_phone']   = $this->input->post('user_phone');
             $user_vata['user_level']   = $this->input->post('user_level');
              $user_vata['client_id']   = $this->input->post('client_id');
            $user_vata['user_street']   = $this->input->post('user_street');
              $user_vata['user_unit']   = $this->input->post('user_unit');
              $user_vata['user_city']   = $this->input->post('user_city');
             $user_vata['user_state']   = $this->input->post('user_state');
           $user_vata['user_zipcode']   = $this->input->post('user_zipcode');
            
        $this->load->library('form_validation');
        
        $this->form_validation->set_data( $user_vata );

        $validation_rules = array(
			array(
				'field' => 'user_name',
				'label' => 'Username',
				'rules' => 'trim|required|is_unique[users.user_name]|max_length[22]|min_length[5]'
			),
                        array(
				'field' => 'user_fname',
				'label' => 'User First Name',
				'rules' => 'trim|required|max_length[45]'
			),
                        array(
				'field' => 'user_mname',
				'label' => 'User Middle Initial',
				'rules' => 'trim|max_length[4]'
			),
                        array(
				'field' => 'user_lname',
				'label' => 'User Last Name',
				'rules' => 'trim|required|max_length[45]'
			),
			array(
				'field' => 'user_pass',
				'label' => 'Password',
				'rules' => 'trim|required|external_callbacks[model,formval_callbacks,_check_password_strength,TRUE]'
			),
                        array(
				'field' => 'passconf',
				'label' => 'Password Confirm',
				'rules' => 'trim|required|matches[user_pass]'
			),
			array(
				'field' => 'user_email',
				'label' => 'User Email',
				'rules' => 'required|valid_email|is_unique[users.user_email]'
			),
                        array(
				'field' => 'user_phone',
				'label' => 'Phone',
				'rules' => 'trim|max_length[14]'
			),
			array(
				'field' => 'user_level',
				'label' => 'User Level',
				'rules' => 'required|integer|in_list[1,2,3,4,5,6,7,8,9]'
			),
                        array(
				'field' => 'user_street',
				'label' => 'Street Address',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'user_unit',
				'label' => 'Unit',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'user_city',
				'label' => 'City',
				'rules' => 'trim|max_length[65]'
			),
                        array(
				'field' => 'user_state',
				'label' => 'State',
				'rules' => 'trim|max_length[45]'
			),
                        array(
				'field' => 'user_zipcode',
				'label' => 'Zipcode',
				'rules' => 'trim|max_length[10]'
			),
                        array(
				'field' => 'client_id',
				'label' => 'Client',
				'rules' => 'required'
			)
		);

		$this->form_validation->set_rules( $validation_rules );
               
		     // run validation 
         if ( $this->form_validation->run() )
		{
            $data['title'] = 'Add new User'; 
            // put $user_data in database - don't put in an array. Will break the user_salt
              $user_data['user_salt']   = $this->authentication->random_salt();
              $user_data['user_pass']   = $this->authentication->hash_passwd($user_vata['user_pass'], $user_data['user_salt']);
                $user_data['user_id']   = $this->_get_unused_id();
              $user_data['user_date']   = date('Y-m-d H:i:s');
          $user_data['user_modified']   = date('Y-m-d H:i:s');
              $user_data['client_id']   = $this->auth_client_id;
              $user_data['user_name']   = $this->input->post('user_name');
             $user_data['user_email']   = $this->input->post('user_email');
             $user_data['user_fname']   = $this->input->post('user_fname');
             $user_data['user_mname']   = $this->input->post('user_mname');
             $user_data['user_lname']   = $this->input->post('user_lname');
             $user_data['user_phone']   = $this->input->post('user_phone');
             $user_data['user_level']   = $this->input->post('user_level');
            $user_data['user_status']   = 'Active';
            $user_data['user_street']   = $this->input->post('user_street');
              $user_data['user_unit']   = $this->input->post('user_unit');
              $user_data['user_city']   = $this->input->post('user_city');
             $user_data['user_state']   = $this->input->post('user_state');
           $user_data['user_zipcode']   = $this->input->post('user_zipcode');            
                  
        //pass the listData the parms we need to pull from the states/level tables - tablename,keyid,value 
            $this->load->helper(array('dropdown_helper','form')); 
            $data['States'] = listData($this->passdb,'states','state_abbrev', 'state_name'); 
            $data['Level'] = listLevels($this->passdb,'level','level_id', 'level_position', $this->auth_level);
            
            $data['link_back'] = secure_anchor(
                    'users/index/',
                    '<i class="fa fa-lg fa-fw fa-arrow-circle-left txt-color-blue"></i> List Of Users',
                    array('class'=>'back')); 
        // use the edit form       
            $this->template->load('client', 'users/user_add', $data);
                    
                        
              
                        
            // If username is not used, it must be entered into the record as NULL
            if( empty( $user_data['user_name'] ) )
            {
                $user_data['user_name'] = NULL;
            }
            
			$this->dbMain->set($user_data)
				->insert(config_item('user_table'));
                        $id = $this->dbMain->insert_id(); // get last id inserted to use for address

                        
            
             //redirect('users/view/'.$id); 
			if ($this->dbMain->affected_rows() == 1) {
				echo '<h1 class="text-right">Congratulations '. $user_data['user_fname']. '</h1>' . '<p>User ' . $user_data['user_name'] . ' was created.</p>';
                            }
                }
		else
		{
			echo '<h1 class="text-right">User Creation Error(s)</h1>' . validation_errors();
                        echo '<p><a href="' .secure_site_url('users/index'). '"> .
                                            <i class="fa fa-lg fa-fw fa-undo txt-color-blue"></i> . 
                                            <span>Back</span></a></p>';
		}
 }
    }
    
    // -----------------------------------------------------------------------



    /**
     * User recovery form
     */
    public function recover()
    {
        // Load resources
        $this->load->model('examples_model');

        /// If IP or posted email is on hold, display message
        if( $on_hold = $this->authentication->current_hold_status( TRUE ) )
        {
            $view_data['disabled'] = 1;
        }
        else
        {
            // If the form post looks good
            if( $this->tokens->match && $this->input->post('user_email') )
            {
                if( $user_data = $this->examples_model->get_recovery_data( $this->input->post('user_email') ) )
                {
                    // Check if user is banned
                    if( $user_data->user_banned == '1' )
                    {
                        // Log an error if banned
                        $this->authentication->log_error( $this->input->post('user_email', TRUE ) );

                        // Show special message for banned user
                        $view_data['user_banned'] = 1;
                    }
                    else
                    {
                        /**
                         * Use the string generator to create a random string
                         * that will be hashed and stored as the password recovery key.
                         */
                        $this->load->library('generate_string');
                        $recovery_code = $this->generate_string->set_options( 
                            array( 'exclude' => array( 'char' ) ) 
                        )->random_string(64)->show();

                        $hashed_recovery_code = $this->_hash_recovery_code( $user_data->user_salt, $recovery_code );

                        // Update user record with recovery code and time
                        $this->examples_model->update_user_raw_data(
                            $user_data->user_id,
                            array(
                                'passwd_recovery_code' => $hashed_recovery_code,
                                'passwd_recovery_date' => date('Y-m-d H:i:s')
                            )
                        );

                        $view_datax['special_link'] = 'Please click on the link or copy it into your address bar. ' 
                                .site_url('users/recovery_verification/' . $user_data->user_id . '/' . $recovery_code);
                        
                        $this->load->library('email');

                        $this->email->from('noreply@ezpay.host', 'Recovery Support');
                        $this->email->to($this->input->post('user_email'));
                        $this->email->cc('steve@ezpay.host');
                        //$this->email->bcc('them@their-example.com');

                        $this->email->subject('Password Recovery');
                        $this->email->message($view_datax['special_link']);

                        $this->email->send();
                        $view_datax['confirmation'] = 1;
                    }
                }

                // There was no match, log an error, and display a message
                else
                {
                    // Log the error
                    $this->authentication->log_error( $this->input->post('user_email', TRUE ) );

                    $view_data['no_match'] = 1;
                }
            }
        }
                    $view_data['title'] = 'EZ Pay Recovery page';
                    
 
        //   echo $this->template->load('default', 'users/recover_form', ( isset( $view_data ) ) ? $view_data : '', TRUE);
        echo $this->load->view('users/recover_form', ( isset( $view_data ) ) ? $view_data : '', TRUE );

    }

    // --------------------------------------------------------------

    /**
     * Verification of a user by email for recovery
     * 
     * @param  int     the user ID
     * @param  string  the passwd recovery code
     */
    public function recovery_verification( $user_id = '', $recovery_code = '' )
    {
        /// If IP is on hold, display message
        if( $on_hold = $this->authentication->current_hold_status( TRUE ) )
        {
            $view_data['disabled'] = 1;
        }
        else
        {
            // Load resources
            $this->load->model('examples_model');

            if( 
                /**
                 * Make sure that $user_id is a number and less 
                 * than or equal to 10 characters long
                 */
                is_numeric( $user_id ) && strlen( $user_id ) <= 10 &&

                /**
                 * Make sure that $recovery code is exactly 64 characters long
                 */
                strlen( $recovery_code ) == 64 &&

                /**
                 * Try to get a hashed password recovery 
                 * code and user salt for the user.
                 */
                $recovery_data = $this->examples_model->get_recovery_verification_data( $user_id ) )
            {
                /**
                 * Check that the recovery code from the 
                 * email matches the hashed recovery code.
                 */
                if( $recovery_data->passwd_recovery_code == $this->_hash_recovery_code( $recovery_data->user_salt, $recovery_code ) )
                {
                    $view_data['user_id']       = $user_id;
                    $view_data['user_name']     = $recovery_data->user_name;
                    $view_data['recovery_code'] = $recovery_data->passwd_recovery_code;
                }

                // Link is bad so show message
                else
                {
                    $view_data['recovery_error'] = 1;

                    // Log an error
                    $this->authentication->log_error('');
                }
            }

            // Link is bad so show message
            else
            {
                $view_data['recovery_error'] = 1;

                // Log an error
                $this->authentication->log_error('');
            }

            /**
             * If form submission is attempting to change password 
             */
            if( $this->tokens->match )
            {
                $this->examples_model->recovery_password_change();
            }
        }

       // echo $this->load->view('users/page_header', '', TRUE);

        echo $this->load->view( 'users/choose_password_form', $view_data, TRUE );

       // echo $this->load->view('users/page_footer', '', TRUE);
    }

    // --------------------------------------------------------------

    /**
     * Hash the password recovery code (uses the authentication library's hash_passwd method)
     */
    private function _hash_recovery_code( $user_salt, $recovery_code )
    {
        return $this->authentication->hash_passwd( $recovery_code, $user_salt );
    }

    // --------------------------------------------------------------
    
    /**
     * Get an unused ID for user creation
     *
     * @return  int between 1200 and 4294967295
     */
    private function _get_unused_id()
    {
        // Create a random user id
        $random_unique_int = 2147483648 + mt_rand( -2147482447, 2147483647 );

        // Make sure the random user_id isn't already in use
        $query = $this->db->where('user_id', $random_unique_int)
            ->get_where(config_item('user_table'));

        if ($query->num_rows() > 0) {
            $query->free_result();

            // If the random user_id is already in use, get a new number
            return $this->_get_unused_id();
        }

        return $random_unique_int;
    }

    // --------------------------------------------------------------
}

/* End of file Examples.php */
/* Location: /application/controllers/Examples.php */
