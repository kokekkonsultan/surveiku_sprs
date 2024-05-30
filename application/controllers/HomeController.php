<?php
defined('BASEPATH') or exit('No direct script access allowed');

class HomeController extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->data = [];
		$this->data['title'] = 'Home';
		$this->data['banner'] = $this->db->get_where('banner', ['is_show' => '1']);

		$this->data['home_config'] = $this->db->query("
			SELECT
			( SELECT constant_value FROM website_constant WHERE id = 1) AS website_title,
			( SELECT constant_value FROM website_constant WHERE id = 2) AS website_description,
			( SELECT constant_value FROM website_constant WHERE id = 3) AS website_object_title,
			( SELECT constant_value FROM website_constant WHERE id = 4) AS website_object_1,
			( SELECT constant_value FROM website_constant WHERE id = 5) AS website_object_2,
			( SELECT constant_value FROM website_constant WHERE id = 6) AS website_object_3,
			( SELECT constant_value FROM website_constant WHERE id = 7) AS website_object_4
			FROM
			website_constant LIMIT 1
			")->row();

		return view('home/index', $this->data);
	}

	public function cari()
	{
		$this->data = [];
		$this->data['title'] = 'Search';

		$keyword = $this->input->post('keyword');

		$query = $this->db->query("SELECT uuid FROM manage_survey WHERE nomor_sertifikat = '$keyword'")->row();
		if ($query == NULL) {
			echo json_encode(array("statusCode" => 500));
		} else if ($query->uuid != NULL) {
			echo json_encode($query);
		}
	}

	public function validasi_sertifikat()
	{
		$this->data = [];
		$this->data['title'] = 'Validasi Sertifikat';
		$this->load->library('ion_auth');
		$this->data['data_login'] = $this->ion_auth->logged_in();

		$uuid = $this->uri->segment(2);

		$this->db->select("*, DATE_FORMAT(survey_start, '%d-%m-%Y') AS survey_mulai, DATE_FORMAT(survey_end, '%d-%m-%Y') AS survey_selesai, manage_survey.slug AS slug_manage_survey");
		$this->db->from('manage_survey');
		$this->db->join('jenis_pelayanan', 'manage_survey.id_jenis_pelayanan =  jenis_pelayanan.id', 'left');
		$this->db->join('klasifikasi_survei', 'klasifikasi_survei.id =  jenis_pelayanan.id_klasifikasi_survei', 'left');
		$this->db->join('sampling', 'manage_survey.id_sampling =  sampling.id', 'left');
		$this->db->where("manage_survey.uuid = '$uuid'");
		$manage_survey = $this->db->get()->row();
		$this->data['manage_survey'] = $manage_survey;


		//PENDEFINISIAN SKALA LIKERT
		$skala_likert = 100 / ($manage_survey->skala_likert == 5 ? 5 : 4);
		$this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$manage_survey->table_identity ORDER BY id DESC");


		//RATA-RATA BOBOT
		$this->db->select("nama_unsur_pelayanan, IF(id_parent = 0,unsur_pelayanan_$manage_survey->table_identity.id, unsur_pelayanan_$manage_survey->table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))) AS nilai, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$manage_survey->table_identity WHERE id_parent = 0)) AS rata_rata_bobot");
		$this->db->from('jawaban_pertanyaan_unsur_' . $manage_survey->table_identity);
		$this->db->join("pertanyaan_unsur_pelayanan_$manage_survey->table_identity", "jawaban_pertanyaan_unsur_$manage_survey->table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id");
		$this->db->join("unsur_pelayanan_$manage_survey->table_identity", "pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id_unsur_pelayanan = unsur_pelayanan_$manage_survey->table_identity.id");
		$this->db->join("survey_$manage_survey->table_identity", "jawaban_pertanyaan_unsur_$manage_survey->table_identity.id_responden = survey_$manage_survey->table_identity.id_responden");
		$this->db->where("survey_$manage_survey->table_identity.is_submit = 1");
		$this->db->group_by('id_sub');
		$rata_rata_bobot = $this->db->get();

		foreach ($rata_rata_bobot->result() as $rata_rata_bobot) {
			$nilai_bobot[] = $rata_rata_bobot->rata_rata_bobot;
			$nilai_tertimbang = array_sum($nilai_bobot);
			$this->data['ikm'] = ROUND($nilai_tertimbang * $skala_likert, 10);
		}


		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('id = ', $manage_survey->id_user);
		$this->data['user'] = $this->db->get()->row();

		//TAMPILKAN PROFIL RESPONDEN
		$this->data['profil'] = $this->db->query("SELECT *,  REPLACE(LOWER(nama_profil_responden), ' ', '_') AS nama_alias, UPPER(nama_profil_responden) AS nama_profil FROM profil_responden_$manage_survey->table_identity WHERE jenis_isian = 1");

		//JUMLAH KUISIONER
		$this->db->select('COUNT(id) AS id');
		$this->db->from('survey_' . $manage_survey->table_identity);
		$this->db->where("is_submit = 1");
		$this->data['jumlah_kuisioner'] = $this->db->get()->row()->id;

		return view('home/validasi_sertifikat', $this->data);
	}

	public function about()
	{
		$this->data = [];
		$this->data['title'] = 'About';

		return view('home/about', $this->data);
	}

	public function team()
	{
		$this->data = [];
		$this->data['title'] = 'Team';

		return view('home/team', $this->data);
	}

	public function contact()
	{
		$this->data = [];
		$this->data['title'] = 'Contact';

		return view('home/contact', $this->data);
	}

	public function privacy()
	{
		$this->data = [];
		$this->data['title'] = 'Privacy';

		return view('home/privacy', $this->data);
	}

	public function legal()
	{
		$this->data = [];
		$this->data['title'] = 'Legal';

		return view('home/legal', $this->data);
	}
}

/* End of file HomeController.php */
/* Location: ./application/controllers/HomeController.php */