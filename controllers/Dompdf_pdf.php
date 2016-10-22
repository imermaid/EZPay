<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dompdf_pdf extends MY_Controller {

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

		// Get output html
		$html = $this->output->get_output();
		
		// Load library
                
		$this->load->library('dompdf_gen');

		// Convert to PDF
		$this->dompdf->load_html($html);
		$this->dompdf->render();

		$this->dompdf->stream("Privacy.pdf");
	}
        
        
        public function privacy() {	
		// Load all views as normal
		$this->load->view('pages/privacy');

		// Get output html
		$html = $this->output->get_output();
		
		// Load library
                
		$this->load->library('dompdf_gen');

		// Convert to PDF
		$this->dompdf->load_html($html);
		$this->dompdf->render();

		$this->dompdf->stream("Privacy.pdf");
	}
        
         public function pdf_holiday() {	
		// Load all views as normal
		$this->load->view('applications/pdf_holiday');

		// Get output html
		$html = $this->output->get_output();
		
		// Load library
                
		$this->load->library('dompdf_gen');

		// Convert to PDF
		$this->dompdf->load_html($html);
		$this->dompdf->render();

		$this->dompdf->stream("Holidays.pdf");
	}
        
         public function pdf_settlement() {	
		// Load all views as normal
		$this->load->view('applications/settlement');

		// Get output html
		$html = $this->output->get_output();
		
		// Load library
                
		$this->load->library('dompdf_gen');

		// Convert to PDF
		$this->dompdf->load_html($html);
		$this->dompdf->render();

		$this->dompdf->stream("Settlement.pdf");
	}
}
