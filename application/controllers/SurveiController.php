<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SurveiController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		$cek = $this->db->get_where('manage_survey', ['slug' => $this->uri->segment(2)]);
		if ($cek->num_rows() == 0) {
			show_404();
		}

		$this->load->library('form_validation');
		$this->load->model('Survei_model');
	}

	public function index()
	{ }


	public function download_link()
	{
		$this->load->helper('download');

		$url = base_url() . 'survey/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/' . $this->uri->segment(4);
		$data = "$url";
		$name = 'Link Survey Kepuasan Pelanggan - RESUME-' . time() . '.txt';
		force_download($name, $data);
	}


	public function form_opening()
	{
		$this->data = [];
		$this->data['title'] = 'SURVEI KEPUASAN PELANGGAN';

		$data_uri = $this->uri->segment('2');


		$this->db->select("*, DATE_FORMAT(survey_end, '%d %M %Y') AS survey_selesai, IF(CURDATE() > survey_end,1,NULL) AS survey_berakhir, IF(CURDATE() < survey_start ,1,NULL) AS survey_belum_mulai");
		$this->db->from('manage_survey');
		$this->db->join('users', 'manage_survey.id_user = users.id');
		$this->db->where("slug = '$data_uri'");
		$manage_survey = $this->db->get()->row();
		$this->data['judul'] = $manage_survey;
		$this->data['manage_survey'] = $manage_survey;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['title'] = $manage_survey->survey_name;
		$tanggalselesai = date('Y-m-d', strtotime('+1 days', strtotime($manage_survey->survey_end)));


		// STATUS SURVEY DI TUNDA< BLUM DIMULAI< ATAU SURVEY SUDAH SELESAI
		if ($manage_survey->is_privacy == 2) {
			return view('survei/form_setting_survey/survey_hold', $this->data);
		} elseif (date("Y-m-d") < $manage_survey->survey_start) {
			return view('survei/form_setting_survey/unopened', $this->data);
			// } elseif ((time() >= strtotime($tanggalselesai))) {
		} elseif (date("Y-m-d") >= $manage_survey->survey_end) {
			return view('survei/form_setting_survey/survey_end', $this->data);
		} elseif ($manage_survey->is_question == 1) {
			return view('survei/form_setting_survey/survey_not_question', $this->data);
		} else {
			if ($manage_survey->is_opening_survey == 'false') {
				$uuid = $this->uri->segment(3) != NULL ? $this->uri->segment(3) : '';
				redirect(base_url() . 'survei/' . $manage_survey->slug . '/data-responden/' . $uuid);
			} else {
				return view('survei/form_opening', $this->data);
			}
		}
	}



	public function getdatalayanan($id1, $id2)
	{
		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $id1);
		$manage_survey = $this->db->get()->row();

		$searchTerm = $this->input->post('searchTerm');
		$this->db->select('*');
		$this->db->from("layanan_survei_$manage_survey->table_identity");
		$this->db->where('id_kategori_layanan', $id2);
		$this->db->where("nama_layanan like '%" . $searchTerm . "%' ");
		$this->db->order_by('urutan', 'asc');
		$fetched_records = $this->db->get()->result_array();

		$data = array();
		foreach ($fetched_records as $kab) {
			$data[] = array("id" => $kab['id'], "text" => $kab['nama_layanan']);
		}


		echo json_encode($data);
	}



	public function data_responden($id1)
	{
		$this->data = [];
		$this->data['title'] = 'Data Responden';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$this->db->select("*, DATE_FORMAT(survey_end, '%d %M %Y') AS survey_selesai, IF(CURDATE() > survey_end,1,NULL) AS survey_berakhir, IF(CURDATE() < survey_start ,1,NULL) AS survey_belum_mulai");
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;
		$tanggalselesai = date('Y-m-d', strtotime('+1 days', strtotime($manage_survey->survey_end)));



		if ($this->uri->segment(4) == NULL) {
			$this->data['form_action'] = base_url() . 'survei/' . $this->uri->segment(2) . '/add-data-responden/';
			$this->data['surveyor'] = 0;
		} else {
			$this->data['form_action'] = base_url() . 'survei/' . $this->uri->segment(2) . '/add-data-responden/' . $this->uri->segment(4);
			$this->data['surveyor'] = $this->db->get_where('surveyor', array('uuid' => $this->uri->segment(4)))->row()->id;
		}

		//LOAD PROFIL RESPONDEN
		$this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity ORDER BY IF(urutan != '',urutan,id) ASC");

		//LOAD KATEGORI PROFIL RESPONDEN JIKA PILIHAN GANDA
		$this->data['kategori_profil_responden'] = $this->db->get('kategori_profil_responden_' . $table_identity);

		$this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'trim|required');

		foreach ($this->data['profil_responden']->result() as $get) {
			$this->form_validation->set_rules("$get->nama_alias", "$get->nama_profil_responden", 'trim|required');
		}



		$this->data['id_layanan_survei'] = [
			'name'         => 'id_layanan_survei',
			'id'         => 'id_layanan_survei',
			'options'     => $this->Survei_model->dropdown_layanan_survei($table_identity),
			'selected'     => $this->form_validation->set_value('id_layanan_survei'),
			'class'     => "form-control",
			'required' => 'required',
		];

		$this->data['id_wilayah'] = [
			'name'         => 'id_wilayah',
			'id'         => 'id_wilayah',
			'options'     => $this->Survei_model->dropdown_wilayah($table_identity),
			'selected'     => $this->form_validation->set_value('id_wilayah'),
			'class'     => "form-control",
			'required' => 'required',
		];


		//STATUS SURVEY DI TUNDA< BLUM DIMULAI< ATAU SURVEY SUDAH SELESAI
		if ($manage_survey->is_privacy == 2) {
			return view('survei/form_setting_survey/survey_hold', $this->data);
		} elseif (date("Y-m-d") < $manage_survey->survey_start) {
			return view('survei/form_setting_survey/unopened', $this->data);
			//} elseif (date("Y-m-d") >= $manage_survey->survey_end) {
		} elseif ((time() >= strtotime($tanggalselesai))) {
			return view('survei/form_setting_survey/survey_end', $this->data);
		} elseif ($manage_survey->is_question == 1) {
			return view('survei/form_setting_survey/survey_not_question', $this->data);
		} else {
			return view('survei/data_responden/form_data_responden', $this->data);
		}
	}

	public function add_data_responden($id1)
	{
		$this->db->select("*, DATE_FORMAT(survey_end, '%d %M %Y') AS survey_selesai, IF(CURDATE() > survey_end,1,NULL) AS survey_berakhir, IF(CURDATE() < survey_start ,1,NULL) AS survey_belum_mulai");
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;

		//LOAD PROFIL RESPONDEN
		$this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity");

		$atribut_pertanyaan = unserialize($manage_survey->atribut_pertanyaan_survey);

		$this->load->library('uuid');
		$input 	= $this->input->post(NULL, TRUE);

		//INSERT DATA RSEPONDEN
		$object = [
			'uuid' => $this->uuid->v4(),
			'id_layanan_survei' => $this->input->post('id_layanan_survei'),
			'id_wilayah' => $this->input->post('id_wilayah'),
			// 'nama_lengkap' 	=> $this->input->post('nama_lengkap'),
			// 'created_at' => date("Y/m/d H:i:s")
		];

		// $data_paket = $this->cek_paket();
		// if ($data_paket[0] > $data_paket[1]) {
		// 	$is_submit = '3';
		// } else {
		// 	$is_submit = '2';
		// }

		foreach ($this->data['profil_responden']->result() as $row) {
			$object[$row->nama_alias] = $this->input->post($row->nama_alias);

			if ($row->is_lainnya == 1) {
				$nama_lainnya = $row->nama_alias . '_lainnya';
				$object[$nama_lainnya] = $this->input->post($nama_lainnya);
			}
		}
		$this->db->insert("responden_$table_identity", $object);

		//INSERT SURVEY
		$id_responden = $this->db->insert_id();
		$value = [
			'uuid' => $this->uuid->v4(),
			'id_responden' 	=> $id_responden,
			'id_surveyor' 	=> $_POST['id_surveyor'],
			// 'is_submit' 	=> $is_submit,
			'is_submit' 	=> 2,
			'waktu_isi' => date("Y/m/d H:i:s"),
			'is_end' => '* Berakhir di Data Responden'
		];
		$this->db->insert("survey_$table_identity", $value);


		//INSERT ID PERTANYAAN UNSUR
		$get_pertanyaan_unsur = $this->db->query("SELECT id FROM pertanyaan_unsur_pelayanan_$table_identity");

		$result = array();
		foreach ($get_pertanyaan_unsur->result() as $key => $value) {
			$result[] = array(
				'id_responden' 				=> $id_responden,
				'id_pertanyaan_unsur' 		=> $value->id,
				'skor_jawaban' => 'a:1:{i:0;s:0:"";}'
			);
		}
		$this->db->insert_batch('jawaban_pertanyaan_unsur_' . $table_identity, $result);


		//PENGECEKAN PERTANYAAN HARAPAN
		if (in_array(1, $atribut_pertanyaan)) {
			$this->db->insert_batch('jawaban_pertanyaan_harapan_' . $table_identity, $result);
		}

		//PENGECEKAN PERTANYAAN TERBUKA
		if (in_array(2, $atribut_pertanyaan)) {

			//INSERT ID PERTANYAAN TERBUKA
			$get_pertanyaan_terbuka = $this->db->query("SELECT id FROM pertanyaan_terbuka_$table_identity ORDER BY id asc");

			$ambil = array();
			foreach ($get_pertanyaan_terbuka->result() as $key => $row) {
				$ambil[] = array(
					'id_responden' 				=> $id_responden,
					'id_pertanyaan_terbuka' 	=> $row->id,
					'jawaban' => 'a:1:{i:0;s:0:"";}'
				);
			}
			$this->db->insert_batch('jawaban_pertanyaan_terbuka_' . $table_identity, $ambil);
		}

		//PENGECEKAN PERTANYAAN KUALITATIF
		if (in_array(3, $atribut_pertanyaan)) {

			//INSERT ID PERTANYAAN KUALITATIF
			$get_pertanyaan_kualitatif = $this->db->query("SELECT id FROM pertanyaan_kualitatif_$table_identity");

			$get_value = array();
			foreach ($get_pertanyaan_kualitatif->result() as $key => $row) {
				$get_value[] = array(
					'id_responden' 				=> $id_responden,
					'id_pertanyaan_kualitatif' 	=> $row->id,
					'is_active' => '1'
				);
			}
			$this->db->insert_batch('jawaban_pertanyaan_kualitatif_' . $table_identity, $get_value);
		}


		//PENGECEKAN PERTANYAAN NPS
		if (in_array(4, $atribut_pertanyaan)) {

			//INSERT ID PERTANYAAN NPS
			$get_pertanyaan_nps = $this->db->query("SELECT id FROM pertanyaan_nps_$table_identity");
			$get_nps = [];
			foreach ($get_pertanyaan_nps->result() as $key => $row) {
				$get_nps[] = array(
					'id_responden' 				=> $id_responden,
					'id_pertanyaan_nps' 	=> $row->id,
				);
			}
			$this->db->insert_batch('jawaban_pertanyaan_nps_' . $table_identity, $get_nps);
		}


		$get_uuid_responden = $this->db->query("SELECT uuid FROM responden_$table_identity WHERE id = $id_responden")->row()->uuid;
		if ($get_uuid_responden) {

			$pesan = 'Data berhasil disimpan';
			$msg = ['sukses' => $pesan, 'uuid' => $get_uuid_responden];
			echo json_encode($msg);
		} else {

			$pesan = '<div class="alert alert-danger font-weight-bold">' . validation_errors() . '</div>';
			$msg = ['validasi' => $pesan];
			echo json_encode($msg);
		}
	}





	public function data_pertanyaan($id1)
	{
		$this->data = [];
		$this->data['title'] = 'Pertanyaan Survei';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;
		$this->data['table_identity'] = $table_identity;

		//CEK APAKAH SURVEY SUDAH DI SUBMIT APA BELUM
		$this->db->select("survey_$table_identity.is_active, is_submit, id_responden");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();

		if ($cek_data_responden->num_rows() == 0) {
			show_404();
		}

		$responden = $cek_data_responden->row();
		$this->data['id_res'] = $responden->id_responden;

		//PERTANYAAN UNSUR
		$this->data['pertanyaan_unsur'] = $this->db->query("SELECT
		id_unsur_pelayanan,
		pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur,
		isi_pertanyaan_unsur,
		unsur_pelayanan_$table_identity.nomor_unsur AS nomor,
		nama_unsur_pelayanan,
		(SELECT alasan_pilih_jawaban FROM jawaban_pertanyaan_unsur_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && id_responden = $responden->id_responden) AS alasan_jawaban,
		(SELECT skor_jawaban FROM jawaban_pertanyaan_unsur_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && id_responden = $responden->id_responden) AS skor_jawaban,
		id_dimensi,
		is_required,
		is_alasan,
		is_model_pilihan_ganda,
		limit_pilih
		
		FROM pertanyaan_unsur_pelayanan_$table_identity
		JOIN unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan
		ORDER BY pertanyaan_unsur_pelayanan_$table_identity.id ASC");


		//PERTANYAAN TERBUKA POSISI PALING ATAS
		$this->data['pertanyaan_terbuka_atas'] = $this->db->query("SELECT *,
		perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka,
		IF(is_required != '', '', 'required') AS stts_required,
		(SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya,
		(SELECT jawaban FROM jawaban_pertanyaan_terbuka_$table_identity WHERE id_pertanyaan_terbuka = pertanyaan_terbuka_$table_identity.id && id_responden = $responden->id_responden) AS jawaban,
		(SELECT jawaban_lainnya FROM jawaban_pertanyaan_terbuka_$table_identity WHERE id_pertanyaan_terbuka = pertanyaan_terbuka_$table_identity.id && id_responden = $responden->id_responden) AS jawaban_lainnya

		FROM pertanyaan_terbuka_$table_identity
		JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
		WHERE is_letak_pertanyaan = 1
		ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");



		//PERTANYAAN TERBUKA MELEKAT PADA UNSUR
		$this->data['pertanyaan_terbuka'] = $this->db->query("SELECT *,
		perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka,
		IF(is_required != '', '', 'required') AS stts_required,
		(SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya,
		(SELECT jawaban FROM jawaban_pertanyaan_terbuka_$table_identity WHERE id_pertanyaan_terbuka = pertanyaan_terbuka_$table_identity.id && id_responden = $responden->id_responden) AS jawaban,
		(SELECT jawaban_lainnya FROM jawaban_pertanyaan_terbuka_$table_identity WHERE id_pertanyaan_terbuka = pertanyaan_terbuka_$table_identity.id && id_responden = $responden->id_responden) AS jawaban_lainnya
		
		FROM pertanyaan_terbuka_$table_identity
		JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
		WHERE is_letak_pertanyaan IS NULL
		ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");



		//PERTANYAAN TERBUKA POSISI PALING BAWAH
		$this->data['pertanyaan_terbuka_bawah'] = $this->db->query("SELECT *,
		perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka,
		IF(is_required != '', '', 'required') AS stts_required,
		(SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya,
		(SELECT jawaban FROM jawaban_pertanyaan_terbuka_$table_identity WHERE id_pertanyaan_terbuka = pertanyaan_terbuka_$table_identity.id && id_responden = $responden->id_responden) AS jawaban,
		(SELECT jawaban_lainnya FROM jawaban_pertanyaan_terbuka_$table_identity WHERE id_pertanyaan_terbuka = pertanyaan_terbuka_$table_identity.id && id_responden = $responden->id_responden) AS jawaban_lainnya

		FROM pertanyaan_terbuka_$table_identity
		JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
		WHERE is_letak_pertanyaan = 2
		ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");



		//JAWABAN PERTANYAAN TERBUKA
		$this->data['jawaban_pertanyaan_terbuka'] = $this->db->query("SELECT * FROM isi_pertanyaan_ganda_$table_identity");



		if ($this->uri->segment(5) == 'edit') {
			$is_edit = '/edit';
		} else {
			$is_edit = '';
		}


		$atribut_pertanyaan = unserialize($manage_survey->atribut_pertanyaan_survey);

		//PENGECEKAN ATRIBUT
		if (in_array(1, $atribut_pertanyaan)) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-harapan/' . $uuid_responden . $is_edit;
		} else if (in_array(3, $atribut_pertanyaan)) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-kualitatif/' . $uuid_responden . $is_edit;
		} else if (in_array(4, $atribut_pertanyaan)) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-nps/' . $uuid_responden . $is_edit;
		} else if ($manage_survey->is_saran == 1) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/saran/' . $uuid_responden . $is_edit;
		} else {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/add-konfirmasi/' . $uuid_responden . $is_edit;
		}


		if ($this->uri->segment(5) == 'edit') {
			return view('survei/pertanyaan_unsur/form_pertanyaan', $this->data);

		} elseif (($responden->is_submit == 2) || ($responden->is_submit == 3)) {

			if($manage_survey->is_dimensi == 1){
				return view('survei/pertanyaan_unsur/form_pertanyaan_in_dimensi', $this->data);

			} else {
				return view('survei/pertanyaan_unsur/form_pertanyaan', $this->data);
			}
			
		} else {
			redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $uuid_responden, 'refresh');
		}
	}



	public function add_pertanyaan($id1)
	{
		$this->data = array();
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;

		$this->db->select('*');
		$this->db->from('responden_' . $table_identity);
		$this->db->where('uuid', $uuid_responden);
		$id_res = $this->db->get()->row()->id;

		$this->db->select('*');
		$this->db->from('pertanyaan_unsur_pelayanan_' . $table_identity);
		$get_data = $this->db->get();

		$result = array();
		$no = 1;
		foreach ($get_data->result() as $key => $val) {


			if(isset($_POST['jawaban_pertanyaan_unsur'][$no])){
				$jawaban_pertanyaan_unsur = serialize($_POST['jawaban_pertanyaan_unsur'][$no]);
			} else {
				$jawaban_pertanyaan_unsur = 'a:1:{i:0;s:0:"";}';
			}


			$id_pertanyaan_unsur = $_POST['id_pertanyaan_unsur'][$no];
			$object = [
				'skor_jawaban' 	=> $jawaban_pertanyaan_unsur,
				'is_active' => 1
			];

			$this->db->where('id_responden', $id_res);
			$this->db->where('id_pertanyaan_unsur', $id_pertanyaan_unsur);
			$this->db->update('jawaban_pertanyaan_unsur_' . $table_identity, $object);

			$no++;
		}




		$atribut_pertanyaan = unserialize($manage_survey->atribut_pertanyaan_survey);

		//PENGECEKAN ATRIBUT PERTANYAAN TERBUKA
		if (in_array(2, $atribut_pertanyaan)) {

			$this->db->select('*');
			$this->db->from('pertanyaan_terbuka_' . $table_identity);
			// $this->db->where("is_letak_pertanyaan != 3");
			$get_data_terbuka = $this->db->get();

			$data = array();
			// $n = 1;
			foreach ($get_data_terbuka->result() as $key) {

				$id_pertanyaan_terbuka[$key->id] = $_POST['id_pertanyaan_terbuka'][$key->id];

				if (isset($_POST['jawaban_pertanyaan_terbuka'][$key->id])) {
					$get_terbuka[$key->id] = serialize($_POST['jawaban_pertanyaan_terbuka'][$key->id]);
				} else {
					//$get_terbuka[$key->id] = '';
					$get_terbuka[$key->id] = 'a:1:{i:0;s:0:"";}';
				};

				if ($get_terbuka[$key->id] == 'a:1:{i:0;s:7:"Lainnya";}') {
					$jawaban_lainnya[$key->id] = $_POST['jawaban_lainnya'][$key->id];
				} else {
					$jawaban_lainnya[$key->id] = '';
				}


				$value = [
					'jawaban' 	=> $get_terbuka[$key->id],
					'is_active' => 1,
					'jawaban_lainnya' => $jawaban_lainnya[$key->id]
				];
				// var_dump($value);

				$this->db->where('id_responden', $id_res);
				$this->db->where('id_pertanyaan_terbuka', $id_pertanyaan_terbuka[$key->id]);
				$this->db->update('jawaban_pertanyaan_terbuka_' . $table_identity, $value);

				// $n++;
			}
		}


		$obj_value = [
			'is_end' => '* Berakhir di Pertanyaan Unsur'
		];
		$this->db->where('id_responden', $id_res);
		$this->db->update("survey_$table_identity", $obj_value);


		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}



	public function data_pertanyaan_harapan($id1)
	{
		$this->data = [];
		$this->data['title'] = 'Pertanyaan Harapan';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;

		$query = $this->db->query("SELECT pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan AS id_unsur_pelayanan, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur, isi_pertanyaan_unsur, IF(unsur_pelayanan_$table_identity.is_sub_unsur_pelayanan = 2, SUBSTRING(nama_unsur_pelayanan, 1, 2), SUBSTRING(nama_unsur_pelayanan, 1, 4)) AS nomor, SUBSTRING(nomor_unsur, 2, 4) AS nomor_harapan
		FROM pertanyaan_unsur_pelayanan_$table_identity
		JOIN unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan
		ORDER BY pertanyaan_unsur_pelayanan_$table_identity.id ASC");
		$this->data['pertanyaan_unsur'] = $query;
		// var_dump($this->data['pertanyaan_unsur']->result());

		//JAWABAN PERTANYAAN HARAPAN
		$this->data['jawaban_pertanyaan_harapan'] = $this->db->query("SELECT id_pertanyaan_unsur_pelayanan, nomor_tingkat_kepentingan, nama_tingkat_kepentingan, skor_jawaban
		FROM nilai_tingkat_kepentingan_$table_identity
		LEFT JOIN jawaban_pertanyaan_harapan_$table_identity ON nilai_tingkat_kepentingan_$table_identity.id_pertanyaan_unsur_pelayanan = jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur
		JOIN responden_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = responden_$table_identity.id
		WHERE responden_$table_identity.uuid = '$uuid_responden'");

		//CEK APAKAH SURVEY SUDAH DI SUBMIT APA BELUM
		$this->db->select("survey_$table_identity.is_active, is_submit");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();
		// $is_selesai_survey = $this->db->get()->row();a

		if ($cek_data_responden->num_rows() == 0) {
			show_404();
		}


		if ($this->uri->segment(5) == 'edit') {
			$is_edit = '/edit';
		} else {
			$is_edit = '';
		}

		$atribut_pertanyaan = unserialize($manage_survey->atribut_pertanyaan_survey);

		//PENGECEKAN ATRIBUT PERTANYAAN KUALITATIF
		if (in_array(3, $atribut_pertanyaan)) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-kualitatif/' . $uuid_responden . $is_edit;
		} else if (in_array(4, $atribut_pertanyaan)) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-nps/' . $uuid_responden . $is_edit;
		} else if ($manage_survey->is_saran == 1) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/saran/' . $uuid_responden . $is_edit;
		} else {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/add-konfirmasi/' . $uuid_responden . $is_edit;
		}



		if ($this->uri->segment(5) == 'edit') {
			return view('survei/form_pertanyaan_harapan', $this->data);
		} elseif (($cek_data_responden->row()->is_submit == 2) || ($cek_data_responden->row()->is_submit == 3)) {
			return view('survei/form_pertanyaan_harapan', $this->data);
		} else {
			redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $uuid_responden, 'refresh');
		}
	}



	public function add_pertanyaan_harapan($id1)
	{
		$this->data = [];
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;

		$this->db->select('*');
		$this->db->from('responden_' . $table_identity);
		$this->db->where('uuid', $uuid_responden);
		$id_res = $this->db->get()->row()->id;

		$this->db->select('*');
		$this->db->from('pertanyaan_unsur_pelayanan_' . $table_identity);
		$get_data_harapan = $this->db->get();

		$result = array();
		$no = 1;
		foreach ($get_data_harapan->result() as $key => $val) {

			$id_pertanyaan_unsur = $_POST['id_pertanyaan_unsur'][$no];
			$object = [
				'skor_jawaban' 	=> $_POST['jawaban_pertanyaan_harapan'][$no]
			];

			$this->db->where('id_responden', $id_res);
			$this->db->where('id_pertanyaan_unsur', $id_pertanyaan_unsur);
			$this->db->update('jawaban_pertanyaan_harapan_' . $table_identity, $object);

			$no++;
		}


		$obj_value = [
			'is_end' => '* Berakhir di Pertanyaan Harapan'
		];
		$this->db->where('id_responden', $id_res);
		$this->db->update("survey_$table_identity", $obj_value);


		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}




	public function pertanyaan_kualitatif($id1)
	{
		$this->data = array();
		$this->data['title'] = 'Pertanyaan Kualitatif';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;

		$this->data['atribut_pertanyaan'] = unserialize($manage_survey->atribut_pertanyaan_survey);


		//CEK APAKAH SURVEY SUDAH DI SUBMIT APA BELUM
		$this->db->select("*, survey_$table_identity.is_active, is_submit");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();
		// $is_selesai_survey = $this->db->get()->row();

		if ($cek_data_responden->num_rows() == 0) {
			show_404();
		}

		$id_res = $cek_data_responden->row()->id_responden;
		$this->data['kualitatif'] = $this->db->query("select *
		FROM pertanyaan_kualitatif_$table_identity
		JOIN jawaban_pertanyaan_kualitatif_$table_identity ON pertanyaan_kualitatif_$table_identity.id = jawaban_pertanyaan_kualitatif_$table_identity.id_pertanyaan_kualitatif
		WHERE id_responden = $id_res && pertanyaan_kualitatif_$table_identity.is_active = 1")->result();


		if ($this->uri->segment(5) == 'edit') {
			$is_edit = '/edit';
		} else {
			$is_edit = '';
		};



		if (in_array(4, $this->data['atribut_pertanyaan'])) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-nps/' . $uuid_responden . $is_edit;
		} else if ($manage_survey->is_saran == 1) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/saran/' . $uuid_responden . $is_edit;
		} else {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/add-konfirmasi/' . $uuid_responden . $is_edit;
		};

		if ($this->uri->segment(5) == 'edit') {
			return view('survei/pertanyaan_kualitatif', $this->data);
		} elseif (($cek_data_responden->row()->is_submit == 2) || ($cek_data_responden->row()->is_submit == 3)) {
			return view('survei/pertanyaan_kualitatif', $this->data);
		} else {
			redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $uuid_responden, 'refresh');
		}
	}

	public function add_kualitatif($id1)
	{
		$this->data = array();
		$this->data['title'] = 'Pertanyaan Kualitatif';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;

		$this->db->select('*');
		$this->db->from('responden_' . $table_identity);
		$this->db->where('uuid', $this->uri->segment(4));
		$id_res = $this->db->get()->row()->id;

		$this->data['kualitatif'] = $this->db->query("select *
		FROM pertanyaan_kualitatif_$table_identity
		JOIN jawaban_pertanyaan_kualitatif_$table_identity ON pertanyaan_kualitatif_$table_identity.id = jawaban_pertanyaan_kualitatif_$table_identity.id_pertanyaan_kualitatif
		WHERE id_responden = $id_res && pertanyaan_kualitatif_$table_identity.is_active = 1")->result();


		$input 	= $this->input->post(NULL, TRUE);

		$id_kualitatif = $input['id_kualitatif'];
		$isi_jawaban_kualitatif = $input['isi_jawaban_kualitatif'];
		$data = array();
		$result = array();
		foreach ($_POST['isi_jawaban_kualitatif'] as $key => $val) {
			$result = [
				'isi_jawaban_kualitatif' => $isi_jawaban_kualitatif[$key]
			];

			$this->db->where('id', $id_kualitatif[$key]);
			$this->db->update('jawaban_pertanyaan_kualitatif_' . $table_identity, $result);
			// var_dump($result);
		}


		$obj_value = [
			'is_end' => '* Berakhir di Pertanyaan Kualitatif'
		];
		$this->db->where('id_responden', $id_res);
		$this->db->update("survey_$table_identity", $obj_value);



		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}





	public function data_pertanyaan_nps($id1)
	{
		$this->data = [];
		$this->data['title'] = 'Pertanyaan NPS';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;
		$this->data['atribut_pertanyaan'] = unserialize($manage_survey->atribut_pertanyaan_survey);

		$this->data['pertanyaan_nps'] = $this->db->query("SELECT *,
		(SELECT skor_jawaban FROM jawaban_pertanyaan_nps_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_nps_$table_identity.id_responden = responden_$table_identity.id WHERE id_pertanyaan_nps = pertanyaan_nps_$table_identity.id && responden_$table_identity.uuid = '$uuid_responden') AS skor_jawaban
		FROM pertanyaan_nps_$table_identity
		ORDER BY id ASC");
		// var_dump($this->data['pertanyaan_unsur']->result());


		//CEK APAKAH SURVEY SUDAH DI SUBMIT APA BELUM
		$this->db->select("survey_$table_identity.is_active, is_submit");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();
		// $is_selesai_survey = $this->db->get()->row();a

		if ($cek_data_responden->num_rows() == 0) {
			show_404();
		}


		if ($this->uri->segment(5) == 'edit') {
			$is_edit = '/edit';
		} else {
			$is_edit = '';
		}


		//PENGECEKAN ATRIBUT PERTANYAAN KUALITATIF
		if ($manage_survey->is_saran == 1) {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/saran/' . $uuid_responden . $is_edit;
		} else {
			$this->data['url_next'] = base_url() . 'survei/' . $this->uri->segment(2) . '/add-konfirmasi/' . $uuid_responden . $is_edit;
		}



		if ($this->uri->segment(5) == 'edit') {
			return view('survei/form_pertanyaan_nps', $this->data);
		} elseif (($cek_data_responden->row()->is_submit == 2) || ($cek_data_responden->row()->is_submit == 3)) {
			return view('survei/form_pertanyaan_nps', $this->data);
		} else {
			redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $uuid_responden, 'refresh');
		}
	}



	public function add_pertanyaan_nps($id1)
	{
		$this->data = [];
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;

		$this->db->select('*');
		$this->db->from('responden_' . $table_identity);
		$this->db->where('uuid', $uuid_responden);
		$id_res = $this->db->get()->row()->id;

		$this->db->select('*');
		$this->db->from('pertanyaan_nps_' . $table_identity);
		$get_data_nps = $this->db->get();

		$result = [];
		$no = 1;
		foreach ($get_data_nps->result() as $key => $val) {

			$id_pertanyaan_nps = $_POST['id'][$no];
			$object = [
				'skor_jawaban' 	=> $_POST['jawaban_pertanyaan_nps'][$no]
			];

			$this->db->where('id_responden', $id_res);
			$this->db->where('id_pertanyaan_nps', $id_pertanyaan_nps);
			$this->db->update('jawaban_pertanyaan_nps_' . $table_identity, $object);

			$no++;
		}


		$obj_value = [
			'is_end' => '* Berakhir di Pertanyaan NPS'
		];
		$this->db->where('id_responden', $id_res);
		$this->db->update("survey_$table_identity", $obj_value);


		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}












	public function saran($id1)
	{
		$this->data = [];
		$this->data['title'] = 'Saran';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$table_identity = $this->data['manage_survey']->table_identity;

		$this->data['atribut_pertanyaan'] = unserialize($this->data['manage_survey']->atribut_pertanyaan_survey);

		$uuid_responden = $this->uri->segment(4);

		//DATA RESPONDEN
		$this->db->select("*, survey_$table_identity.is_active, is_submit");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();

		if ($cek_data_responden->num_rows() == 0) {
			show_404();
		}

		$this->data['saran'] = [
			'name' 		=> 'saran',
			'id'		=> 'saran',
			'type'		=> 'text',
			'value'		=>	$this->form_validation->set_value('saran', $cek_data_responden->row()->saran),
			'class'		=> 'form-control',
			'autofocus' => 'autofocus',
			'placeholder' => 'Masukkan saran atau opini anda terhadap survei ini ..',
			'pattern' => "^[a-zA-Z0-9.,\s]*$|^\w$",
			// 'rows' => 5
		];


		if ($this->uri->segment(5) == 'edit') {
			return view('survei/form_saran', $this->data);
		} elseif (($cek_data_responden->row()->is_submit == 2) || ($cek_data_responden->row()->is_submit == 3)) {
			return view('survei/form_saran', $this->data);
		} else {
			redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $uuid_responden, 'refresh');
		}
	}

	public function add_saran($id1)
	{

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$table_identity = $this->data['manage_survey']->table_identity;

		//DATA RESPONDEN
		$this->db->select("*, survey_$table_identity.is_active, is_submit");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();

		$input 	= $this->input->post(NULL, TRUE);

		$object = [
			// 'waktu_isi' => date("Y/m/d H:i:s"),
			'saran' 	=> $input['saran'],
			'is_active' 	=> '1',
			'is_end' => '* Berakhir di Pengisian Saran'
		];

		$id_res = $cek_data_responden->row()->id;
		$this->db->where('id_responden', $id_res);
		$this->db->update('survey_' . $table_identity, $object);


		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}



	public function form_konfirmasi($id1)
	{
		$this->data = [];
		$this->data['title'] = 'Form Konfirmasi';
		$this->data['profiles'] = $this->_get_data_profile($id1);

		$uuid_responden = $this->uri->segment(4);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;


		//DATA RESPONDEN
		$this->db->select("*, survey_$table_identity.is_active, is_submit");
		$this->db->from('survey_' . $table_identity);
		$this->db->join("responden_$table_identity", "survey_$table_identity.id_responden = responden_$table_identity.id");
		$this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
		$cek_data_responden = $this->db->get();

		if ($cek_data_responden->num_rows() == 0) {
			show_404();
		}


		if ($this->uri->segment(5) == 'edit') {
			$is_edit = '/edit';
		} else {
			$is_edit = '';
		};


		if ($manage_survey->is_saran == 1) {
			$this->data['link_back'] =  anchor(base_url() . 'survei/' . $this->uri->segment(2) . '/saran/' . $this->uri->segment(4) . $is_edit, '<i class="fa fa-arrow-left"></i> Lengkapi Kembali', ['class' => 'btn btn-secondary btn-lg font-weight-bold shadow tombolCancel']);
		} else if (in_array(3, unserialize($manage_survey->atribut_pertanyaan_survey))) {

			$this->data['link_back'] =  anchor(base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-kualitatif/' . $this->uri->segment(4) . $is_edit, '<i class="fa fa-arrow-left"></i> Lengkapi Kembali', ['class' => 'btn btn-secondary btn-lg font-weight-bold shadow tombolCancel']);
		} else if (in_array(1, unserialize($manage_survey->atribut_pertanyaan_survey))) {

			$this->data['link_back'] =  anchor(base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan-harapan/' . $this->uri->segment(4) . $is_edit, '<i class="fa fa-arrow-left"></i> Lengkapi Kembali', ['class' => 'btn btn-secondary btn-lg font-weight-bold shadow tombolCancel']);
		} else {

			$this->data['link_back'] =  anchor(base_url() . 'survei/' . $this->uri->segment(2) . '/pertanyaan/' . $this->uri->segment(4) . $is_edit, '<i class="fa fa-arrow-left"></i> Lengkapi Kembali', ['class' => 'btn btn-secondary btn-lg font-weight-bold shadow tombolCancel']);
		}


		if ($this->uri->segment(5) == 'edit') {
			return view('survei/form_konfirmasi', $this->data);
		} elseif (($cek_data_responden->row()->is_submit == 2) || ($cek_data_responden->row()->is_submit == 3)) {
			return view('survei/form_konfirmasi', $this->data);
		} else {
			redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $uuid_responden, 'refresh');
		}
	}


	public function add_konfirmasi($id1)
	{
		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$this->db->select('*');
		$this->db->from('responden_' . $manage_survey->table_identity);
		$this->db->where('uuid', $this->uri->segment(4));
		$id_res = $this->db->get()->row()->id;

		// $data_paket = $this->cek_paket();
		// if ($data_paket[0] > $data_paket[1]) {
		// 	$is_submit = '3';
		// } else {
		// 	$is_submit = '1';
		// }

		$object = [
			'is_submit' => 1,
			// 'is_submit' => $is_submit,
			'is_end' => '* Finish'
		];

		$this->db->where('id_responden', $id_res);
		$this->db->update('survey_' . $manage_survey->table_identity, $object);

		redirect(base_url() . 'survei/' . $this->uri->segment(2) . '/selesai/' . $this->uri->segment(4), 'refresh');

		// $pesan = 'Data berhasil disimpan';
		// $msg = ['sukses' => $pesan];
		// echo json_encode($msg);
	}




	public function form_closing()
	{
		$this->data = [];
		$this->data['title'] = 'Sukses';

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$this->data['status_saran'] = $this->data['manage_survey']->is_saran;

		$this->db->select('*');
		$this->db->from('responden_' . $this->data['manage_survey']->table_identity);
		$this->db->where('uuid', $this->uri->segment(4));
		$this->data['responden'] = $this->db->get()->row();
		// var_dump($this->data['responden']);


		return view('survei/form_closing', $this->data);
	}

	public function unopened()
	{
		$this->data = [];
		$this->data['title'] = 'Link Survei';

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['judul'] = $manage_survey;


		return view('survei/form_setting_survey/unopened', $this->data);
	}

	public function survey_end()
	{
		$this->data = [];
		$this->data['title'] = 'Link Survei';

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['judul'] = $manage_survey;


		return view('survei/form_setting_survey/survey_end', $this->data);
	}

	public function survey_hold()
	{
		$this->data = [];
		$this->data['title'] = 'Link Survei';

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['judul'] = $manage_survey;

		return view('survei/form_setting_survey/survey_hold', $this->data);
	}

	public function survey_not_question()
	{
		$this->data = [];
		$this->data['title'] = 'Link Survei';

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['judul'] = $manage_survey;


		return view('survei/form_setting_survey/survey_not_question', $this->data);
	}

	public function petunjuk_pengisian_survey()
	{
		$this->data = [];
		$this->data['title'] = "Petunjuk Pengisian Survey";

		return view('survei/form_bantuan/form_petunjuk_pengisian_survey', $this->data);
		// $profiles =  $this->_get_data_profile($id1, $id2);
		// $this->data['profiles'] = $profiles;
	}

	public function kontak_kami()
	{
		$this->data = [];
		$this->data['title'] = "";

		// $profiles =  $this->_get_data_profile($id1, $id2);
		// $this->data['profiles'] = $profiles;
	}

	public function faq()
	{
		$this->data = [];
		$this->data['title'] = "";

		// $profiles =  $this->_get_data_profile($id1, $id2);
		// $this->data['profiles'] = $profiles;
	}


	public function edit_data_responden()
	{

		$this->data = [];
		$this->data['title'] = 'Edit Data Responden';

		$this->db->select("*, DATE_FORMAT(survey_end, '%d %M %Y') AS survey_selesai, IF(CURDATE() > survey_end,1,NULL) AS survey_berakhir, IF(CURDATE() < survey_start ,1,NULL) AS survey_belum_mulai");
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['status_saran'] = $manage_survey->is_saran;
		$this->data['manage_survey'] = $manage_survey;


		//LOAD PROFIL RESPONDEN
		$this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity ORDER BY IF(urutan != '',urutan,id) ASC");
		// var_dump($this->data['profil_responden']->result());

		//LOAD KATEGORI PROFIL RESPONDEN JIKA PILIHAN GANDA
		$this->data['kategori_profil_responden'] = $this->db->get('kategori_profil_responden_' . $table_identity);


		$uuid_responden = $this->uri->segment(4);
		$this->data['responden'] = $this->db->get_where("responden_$table_identity", array("uuid" => $uuid_responden))->row();
		// var_dump($this->data['responden']);

		$this->data['nama_lengkap'] = [
			'name'         => 'nama_lengkap',
			'id'        => 'nama_lengkap',
			'type'        => 'text',
			'value'        =>  $this->form_validation->set_value('nama_lengkap', $this->data['responden']->nama_lengkap),
			'class'        => 'form-control',
			'autofocus' => 'autofocus',
			'required' => 'required',
			'placeholder' => 'Masukkan data anda ...',
		];

		$this->data['id_layanan_survei'] = [
			'name'         => 'id_layanan_survei',
			'id'         => 'id_layanan_survei',
			'options'     => $this->Survei_model->dropdown_layanan_survei($table_identity),
			'selected'     => $this->form_validation->set_value('id_layanan_survei', $this->data['responden']->id_layanan_survei),
			'class'     => "form-control",
		];


		return view('survei/data_responden/edit_data_responden', $this->data);
	}

	function update_data_responden()
	{
		$this->db->select("*, DATE_FORMAT(survey_end, '%d %M %Y') AS survey_selesai, IF(CURDATE() > survey_end,1,NULL) AS survey_berakhir, IF(CURDATE() < survey_start ,1,NULL) AS survey_belum_mulai");
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$table_identity = $manage_survey->table_identity;

		//LOAD PROFIL RESPONDEN
		$this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity");


		if ($manage_survey->is_layanan_survei != 0) {
			$id_layanan_survei = $this->input->post('id_layanan_survei');
		} else {
			$id_layanan_survei = '';
		}

		//INSERT DATA RSEPONDEN
		$uuid_responden = $this->uri->segment(4);
		$object = [
			'nama_lengkap'     => $this->input->post('nama_lengkap'),
			'updated_at' => date("Y/m/d H:i:s"),
			'id_layanan_survei' => $id_layanan_survei
		];

		foreach ($this->data['profil_responden']->result() as $row) {
			$object[$row->nama_alias] = $this->input->post($row->nama_alias);

			if ($row->is_lainnya == 1) {
				$nama_lainnya = $row->nama_alias . '_lainnya';
				$object[$nama_lainnya] = $this->input->post($nama_lainnya);
			}
		}

		$this->db->where('uuid', $uuid_responden);
		$this->db->update("responden_$table_identity", $object);

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}



	public function _get_data_profile($id1)
	{

		$this->db->select('users.username, manage_survey.survey_name, manage_survey.slug, manage_survey.description, manage_survey.is_privacy');
		$this->db->from('users');
		$this->db->join('manage_survey', 'manage_survey.id_user = users.id');
		$this->db->where('manage_survey.slug', $id1);
		$profiles = $this->db->get();

		if ($profiles->num_rows() == 0) {
			// echo 'Survey tidak ditemukan atau sudah dihapus !';
			// exit();
			show_404();
		}

		return $profiles->row();
	}


	// public function cek_paket()
	// {
	// 	$this->db->select("*, DATE_FORMAT(survey_end, '%d %M %Y') AS survey_selesai, IF(CURDATE() > survey_end,1,NULL) AS survey_berakhir, IF(CURDATE() < survey_start ,1,NULL) AS survey_belum_mulai");
	// 	$this->db->from('manage_survey');
	// 	$this->db->where('manage_survey.slug', $this->uri->segment(2));
	// 	$manage_survey = $this->db->get()->row();
	// 	$table_identity = $manage_survey->table_identity;

	// 	$get_parent_induk = $this->db->query("SELECT id_parent_induk FROM users WHERE id = $manage_survey->id_user")->row();

	// 	$this->db->select('*, berlangganan.id AS id_berlangganan');
	// 	$this->db->from('berlangganan');
	// 	$this->db->join('users', 'users.id = berlangganan.id_user');
	// 	$this->db->join('paket', 'paket.id = berlangganan.id_paket');
	// 	$this->db->where('berlangganan.id_user', $get_parent_induk->id_parent_induk);
	// 	$this->db->where('berlangganan.id_produk', '9');
	// 	$this->db->order_by('berlangganan.id', 'asc');
	// 	$get_data = $this->db->get();
	// 	$data_paket = $get_data->last_row();
	// 	$jumlah_responden = $data_paket->jumlah_responden;

	// 	$this->db->select('COUNT(id) AS id');
	// 	$this->db->from('survey_' . $table_identity);
	// 	// $this->db->where("is_submit = 1");
	// 	$total_responden = $this->db->get()->row()->id;

	// 	return array($total_responden, $jumlah_responden);

	// 	// if($total_responden > $data_paket->jumlah_responden){
	// 	// 	$is_submit = '3';
	// 	// }else{
	// 	// 	$is_submit = '2';
	// 	// }

	// 	// return $is_submit;
	// }
}

/* End of file SurveiController.php */
/* Location: ./application/controllers/SurveiController.php */
