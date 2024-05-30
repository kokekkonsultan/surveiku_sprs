<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VisualisasiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}
	}


	public function index($id1 = NULL, $id2 = NULL)
	{
		$this->data = [];
		$this->data['title'] = "Visualisasi Data";

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$this->data['table_identity'] = $this->data['manage_survey']->table_identity;
		$table_identity = $this->data['table_identity'];

		if ($this->db->get_where('survey_' . $this->data['table_identity'], array('is_submit' => 1))->num_rows() == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}


		$data = [];
		foreach($this->db->query("SELECT *
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1")->result() as $row){

				$skor_jawaban = implode(", ", unserialize($row->skor_jawaban));
				$data[] = "UNION ALL
							SELECT *
							FROM kategori_unsur_pelayanan_$table_identity
							WHERE nomor_kategori_unsur_pelayanan IN ($skor_jawaban) && id_pertanyaan_unsur = $row->id_pertanyaan_unsur";
		}
		$tabel_union = implode(" ", $data);
		// var_dump($tabel_union);


		$this->data['jawaban_ganda'] = $this->db->query("SELECT id,
		id_pertanyaan_unsur,
		nomor_kategori_unsur_pelayanan,
		nama_kategori_unsur_pelayanan, 
		COUNT(id) - 1 AS perolehan,
		SUM(COUNT(id) - 1) OVER (PARTITION BY id_pertanyaan_unsur) AS total_survei
		FROM (
		SELECT * FROM kategori_unsur_pelayanan_$table_identity
		$tabel_union
		) kup_$table_identity
		WHERE id != ''
		GROUP BY id");

		$this->data['pertanyaan_unsur'] = $this->db->query("SELECT *, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur
		FROM pertanyaan_unsur_pelayanan_$table_identity
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");


		return view('visualisasi/index', $this->data);
	}


	// public function convert_chart()
	// {
	// 	$slug = $this->uri->segment(2);
	// 	$manage_survey = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(2)))->row();

	// 	$id = $this->uri->segment(5);
	// 	$img = $_POST['imgBase64'];
	// 	$img = str_replace('data:image/png;base64,', '', $img);
	// 	$img = str_replace(' ', '+', $img);
	// 	$fileData = base64_decode($img);
	// 	// $fileName = 'assets/klien/img_rekap_responden/chart_' . $id . '.png';

	// 	$fileName = 'assets/klien/survei/' . $manage_survey->table_identity . '/chart_unsur/' . $id . '.png';
	// 	file_put_contents($fileName, $fileData);

	// 	// $data = [
	// 	// 	'atribut_kuadran' => serialize(array('kuadran-' . $manage_survey->table_identity . '.png', date("d/m/Y")))
	// 	// ];
	// 	// $this->db->where('slug', $slug);
	// 	// $this->db->update('manage_survey', $data);

	// 	$msg = ['sukses' => 'Data berhasil disimpan'];
	// 	echo json_encode($msg);
	// }

}

/* End of file VisualisasiController.php */
/* Location: ./application/controllers/VisualisasiController.php */
