<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DimensiSurveiController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}

		$this->load->library('form_validation');
		$this->load->model('DimensiSurvei_model', 'models');
	}

	public function index($id1, $id2)
	{

		$url = $this->uri->uri_string();
		$this->session->set_userdata('urlback', $url);

		$this->data = [];
		$this->data['title'] = 'Dimensi Survei';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$this->db->select('manage_survey.id AS id_manage_survey, manage_survey.table_identity AS table_identity, is_question');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();
		$this->data['table_identity'] = $manage_survey->table_identity;

		$this->data['is_question'] = $manage_survey->is_question;
		$this->data['jumlah_tambahan'] = ($this->db->get("dimensi_$manage_survey->table_identity")->num_rows()) + 1;
		

		return view('dimensi_survei/index', $this->data);
	}

	public function ajax_list()
	{
		$slug = $this->uri->segment(2);

		$get_identity = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $get_identity->table_identity;
		$dimensi = $this->db->get("dimensi_$table_identity")->num_rows();

		$list = $this->models->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			$no++;
			$row = array();

			for ($i = 1; $i <= $dimensi; ++$i) {
				$selected = $no == $i ? 'selected' : '';
				$urutan[$no][] = '<option value="' . $i . '"' . $selected . '>D' . $i . '</option>';
			}

			
			// $row[] = $no;

			$row[] = '<input value="' . $value->id . '" name="id[]" hidden>
			<select name="kode[]" class="form-control-sm">'
				. implode("<br>", $urutan[$no]) .
				'</select>';


			$row[] = $value->dimensi;
			$row[] = $value->keterangan;
			$row[] = '<button type="button" class="btn btn-light-primary btn-sm" data-toggle="modal" data-target="#edit_' . $value->id . '">
					<i class="fas fa-edit"></i> Edit </button>';

			if ($get_identity->is_question == 1) {
				$row[] = '<a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus ' . $value->dimensi . '" onclick="delete_dimensi(' .$value->id . ')"><i class="fa fa-trash"></i> Delete</a>';
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


	public function update_urutan()
	{
		$manage_survey = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(2)))->row();
		$table_identity = $manage_survey->table_identity;

		foreach ($_POST['id'] as $key => $val) {
			$id = (int)$_POST['id'][$key];
			$kode = $_POST['kode'][$key];
			$this->db->query("UPDATE dimensi_$table_identity SET kode = 'D$kode' WHERE id = $id");
		}

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}



	public function add()
	{
		$this->db->select('manage_survey.id AS id_manage_survey, manage_survey.table_identity AS table_identity');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$input 	= $this->input->post(NULL, TRUE);
		$object = [
			'dimensi' 	=> $input['dimensi'],
			'keterangan' 	=> $input['keterangan'],
			'kode' 	=> $input['kode'],
		];
		$this->db->insert('dimensi_' . $manage_survey->table_identity, $object);

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}



	public function edit()
	{
		$this->db->select('manage_survey.id AS id_manage_survey, manage_survey.table_identity AS table_identity');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$input 	= $this->input->post(NULL, TRUE);
		$object = [
			'dimensi' 	=> $input['dimensi'],
			'keterangan' 	=> $input['keterangan']
		];
		$this->db->where('id', $input['id']);
		$this->db->update('dimensi_' . $manage_survey->table_identity, $object);

		$pesan = 'Data berhasil disimpan';
		$msg = ['sukses' => $pesan];
		echo json_encode($msg);
	}

	public function delete()
	{
		$this->db->select('manage_survey.id AS id_manage_survey, manage_survey.table_identity AS table_identity');
		$this->db->from('manage_survey');
		$this->db->where('manage_survey.slug', $this->uri->segment(2));
		$manage_survey = $this->db->get()->row();

		$this->db->delete('dimensi_' . $manage_survey->table_identity, array('id' => $this->uri->segment(5)));

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
}

/* End of file PertanyaanKualitatifController.php */
/* Location: ./application/controllers/PertanyaanKualitatifController.php */