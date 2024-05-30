<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PertanyaanUnsurSurveiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}
		$this->load->model('PertanyaanUnsurSurvei_model');
		$this->load->library('form_validation');
	}

	public function index($id1 = NULL, $id2 = NULL)
	{
		$this->data = [];
		$this->data['title'] = "Pertanyaan Unsur Pelayanan";

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);
		$this->data['is_question'] = $this->data['profiles']->is_question;
		$table_identity = $this->data['profiles']->table_identity;

		$this->db->select("*, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur, (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 1) AS pilihan_1,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 2) AS pilihan_2,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 3) AS pilihan_3,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 4) AS pilihan_4, 
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 5) AS pilihan_5, 
        pertanyaan_unsur_pelayanan_$table_identity.jenis_pilihan_jawaban AS pilihan, 
        (SELECT COUNT(jawaban_pertanyaan_unsur_$table_identity.id) FROM jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && is_submit = 1 && alasan_pilih_jawaban != '' && jawaban_pertanyaan_unsur_$table_identity.is_active = 1 && skor_jawaban IN (1,2)) AS jumlah_alasan, unsur_pelayanan_$table_identity.id AS id_unsur, if(id_unsur_pelayanan != '', 1, 2) AS unsur_turunan, (SELECT nomor_pertanyaan_terbuka FROM pertanyaan_terbuka_$table_identity WHERE pertanyaan_terbuka_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id GROUP BY id_unsur_pelayanan) AS is_pertanyaan_terbuka, SUBSTR(nomor_unsur,2) AS nomor_harapan");

		$this->db->from("unsur_pelayanan_$table_identity");
		$this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id", 'left');
		$this->db->order_by("unsur_pelayanan_$table_identity.id", 'asc');
		$this->data['unsur_pelayanan'] = $this->db->get();

		//CEK KOLOM
		/*foreach ($this->db->get("manage_survey")->result() as $row) {
			if ($this->db->field_exists('is_required', 'unsur_pelayanan_' . $row->table_identity))
			{
	
			}else{
				$this->db->query("ALTER TABLE unsur_pelayanan_$row->table_identity ADD is_required tinyint(1) NULL DEFAULT '1'");
			}
			
			if ($this->db->field_exists('is_alasan', 'unsur_pelayanan_' . $row->table_identity))
			{
	
			}else{
				$this->db->query("ALTER TABLE unsur_pelayanan_$row->table_identity ADD is_alasan tinyint(1) NULL DEFAULT '1'");
			}
        }*/

		return view('pertanyaan_unsur_survei/index', $this->data);
	}

	public function ajax_list()
	{
		$slug = $this->uri->segment(2);

		$get_identity = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $get_identity->table_identity;

		$list = $this->PertanyaanUnsurSurvei_model->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			$cek_unsur = $this->db->get_where("unsur_pelayanan_$table_identity", array('id_parent' => $value->id_unsur));

			$required = $value->is_required == 1 ? '<b class="text-danger">*</b>' : '';
			$dimensi = $get_identity->is_dimensi == 1 ? '<b class="text-primary">' . $value->kode_dimensi . '. ' . $value->dimensi . '</b>' : '';
			$is_model_pilihan_ganda = $value->is_model_pilihan_ganda == 2 ? '<b class="text-dark">*</b>' : '';

			// //MODEL PILIHAN GANDA
			// if ($value->is_model_pilihan_ganda == 2) {

			// 	$limit_pilih = $value->limit_pilih > 0 ? ' (Max ' . $value->limit_pilih . ')' : '';
			// 	$is_model_pilihan_ganda = '<hr><span class="text-info">** Pertanyaan <b>Bisa Memilih Lebih dari 1</b> Pilihan Jawaban' . $limit_pilih . '</span>';
			// } else {
			// 	$is_model_pilihan_ganda = '<hr><span class="text-info">** Pertanyaan <b>Hanya Bisa Memilih 1</b> Pilihan Jawaban</span>';
			// }



			$no++;
			$row = array();
			$row[] = $no;

			$row[] = $dimensi; //<button type="button" class="btn btn-link" data-toggle="modal" data-target="#exampleModal' . $no . '" title="Ubah nomor unsur dan nama unsur"><i class="flaticon-edit-1"></i></button>';
			$row[] = '<b>' . $value->nomor_unsur . '. ' . $value->nama_unsur_pelayanan . '</b> ' . $required . $is_model_pilihan_ganda . '<br>' . $value->isi_pertanyaan_unsur;


			if ($value->unsur_turunan == 1) {
				$pilihan = [];
				foreach ($this->db->get_where("kategori_unsur_pelayanan_$table_identity", array('id_pertanyaan_unsur' => $value->id_pertanyaan_unsur))->result() as $get) {
					$pilihan[] = '<label><input type="radio">&ensp;' . $get->nama_kategori_unsur_pelayanan . '&emsp;</label>';
				};
				$row[] = implode("", $pilihan);// . $is_model_pilihan_ganda;
			} else {
				$row[] = '<span class="text-danger">Unsur ini memiliki Sub.</span>';
			}


			$row[] = anchor($this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur/edit/' . $value->id_unsur, '<i class="fa fa-edit"></i> Edit', ['class' => 'btn btn-light-primary btn-sm font-weight-bold shadow']);


			if ($get_identity->is_question == 1) {
				if ($cek_unsur->num_rows() == 0) {
					$row[] = '<a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus ' . $value->nama_unsur_pelayanan . '" onclick="delete_data(' . $value->id_unsur . ')"><i class="fa fa-trash"></i> Delete</a>';
				} else {
					$row[] = '<a class="btn btn-light-primary btn-sm font-weight-bold shadow" onclick="cek()"><i class="fa fa-trash"></i> Delete</a>';
				}
			}

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->PertanyaanUnsurSurvei_model->count_all($table_identity),
			"recordsFiltered" => $this->PertanyaanUnsurSurvei_model->count_filtered($table_identity),
			"data" => $data,
		);

		echo json_encode($output);
	}

	public function add($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = "Tambah Pertanyaan Unsur";
		$this->load->library('uuid');

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['manage_survey'] = $manage_survey;
		$table_identity = $manage_survey->table_identity;

		if ($manage_survey->skala_likert == 5) {
			$skala_likert = 5;
		} else {
			$skala_likert = 4;
		};

		$this->data['pilihan'] = $this->PertanyaanUnsurSurvei_model->tampil_data($skala_likert);


		$this->form_validation->set_rules('nama_unsur_pelayanan', 'Nama Unsur Pelayanan', 'trim|required');


		$this->data['id_dimensi'] = [
			'name'         => 'id_dimensi',
			'id'         => 'id_dimensi',
			'options'     => $this->PertanyaanUnsurSurvei_model->dropdown_dimensi($table_identity),
			'selected'     => $this->form_validation->set_value('id_dimensi'),
			'class'     => "form-control",
			'required' => 'required',
		];


		$this->data['nama_unsur_pelayanan'] = [
			'name' 		=> 'nama_unsur_pelayanan',
			'id'		=> '',
			'type'		=> 'text',
			'value'		=>	$this->form_validation->set_value('nama_unsur_pelayanan'),
			'class'		=> 'form-control',
			'autofocus' => 'autofocus',
			'required' => 'required'
		];

		$this->data['isi_pertanyaan_unsur'] = [
			'name' 		=> 'isi_pertanyaan_unsur',
			'id'		=> 'isi_pertanyaan_unsur',
			'type'		=> 'text',
			'value'		=>	$this->form_validation->set_value('isi_pertanyaan_unsur'),
			'class'		=> 'form-control',
			// 'rows'		 => '3',
			// 'required' => 'required'
		];

		$this->data['jumlah_unsur'] = ($this->db->get_where("unsur_pelayanan_$table_identity", array('id_parent' => 0))->num_rows()) + 1;

		if ($this->form_validation->run() == FALSE) {

			return view('pertanyaan_unsur_survei/add', $this->data);
		} else {
			$input 	= $this->input->post(NULL, TRUE);

			if ($input['is_sub_unsur_pelayanan'] == 2) {

				if ($input['isi_pertanyaan_unsur'] == NULL) {
					$this->session->set_flashdata('message_danger', 'Gagal Menambah Pertanyaan Unsur!');
					redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur', 'refresh');
				}

				$this->db->select('(COUNT(nomor_unsur)+1)AS kode_unsur');
				$this->db->from('unsur_pelayanan_' . $table_identity);
				$this->db->where('id_parent = 0');
				$nomor_unsur = $this->db->get()->row()->kode_unsur;

				$object = [
					'uuid' => $this->uuid->v4(),
					'id_jenis_pelayanan' => $manage_survey->id_jenis_pelayanan,
					'nama_unsur_pelayanan'     => $input['nama_unsur_pelayanan'],
					'nomor_unsur' => 'U' . $nomor_unsur,
					'is_sub_unsur_pelayanan' => 2,
					'id_parent' => '0',
					'id_dimensi' => $input['id_dimensi'],
					'is_required' => $input['is_required'],
					'is_alasan' => 2,
				];
				$this->db->insert('unsur_pelayanan_' . $table_identity, $object);

				$id_unsur = $this->db->insert_id();
				$limit_pilih = $input['is_model_pilihan_ganda'] == 2 ? $input['limit_pilih'] : 0;
				$object_1 = [
					'id_unsur_pelayanan' 	=> $id_unsur,
					'isi_pertanyaan_unsur' 	=> $input['isi_pertanyaan_unsur'],
					'is_model_pilihan_ganda' => $input['is_model_pilihan_ganda'],
					'limit_pilih' => $limit_pilih
				];
				$this->db->insert('pertanyaan_unsur_pelayanan_' . $table_identity, $object_1);
				$id_pertanyaan_unsur = $this->db->insert_id();

				$result = array();
				foreach ($_POST['pilihan_jawaban'] as $key => $val) {
					$result[] = array(
						'id_unsur_pelayanan' => $id_unsur,
						'id_pertanyaan_unsur' => $id_pertanyaan_unsur,
						'nomor_kategori_unsur_pelayanan' => $key + 1,
						'nama_kategori_unsur_pelayanan' => $input['pilihan_jawaban'][$key],
					);
				}
				$this->db->insert_batch('kategori_unsur_pelayanan_' . $table_identity, $result);

				//DELETE PILIHAN JAWABAN YANG KOSONG
				$this->db->query("DELETE FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = $id_pertanyaan_unsur && nama_kategori_unsur_pelayanan = ''");


				$this->session->set_flashdata('message_success', 'Berhasil Menambah Data!');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur', 'refresh');
			} else {

				$this->db->select('(COUNT(nomor_unsur)+1)AS kode_unsur');
				$this->db->from('unsur_pelayanan_' . $table_identity);
				$this->db->where('id_parent = 0');
				$nomor_unsur = $this->db->get()->row()->kode_unsur;

				$object = [
					'uuid' => $this->uuid->v4(),
					'id_jenis_pelayanan' => $manage_survey->id_jenis_pelayanan,
					'nama_unsur_pelayanan'     => $input['nama_unsur_pelayanan'],
					'nomor_unsur' => 'U' . $nomor_unsur,
					'is_sub_unsur_pelayanan' => 2,
					'id_parent' => '0',
					'id_dimensi' => $input['id_dimensi'],
					'is_required' => $input['is_required'],
					'is_alasan' => 2,
				];
				$this->db->insert('unsur_pelayanan_' . $table_identity, $object);
				$id_unsur = $this->db->insert_id();

				$this->session->set_flashdata('message_success', 'Berhasil Menambah Data!');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur/add-sub/' . $id_unsur, 'refresh');
			};
		}
	}

	public function add_sub($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = "Tambah Pertanyaan Sub Unsur";
		$this->load->library('uuid');

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['manage_survey'] = $manage_survey;
		$table_identity = $manage_survey->table_identity;

		if ($manage_survey->skala_likert == 5) {
			$skala_likert = 5;
		} else {
			$skala_likert = 4;
		};
		$this->data['pilihan'] = $this->PertanyaanUnsurSurvei_model->tampil_data($skala_likert);

		$this->form_validation->set_rules('nama_unsur_pelayanan', 'Nama Unsur Pelayanan', 'trim|required');
		$this->form_validation->set_rules('id_parent', 'Id Parent', 'trim|required');

		$this->data['id_parent'] = [
			'name'         => 'id_parent',
			'id'         => 'id_parent',
			'options'     => $this->PertanyaanUnsurSurvei_model->dropdown_sub_unsur_pelayanan($table_identity),
			'selected'     => $this->form_validation->set_value('id_parents'),
			'class'     => "form-control",
			'required' => 'required',
		];

		$this->data['nama_unsur_pelayanan'] = [
			'name' 		=> 'nama_unsur_pelayanan',
			'id'		=> '',
			'type'		=> 'text',
			'value'		=>	$this->form_validation->set_value('nama_unsur_pelayanan'),
			'class'		=> 'form-control',
			'required' => 'required'
		];

		$this->data['isi_pertanyaan_unsur'] = [
			'name' 		=> 'isi_pertanyaan_unsur',
			'id'		=> 'isi_pertanyaan_unsur',
			'type'		=> 'text',
			'value'		=>	$this->form_validation->set_value('isi_pertanyaan_unsur'),
			'class'		=> 'form-control'
		];


		if ($this->form_validation->run() == FALSE) {

			return view('pertanyaan_unsur_survei/add_sub', $this->data);
		} else {
			$input 	= $this->input->post(NULL, TRUE);

			if ($input['isi_pertanyaan_unsur'] == NULL) {
				$this->session->set_flashdata('message_danger', 'Gagal Menambah Pertanyaan Unsur!');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur', 'refresh');
			}

			$id_parent = $input['id_parent'];

			$this->db->select('*');
			$this->db->from('unsur_pelayanan_' . $table_identity);
			$this->db->where('id =' . $id_parent);
			$unsur = $this->db->get()->row();
			$nomor = $unsur->nomor_unsur;

			$this->db->select('(COUNT(id_parent)+1)AS nomor_sub');
			$this->db->from('unsur_pelayanan_' . $table_identity);
			$this->db->where('id_parent =' . $id_parent);
			$sub = $this->db->get()->row()->nomor_sub;

			$object = [
				'uuid' => $this->uuid->v4(),
				'nomor_unsur' => $nomor . '.' . $sub,
				'nama_unsur_pelayanan'     => $input['nama_unsur_pelayanan'],
				'is_sub_unsur_pelayanan' => 1,
				'id_parent' => $id_parent,
				'id_jenis_pelayanan' => $manage_survey->id_jenis_pelayanan,
				'id_dimensi' => $unsur->id_dimensi,
				'is_required' => $input['is_required'],
				'is_alasan' => 2
			];
			$this->db->insert('unsur_pelayanan_' . $table_identity, $object);

			$id_unsur = $this->db->insert_id();
			$limit_pilih = $input['is_model_pilihan_ganda'] == 2 ? $input['limit_pilih'] : 0;
			$object_1 = [
				'id_unsur_pelayanan' 	=> $id_unsur,
				'isi_pertanyaan_unsur' 	=> $input['isi_pertanyaan_unsur'],
				'is_model_pilihan_ganda' => $input['is_model_pilihan_ganda'],
				'limit_pilih' => $limit_pilih
			];
			$this->db->insert('pertanyaan_unsur_pelayanan_' . $table_identity, $object_1);
			$id_pertanyaan_unsur = $this->db->insert_id();


			$result = array();
			foreach ($_POST['pilihan_jawaban'] as $key => $val) {
				$result[] = array(
					'id_unsur_pelayanan' => $id_unsur,
					'id_pertanyaan_unsur' => $id_pertanyaan_unsur,
					'nomor_kategori_unsur_pelayanan' => $key + 1,
					'nama_kategori_unsur_pelayanan' => $input['pilihan_jawaban'][$key]
				);
			}
			$this->db->insert_batch('kategori_unsur_pelayanan_' . $table_identity, $result);

			//DELETE PILIHAN JAWABAN YANG KOSONG
			$this->db->query("DELETE FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = $id_pertanyaan_unsur && nama_kategori_unsur_pelayanan = ''");



			if ($input['is_submit'] == 1) {
				$this->session->set_flashdata('message_success', 'Berhasil Menambah Data!');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur', 'refresh');
			} else {
				$this->session->set_flashdata('message_success', 'Berhasil Menambah Data!');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur/add-sub/' . $id_parent, 'refresh');
			}
		}
	}


	public function edit($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = "Edit Pertanyaan Unsur";
		$this->load->library('uuid');

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$table_identity = $this->data['manage_survey']->table_identity;


		$this->db->select("*, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur, if(id_unsur_pelayanan != '', 1, 2) AS unsur_turunan");
		$this->db->from("unsur_pelayanan_$table_identity");
		$this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id", 'left');
		$this->db->where("unsur_pelayanan_$table_identity.id =", $this->uri->segment(5));
		$pertanyaan_unsur = $this->db->get()->row();
		$this->data['nomor_unsur'] = $pertanyaan_unsur->nomor_unsur;
		$this->data['unsur_turunan'] = $pertanyaan_unsur->unsur_turunan;
		$this->data['pertanyaan_unsur'] = $pertanyaan_unsur;


		$this->db->select('*');
		$this->db->from('kategori_unsur_pelayanan_' . $table_identity);
		$this->db->where('id_pertanyaan_unsur', $pertanyaan_unsur->id_pertanyaan_unsur);
		$this->data['nama_kategori_unsur'] = $this->db->get()->result();

		$this->form_validation->set_rules('nama_unsur_pelayanan', 'Nama Unsur Pelayanan', 'trim|required');

		if ($this->form_validation->run() == FALSE) {

			$this->data['id_dimensi'] = [
				'name'         => 'id_dimensi',
				'id'         => 'id_dimensi',
				'options'     => $this->PertanyaanUnsurSurvei_model->dropdown_dimensi($table_identity),
				'selected'     => $this->form_validation->set_value('id_dimensi', $pertanyaan_unsur->id_dimensi),
				'class'     => "form-control",
				'required' => 'required',
			];

			$this->data['nama_unsur_pelayanan'] = [
				'name' 		=> 'nama_unsur_pelayanan',
				'id'		=> 'nama_unsur_pelayanan',
				'type'		=> 'text',
				'class'		=> 'form-control',
				'value'		=>	$this->form_validation->set_value('nama_unsur_pelayanan', $pertanyaan_unsur->nama_unsur_pelayanan),
				'autofocus' => 'autofocus'
			];

			$this->data['isi_pertanyaan_unsur'] = [
				'name' 		=> 'isi_pertanyaan_unsur',
				'id'		=> 'isi_pertanyaan_unsur',
				'type'		=> 'text',
				'value'		=>	$this->form_validation->set_value('isi_pertanyaan_unsur', $pertanyaan_unsur->isi_pertanyaan_unsur),
				'class'		=> 'form-control',
				// 'rows'		 => '3',
				// 'autofocus' => 'autofocus',
				// 'required' => 'required'
			];

			return view('pertanyaan_unsur_survei/edit', $this->data);
		} else {
			$input = $this->input->post(NULL, TRUE);

			$object = [
				'nama_unsur_pelayanan' 	=> $input['nama_unsur_pelayanan'],
				'id_dimensi' => $input['id_dimensi'],
				'is_required' => $input['is_required'],
			];
			$this->db->where('id', $this->uri->segment(5));
			$this->db->update('unsur_pelayanan_' . $table_identity, $object);

			if ($pertanyaan_unsur->unsur_turunan == 1) {

				if ($input['isi_pertanyaan_unsur'] == NULL) {
					$this->session->set_flashdata('message_danger', 'Gagal Menambah Pertanyaan Unsur!');
					redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur/edit/' . $this->uri->segment(5), 'refresh');
				}


				$limit_pilih = $input['is_model_pilihan_ganda'] == 2 ? $input['limit_pilih'] : 0;
				$object_1 = [
					'isi_pertanyaan_unsur' 	=> $input['isi_pertanyaan_unsur'],
					'is_model_pilihan_ganda' => $input['is_model_pilihan_ganda'],
					'limit_pilih' => $limit_pilih
				];
				$this->db->where('id', $pertanyaan_unsur->id_pertanyaan_unsur);
				$this->db->update('pertanyaan_unsur_pelayanan_' . $table_identity, $object_1);


				$id = $input['id_kategori'];
				$nama_kategori_input = $input['nama_kategori_unsur_pelayanan'];
				for ($i = 0; $i < sizeof($id); $i++) {
					$kategori = array(
						'id' => $id[$i],
						'nama_kategori_unsur_pelayanan' => $nama_kategori_input[$i]
					);
					$this->db->where('id', $id[$i]);
					$this->db->update('kategori_unsur_pelayanan_' . $table_identity, $kategori);
				}
			}

			$this->session->set_flashdata('message_success', 'Berhasil mengubah data');
			redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-unsur', 'refresh');
		}
	}

	public function delete()
	{
		$table_identity = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(2)))->row()->table_identity;

		$pertanyaan_unsur = $this->db->query("SELECT *,
		pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur,
		IF(id_unsur_pelayanan != '', 1, 2) AS unsur_turunan
		
		FROM unsur_pelayanan_$table_identity
		LEFT JOIN pertanyaan_unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		WHERE unsur_pelayanan_$table_identity.id = " . $this->uri->segment(5))->row();
		// var_dump($pertanyaan_unsur);

		if ($pertanyaan_unsur->unsur_turunan == 1) {

			$cek_id_terbuka = $this->db->get_where('pertanyaan_terbuka_' . $table_identity, array('id_unsur_pelayanan' => $this->uri->segment(5)));
			if ($cek_id_terbuka->num_rows() > 0) {

				$num_rows = $cek_id_terbuka->num_rows();
				$rows = $cek_id_terbuka->row();

				$this->db->query("UPDATE pertanyaan_terbuka_$table_identity
				SET nomor_pertanyaan_terbuka = CONCAT('T', SUBSTR(nomor_pertanyaan_terbuka, 2) - $num_rows)
				WHERE (SUBSTR(nomor_pertanyaan_terbuka, 2) + 0) > (SUBSTR('$rows->nomor_pertanyaan_terbuka', 2) + 0)");

				foreach ($cek_id_terbuka->result() as $row) {
					$this->db->where('id_pertanyaan_terbuka', $row->id);
					$this->db->delete('perincian_pertanyaan_terbuka_' . $table_identity);
				}

				$this->db->where('id_unsur_pelayanan', $this->uri->segment(5));
				$this->db->delete('pertanyaan_terbuka_' . $table_identity);
			} 


			//Urutkan lagi nomor unsur
			if ($pertanyaan_unsur->id_parent == 0) {
				$this->db->query("UPDATE unsur_pelayanan_$table_identity
				SET nomor_unsur = CONCAT('U', SUBSTR(nomor_unsur, 2) - 1)
				WHERE (SUBSTR(nomor_unsur, 2) + 0) > (SUBSTR('$pertanyaan_unsur->nomor_unsur', 2) + 0)");
			} else {

				foreach ($this->db->query("SELECT *, SUBSTR(nomor_unsur, 2) AS substr
				FROM unsur_pelayanan_$table_identity
				WHERE id_parent = $pertanyaan_unsur->id_parent && (SUBSTR(nomor_unsur, 2) + 0) > (SUBSTR('$pertanyaan_unsur->nomor_unsur', 2) + 0)")->result() as $row) {

					$new_nomor_unsur = 'U' . ($row->substr - 0.1);
					$this->db->query("UPDATE unsur_pelayanan_$table_identity
					SET nomor_unsur = '$new_nomor_unsur'
					WHERE id = $row->id");
				}
			}

			$this->db->where('id_pertanyaan_unsur', $pertanyaan_unsur->id_pertanyaan_unsur);
			$this->db->delete('kategori_unsur_pelayanan_' . $table_identity);

			$this->db->where('id', $pertanyaan_unsur->id_pertanyaan_unsur);
			$this->db->delete('pertanyaan_unsur_pelayanan_' . $table_identity);
		}
		$this->db->where('id', $this->uri->segment(5));
		$this->db->delete('unsur_pelayanan_' . $table_identity);

		echo json_encode(array("status" => TRUE));
	}


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


	public function edit_unsur()
	{
		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$input 	= $this->input->post(NULL, TRUE);
		$object = [
			'nomor_unsur' 	=> $input['nomor_unsur'],
			'nama_unsur_pelayanan' 	=> $input['nama_unsur_pelayanan']
		];

		$this->db->where('id', $input['id']);
		$this->db->update('unsur_pelayanan_' . $manage_survey->table_identity, $object);

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}


	public function cari()
	{
		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$skala_likert = $manage_survey->skala_likert;

		$id = $_GET['id'];
		$cari = $this->PertanyaanUnsurPelayanan_model->cari($id)->result();
		echo json_encode($cari);
	}
}

/* End of file PertanyaanUnsurSurveiController.php */
/* Location: ./application/controllers/PertanyaanUnsurSurveiController.php */
