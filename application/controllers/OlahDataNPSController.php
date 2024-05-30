<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OlahDataNPSController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}
		$this->load->model('OlahData_model', 'models');
	}

	public function index($id1, $id2)
	{
		$url = $this->uri->uri_string();
		$this->session->set_userdata('urlback', $url);

		$this->data = [];
		$this->data['title'] = 'Olah Data NPS';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*, manage_survey.id AS id_manage_survey');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		//$this->data['atribut_pertanyaan'] = unserialize($manage_survey->atribut_pertanyaan_survey);
		// var_dump($manage_survey);

		$this->data['benchmark_nps'] = $this->db->query("SELECT * FROM benchmark_nps_$table_identity ORDER BY id DESC");

		$this->data['nps'] = $this->db->query("SELECT * FROM pertanyaan_nps_$table_identity");

		$promoters = $this->db->query("SELECT id FROM jawaban_pertanyaan_nps_$table_identity WHERE skor_jawaban IN (9,10)")->num_rows();
		$detractors = $this->db->query("SELECT id FROM jawaban_pertanyaan_nps_$table_identity WHERE skor_jawaban IN (7,8)")->num_rows();
		$passives = $this->db->query("SELECT id FROM jawaban_pertanyaan_nps_$table_identity WHERE skor_jawaban IN (0,1,2,3,4,5,6)")->num_rows();
		$total_nps = $promoters+$detractors+$passives;
		$this->data['promoters'] = ($total_nps>0) ? round($promoters/$total_nps * 100, 2) : 0;
		$this->data['detractors'] = ($total_nps>0) ? round($detractors/$total_nps * 100, 2) : 0;
		$this->data['passives'] = ($total_nps>0) ? round($passives/$total_nps * 100, 2) : 0;
		$this->data['nilai_nps'] = $this->data['promoters']-$this->data['detractors'];

		for ($x = 0; $x <= 10; $x++) {
			$total_jawaban = $this->db->query("SELECT * FROM jawaban_pertanyaan_nps_$table_identity WHERE skor_jawaban IN (" . $x . ")")->num_rows();
			$new_chart[] = '{ label: "' . $x . '", value: "' . $total_jawaban . '" }';
		};
		$this->data['new_chart'] = implode(", ", $new_chart);


		//TOTAL
		/*$this->data['total'] = $this->db->query("SELECT SUM(skor_jawaban) AS sum_skor_jawaban
		FROM jawaban_pertanyaan_nps_$table_identity
		JOIN responden_$table_identity ON jawaban_pertanyaan_nps_$table_identity.id_responden = responden_$table_identity.id
		JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
		WHERE is_submit = 1
		GROUP BY id_pertanyaan_nps");*/

		//JUMLAH KUISIONER
		$this->db->select('COUNT(id) AS id');
		$this->db->from('survey_' . $table_identity);
		$this->db->where("is_submit = 1");
		$this->data['jumlah_kuisioner'] = $this->db->get()->row()->id;

		if ($this->data['jumlah_kuisioner'] == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}


		return view('olah_data_nps/index', $this->data);
	}

	public function ajax_list()
	{
		$slug = $this->uri->segment(2);

		$get_identity = $this->db->get_where('manage_survey', ['slug' => "$slug"])->row();
		$table_identity = $get_identity->table_identity;

		$jawaban_nps = $this->db->get("jawaban_pertanyaan_nps_$table_identity");

		$list = $this->models->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {


			$no++;
			$row = array();
			$row[] = $no;
			$row[] = 'Responden ' . $no;

			foreach ($jawaban_nps->result() as $get_nps) {
				if ($get_nps->id_responden == $value->id_responden) {
					$row[] = $get_nps->skor_jawaban;
				}
			}

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->models->count_all($table_identity),
			"recordsFiltered" => $this->models->count_filtered($table_identity),
			"data" => $data,
		);

		echo json_encode($output);
	}

}

/* End of file DataPerolehanSurveiController.php */
/* Location: ./application/controllers/DataPerolehanSurveiController.php */