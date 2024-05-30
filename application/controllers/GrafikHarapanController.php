<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GrafikHarapanController extends Client_Controller
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
		$this->data['title'] = "Grafik Pertanyaan Harapan";

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$this->data['table_identity'] = $this->data['manage_survey']->table_identity;
		$table_identity = $this->data['table_identity'];

		//PENDEFINISIAN SKALA LIKERT
		$this->data['skala_likert'] = 100 / ($this->data['manage_survey']->skala_likert == 5 ? 5 : 4);
		$this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");



		$this->data['unsur_pelayanan'] = $this->db->query("SELECT *, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan, SUBSTRING(nomor_unsur, 2, 4) AS nomor_harapan, (SELECT isi_pertanyaan_unsur FROM pertanyaan_unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = unsur_pelayanan_$table_identity.id) as isi_pertanyaan_unsur
		FROM unsur_pelayanan_$table_identity
		WHERE id_parent = 0");

		$this->data['get_pilihan_jawaban'] = $this->db->query("SELECT *,
		(SELECT id_unsur_pelayanan FROM pertanyaan_unsur_pelayanan_$table_identity WHERE nilai_tingkat_kepentingan_$table_identity.id_pertanyaan_unsur_pelayanan = pertanyaan_unsur_pelayanan_$table_identity.id) AS id_unsur_pelayanan,
		(SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden 
		WHERE jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur = nilai_tingkat_kepentingan_$table_identity.id_pertanyaan_unsur_pelayanan && nilai_tingkat_kepentingan_$table_identity.nomor_tingkat_kepentingan = jawaban_pertanyaan_harapan_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,
		(SELECT COUNT(id) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_pengisi
		FROM nilai_tingkat_kepentingan_$table_identity");


		$this->data['rekap_turunan_unsur'] = $this->db->query("SELECT *, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur_pelayanan,
		(SELECT COUNT(skor_jawaban)
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 1) AS perolehan_1,
		(SELECT COUNT(skor_jawaban)
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 2) AS perolehan_2,
		(SELECT COUNT(skor_jawaban)
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 3) AS perolehan_3,
		(SELECT COUNT(skor_jawaban)
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 4) AS perolehan_4,
		(SELECT COUNT(skor_jawaban)
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 5) AS perolehan_5,
		(SELECT COUNT(id) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_pengisi,
		(SELECT AVG(skor_jawaban)
		FROM jawaban_pertanyaan_harapan_$table_identity
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) AS rata_rata
		FROM unsur_pelayanan_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");


		if ($this->db->get_where('survey_' . $this->data['table_identity'], array('is_submit' => 1))->num_rows() == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}

		return view('grafik_harapan/index', $this->data);
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


	public function _get_data_profile($id1, $id2)
	{
		$this->db->select('*');
		$this->db->from('users');
		$this->db->join('users_groups', 'users.id = users_groups.user_id');
		$this->db->where('users.username', $this->session->userdata('username'));
		$data_user = $this->db->get()->row();
		// $user_identity = 'drs' . $data_user->is_parent;

		$this->db->select('users.username, manage_survey.survey_name, is_question, manage_survey.slug, manage_survey.description, manage_survey.is_privacy, manage_survey.table_identity, manage_survey.id_jenis_pelayanan, manage_survey.atribut_pertanyaan_survey');
		// if ($data_user->group_id == 2) {
			$this->db->from('users');
			$this->db->join('manage_survey', 'manage_survey.id_user = users.id');
		/*} else {
			$this->db->from('manage_survey');
			$this->db->join("supervisor_$user_identity", "manage_survey.id_berlangganan = supervisor_$user_identity.id_berlangganan");
			$this->db->join("users", "supervisor_$user_identity.id_user = users.id");
		}*/
		$this->db->where('users.username', $id1);
		$this->db->where('manage_survey.slug', $id2);
		$profiles = $this->db->get();

		if ($profiles->num_rows() == 0) {
			// echo 'Survey tidak ditemukan atau sudah dihapus !';
			// exit();
			show_404();
		}
		return $profiles->row();
	}
}

/* End of file VisualisasiController.php */
/* Location: ./application/controllers/VisualisasiController.php */