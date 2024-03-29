<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Questionnaire extends CI_Controller {
	function __construct() {
		parent::__construct(); 
		$this->load->helper(array('form', 'url'));
		$this->load->Model('QuestionnaireModel');
		$this->load->library('session');
		$this->load->library('pagination');
    }
	
	public function index(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		
		//$this->load->view('questionnaire/view_questionnaire');
		
		$user_id = $this->session->userdata('user_id');	
		
		$search = $this->input->post('search');
		if($search){
			$total_records = $this->QuestionnaireModel->get_filter($user_id,$search);
		}else {
			$total_records = $this->QuestionnaireModel->get_total($user_id);
		}
					
			// init params
			$params = array();
			$limit_per_page = 6;
			$start_index = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
			$page = $start_index / $limit_per_page + 1;
	 
			if ($total_records > 0) {
				// get current page records
				if($search){
					$params['questionnaire']=$this->QuestionnaireModel->get_current_page_records_filter($user_id, $limit_per_page, $start_index, $search);
				}else{
					$params['questionnaire']=$this->QuestionnaireModel->get_current_page_records($user_id, $limit_per_page, $start_index);
				}
		
				$config['base_url'] = base_url().'Questionnaire/index';
				$config['total_rows'] = $total_records;
				$config['per_page'] = $limit_per_page;
				$config["uri_segment"] = 3;

				//bootstrap class
				$config['first_link']       = '<i class="fe fe-chevrons-left"></i>';
				$config['last_link']        = '<i class="fe fe-chevrons-right"></i>';
				$config['next_link']        = '<i class="fe fe-chevron-right"></i>';
				$config['prev_link']        = '<i class="fe fe-chevron-left"></i>';
				$config['full_tag_open']    = '<div class="pagging text-center"><nav style="border-right:0"><ul class="pagination justify-content-center">';
				$config['full_tag_close']   = '</ul></nav></div>';
				$config['num_tag_open']     = '<li class="page-item"><span class="page-link">';
				$config['num_tag_close']    = '</span></li>';
				$config['cur_tag_open']     = '<li class="page-item active"><span class="page-link">';
				$config['cur_tag_close']    = '<span class="sr-only">(current)</span></span></li>';
				$config['next_tag_open']    = '<li class="page-item"><span class="page-link">';
				$config['next_tagl_close']  = '<span aria-hidden="true">&raquo;</span></span></li>';
				$config['prev_tag_open']    = '<li class="page-item"><span class="page-link">';
				$config['prev_tagl_close']  = '</span>Next</li>';
				$config['first_tag_open']   = '<li class="page-item"><span class="page-link">';
				$config['first_tagl_close'] = '</span></li>';
				$config['last_tag_open']    = '<li class="page-item"><span class="page-link">';
				$config['last_tagl_close']  = '</span></li>';
				 
				$this->pagination->initialize($config);
				 
				// build paging links
				$params["links"] = $this->pagination->create_links();
				
			}
			 
			$this->load->view('questionnaire/view_questionnaire',$params);
			
	}

	public function create(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		  
		$this->load->view('questionnaire/create_questionnaire');
	}
	
	public function insert_questionnaire(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		
		$data = array(
			  "user_id" 						=> $this->session->userdata('user_id'),
			  "questionnaire_name" 				=> $this->input->post('question_name'),
			  "questionnaire_date_create" 		=> date("Y-m-d"),
			  "questionnaire_send_on"			=> $this->input->post('send_on'),
			  "questionnaire_message"			=> $this->input->post('question_email')
			);	

			//QUERY UNTUK NYIMPEN DATA Questionnaire DAN MENDAPATKAN Questionnaire_Id PALING AKHIR (ARTINYA YANG BARU DIINPUTIN) Questionnaire_Id KEMUDIAN DI SIMPEN DI VARIABLE ID
			$id = $this->QuestionnaireModel->insert_questionnaire($data);	
			//redirect ke FUNCTION create_question DENGAN PARAMETER Questionnaire_Id
			redirect(base_url("Questionnaire/create_question/".$id),'location');	
	}
		
	public function edit($id){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		
		$result['data_edit'] = $this->EventModel->view_event_by_id($id);
		$result['data_edit_event_photos'] = $this->EventModel->view_event_photos_by_id($id);
		$this->load->view('Event/edit_event',$result);
	}
	
		public function update_questionnaire(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		
		$id=$this->input->post('questionnaire_id');
		
		$data = array(
			  "questionnaire_name" 				=> $this->input->post('questionnaire_name'),
			  "questionnaire_send_on"			=> $this->input->post('questionnaire_send_date'),
			  "questionnaire_message"			=> $this->input->post('question_email_preview')
			);	
		$this->QuestionnaireModel->update_questionnaire($data,$id);
		echo "success";
	}
	
	
	public function create_question($id){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		//$user DIBUAT UNTUK MEMASTIKAN KALAU Questionnaire YANG MAU DI BUAT QUESTIONNYA ITU DARI USER YANG BENER
		$user = $this->session->userdata('user_id');
		$result['data_questionnaire'] = $this->QuestionnaireModel->view_question_option($id,$user);
		$this->load->view('questionnaire/create_question',$result);
	}
	
	public function insert_question(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		
		$data_question = array(
			  "questionnaire_id" 				=> $this->input->post('questionnaire_id'),
			  "question"				 		=> $this->input->post('question')
		);	
		
		//QUERY UNTUK NYIMPEN DATA QUESTION DAN MENDAPATKAN QUESTION_ID PALING AKHIR (ARTINYA YANG BARU DIINPUTIN) QUESTION_ID KEMUDIAN DI SIMPEN DI VARIABLE ID_QUESTION
		$id_question = $this->QuestionnaireModel->insert_question($data_question);	
		
		//PERULANGAN BUAT INSERT KE TABEL QUESTION OPTION 
		foreach ($this->input->post('option') as $key => $option) {
			
			$data_option = array(
			  "question_id" 				=> $id_question,
			  "question_option_value"		=> $option
			);
			
			$this->QuestionnaireModel->insert_option($data_option);
			
		}
		
		echo "success";
	}
	
	public function delete_question(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}

		$id = $this->input->post('id'); 
		$data = array(
		  "question_status_delete" => $this->input->post('delete_sts')
		);	 
		$this->QuestionnaireModel->delete_question($data,$id); 
		echo "success";
	}
	
	public function view_question_by_id($id){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}		
		
		$data['data_edit']=$this->QuestionnaireModel->view_question_by_id($id);
		echo json_encode($data);
	}
	
	public function view_event_email($event_id){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}		
		
		$data['data_detail']=$this->EventModel->view_event_email($event_id);
		echo json_encode($data);
	}
	
	public function update_question(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}
		
		$id_question =  $this->input->post('edit_question_id');
		$data_question = array(
			  "question" => $this->input->post('edit_question_text')
		);	
		
		$this->QuestionnaireModel->update_question($data_question,$id_question);	
		
		//HAPUS SEMUA OPTION DENGAN ID QUESTION 
		$this->QuestionnaireModel->delete_option($id_question);
		
		//PERULANGAN INPUT BARU OPTION DENGAN ID QUESTION KE TABEL QUESTION OPTION 
		if($this->input->post('option')){
			foreach ($this->input->post('option') as $key => $option) {
				
				$data_option = array(
				  "question_id" 				=> $id_question,
				  "question_option_value"		=> $option
				);
				
				$this->QuestionnaireModel->insert_option($data_option);
				
			}
		}	
		
		echo "success";
	}
	
	public function aktif(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}

		$id = $this->input->post('id'); 
		$data = array(
		  "event_status_active" => $this->input->post('aktif_sts')
		);	 
		$this->EventModel->aktif($id,$data); 
		echo "success";
	}
	
	public function delete(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}

		$id = $this->input->post('id'); 
		$data = array(
		  "event_status_delete" => $this->input->post('delete_sts')
		);	 
		$this->EventModel->delete($id,$data); 
		echo "success";
	}
	
	public function view_questionnaire_email($questionnaire_id){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}		
		
		$user_id = $this->session->userdata('user_id');	
		$data['data_detail']=$this->QuestionnaireModel->view_questionnaire_email($questionnaire_id,$user_id);
		
		echo json_encode($data);
	}
	
	public function fill($id){
		//$user_id = 46;
		//NDY%3D
		
		//$b64_uid = urlencode(base64_encode($id));
		//$questionnaire_id_code = base64_decode(urldecode($id));
		
		$result['data_questionnaire'] = $this->QuestionnaireModel->view_questionnaire_to_fill($id);
		foreach($result['data_questionnaire'] as $questionaire){
			$questionnaire_fill_status = $questionaire->questionnaire_fill_status;
			$questionnaire_name = $questionaire->questionnaire_name;
		}
		
		if($questionnaire_fill_status==0){
			$this->load->view('questionnaire/fill_questionnaire',$result);
		}else{
			$questionnaire_name_data['questionnaire_name']=$questionnaire_name;
			$this->load->view('questionnaire/fill_questionnaire_success',$questionnaire_name_data);
		}
		
		
	}
	
	public function insert_questionnaire_result(){
				
		$send_questionnaire_id = $this->input->post('send_questionnaire_id');	
		$questionnaire_name = $this->input->post('questionnaire_name');	
		$fill_status = array(
		  "questionnaire_fill_status" => 1
		);

		$option_count = $this->input->post('option_count');
		for($i=1;$i<=$option_count;$i++) {
			
			$data_option = array(
			  "question_option_id" 				=> $this->input->post('option'.$i.''),
			  "question_option_date_filled"		=> date("Y-m-d")
			);
			
			$this->QuestionnaireModel->insert_questionnaire_result($data_option,$send_questionnaire_id,$fill_status);			
		}
		
		$questionnaire_name_data['questionnaire_name']=$questionnaire_name;
		$this->load->view('questionnaire/fill_questionnaire_success',$questionnaire_name_data);
	}
	
	public function questionnair_result($questionnaire_id){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}		
		
		$user_id = $this->session->userdata('user_id');	
		$data['data_questionnaire']=$this->QuestionnaireModel->view_questionnaire_result($questionnaire_id,$user_id);
		
		$this->load->view('questionnaire/view_questionnaire_result',$data);
	}
	
	public function delete_questionnaire(){
		if($this->session->userdata('login_status')!=="login"){
			redirect(base_url(),'location');
		}

		$id = $this->input->post('id'); 
		$data = array(
		  "questionnaire_status_delete" => $this->input->post('delete_sts')
		);	 
		$this->QuestionnaireModel->delete_questionnaire($id,$data); 
		echo "success";
	}

}

?>