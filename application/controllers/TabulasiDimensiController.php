<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TabulasiDimensiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}

		$this->load->library('form_validation');
		$this->load->model('PertanyaanKualitatif_model');
	}

	public function index($id1, $id2)
	{

		$url = $this->uri->uri_string();
		$this->session->set_userdata('urlback', $url);

		$this->data = [];
		$this->data['title'] = 'Indeks Per Dimensi Survei';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$manage_survey = $this->db->get_where("manage_survey", ['manage_survey.slug' => $this->uri->segment(2)])->row();
		$this->data['table_identity'] = $manage_survey->table_identity;
		$this->data['skala_likert'] = 100 / ($manage_survey->skala_likert == 5 ? 5 : 4);
		// var_dump($id_manage_survey);


		return view('tabulasi_dimensi/index', $this->data);
	}

	
}

/* End of file PertanyaanKualitatifController.php */
/* Location: ./application/controllers/PertanyaanKualitatifController.php */