<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TabulasiAspekController extends Client_Controller
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
		$this->data = [];
		$this->data['title'] = 'Tabulasi Per Aspek & Dimensi';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$manage_survey = $this->db->get_where("manage_survey", ['manage_survey.slug' => $this->uri->segment(2)])->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['table_identity'] = $manage_survey->table_identity;
		$this->data['skala_likert'] = 100 / ($manage_survey->skala_likert == 5 ? 5 : 4);
		// var_dump($id_manage_survey);

		//CEK RESPONDEN
		if ($this->db->get_where("survey_$table_identity", ['is_submit' => 1])->num_rows() == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}


		$this->data['unsur'] = $this->db->query("SELECT *, SUBSTR(nomor_unsur,2) AS nomor_harapan
		FROM unsur_pelayanan_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan");
		$this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");


		$this->data['chart_aspek'] = $this->db->query("SELECT *,
		(SELECT nama_aspek FROM aspek_$table_identity WHERE id_aspek = aspek_$table_identity.id) AS nama_aspek,
		AVG(rata_rata) AS skor

		FROM (
		SELECT unsur_pelayanan_$table_identity.id,
		(SELECT id_aspek FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_aspek,
		(SELECT id FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_dimensi,
		nama_unsur_pelayanan,
		AVG(skor_jawaban) AS rata_rata


		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1
		GROUP BY IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent)
		) as_$table_identity

		GROUP BY id_aspek");



		$this->data['chart_dimensi'] = $this->db->query("SELECT *,
		(SELECT dimensi FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS dimensi,
		AVG(rata_rata) AS skor

		FROM (
		SELECT unsur_pelayanan_$table_identity.id,
		(SELECT id_aspek FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_aspek,
		(SELECT id FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_dimensi,
		nama_unsur_pelayanan,
		AVG(skor_jawaban) AS rata_rata


		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1
		GROUP BY IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent)
		) as_$table_identity

		GROUP BY id_dimensi");


		$this->data['chart_unsur'] = $this->db->query("SELECT *,
		(SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id = as_$table_identity.id_parent) AS nomor_unsur,
		(SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id = as_$table_identity.id_parent) AS nama_unsur_pelayanan
		
		FROM (SELECT 
		
		(SELECT id_aspek FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_aspek,
		(SELECT id FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_dimensi,
		AVG(skor_jawaban) AS rata_rata,
		IF(id_parent = 0, unsur_pelayanan_$table_identity.id, id_parent) AS id_parent
		
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE survey_$table_identity.is_submit = 1
		GROUP BY IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent)
		) as_$table_identity");



		if ($manage_survey->is_aspek == 1) {
			return view('tabulasi_aspek/index_aspek', $this->data);
		} else {
			return view('tabulasi_aspek/index_dimensi', $this->data);
		}
	}


	public function ajax_list($id1, $id2, $id3)
	{
		$get_identity = $this->db->get_where('manage_survey', ['slug' => "$id2"])->row();
		$table_identity = $get_identity->table_identity;


		$list = $this->models->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			$no++;
			$row = array();
			$row[] = $no;
			$row[] = 'Responden ' . $no;

			foreach ($this->db->query("SELECT id_responden, skor_jawaban, IF(id_parent = 0, id_unsur_pelayanan, id_parent) AS id_parent
			FROM jawaban_pertanyaan_unsur_$table_identity
			JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
			JOIN unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan
			WHERE id_responden = $value->id_responden && IF(id_parent = 0, id_unsur_pelayanan, id_parent) = $id3")->result() as $get_unsur) {

				$row[] = $get_unsur->skor_jawaban;
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

/* End of file PertanyaanKualitatifController.php */
/* Location: ./application/controllers/PertanyaanKualitatifController.php */
