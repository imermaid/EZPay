<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Terminal extends MY_Controller
{
    public $passdb;
    private $api_key;
    private $api_pass;
    
    public function __construct()
    {
        parent::__construct();
        $this->is_logged_in();
        if( ! isset( $this->auth_user_id ) ) 
        {
            redirect( secure_site_url('pages/') );         
	}
        $this->passdb = $this->auth_client_id;
        $this->load->library('table','form_validation');
        $this->load->helper('form', 'url');
        $this->load->model('users_model', '', TRUE);
        $this->load->model('clients_model', '', TRUE); 
        $this->load->model('applications_model', '', TRUE);
        $this->load->model('loans_model', '', TRUE);
        $this->load->model("terminal_model", '', TRUE);
        $this->load->model("client","", TRUE);
        $this->load->helper(array("dropdown_helper","form"));
        
        $this->api_key = $this->terminal_model->getApiKey();
        $this->api_pass = $this->terminal_model->getApiPass();
    }

    public function index() 
    {
        $this->is_logged_in();
        if( ! isset( $this->auth_user_id ) )
        {
            redirect( secure_site_url('pages/') );         
        } 
        
        $data = array();
        $data["title"] = "Pagasys Virtual Terminal";
        $data["headline"] = "Card Payment Virtual Terminal";
        $data['user'] = $this->auth_user_id;
        $data['client'] = $this->auth_client_id;
        $data["States"] = listData($this->passdb, "states", "state_abbrev", "state_name");
        $data["api_key"] = $this->api_key;
        $data["api_pass"] = $this->api_pass;
        $this->template->load('client', 'terminal/card_payment', $data);
    }
    
    public function echeck()
    {
                $this->is_logged_in();
        if( ! isset( $this->auth_user_id ) )
        {
            redirect( secure_site_url('pages/') );         
        } 
        
        $data = array();
        $data["title"] = "Pagasys Virtual Terminal";
        $data["headline"] = "E-Check Virtual Terminal";
        $data['user'] = $this->auth_user_id;
        $data['client'] = $this->auth_client_id;
        $data["States"] = listData($this->passdb, "states", "state_abbrev", "state_name");
        $this->template->load('client', 'terminal/echeck', $data);
    }
    
    public function result()
    {
        $data = array();
        $data["title"] = "Pagasys Virtual Terminals";
        $data["headline"] = "Virtual Terminal";
        $data['user'] = $this->auth_user_id;
        $data['client'] = $this->auth_client_id;
        $this->template->load('client', 'terminal/result', $data);
    }
    
    public function process()
    { 
        //handle the payment and processing fees
        $model = $this->terminal_model->getProcessingModel();
        $payment = $this->input->post("payment");
        $processing_fee = 0;
        if($model["type"] == "PCT")
        {
            $processing_fee = $payment * $model["percent"];
        }
        else if($model["type"] == "CONV")
        {
            $processing_fee = $model["conv_fee"];
            $payment += $processing_fee; 
        }
        else if($model["type"] == "MIX")
        {
            $processing_fee = ($model["percent"] * $payment) + $model["conv_fee"];
            $payment += $model["conv_fee"];
        }
       
          $api_user = $this->terminal_model->getApiKey();
          $api_pass = $this->terminal_model->getApiPass();
          $card_num = $this->input->post("card_num");
               $ccv = $this->input->post("ccv");
         $card_type = $this->input->post("type");
            $amount = $this->input->post("payment");
               $exp = $this->input->post("exp");
              $name = $this->input->post("name");
           $cust_id = $this->input->post("cust_id");
           $address = $this->input->post("address");
              $city = $this->input->post("city");
             $state = $this->input->post("state");
               $zip = $this->input->post("zip");  
          $cc_email = $this->input->post("email");
        
        $soap = new SoapClient("http://ezpay.host/Pegasys/PegasysAPI/wsdl.wsdl", array("trace"=>true, "cache_wsdl"=>WSDL_CACHE_NONE));
        $result = $soap->processCreditCard($api_user, $api_pass, "Sale", $card_num, $ccv, $card_type, $amount, $exp, $name, $cust_id, $address, $city, $state, $zip);
        
        
        //var_dump($result); 
        $resp = json_decode($result);
        //var_dump($resp); 
        
        // Add card info to credit_card_holder table
       
        $dat2['name_on_card'] = $name;
             $dat2['cc_type'] = $card_type;
        $dat2['expire_month'] = date("m", strtotime($exp));
         $dat2["expire_year"] = date("Y", strtotime($exp));
       $dat2["card_security"] = $ccv;
          $dat2["cc_address"] = $address;
            $dat2["cc_state"] = $state;
              $dat2["cc_zip"] = $zip;
            $dat2["cc_debit"] = "";
           $dat2["cc_status"] = "";
            $dat2["cc_email"] = $cc_email;
                 
        $db1 = $this->load->database($this->auth_client_id, true);
			$db1->set($dat2)
				->insert('credit_card_holder');
                     $CC_ID = $db1->insert_id();   // get last id inserted to use for card_tran table
                     
                     
        // Add transaction data to card_tran table 
                     $dat['FCC_ID'] = $CC_ID;  // this is last insert_id() (above) used as foreign key for this table.
                     $dat['CUST_ID'] = $cust_id;
                     $dat['CARD_NUM'] = $card_num;
                     $dat['DATE'] = date("Y-m-d");
                     $dat['TIME'] = date("h:i:s A");
                     $dat['AMOUNT'] = $payment;
                     $dat['STATUS'] = $resp->status;
                     $dat['AUTHID'] = $resp->authCode;
                     $dat['REFID'] = $resp->referenceId;
       
        $this->terminal_model->storeTran($dat);
        
        //Email the results to the card holder and the client
        //First, get the default email address the client wants the receipts to go
        $Appls = $this->clients_model->get_config();
                foreach ($Appls as $cc){ 
                    $cc_client_email = $cc->cc_client_email;
                }

            $Rdate = date("Y-m-d");
       // $this->load->library('email');
                    // localize post data to pass to email functions
//                    $headers = '!DOCTYPE html PUBLIC: "-//W3C//DTD XHTML 1.0 Transitional//EN"';
//                    $headers .= '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"';
//                    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/html; charset=utf-8\r\n";
                    
                        
                         $body = '<html><head><title>Online Payment</title></head>
                                    <body bgcolor="#f6f8f1 width="600" border="1" style="margin: 0; padding: 0;">
                                     <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
                                     <tr align="center">
                                       <td bgcolor="#70bbd9">Online Payment Receipt</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Card Holder: ' .$name.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Street Address: '  .$address. '</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">State: ' .$state.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Zipcode: ' .$zip.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Cust ID: ' .$cust_id.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Amount: ' .$payment.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Date: ' .$Rdate.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff">Status: ' .$resp->status.'</td>
                                      </tr>
                                      <tr>
                                       <td bgcolor="#ffffff"> AuthId: ' .$resp->authCode.'</td>
                                      </tr>
                                      <tr align="center">
                                       <td align="center" bgcolor="#ffffff">Print this for your records.</td>
                                      </tr>
                                     </table>
                                    </body>
                                    </html>';
//                $header  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=utf-8\r\n";        
//                $header .= 'From:'.$cc_client_email."\r\n";
//                $header .= 'Cc:'.$cc_client_email."\r\n";
//                $header .= 'Bcc: im.steve@live.com' . "\r\n";         

                        
                        
        
//        mail($cc_email, $sub, $body, $header);       
                        
                        
                        $subject = 'Card Payment - '.$name;
                        
                        $to = $cc_email;
                        $cc = $cc_client_email;
                     
                        
                        $headers   = array();
                        $headers[] = "MIME-Version: 1.0";
          //              $headers[] = "Content-type: text/html; charset=iso-8859-1";
                        $headers[] = "Content-type: text/html; charset=utf-8";
                        $headers[] = "From: ".$cc_client_email;
                        //$headers[] = "CC: Support ".$cc_client_email;
                        $headers[] = "Reply-To: ".$cc_client_email;
                        $headers[] = "Subject: ".$subject;
                        $headers[] = "X-Mailer: PHP/".phpversion();

mail($to, $subject, $body, implode("\r\n", $headers));  

mail($cc, $subject, $body, implode("\r\n", $headers)); 
                        
                   //If valid, email out. 
//                $this->email->from($cc_client_email);            
//                $this->email->to($cc_email);
//                $this->email->cc($cc_client_email);
//                $this->email->bcc('im.steve@live.com');
//                $this->email->subject($sub);
//                //$this->email->set_header('!DOCTYPE html PUBLIC', '"-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"');
//               // $this->email->set_header('MIME-Version:', '1.0');
//                $this->email->set_header('Content-Type', 'text/html; charset=utf-8');
//               // $this->email->set_header('headers',$headers);
//                $this->email->message($msg);
//                $this->email->send();
        
        
        $data = array();
        $data["title"] = "Pagasys Virtual Terminal";
        $data["headline"] = "Virtual Terminal";
        $data['user'] = $this->auth_user_id;
        $data['client'] = $this->auth_client_id;
        $data["result"] = $result;
        $data["exp"] = $exp;
        $data["status"] = "Approved";// $resp->status == "PendingSettlement" ? "Approved" : "Decline";
        $data["refId"] = rand(10000, 99999); //$resp->referenceId;
        $data["auth"] = rand(20000, 120000);
        $this->template->load('client', 'terminal/result', $data);
        
        
    }
    
    public function processCheck()
    {
        //handle the payment and processing fees
        $model = $this->terminal_model->getProcessingModel();
        $payment = $this->input->post("payment");
        $processing_fee = 0;
        if($model["type"] == "PCT")
        {
            $processing_fee = $payment * $model["percent"];
        }
        else if($model["type"] == "CONV")
        {
            $processing_fee = $model["conv_fee"];
            $payment += $processing_fee; 
        }
        else if($model["type"] == "MIX")
        {
            $processing_fee = ($model["percent"] * $payment) + $model["conv_fee"];
            $payment += $model["conv_fee"];
        }
       
         $api_user = $this->terminal_model->getApiKey();
         $api_pass = $this->terminal_model->getApiPass();
         $bank = $this->input->post("bank");
         $routing = $this->input->post("routing");
         //$card_type = $this->input->post("type");
         $amount = $this->input->post("payment");
         //$exp = $this->input->post("exp");
         $name = $this->input->post("name");
         $cust_id = $this->input->post("cust_id");
         $address = $this->input->post("address");
         $city = $this->input->post("city");
         $state = $this->input->post("state");
         $zip = $this->input->post("zip1");  
        
        
        $soap = new SoapClient("http://ezpay.host/Pegasys/PegasysAPI/wsdl.wsdl", array("trace"=>true, "cache_wsdl"=>WSDL_CACHE_NONE));
       // $result = $soap->processCreditCard($api_user, $api_pass, "Sale", $card_num, $ccv, $card_type, $amount, $exp, $name, $cust_id, $address, $city, $state, $zip);
        
        //var_dump($result); 
        //$resp = json_decode($result);
        
        $data = array();
        $data["title"] = "Pagasys Virtual Terminals";
        $data["headline"] = "Virtual Terminal"; 
        $data['user'] = $this->auth_user_id;
        $data['client'] = $this->auth_client_id;
       // $data["result"] = $result;
        //$data["exp"] = $exp;
        $data["status"] = "Approved";// $resp->status == "PendingSettlement" ? "Approved" : "Decline";
        $data["refId"] = rand(10000, 99999); //$resp->referenceId;
        $data["auth"] = rand(20000, 120000);
        $this->template->load('client', 'terminal/result', $data); 
    }

}
