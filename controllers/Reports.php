<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Reports
 *
 * @author Steve
 */

class Reports extends MY_Controller {
    
     private $limit = 30;
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
         $this->load->helper('form', 'url', 'dropdown_helper');
         $this->load->model('users_model', '', TRUE);
         $this->load->model('clients_model', '', TRUE);
         $this->load->model('applications_model', '', TRUE);
         $this->load->model('states_model', '', TRUE);
         $this->load->model('reports_model', '', TRUE);

         
        }
        
            public function index()
	{
           
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
              
              
              $this->load->helper(array('dropdown_helper','form'));
              $data["Fields"] = listFields($this->passdb, "card_tran");
              // load view 
            $data['title'] = 'Reports';
            $this->template->load('client', 'reports/reports_view', $data);
              
	}
        
        
   /****************************************************************************************************
    *                     show_report
    ******************************************************************************************************/
        
        
    public function show_report($offset=0, $order_column = '', $order_type = 'desc')
	{
           
            $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('pages/') );         
			}
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           // set default values for pagination      
              if (empty($offset)) $offset = 0; 
              if (empty($order_column)) $order_column = 'FCC_ID'; 
              if (empty($order_type)) $order_type = 'desc'; 
//TODO: check for valid column 

            
          //**************  if search by AUTHID get this $rpt   ************************    
if ($this->input->get_post('dash_search_by') == 'auth_id') { 
             
                       $table = 'card_tran';
                       $field = 'card_tran.AUTHID'; //if missing table, query is ambiguous
                       $value = $this->input->get_post('auth_id', TRUE); 
                 $data['tbl'] = $table;  // these are used to pass to pdf_creator
                 $data['fld'] = $field;  // pdf_creator
                 $data['val'] = $value;  //pdf_creator
                $order_column = $this->input->post_get('order_column', TRUE);
                  $order_type = $this->input->post_get('order_type', TRUE);
               $data['title'] = 'Search By Auth ID';
            $data['pdf_type'] = 'generic_pdf';
        $data['count_search'] = $this->reports_model->count_search($table,$field,$value);
                $count_search = $this->reports_model->count_search($table,$field,$value); // for the tab on quick search window       
            $data['headline'] = 'Search By Auth ID <span class="badge pull-right inbox-badge">' .$count_search. '</span>';
                         $rpt = $this->reports_model->search_by_id($this->limit, $offset, $table, $field, $value, $order_column, $order_type)->result();
                   $view_page = 'reports_search_by';
  }
          //***************  if search by dates, get this $rpt   **********************  
  if ($this->input->get_post('dash_search_by') == 'app_date') {      
                 $data['tab'] = 'Search By Dates';
                       $table = 'card_tran';
                       $start = $this->input->get_post('app_sdate', TRUE);
                         $end = $this->input->get_post('app_todate', TRUE); 
                $order_column = $this->input->post_get('order_column', TRUE);
                  $order_type = $this->input->post_get('order_type', TRUE);
                   // check if only one date entered, if so make both dates that same date or if both dates empty.
                         if( isset($start) && $end == "") $end = $start;
                         if( isset($end) && $start == "") $start = $end;
                         if( empty($start) && empty($end)) { 
                                 $start = date('Y-m-d');
                                   $end = date('Y-m-d');
                         }
               $data['start'] = $start;
                 $data['end'] = $end;
               $data['title'] = 'Search By Dates';
            $data['pdf_type'] = 'by_date_pdf';
        $data['count_search'] = $this->reports_model->count_start_end($start, $end);
                $count_search = $this->reports_model->count_start_end($start, $end); // for the tab on quick search window       
            $data['headline'] = 'Search By Dates <span class="badge pull-right inbox-badge">' .$count_search. '</span>';
                         $rpt = $this->reports_model->get_paged_start_end($this->limit, $offset, $table, $start, $end, $order_column, $order_type)->result();
                   $view_page = 'reports_search_by';
  }
          //***************  if search by REFID, get this $rpt   ************************    
  if ($this->input->get_post('dash_search_by') == 'ref_id') {      
                 $data['tab'] = 'Ref ID';
                       $table = 'card_tran';
                       $field = 'card_tran.REFID'; //if missing table, query is ambiguous
                       $value = $this->input->get_post('ref_id', TRUE); 
                 $data['tbl'] = $table;  // these are used to pass to pdf_creator
                 $data['fld'] = $field;  // pdf_creator
                 $data['val'] = $value;  //pdf_creator
                $order_column = $this->input->post_get('order_column', TRUE);
                  $order_type = $this->input->post_get('order_type', TRUE);
               $data['title'] = 'Search By Ref ID';
            $data['pdf_type'] = 'generic_pdf';
        $data['count_search'] = $this->reports_model->count_search($table,$field,$value);
                $count_search = $this->reports_model->count_search($table,$field,$value); // for the tab on quick search window       
            $data['headline'] = 'Search By Ref ID <span class="badge pull-right inbox-badge">' .$count_search. '</span>';
                         $rpt = $this->reports_model->search_by_id($this->limit, $offset, $table, $field, $value, $order_column, $order_type)->result();
                   $view_page = 'reports_search_by';
  }        
  
       //***************  if search by app_name, get this $rpt   ************************    
  if ($this->input->get_post('dash_search_by') == 'app_name') {      
                 $data['tab'] = 'Name';
                       $table = 'credit_card_holder';
                       $field = 'name_on_card'; //if missing table, query is ambiguous
                       $value = $this->input->get_post('card_holder_name', TRUE); 
                $data['tbln'] = $table;  // these are used to pass to pdf_creator      
                $data['fldn'] = $field;  // pdf_creator
                $data['valn'] = $value;  //pdf_creator
                $order_column = $this->input->post_get('order_column', TRUE);
                  $order_type = $this->input->post_get('order_type', TRUE);
               $data['title'] = 'Search By Name';
            $data['pdf_type'] = 'generic_pdf';
        $data['count_search'] = $this->reports_model->count_search($table,$field,$value, 'name');
                $count_search = $this->reports_model->count_search($table,$field,$value, 'name'); // for the tab on quick search window       
            $data['headline'] = 'Search By Card Holder Name <span class="badge pull-right inbox-badge">' .$count_search. '</span>';
                         $rpt = $this->reports_model->get_paged_search_card_name($this->limit, $offset, $value, $order_column, $order_type)->result();
                   $view_page = 'reports_search_by';
  } 
  switch ($this->input->get_post('side_search_by')) {
      case "trans_7_days":
          $data['title'] = 'Last 7 Days Transactions';
          $headtitle = "Last 7 Days Transactions ";
          $value = 'week'; //had to go with week instead of 7 because when month was 7 it went with the week sql.
          $runset = "transDays";
          break;
      case "trans_30_days":
          $data['title'] = 'Last 30 Days Transactions';
          $headtitle = "Last 30 Days Transactions ";
          $value = 30;
          $runset = "transDays";
          break;
      case "trans_this_month":
          $data['title'] = "This Month's Transactions";
          $headtitle = "This Month's Transactions ";
          $value = date('m');
          $runset = "transDays";
          break;
      case "trans_last_month":
          $data['title'] = "Last Month's Transactions";
          $headtitle = "Last Month's Transactions ";
          $value = date('m')-1;
          $runset = "transDays";
          break;
      case "last_30_approvals":
          $data['title'] = "Last 30 Transactions";
          $headtitle = "Last 30 Approved Transactions ";
          $value = 'PendingSettlement';
          $runset = "transType";
          break;
      case "last_30_denials":
          $data['title'] = "Last 30 Transactions";
          $headtitle = "Last 30 Denied Transactions ";
          $value = 'Denied';
          $runset = "transType";
          break;
      default:
          $runset = "dontRun";
          break;
  }
                if($runset == "transDays") { 
                       $table = 'card_tran';
                       $field = 'DATE'; //if missing table, query is ambiguous
                $data['tbl'] = $table;  // these are used to pass to pdf_creator
                $data['fld'] = $field;  // pdf_creator
                $data['val'] = $value;  //pdf_creator
            $data['pdf_type'] = 'generic_pdf';
        $data['count_search'] = $this->reports_model->count_search_days($table,$field,$value);
                $count_search = $this->reports_model->count_search_days($table,$field,$value); // for the tab on quick search window       
            $data['headline'] = $headtitle.' <span class="badge pull-right inbox-badge">' .$count_search. '</span>';
                         $rpt = $this->reports_model->transdays($this->limit, $offset, $table,$field,$value )->result();
                   $view_page = 'reports_show';
  }elseif ($runset == "transType") {
                       $table = 'card_tran';
                       $field = 'STATUS'; //if missing table, query is ambiguous
                 $data['tbl'] = $table;  // these are used to pass to pdf_creator
                 $data['fld'] = $field;  // pdf_creator
                 $data['val'] = $value;  //pdf_creator
            $data['pdf_type'] = 'generic_pdf';
                $order_column = 'FCC_ID';
                  $order_type = 'DESC';
     //   $data['count_search'] = 30;
                $count_search = 30; // for the tab on quick search window       
            $data['headline'] = $headtitle.' <span class="badge pull-right inbox-badge">' .$count_search. '</span>';
                         $rpt = $this->reports_model->search_type_limit(30, 0, $table,$field,$value, $order_column, $order_type )->result();
                   $view_page = 'reports_show'; //view_page determines which page will be opened
        }
  
  
  
  
 // generate pagination 
            $this->load->library('pagination'); 
                  $config['base_url'] = site_url('reports/show_report'); 
                $config['total_rows'] = $count_search;
                  $config['per_page'] = $this->limit;
               $config['uri_segment'] = 3; 
                    $config['suffix'] = '?'.http_build_query($_REQUEST, '', "&"); // When passing in params, built GET
                 $config['first_url'] = $config['base_url'].'?'.http_build_query($_GET); // must use if passing in params for the 1st page to work
                 $config['next_link'] = 'Next &gt;';
                 $config['prev_link'] = '&lt; Previous';
             $config['full_tag_open'] = '<div id="pagination" class="pull-right">';
            $config['full_tag_close'] = '</div>';
            $this->pagination->initialize($config); 
            $data['pagination'] = $this->pagination->create_links();               
                 
 // generate table data 
            $this->load->library('table'); 
            $this->table->set_empty("");  
            $this->table->set_heading(  
                    '<i class="h_row">#</i>',
                    '<i class="h_row">FFC_ID</i>',
                    '<i class="h_row">CUSTID</i>',
                    '<i class="h_row">Card Holder</i>',
                    '<i class="h_row">Card Number</i>',
                    '<i class="h_row">Amount</i>',
                    '<i class="h_row">Date</i>',
                    '<i class="h_row">Time</i>',
                    '<i class="h_row">Status</i>',
                    '<i class="h_row">Auth ID</i>',
                    '<i class="h_row">Ref ID</i>'

                    );        
              
            $i = 0 + $offset; 
            foreach ($rpt as $val){ 
                $EZ = substr($val->CARD_NUM, 12);
                $EZ = '************'.$EZ;
                $AMT = '$'.number_format($val->AMOUNT, 2);
                
            $this->table->add_row(
                ++$i,
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
            
            
            
      /************************ For Google Chart  ************************************************************/
            
            $cntDate[] = ''; // declare the arrays first
            $cntTrans[] = ''; //
            
            // if report is this month....
            if($this->input->get_post('side_search_by') == "trans_this_month") {
                     $timecnt = date('t', mktime(0,0,0,date('m')));  // Get the count of days in last month for the last month report
                       for($i = 1; $i<=$timecnt; $i++) {  // loop as many days in the month to build date to pass to query to count the transactions  
                   $cntDate[] = date('Y-m-d', mktime(0,0,0,date('m'), $i, date('Y'))); // every day every loop make the date for last month
                  $cntTrans[] = array($i => $this->reports_model->count_by_day('card_tran','DATE',$cntDate[$i] )); // loop thru each time to pass the day to the query to count how many transactions for that day
            $data['cntTrans'] = $cntTrans; // send the transaction count to the reports_show
             $data['timecnt'] = $timecnt;  // paas reports_show the timecnt
               }
            }
            // if report is last month.....
            if($this->input->get_post('side_search_by') == "trans_last_month") {
                     $timecnt = date('t', mktime(0,0,0,date('m')-1));  // Get the count of days in last month for the last month report
                       for($i = 1; $i<=$timecnt; $i++) {  // loop as many days in the month to build date to pass to query to count the transactions  
                   $cntDate[] = date('Y-m-d', mktime(0,0,0,date('m')-1, $i, date('Y'))); // every day every loop make the date for last month
                  $cntTrans[] = array($i => $this->reports_model->count_by_day('card_tran','DATE',$cntDate[$i] )); // loop thru each time to pass the day to the query to count how many transactions for that day
            $data['cntTrans'] = $cntTrans; // send the transaction count to the reports_show
             $data['timecnt'] = $timecnt; // paas reports_show the timecnt
              } 
            }
            
// load view 
            $data['title'] = 'Transactions Reports';
            
            $this->template->load('client', 'reports/'.$view_page, $data);
              
	}
 /****************************************************************************************************/
        
 public function pdf_creator()
 {
     
     
        $this->is_logged_in();
            if( ! isset( $this->auth_user_id ) ) {
                 redirect( secure_site_url('login') );         
			}
                        
                        $this->load->library('dompdf_gen');
                        
          // Get the individual client to list as the heading               
          if( $sql = $this->clients_model->view_record())
           { $data['cli_rec'] = $sql; } 
           
           
           if($this->input->get_post('start') ) {
               $Rec = $this->reports_model->get_pdf_start_end($this->input->get_post('start'), $this->input->get_post('end'))->result();
           }
           if($this->input->get('tbl') && $this->input->get('fld') && $this->input->get('val')) {
               $Rec = $this->reports_model->get_pdf_by_id($this->input->get('tbl'), $this->input->get('fld'), $this->input->get('val'))->result();
           }
//           if($this->input->get('app_name')) {
//               $Rec = $this->reports_model->get_pdf_by_name($this->input->get('app_name'))->result();
//           }
           if($this->input->get('tbln') && $this->input->get('fldn') && $this->input->get('valn')) {
               $Rec = $this->reports_model->get_pdf_by_name($this->input->get('tbln'), $this->input->get('fldn'), $this->input->get('valn'))->result();
           }
           
           
           
            // load data 
             // $Rec = $this->reports_model->get_pdf_creator()->result();                             
 // generate table data        
              
                    $i = 1;   //Start the counter   
            foreach ($Rec as $val){         // get all the data that will be displayed on the pdf
                $EZ = substr($val->CARD_NUM, 12);   // get only the last 4 numbers
                $EZ = '**'.$EZ;
                $AMT = '$'.number_format($val->AMOUNT, 2);  // format the amount
                $count = $i;
                $cust = $val->CUST_ID;
                
                $data['ret'][] = (object) array(    // pass it in an array (object)
                     'CUST_ID' => $cust,
                'name_on_card' => $val->name_on_card,
                     'CNumber' => $EZ,
                         'AMT' => $AMT,
                       'CDate' => $val->DATE,
                       'CTime' => $val->TIME,
                      'Status' => $val->STATUS
                );
               
               $data['total'] = $count;   // pass the number of records found
                         $i += 1;           
            }

            $data['title'] = 'Report';
		$this->load->view('pdf/pdf_reports', $data);  // load it all in the page

		// Get output html
		$html = $this->output->get_output();
		$this->dompdf->set_paper('A4','potrait');
		// Load library
		$this->load->library('dompdf_gen');
		// Convert to PDF

		$this->dompdf->load_html($html);
		$this->dompdf->render();

		$this->dompdf->stream("Report.pdf");
  
         
     }
/*************************************************************************************************/     
     public function trans_7_days($offset=0, $order_column = '', $order_type = 'desc') {
         
     }




     /**************************************************************************************************/    
    
}
