<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ProfilRespondenSurveiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->load->library('ion_auth');

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be logged in to access this page');
			redirect('auth', 'refresh');
		}

		$this->load->library('form_validation');
		$this->load->model('ProfilRespondenSurvei_model', 'models');
	}


	public function index($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Profil Responden Survei';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$manage_survey = $this->db->get_where('manage_survey', array('slug' => $id2))->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['is_question'] = $manage_survey->is_question;

		$this->data['profil_default'] = $this->db->query("SELECT *
		FROM profil_responden
		WHERE NOT EXISTS (SELECT * FROM profil_responden_$table_identity WHERE profil_responden.nama_profil_responden = profil_responden_$table_identity.nama_profil_responden)  && id NOT IN (2,3)");



		// foreach($this->db->query("SELECT * FROM manage_survey WHERE id = 384")->result() as $row){
		// 	$insert_id = $row->id;


		// 	$fk = 'survey_cst' . $insert_id . '_ibfk_1';
		// 	$fk1 = 'jawaban_pertanyaan_unsur_cst' . $insert_id . '_ibfk_2';
		// 	$fk2 = 'jawaban_pertanyaan_terbuka_cst' . $insert_id . '_ibfk_2';
		// 	$fk3 = 'jawaban_pertanyaan_kualitatif_cst' . $insert_id . '_ibfk_2';
		// 	$fk4 = 'pertanyaan_kualitatif_cst' . $insert_id . '_ibfk_2';
		// 	$fk5 = 'pertanyaan_unsur_pelayanan_cst' . $insert_id . '_ibfk_2';
		// 	$fk6 = 'kategori_unsur_pelayanan_cst' . $insert_id . '_ibfk_1';
		// 	$fk7 = 'pertanyaan_terbuka_cst' . $insert_id . '_ibfk_3';
		// 	$fk8 = 'perincian_pertanyaan_terbuka_cst' . $insert_id . '_ibfk_2';
		// 	$fk9 = 'isi_pertanyaan_ganda_cst' . $insert_id . '_ibfk_2';
		// 	$fk10 = 'jawaban_pertanyaan_harapan_cst' . $insert_id . '_ibfk_3';
		// 	$fk11 = 'nilai_tingkat_kepentingan_cst' . $insert_id . '_ibfk_3';
		// 	$fk12 = 'kategori_profil_responden_cst' . $insert_id . '_ibfk_3';
		// 	$fk13 = 'koreksi_survey_cst' . $insert_id . '_ibfk_1';
		// 	$fk14 = 'koreksi_jawaban_pertanyaan_unsur_cst' . $insert_id . '_ibfk_2';


		// 	$tb_survey = 'survey_cst' . $insert_id;
		// 	$tb_responden = 'responden_cst' . $insert_id;
		// 	$tb_jawaban_pertanyaan_unsur = 'jawaban_pertanyaan_unsur_cst' . $insert_id;
		// 	$tb_jawaban_pertanyaan_terbuka = 'jawaban_pertanyaan_terbuka_cst' . $insert_id;
		// 	$tb_pertanyaan_kualitatif = 'pertanyaan_kualitatif_cst' . $insert_id;
		// 	$tb_jawaban_pertanyaan_kualitatif = 'jawaban_pertanyaan_kualitatif_cst' . $insert_id;
		// 	$tb_unsur_pelayanan = 'unsur_pelayanan_cst' . $insert_id;
		// 	$tb_pertanyaan_unsur_pelayanan = 'pertanyaan_unsur_pelayanan_cst' . $insert_id;
		// 	$tb_kategori_unsur_pelayanan = 'kategori_unsur_pelayanan_cst' . $insert_id;
		// 	$tb_pertanyaan_terbuka = 'pertanyaan_terbuka_cst' . $insert_id;
		// 	$tb_perincian_pertanyaan_terbuka = 'perincian_pertanyaan_terbuka_cst' . $insert_id;
		// 	$tb_isi_pertanyaan_ganda = 'isi_pertanyaan_ganda_cst' . $insert_id;
		// 	$tb_jawaban_pertanyaan_harapan = 'jawaban_pertanyaan_harapan_cst' . $insert_id;
		// 	$tb_nilai_tingkat_kepentingan = 'nilai_tingkat_kepentingan_cst' . $insert_id;
		// 	$tb_data_prospek_survey = 'data_prospek_survey_cst' . $insert_id;
		// 	$tb_log_survey = 'log_survey_cst' . $insert_id;
		// 	$tb_profil_responden = 'profil_responden_cst' . $insert_id;
		// 	$tb_kategori_profil_responden = 'kategori_profil_responden_cst' . $insert_id;
		// 	$tb_analisa = 'analisa_cst' . $insert_id;
		// 	$tb_definisi_skala = 'definisi_skala_cst' . $insert_id;
		// 	$tb_dimensi = 'dimensi_cst' . $insert_id;



		// 	$this->db->query("CREATE TABLE $tb_profil_responden (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) AS SELECT * FROM profil_responden_cst383");
        //     $this->db->query("CREATE TABLE $tb_kategori_profil_responden (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) AS SELECT * FROM kategori_profil_responden_cst383");

        //     $this->db->query("CREATE TABLE kategori_layanan_survei_cst$insert_id (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) AS SELECT * FROM kategori_layanan_survei_cst383");
        //     $this->db->query("CREATE TABLE layanan_survei_cst$insert_id (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY) AS SELECT * FROM layanan_survei_cst383");
        //     $this->db->query("CREATE TABLE $tb_responden LIKE responden_cst383");
        //     $this->db->query("CREATE TABLE trash_responden_cst$insert_id LIKE responden_cst383");



		// 	$this->db->query("CREATE TABLE $tb_survey LIKE survey_cst383");
		// 	$this->db->query("CREATE TABLE $tb_jawaban_pertanyaan_unsur LIKE jawaban_pertanyaan_unsur_cst383");
		// 	$this->db->query("CREATE TABLE $tb_jawaban_pertanyaan_terbuka LIKE jawaban_pertanyaan_terbuka_cst383");
		// 	$this->db->query("CREATE TABLE $tb_pertanyaan_kualitatif LIKE pertanyaan_kualitatif_cst383");
		// 	$this->db->query("CREATE TABLE $tb_jawaban_pertanyaan_kualitatif LIKE jawaban_pertanyaan_kualitatif_cst383");
		// 	$this->db->query("CREATE TABLE $tb_jawaban_pertanyaan_harapan LIKE jawaban_pertanyaan_harapan_cst383");
		// 	$this->db->query("CREATE TABLE $tb_log_survey LIKE log_survey_cst383");



		// 	$this->db->query("CREATE TABLE $tb_analisa LIKE analisa");

		// 	$this->db->query("CREATE TABLE $tb_definisi_skala LIKE definisi_skala");
		// 	$this->db->query("INSERT INTO $tb_definisi_skala SELECT * FROM definisi_skala_cst383");

		// 	$this->db->query("ALTER TABLE $tb_survey  ADD CONSTRAINT $fk FOREIGN KEY (`id_responden`) REFERENCES $tb_responden(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

		// 	$this->db->query("ALTER TABLE $tb_jawaban_pertanyaan_unsur  ADD CONSTRAINT $fk1 FOREIGN KEY (`id_responden`) REFERENCES $tb_responden(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

		// 	$this->db->query("ALTER TABLE $tb_jawaban_pertanyaan_harapan  ADD CONSTRAINT $fk10 FOREIGN KEY (`id_responden`) REFERENCES $tb_responden(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

		// 	$this->db->query("ALTER TABLE $tb_jawaban_pertanyaan_terbuka  ADD CONSTRAINT $fk2 FOREIGN KEY (`id_responden`) REFERENCES $tb_responden(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

		// 	$this->db->query("ALTER TABLE $tb_jawaban_pertanyaan_kualitatif  ADD CONSTRAINT $fk3 FOREIGN KEY (`id_responden`) REFERENCES $tb_responden(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

		// 	$this->db->query("ALTER TABLE $tb_jawaban_pertanyaan_kualitatif  ADD CONSTRAINT $fk4 FOREIGN KEY (`id_pertanyaan_kualitatif`) REFERENCES $tb_pertanyaan_kualitatif(`id`) ON DELETE CASCADE ON UPDATE CASCADE");

		// 	//BUAT TABEL UNTUK MENAMPUNG DATA RESPONDEN YANG DI HAPUS
		// 	// $this->db->query("CREATE TABLE trash_responden_cst$insert_id LIKE responden");
		// 	$this->db->query("CREATE TABLE trash_survey_cst$insert_id LIKE survey");
		// 	$this->db->query("CREATE TABLE trash_jawaban_pertanyaan_unsur_cst$insert_id LIKE jawaban_pertanyaan_unsur");
		// 	$this->db->query("CREATE TABLE trash_jawaban_pertanyaan_terbuka_cst$insert_id LIKE jawaban_pertanyaan_terbuka");
		// 	$this->db->query("CREATE TABLE trash_jawaban_pertanyaan_kualitatif_cst$insert_id LIKE jawaban_pertanyaan_kualitatif");
		// 	$this->db->query("CREATE TABLE trash_jawaban_pertanyaan_harapan_cst$insert_id LIKE jawaban_pertanyaan_harapan");

		// 	$this->db->query("CREATE TABLE $tb_dimensi LIKE dimensi");



        //     $this->db->query("INSERT INTO $tb_dimensi SELECT * FROM dimensi_cst383");

        //     $this->db->query("CREATE TABLE $tb_unsur_pelayanan LIKE unsur_pelayanan_cst383");
        //     $this->db->query("INSERT INTO $tb_unsur_pelayanan SELECT * FROM unsur_pelayanan_cst383");

        //     $this->db->query("CREATE TABLE $tb_pertanyaan_unsur_pelayanan LIKE pertanyaan_unsur_pelayanan_cst383");
        //     $this->db->query("INSERT INTO $tb_pertanyaan_unsur_pelayanan SELECT * FROM pertanyaan_unsur_pelayanan_cst383");

        //     $this->db->query("CREATE TABLE $tb_kategori_unsur_pelayanan LIKE kategori_unsur_pelayanan_cst383");
        //     $this->db->query("INSERT INTO $tb_kategori_unsur_pelayanan SELECT * FROM kategori_unsur_pelayanan_cst383");

        //     $this->db->query("CREATE TABLE $tb_nilai_tingkat_kepentingan LIKE nilai_tingkat_kepentingan_cst383");
        //     $this->db->query("INSERT INTO $tb_nilai_tingkat_kepentingan SELECT * FROM nilai_tingkat_kepentingan_cst383");

        //     $this->db->query("CREATE TABLE $tb_pertanyaan_terbuka LIKE pertanyaan_terbuka_cst383");
        //     $this->db->query("INSERT INTO $tb_pertanyaan_terbuka SELECT * FROM pertanyaan_terbuka_cst383");

        //     $this->db->query("CREATE TABLE $tb_perincian_pertanyaan_terbuka LIKE perincian_pertanyaan_terbuka_cst383");
        //     $this->db->query("INSERT INTO $tb_perincian_pertanyaan_terbuka SELECT * FROM perincian_pertanyaan_terbuka_cst383");

        //     $this->db->query("CREATE TABLE $tb_isi_pertanyaan_ganda LIKE isi_pertanyaan_ganda_cst383");
        //     $this->db->query("INSERT INTO $tb_isi_pertanyaan_ganda SELECT * FROM isi_pertanyaan_ganda_cst383");



		// 	$this->db->query("CREATE TABLE $tb_data_prospek_survey LIKE data_prospek_survey_cst383;");
	
		// 	$this->db->query("
		// 		ALTER TABLE $tb_pertanyaan_unsur_pelayanan  ADD CONSTRAINT $fk5 FOREIGN KEY (`id_unsur_pelayanan`) REFERENCES $tb_unsur_pelayanan(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		// 		");
	
		// 	$this->db->query("
		// 		ALTER TABLE $tb_kategori_unsur_pelayanan  ADD CONSTRAINT $fk6 FOREIGN KEY (`id_unsur_pelayanan`) REFERENCES $tb_unsur_pelayanan(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		// 		");
	
		// 	$this->db->query("
		// 		ALTER TABLE $tb_nilai_tingkat_kepentingan  ADD CONSTRAINT $fk11 FOREIGN KEY (`id_pertanyaan_unsur_pelayanan`) REFERENCES $tb_pertanyaan_unsur_pelayanan(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		// 		");
	
		// 	$this->db->query("
		// 		ALTER TABLE $tb_pertanyaan_terbuka  ADD CONSTRAINT $fk7 FOREIGN KEY (`id_unsur_pelayanan`) REFERENCES $tb_unsur_pelayanan(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		// 		");
	
		// 	$this->db->query("
		// 		ALTER TABLE $tb_perincian_pertanyaan_terbuka  ADD CONSTRAINT $fk8 FOREIGN KEY (`id_pertanyaan_terbuka`) REFERENCES $tb_pertanyaan_terbuka(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		// 		");
	
		// 	$this->db->query("
		// 		ALTER TABLE $tb_isi_pertanyaan_ganda ADD CONSTRAINT $fk9 FOREIGN KEY (`id_perincian_pertanyaan_terbuka`) REFERENCES $tb_perincian_pertanyaan_terbuka(`id`) ON DELETE CASCADE ON UPDATE CASCADE
		// 		");
	
		// 	$this->db->query("
		// 	CREATE TRIGGER log_app_cst$insert_id AFTER INSERT ON responden_cst$insert_id
		// 	FOR EACH ROW BEGIN 
		// 	INSERT INTO log_survey_cst$insert_id(log_value, log_time) VALUES(CONCAT(NEW.uuid, ', sudah mengisi survei'), DATE_ADD(NOW(), INTERVAL 13 HOUR));		
		// 	END");
		// }




		return view('profil_responden_survei/index', $this->data);
	}


	public function ajax_list()
	{
		$slug = $this->uri->segment(2);
		$get_identity = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $get_identity->table_identity;

		$kategori_profil = $this->db->get('kategori_profil_responden_' . $table_identity);

		$profil_responden = $this->db->get("profil_responden_$table_identity")->num_rows();

		$list = $this->models->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			$pilihan = [];
			foreach ($kategori_profil->result() as $get) {
				if ($get->id_profil_responden == $value->id) {
					$pilihan[] =  '<label><input type="radio">&ensp;' . $get->nama_kategori_profil_responden . '&emsp;</label>';
				}
			}
			$jawaban = implode("<br>", $pilihan);

			$no++;
			$row = array();

			for ($i = 1; $i <= $profil_responden; ++$i) {
				$selected = $no == $i ? 'selected' : '';
				$urutan[$no][] = '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
			}

			if ($value->is_required == 1) {
				$required = '<span class="text-danger">*</span>';
			} else {
				$required = '';
			}

			$row[] = '<input value="' . $value->id . '" name="id[]" hidden>
			<select name="urutan[]" class="form-control-sm">'
				. implode("<br>", $urutan[$no]) .
				'</select>';

			// $row[] = $no;
			$row[] =  $value->nama_profil_responden . ' ' . $required;

			$row[] = $jawaban;


			$row[] = anchor($this->session->userdata('username') . '/' . $this->uri->segment(2) . '/profil-responden-survei/edit/' . $value->id, '<i class="fa fa-edit"></i> Edit', ['class' => 'btn btn-light-primary btn-sm font-weight-bold shadow']);

			$row[] = '<a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus ' . $value->nama_profil_responden . '" onclick="delete_data(' . "'" . $value->id . "'" . ')"><i class="fa fa-trash"></i> Delete</a>';


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


	public function update_urutan()
	{
		$manage_survey = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(2)))->row();
		$table_identity = $manage_survey->table_identity;

		foreach ($_POST['id'] as $key => $val) {
			$id = (int)$_POST['id'][$key];
			$urutan = $_POST['urutan'][$key];
			$this->db->query("UPDATE profil_responden_$table_identity SET urutan=$urutan WHERE id=$id");
		}

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}


	public function add_default()
	{
		$manage_survey = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(2)))->row();
		$table_identity = $manage_survey->table_identity;

		//CEK KOLOM
		// if ($this->db->field_exists('label_isian', 'profil_responden_' . $table_identity))
		// {
		// $field_label_isian = 'label_isian';
		// $field_posisi_label_isian = 'posisi_label_isian';
		// }else{
		// $field_label_isian = 'label_isian';
		// $field_posisi_label_isian = 'posisi_label_isian';
		//     $this->db->query("ALTER TABLE profil_responden_$table_identity ADD label_isian varchar(255) NULL");
		//     $this->db->query("ALTER TABLE profil_responden_$table_identity ADD posisi_label_isian tinyint(1) NULL");
		//     $this->db->query("ALTER TABLE profil_responden_$table_identity ADD is_required tinyint(1) NULL DEFAULT '1'");
		//     $this->db->query("ALTER TABLE profil_responden_$table_identity ADD id_profil tinyint(1) NULL DEFAULT '0'");
		// }

		// $urutan = $this->db->get("profil_responden_$table_identity")->num_rows() + 1;

		$check = $this->input->post('check_list[]');
		$kode = [];
		foreach ($check as $row) {
			$kode[] = $row;
		}
		$id = implode(",", $kode);

		$this->db->query("INSERT INTO profil_responden_$table_identity SELECT * FROM profil_responden WHERE id IN ($id)");

		$this->db->query("INSERT INTO kategori_profil_responden_$table_identity (id_profil_responden, nama_kategori_profil_responden) SELECT id_profil_responden, nama_kategori_profil_responden FROM kategori_profil_responden WHERE id_profil_responden IN ($id)");

		//BUAT COLUMN BARU DI TABEL RESPONDEN
		$data_profil = $this->db->query("SELECT *, IF(type_data != '' ,'VARCHAR (255)','INT') AS type_data_db FROM profil_responden_$table_identity WHERE id IN ($id)")->result();

		foreach ($data_profil as $row) {
			// $this->db->query("UPDATE profil_responden_$table_identity SET urutan = $urutan WHERE id = $row->id");

			$this->db->query("ALTER TABLE responden_$table_identity ADD $row->nama_alias VARCHAR (255)");
			$this->db->query("ALTER TABLE trash_responden_$table_identity ADD $row->nama_alias VARCHAR (255)");

			if ($row->is_lainnya == 1) {
				$name_lainnya = $row->nama_alias . '_lainnya';
				$this->db->query("ALTER TABLE responden_$table_identity ADD $name_lainnya TEXT");
				$this->db->query("ALTER TABLE trash_responden_$table_identity ADD $name_lainnya TEXT");
			}
			// $urutan++;
		}
		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}


	public function add_custom($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Custom Profil Responden';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$table_identity = $this->db->get()->row()->table_identity;

		$this->form_validation->set_rules('nama_profil_responden', 'Nama Profil Responden', 'trim|required');
		$this->form_validation->set_rules('jenis_jawaban', 'Jenis Isian', 'trim|required');

		$this->data['nama_profil_responden'] = [
			'name'         => 'nama_profil_responden',
			'id'        => 'nama_profil_responden',
			'type'        => 'text',
			'value'        =>    $this->form_validation->set_value('nama_profil_responden'),
			'class'        => 'form-control',
			'autofocus' => 'autofocus',
			'required' => 'required',
			// 'maxlength' => '15'
		];

		// $this->data['label_isian'] = [
		// 	'name'         => 'label_isian',
		// 	'id'        => 'label_isian',
		// 	'type'        => 'text',
		// 	'value'        =>    $this->form_validation->set_value('label_isian'),
		// 	'class'        => 'form-control',
		// 	'autofocus' => 'autofocus'
		// ];

		if ($this->form_validation->run() == false) {

			return view('profil_responden_survei/form_add', $this->data);
		} else {

			$input     = $this->input->post(null, true);


			$profil_survei = $this->db->get_where('profil_responden_' . $table_identity, array('is_default' => 2));

			//CEK PERTANYAAN CUSTOM CUDAH ADA APA BELUM
			if ($profil_survei->num_rows() == 0) {
				$cek_id = $this->db->get('profil_responden')->num_rows() + 1;
			} else {
				$cek_id = '';
			};

			//CEK TYPE JENIS JAWABAN
			if ($input['jenis_jawaban'] == 2) {

				if (isset($_POST['type_data'])) {
					$cek_type_data = $input['type_data'];
				} else {
					$cek_type_data =  'text';
				}
				$is_lainnya = '';
			} else {
				$cek_type_data = '';
				$is_lainnya = $input['opsi_lainnya'];
			};



			$data = [
				'id' => $cek_id,
				'nama_profil_responden' => $input['nama_profil_responden'],
				'jenis_isian' => $input['jenis_jawaban'],
				'is_default' => 2,
				'type_data' => $cek_type_data,
				'is_lainnya' => $is_lainnya,
				// 'label_isian' => $input['label_isian'],
				// 'posisi_label_isian' => $input['posisi_label_isian'],
				'is_required' => $input['is_required'],
				// 'nama_alias' => $nama_alias
			];
			$this->db->insert('profil_responden_' . $table_identity, $data);
			$id_profil_responden = $this->db->insert_id();

			$nama_alias = "profil_$id_profil_responden";
			$this->db->query("UPDATE profil_responden_$table_identity
			SET nama_alias = '$nama_alias'
			WHERE id = $id_profil_responden");

			// $data_profil = $this->db->query("SELECT *, IF(type_data != '' ,'VARCHAR (255)','INT') AS type_data_db FROM profil_responden_$table_identity WHERE id = $id_profil_responden")->row();

			$this->db->query("ALTER TABLE responden_$table_identity ADD $nama_alias VARCHAR (255)");
			$this->db->query("ALTER TABLE trash_responden_$table_identity ADD $nama_alias VARCHAR (255)");

			if ($is_lainnya == 1) {
				$nama_lainnya = $nama_alias . '_lainnya';
				$this->db->query("ALTER TABLE responden_$table_identity ADD $nama_lainnya TEXT");
				$this->db->query("ALTER TABLE trash_responden_$table_identity ADD $nama_lainnya TEXT");
			}



			//INPUT JAWABAN
			if ($input['jenis_jawaban'] == '1') {
				$pilihan_jawaban = $input['pilihan_jawaban'];

				$result = array();
				foreach ($_POST['pilihan_jawaban'] as $key => $val) {
					$result[] = array(
						'id_profil_responden' => $id_profil_responden,
						'nama_kategori_profil_responden' => $pilihan_jawaban[$key]
					);
				}

				if ($input['opsi_lainnya'] == 1) {
					$result[] = array(
						'id_profil_responden' => $id_profil_responden,
						'nama_kategori_profil_responden' => 'Lainnya'
					);
				}
				$this->db->insert_batch('kategori_profil_responden_' . $table_identity, $result);

				//DELETE PILIHAN JAWABAN YANG KOSONG
				$this->db->query("DELETE FROM kategori_profil_responden_$table_identity WHERE id_profil_responden = $id_profil_responden && nama_kategori_profil_responden = ''");
			}



			$this->session->set_flashdata('message_success', 'Berhasil menambah data');
			redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/profil-responden-survei', 'refresh');
		}
	}


	public function edit($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Edit Profil Responden';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$table_identity = $this->db->get()->row()->table_identity;

		$this->data['profil_responden'] = $this->db->query("SELECT *, IF(type_data != '' ,'VARCHAR (255)','INT') AS type_data_db FROM profil_responden_$table_identity WHERE id =" . $this->uri->segment(5))->row();
		$profil_responden = $this->data['profil_responden'];

		$this->data['kategori_profil_responden'] = $this->db->query("SELECT *, (SELECT is_lainnya FROM profil_responden_$table_identity WHERE id_profil_responden = profil_responden_$table_identity.id) AS is_lainnya
		FROM kategori_profil_responden_$table_identity
		WHERE nama_kategori_profil_responden != 'Lainnya' && id_profil_responden = " . $this->uri->segment(5));

		$this->form_validation->set_rules('nama_profil_responden', 'Nama Profil Responden', 'trim|required');

		if ($this->form_validation->run() == false) {

			$this->data['nama_profil_responden'] = [
				'name'         => 'nama_profil_responden',
				'id'        => 'nama_profil_responden',
				'type'        => 'text',
				'value'        =>    $this->form_validation->set_value('nama_profil_responden', $profil_responden->nama_profil_responden),
				'class'        => 'form-control',
				'autofocus' => 'autofocus',
				'required' => 'required',
				// 'maxlength' => '20'
			];

			// $this->data['label_isian'] = [
			// 	'name'         => 'label_isian',
			// 	'id'        => 'label_isian',
			// 	'type'        => 'text',
			// 	'value'        =>    $this->form_validation->set_value('label_isian', $profil_responden->label_isian),
			// 	'class'        => 'form-control',
			// 	'autofocus' => 'autofocus'
			// ];

			$this->data['jenis_isian'] = [
				'name'         => 'jenis_isian',
				'type'        => 'hidden',
				'value'        =>    $this->form_validation->set_value('jenis_isian', $profil_responden->jenis_isian)
			];

			return view('profil_responden_survei/form_edit', $this->data);
		} else {

			$input     = $this->input->post(null, true);
			$id_profil_responden = $this->uri->segment(5);



			$data = [
				'nama_profil_responden'     => $input['nama_profil_responden'],
				'type_data' => $input['type_data'],
				// 'label_isian' => $input['label_isian'],
				// 'posisi_label_isian' => $input['posisi_label_isian'],
				'is_required' => $input['is_required']
			];
			$this->db->where('id', $id_profil_responden);
			$this->db->update('profil_responden_' . $table_identity, $data);

			// if ($this->input->post('jenis_isian') == 1) {

			//     $id = $input['id_kategori'];
			//     $pertanyaan_ganda = $input['jawaban'];

			//     for ($i = 0; $i < sizeof($id); $i++) {
			//         $kategori = array(
			//             'nama_kategori_profil_responden' => ($pertanyaan_ganda[$i])
			//         );
			//         $this->db->where('id', $id[$i]);
			//         $this->db->update('kategori_profil_responden_' . $table_identity, $kategori);
			//     }
			// }

			if ($this->data['kategori_profil_responden']->num_rows() > 0) {
				$this->db->query("DELETE FROM kategori_profil_responden_$table_identity WHERE id_profil_responden = $id_profil_responden");
			}

			if ($this->input->post('jenis_isian') == 1) {
				$result = [];
				foreach ($_POST['pilihan_jawaban'] as $key => $val) {
					$result[] = [
						'id_profil_responden' => $id_profil_responden,
						'nama_kategori_profil_responden' => $input['pilihan_jawaban'][$key]
					];
				}
				$this->db->insert_batch('kategori_profil_responden_' . $table_identity, $result);

				//HAPUS KATEGORI YANG KOSONG
				$this->db->query("DELETE FROM kategori_profil_responden_$table_identity WHERE id_profil_responden = $id_profil_responden && nama_kategori_profil_responden = ''");
			}
			$this->session->set_flashdata('message_success', 'Berhasil mengubah data');
			redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/profil-responden-survei', 'refresh');
		}
	}

	public function delete($id = null)
	{
		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$table_identity = $this->db->get()->row()->table_identity;

		$profil_responden = $this->db->get_where('profil_responden_' . $table_identity, array('id' => $this->uri->segment('5')))->row();
		$nama_alias = $profil_responden->nama_alias;

		$this->db->query("ALTER TABLE responden_$table_identity DROP COLUMN $nama_alias");
		$this->db->query("ALTER TABLE trash_responden_$table_identity DROP COLUMN $nama_alias");

		if ($profil_responden->is_lainnya == 1) {
			$nama_lainnya = $nama_alias . '_lainnya';
			$this->db->query("ALTER TABLE responden_$table_identity DROP COLUMN $nama_lainnya");
			$this->db->query("ALTER TABLE trash_responden_$table_identity DROP COLUMN $nama_lainnya");
		}

		$this->db->where('id_profil_responden', $this->uri->segment('5'));
		$this->db->delete('kategori_profil_responden_' . $table_identity);

		$this->db->where('id', $this->uri->segment('5'));
		$this->db->delete('profil_responden_' . $table_identity);

		echo json_encode(array("status" => true));
	}
}