<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RekapUnsurIndukController extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->library('ion_auth');

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be logged in to access this page');
			redirect('auth', 'refresh');
		}
	}


	public function index()
	{
		$this->data = [];
		$this->data['title'] = 'Rekapitulasi Perolehan';


		return view('rekap_unsur_induk/index', $this->data);
	}

	public function proses()
	{
		$akun_anak = $_GET['akun_anak'];

		// echo $akun_anak;

		if ($akun_anak) {

			$this->db->select('*');
			$this->db->from('manage_survey');
			$this->db->where('manage_survey.id_user', $akun_anak);
			$this->data['manage_survey'] = $this->db->get();

			$this->data['users'] = $this->db->get_where("users", ['id' => $akun_anak])->row();
			// var_dump($this->data['manage_survey']->result());

			return view('rekap_unsur_induk/proses', $this->data);
		} else {
			echo '<div class="text-center mt-5">Silahkan pilih instansi terlebih dahulu</div>';
		}
	}


	public function detail($id)
	{
		$this->data = [];

		$this->db->select('*, (SELECT company FROM users WHERE id = manage_survey.id_user) AS company');
		$this->db->from('manage_survey');
		$this->db->where('id', $id);
		$this->data['manage_survey'] = $this->db->get()->row();
		$this->data['table_identity'] = $this->data['manage_survey']->table_identity;
		$table_identity = $this->data['table_identity'];

		if ($this->db->get_where('survey_' . $this->data['table_identity'], array('is_submit' => 1))->num_rows() == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}

		if(isset($_GET['id_wilayah'])) {
			$id_wilayah = $_GET['id_wilayah'];

			$this->data['survey'] = $this->db->query("SELECT *
			FROM jawaban_pertanyaan_unsur_$table_identity
			JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
			JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
			WHERE is_submit = 1 && responden_$table_identity.id_wilayah = $id_wilayah");

			$wilayah = $this->db->get_where("wilayah_survei_$table_identity", ['id' => $id_wilayah])->row();

			$this->data['title'] = $this->data['manage_survey']->survey_name . ' / ' . $this->data['manage_survey']->company . ' / ' . $wilayah->nama_wilayah;

		} else {
			$this->data['survey'] = $this->db->query("SELECT *
			FROM jawaban_pertanyaan_unsur_$table_identity
			JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
			WHERE is_submit = 1");

			$this->data['title'] = $this->data['manage_survey']->survey_name . ' / ' . $this->data['manage_survey']->company;
		}


		$data = [];
		foreach ($this->data['survey']->result() as $row) {

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


		return view('rekap_unsur_induk/detail', $this->data);
	}


	public function unsur_keseluruhan()
	{
		$this->data = [];
		$this->data['title'] = 'Rekap Unsur Induk Keseluruhan';


		foreach($this->db->get("manage_survey")->result() as $row){

			$array_query[] = "SELECT *,
			(SELECT COUNT(*) FROM lap_jawaban_pertanyaan_unsur_$row->table_identity
			JOIN survey_$row->table_identity ON lap_jawaban_pertanyaan_unsur_$row->table_identity.id_responden = survey_$row->table_identity.id_responden
			WHERE is_submit = 1 && lap_jawaban_pertanyaan_unsur_$row->table_identity.skor_jawaban = kategori_unsur_pelayanan_$row->table_identity.nomor_kategori_unsur_pelayanan && lap_jawaban_pertanyaan_unsur_$row->table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$row->table_identity.id_pertanyaan_unsur) AS perolehan,

			(SELECT COUNT(*) FROM lap_jawaban_pertanyaan_unsur_$row->table_identity
			JOIN survey_$row->table_identity ON lap_jawaban_pertanyaan_unsur_$row->table_identity.id_responden = survey_$row->table_identity.id_responden
			WHERE is_submit = 1 && lap_jawaban_pertanyaan_unsur_$row->table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$row->table_identity.id_pertanyaan_unsur) AS jumlah

			FROM kategori_unsur_pelayanan_$row->table_identity";
		}


		$this->data['kategori_unsur'] = $this->db->query("SELECT id, id_pertanyaan_unsur, nomor_kategori_unsur_pelayanan, nama_kategori_unsur_pelayanan, SUM(perolehan) AS perolehan, SUM(jumlah) AS total_survei
		FROM (" . implode(" UNION ALL ", $array_query) . ") lap
		GROUP BY id");

		return view('rekap_unsur_induk/index-keseluruhan', $this->data);
	}

	

	public function profil_keseluruhan()
	{
		$this->data = [];
		$this->data['title'] = 'Rekap Profil Induk Keseluruhan';


		foreach($this->db->get("manage_survey")->result() as $row){

			$array_query[] = "SELECT *,
			(CASE WHEN id_profil_responden = 11 THEN
			(SELECT COUNT(*) FROM responden_$row->table_identity JOIN survey_$row->table_identity ON responden_$row->table_identity.id = survey_$row->table_identity.id_responden WHERE kategori_profil_responden_$row->table_identity.id = responden_$row->table_identity.profil_11 && is_submit = 1)
			WHEN id_profil_responden = 12 THEN 
			(SELECT COUNT(*) FROM responden_$row->table_identity JOIN survey_$row->table_identity ON responden_$row->table_identity.id = survey_$row->table_identity.id_responden WHERE kategori_profil_responden_$row->table_identity.id = responden_$row->table_identity.profil_12 && is_submit = 1)
			ELSE 
			(SELECT COUNT(*) FROM responden_$row->table_identity JOIN survey_$row->table_identity ON responden_$row->table_identity.id = survey_$row->table_identity.id_responden WHERE kategori_profil_responden_$row->table_identity.id = responden_$row->table_identity.profil_13 && is_submit = 1)
			END) AS perolehan,

			(SELECT COUNT(*) FROM survey_$row->table_identity WHERE is_submit = 1) AS jumlah
			FROM kategori_profil_responden_$row->table_identity";
		}


		$this->data['kategori_profil'] = $this->db->query("SELECT id, id_profil_responden, nama_kategori_profil_responden, SUM(perolehan) AS perolehan, SUM(jumlah) AS total_survei
		FROM (" . implode(" UNION ALL ", $array_query) . ") kup
		GROUP BY id");


		return view('rekap_unsur_induk/_profil/index-keseluruhan', $this->data);
	}

	
	public function profil_per_bagian($id) {
		$this->data = [];
		$this->data['manage_survey'] = $this->db->get_where("manage_survey", ['table_Identity' => $id])->row();
		$this->data['table_identity'] = $this->data['manage_survey']->table_identity;
		$this->data['title'] = 'Rekap Profil Induk ' . $this->data['manage_survey']->organisasi;
		$table_identity = $this->data['table_identity'];

		$data = $this->db->get_where("profil_responden_$table_identity", ['jenis_isian' => 1])->result();
		// var_dump($data);
		return view('rekap_unsur_induk/_profil/detail', $this->data);

	}


	public function terbuka_keseluruhan()
	{
		$this->data = [];
		$this->data['title'] = 'Rekap Pertanyaan Terbuka Induk Keseluruhan';


		foreach($this->db->get("manage_survey")->result() as $row){

			$array_1[] = "SELECT isi_pertanyaan_ganda_$row->table_identity.*,

			(SELECT COUNT(*) FROM lap_jawaban_pertanyaan_terbuka_$row->table_identity
			JOIN survey_$row->table_identity ON lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_responden = survey_$row->table_identity.id_responden
			WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$row->table_identity.jawaban = isi_pertanyaan_ganda_$row->table_identity.pertanyaan_ganda && lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka) AS perolehan,
			
			(SELECT COUNT(*) FROM lap_jawaban_pertanyaan_terbuka_$row->table_identity
			JOIN survey_$row->table_identity ON lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_responden = survey_$row->table_identity.id_responden
			WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$row->table_identity.jawaban != '' && lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka) AS jumlah
			
			FROM isi_pertanyaan_ganda_$row->table_identity
			JOIN perincian_pertanyaan_terbuka_$row->table_identity ON isi_pertanyaan_ganda_$row->table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$row->table_identity.id";


			$array_2[] = "SELECT lap_jawaban_pertanyaan_terbuka_$row->table_identity.* FROM lap_jawaban_pertanyaan_terbuka_$row->table_identity
			JOIN survey_$row->table_identity ON lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_responden = survey_$row->table_identity.id_responden
			WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$row->table_identity.jawaban != ''";
		}


		$this->data['jawaban_ganda'] = $this->db->query("SELECT id, id_perincian_pertanyaan_terbuka, pertanyaan_ganda, SUM(perolehan) AS perolehan, SUM(jumlah) AS total_survei
		FROM (" . implode(" UNION ALL ", $array_1) . ") lap
		GROUP BY id");


		$this->data['jawaban_isian'] = $this->db->query("SELECT *
		FROM (" . implode(" UNION ALL ", $array_2) . ") isp");

		return view('rekap_unsur_induk/_terbuka/index-keseluruhan', $this->data);
	}

}

/* End of file PerolehanSurveyorController.php */
/* Location: ./application/controllers/PerolehanSurveyorController.php */