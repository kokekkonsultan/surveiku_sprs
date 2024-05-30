<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OlahDataController extends Client_Controller
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
		$this->data['title'] = 'Olah Data';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*, manage_survey.id AS id_manage_survey');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['atribut_pertanyaan'] = unserialize($manage_survey->atribut_pertanyaan_survey);
		// var_dump($manage_survey);

		//PENDEFINISIAN SKALA LIKERT
		$this->data['skala_likert'] = 100 / ($manage_survey->skala_likert == 5 ? 5 : 4);
		$this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");




		$this->data['unsur'] = $this->db->query("SELECT *, SUBSTR(nomor_unsur,2) AS nomor_harapan
		FROM unsur_pelayanan_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan
		-- ORDER BY SUBSTR(nomor_unsur, 2) + 0
		");

		//TOTAL
		$this->data['total'] = $this->db->query("SELECT SUM(skor_jawaban) AS sum_skor_jawaban
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
		JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
		WHERE is_submit = 1
		GROUP BY id_pertanyaan_unsur");

		//TOTAL HARAPAN
		$this->data['total_harapan'] = $this->db->query("SELECT SUM(skor_jawaban) AS sum_skor_jawaban
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN responden_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = responden_$table_identity.id
		JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
		WHERE is_submit = 1
		GROUP BY id_pertanyaan_unsur");

		//JUMLAH KUISIONER
		$this->db->select('COUNT(id) AS id');
		$this->db->from('survey_' . $table_identity);
		$this->db->where("is_submit = 1");
		$this->data['jumlah_kuisioner'] = $this->db->get()->row()->id;

		if ($this->data['jumlah_kuisioner'] == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}


		//RATA-RATA
		$this->data['rata_rata'] = $this->db->query("SELECT (SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_unsur_$table_identity.skor_jawaban != 0, 1, NULL))) AS rata_rata

		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1
		GROUP BY id_pertanyaan_unsur");


		//RATA-RATA HARAPAN
		$this->data['rata_rata_harapan'] = $this->db->query("SELECT (SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_harapan_$table_identity.skor_jawaban != 0, 1, NULL))) AS rata_rata
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1
		GROUP BY id_pertanyaan_unsur");


		//NILAI PER UNSUR
		$this->data['nilai_per_unsur'] = $this->db->query("SELECT nama_unsur_pelayanan,
		IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub,
		
		(SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_unsur_$table_identity.skor_jawaban != 0, 1, NULL))) AS rata_rata,
		(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan,
		((SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_unsur_$table_identity.skor_jawaban != 0, 1, NULL)))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur
		
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1 GROUP BY id_sub");



		//NILAI PER HARAPAN
		$this->data['nilai_per_unsur_harapan'] = $this->db->query("SELECT nama_unsur_pelayanan,
		IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub,
		
		(SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_harapan_$table_identity.skor_jawaban != 0, 1, NULL))) AS rata_rata,
		(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan,
		((SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_harapan_$table_identity.skor_jawaban != 0, 1, NULL)))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur
		
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1 GROUP BY id_sub");



		//RATA-RATA BOBOT
		$this->data['rata_rata_bobot'] = $this->db->query("SELECT nama_unsur_pelayanan,
		IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub,
		(SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_unsur_$table_identity.skor_jawaban != 0, 1, NULL))) AS rata_rata,
		(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan,
		((SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_unsur_$table_identity.skor_jawaban != 0, 1, NULL)))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai,
		
		(((SUM(skor_jawaban)/COUNT(IF(jawaban_pertanyaan_unsur_$table_identity.skor_jawaban != 0, 1, NULL)))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$table_identity WHERE id_parent = 0)) AS rata_rata_bobot
		
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1
		GROUP BY id_sub");



		//TERTIMBANG
		$this->data['tertimbang'] = $this->db->query("SELECT IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub,
		(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan,
		(((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)))) AS tertimbang,
		((((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))))*25) AS skm
		
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1")->row();

		return view('olah_data/index', $this->data);
	}

	public function ajax_list()
	{
		$slug = $this->uri->segment(2);

		$get_identity = $this->db->get_where('manage_survey', ['slug' => "$slug"])->row();
		$table_identity = $get_identity->table_identity;

		// $jawaban_unsur = $this->db->get("jawaban_pertanyaan_unsur_$table_identity");

		$jawaban_unsur = $this->db->query("SELECT *
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		-- ORDER BY SUBSTR(nomor_unsur, 2) + 0
		");

		$list = $this->models->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			// if ($value->is_submit == 1) {
			// 	$status = '<span class="badge badge-primary">Valid</span>';
			// } else {
			// 	$status = '<span class="badge badge-danger">Tidak Valid</span>';
			// }

			$no++;
			$row = array();
			$row[] = $no;
			// $row[] = $status;
			// $row[] = '<b>' . $value->kode_surveyor . '</b>--' . $value->first_name . ' ' . $value->last_name;
			$row[] = 'Responden ' . $no;

			foreach ($jawaban_unsur->result() as $get_unsur) {
				if ($get_unsur->id_responden == $value->id_responden) {
					$row[] = $get_unsur->skor_jawaban;
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
