<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 *  This class holds all the front page view loads.
 */
class Pages extends MY_Controller {
  public function __construct()
        {
         parent::__construct();
        }
    
        //***************************************************************************************************
	public function index()
	{
        $this->is_logged_in();
            $data = array(
                    'title'       =>   'EZ Pay Home Page!',
                    'description' => 'EZ Pay allows you to offer financing to your customers. Choose to In-house finance your customers or third party cash out financing.'
                    );
       
            //load the template and what content
            $this->template->load('default', 'pages/home', $data);
         
	}
        //***************************************************************************************************
        public function product()
        {
            $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Products!',
                    'description' => 'EZ Pay offers products that allow third party financing for EZ cash out or the option to in-house finance your customers.'
                    );
            $this->template->load('default', 'pages/product', $data);
        
        }
        //*****************************************************************************************************
  public function contact()
        {
          $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Contact Us!',
                    'description' => 'Contact EZ Pay to see what financial services you can offer to your customers.',
                    'action'      => site_url('pages/contact')
                    );
            
             
             $this->load->library('form_validation');
        
            // create array to pass all the rules into the set_rules()
        $validation_rules = array(
			
                        array(
				'field' => 'app_fname',
				'label' => 'First Name',
				'rules' => 'trim|required|max_length[45]'
			),
                        array(
				'field' => 'app_lname',
				'label' => 'Last Name',
				'rules' => 'trim|required|max_length[45]'
			),
                        array(
				'field' => 'app_phone',
				'label' => 'Primary Phone',
				'rules' => 'trim|max_length[14]'
			),
			array(
				'field' => 'app_email',
				'label' => 'Email Address',
				'rules' => 'trim|required|valid_email'
			),
                        array(
				'field' => 'epurpose',
				'label' => 'Purpose of Email',
				'rules' => 'required'
			),
                        array(
				'field' => 'ecomment',
				'label' => 'Comment',
				'rules' => 'trim|required'
			)
		);

		$this->form_validation->set_rules( $validation_rules ); // set rules to check for

        // if form not submitted, display form. RUN() returns TRUE if validated
        if ($this->form_validation->run() == FALSE)
                {
 
            // set common properties to blank for a fresh copy of the form
               $data['action']  = site_url('pages/contact'); 
                $data['title']  = 'Pagasys Contact'; 
            $data['app_fname']  = ''; 
            $data['app_lname']  = '';
            $data['app_phone']  = '';
            $data['app_email']  = '';
             $data['epurpose']  = '';
             $data['ecomment']  = '';
            
            $this->template->load('default', 'pages/contact', $data);
            
                } else {
                    
                    $this->load->library('email');
                    // localize post data to pass to email functions
                       $name = $this->input->post('app_fname'). ' '.$this->input->post('app_lname');
                      $phone = $this->input->post('app_phone');
                    $comment = $this->input->post('ecomment');
                $senderemail = $this->input->post('app_email');
                    $purpose = $this->input->post('epurpose');
                        $msg = "From: $name\n Phone:  $phone\n $comment";
                        
                   //If valid, email out. 
                $this->email->from($senderemail, $name);            
                $this->email->to('steve@ezpay.host');
                $this->email->cc('im.steve@live.com');
                $this->email->bcc($senderemail);
                $this->email->subject($purpose);
                $this->email->message($msg);
                $this->email->send();
                
                // Pass this back to contacts as success page.
                   $data['title']  = 'EZ Pay Email Success!'; 
                 $data['success']  = "Your message was successfully sent!";
                    $data['name']  = $name;
                   $data['phone']  = $phone;
                 $data['comment']  = $comment;
            $this->template->load('default', 'pages/contact', $data);
                }

        }
        
        
        public function fees()
        {
            $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Fees!',
                    'description' => 'EZ Pay offers products that allow integration into your software system to process your customer payments.'
                    );
            $this->template->load('default', 'pages/fees', $data);
        }
        
        public function paymenttypes()
        {
            $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Payment Types!',
                    'description' => 'EZ Pay offers products that allow integration into your software system to process your customer payments.'
                    );
            $this->template->load('default', 'pages/payment_types', $data);
        }
        
        public function integration()
        {
            $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Integration!',
                    'description' => 'EZ Pay offers products that allow integration into your software system to process your customer payments.'
                    );
            $this->template->load('default', 'pages/integration', $data);
        }
        
         public function reports()
        {
            $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Payment Types!',
                    'description' => 'EZ Pay offers products that allow integration into your software system to process your customer payments.'
                    );
            $this->template->load('default', 'pages/reports', $data);
        }
        
        public function privacy()
        {
            $this->is_logged_in();
            $data = array(
                    'title'     =>   'EZ Pay Privacy!',
                    'description' => 'EZ Pay offers products that allow integration into your software system to process your customer payments.'
                    );
            $this->template->load('default', 'pages/privacy', $data);
        }
  
    
}

