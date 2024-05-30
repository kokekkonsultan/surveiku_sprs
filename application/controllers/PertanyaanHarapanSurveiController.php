<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PertanyaanHarapanSurveiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}
		$this->load->model('PertanyaanHarapanSurvei_model');
		$this->load->library('form_validation');
	}

	public function index($id1 = NULL, $id2 = NULL)
	{
		$this->data = [];
		$this->data['title'] = "Pertanyaan Harapan";

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();

		if ($this->data['manage_survey']->skala_likert == 5) {
			$skala_likert = 5;
		} else {
			$skala_likert = 4;
		}

		$this->data['pilihan_jawaban'] = $this->PertanyaanHarapanSurvei_model->tampil_data($skala_likert);

		return view('pertanyaan_harapan_survei/index', $this->data);
	}

	public function ajax_list()
	{
		$slug = $this->uri->segment(2);

		$get_identity = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $get_identity->table_identity;

		$list = $this->PertanyaanHarapanSurvei_model->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			if ($get_identity->skala_likert == 5) {
				$pilihan_5 = '<label><input type="radio">&ensp;' . $value->pilihan_5 . '&emsp;</label>';
			} else {
				$pilihan_5 = '';
			}

			$no++;
			$row = array();
			$row[] = $no;
			$row[] = '<b>' . $value->nomor_unsur . '. ' . $value->nama_unsur_pelayanan . '</b>';
			$row[] = $value->isi_pertanyaan_unsur;
			$row[] = '<label><input type="radio">&ensp;' . $value->pilihan_1 . '&emsp;</label><label><input type="radio">&ensp;' . $value->pilihan_2 . '&emsp;</label><label><input type="radio">&ensp;' . $value->pilihan_3 . '&emsp;</label><label><input type="radio">&ensp;' . $value->pilihan_4 . '&emsp;</label>' . $pilihan_5;


			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->PertanyaanHarapanSurvei_model->count_all($table_identity),
			"recordsFiltered" => $this->PertanyaanHarapanSurvei_model->count_filtered($table_identity),
			"data" => $data,
		);

		echo json_encode($output);
	}

	public function edit($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = "Pertanyaan Harapan";

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$this->data['manage_survey'] = $this->db->get()->row();
		$table_identity = $this->data['manage_survey']->table_identity;

		$this->form_validation->set_rules('pilihan_1', 'Pilihan 1', 'trim|required');
		$this->form_validation->set_rules('pilihan_2', 'Pilihan 2', 'trim|required');
		$this->form_validation->set_rules('pilihan_3', 'Pilihan 3', 'trim|required');
		$this->form_validation->set_rules('pilihan_4', 'Pilihan 4', 'trim|required');

		if ($this->form_validation->run() == FALSE) {
			return view('pertanyaan_harapan_survei/index', $this->data);
		} else {
			$input     = $this->input->post(NULL, TRUE);
			$object_1 = [
				'nama_tingkat_kepentingan'     => $input['pilihan_1']
			];
			$this->db->where('nomor_tingkat_kepentingan', 1);
			$this->db->update('nilai_tingkat_kepentingan_' . $table_identity, $object_1);

			$object_2 = [
				'nama_tingkat_kepentingan'     => $input['pilihan_2']
			];
			$this->db->where('nomor_tingkat_kepentingan', 2);
			$this->db->update('nilai_tingkat_kepentingan_' . $table_identity, $object_2);

			$object_3 = [
				'nama_tingkat_kepentingan'     => $input['pilihan_3']
			];
			$this->db->where('nomor_tingkat_kepentingan', 3);
			$this->db->update('nilai_tingkat_kepentingan_' . $table_identity, $object_3);

			$object_4 = [
				'nama_tingkat_kepentingan'     => $input['pilihan_4']
			];
			$this->db->where('nomor_tingkat_kepentingan', 4);
			$this->db->update('nilai_tingkat_kepentingan_' . $table_identity, $object_4);


			if ($this->data['manage_survey']->skala_likert == 5) {
				$object_5 = [
					'nama_tingkat_kepentingan'     => $input['pilihan_5']
				];
				$this->db->where('nomor_tingkat_kepentingan', 5);
				$this->db->update('nilai_tingkat_kepentingan_' . $table_identity, $object_5);
			}

			// var_dump($object_1);
		}

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}

	public function cari()
	{
		$id = $_GET['id'];
		$cari = $this->PertanyaanHarapanSurvei_model->cari($id)->result();
		echo json_encode($cari);
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
}

/* End of file PertanyaanUnsurSurveiController.php */
/* Location: ./application/controllers/PertanyaanUnsurSurveiController.php */