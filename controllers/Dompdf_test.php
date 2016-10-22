<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dompdf_test extends MY_Controller {

	/**
	 * Example: DOMPDF 
	 *
	 * Documentation: 
	 * http://code.google.com/p/dompdf/wiki/Usage
	 *
	 */
	public function index() {	
		// Load all views as normal
		$this->load->view('pages/privacy');
                //
//                $this->is_logged_in();
//            $data = array(
//                    'title'     =>   'EZ Pay Privacy!',
//                    'description' => 'EZ Pay offers products that allow integration into your software system to process your customer payments.'
//                    );
//            $this->template->load('default', 'pages/privacy', $data);
		// Get output html
		$html = $this->output->get_output();
		
		// Load library
                
		$this->load->library('dompdf_gen');
               
		
		// Convert to PDF
		$this->dompdf->load_html($html);
		$this->dompdf->render();
              //  $this->load->view('pages/privacy');
		//$this->dompdf->stream("welcome.pdf");
		$this->dompdf->stream("Privacy.pdf");
	}
}
