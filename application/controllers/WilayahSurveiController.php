<?php
defined('BASEPATH') or exit('No direct script access allowed');

class WilayahSurveiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}

		$this->load->library('form_validation');
		// $this->load->model('DefinisiSkalaSurvei_model', 'Models');
		$this->load->model('LayananSurvei_model', 'models');
	}

	public function index($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Wilayah Survei';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$slug = $this->uri->segment(2);
		$manage_survey = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $manage_survey->table_identity;

		$this->data['layanan'] = $this->db->get("wilayah_survei_$table_identity");


		// foreach ($this->db->get("manage_survey")->result() as $row) {

		// 	$this->db->query("ALTER TABLE responden_$row->table_identity ADD id_wilayah TEXT");
		// 	$this->db->query("ALTER TABLE trash_responden_$row->table_identity ADD id_wilayah TEXT");
		// 	// $this->db->query("ALTER TABLE aspek_$row->table_identity ADD kode VARCHAR(255)");
		// 	// $this->db->query("CREATE TABLE kategori_pertanyaan_terbuka_$row->table_identity LIKE kategori_pertanyaan_terbuka");
		// }



		return view('wilayah_survei/index', $this->data);
	}


	


	public function add()
	{
		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		// $urutan = $this->db->get("wilayah_survei_$manage_survey->table_identity")->num_rows() + 1;

		$input 	= $this->input->post(NULL, TRUE);
		$object = [
			'nama_wilayah' 	=> $input['nama_wilayah'],
			// 'urutan' 	=> $urutan,
		];
		$this->db->insert('wilayah_survei_' . $manage_survey->table_identity, $object);

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}


	public function edit()
	{
		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$input 	= $this->input->post(NULL, TRUE);
		$object = [
			'nama_wilayah' 	=> $input['nama_wilayah'],
			// 'is_active' 	=> $input['is_active']
		];

		$this->db->where('id', $input['id']);
		$this->db->update('wilayah_survei_' . $manage_survey->table_identity, $object);

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}


	public function delete()
	{
		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$this->db->where('id', $this->uri->segment(5));
		$this->db->delete('wilayah_survei_' . $manage_survey->table_identity);

		echo json_encode(array("status" => TRUE));
	}



	public function grafik($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Grafik Wilayah Survei';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);


		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['table_identity'] = $manage_survey->table_identity;


		if ($this->db->get_where('survey_' . $manage_survey->table_identity, array('is_submit' => 1))->num_rows() == 0) {
			$this->data['pesan'] = 'Survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}

		return view('wilayah_survei/grafik', $this->data);
	}


	// public function update_urutan()
	// {
	// 	$manage_survey = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(2)))->row();
	// 	$table_identity = $manage_survey->table_identity;

	// 	foreach ($_POST['id'] as $key => $val) {
	// 		$id = (int)$_POST['id'][$key];
	// 		$urutan = $_POST['urutan'][$key];
	// 		$this->db->query("UPDATE wilayah_survei_$table_identity SET urutan=$urutan WHERE id=$id");
	// 	}

	// 	$pesan = 'Data berhasil disimpan';
	// 	$msg = ['sukses' => $pesan];
	// 	echo json_encode($msg);
	// }


	



	// public function update_chart($id1, $id2)
	// {
	// 	$mode = $_POST['mode'];
	// 	$id = $_POST['nilai_id'];

	// 	if ($mode == 'true') {
	// 		$object = [
	// 			'is_chart_layanan_survei' => 1
	// 		];
	// 		$this->db->where('slug', $id2);
	// 		$this->db->update('manage_survey', $object);


	// 		$message = 'Chart menampilkan semua data layanan!';
	// 		$success = 'Enabled';
	// 		echo json_encode(array('message' => $message, '$success' => $success));
	// 	} else if ($mode == 'false') {
	// 		$object = [
	// 			'is_chart_layanan_survei' => 2
	// 		];
	// 		$this->db->where('slug', $id2);
	// 		$this->db->update('manage_survey', $object);


	// 		$message = 'Chart hanya menampilkan data layanan yang bernilai!';
	// 		$success = 'Disabled';
	// 		echo json_encode(array('message' => $message, 'success' => $success));
	// 	}
	// }
}

/* End of file PertanyaanKualitatifController.php */
/* Location: ./application/controllers/PertanyaanKualitatifController.php */
