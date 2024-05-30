<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Carbon\Carbon;

class DivisiController extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}

		$this->load->library('form_validation');
		$this->load->helper('security');
		$this->load->model('KelolaDivision_model');
	}

	public function index()
	{
		$user = $this->ion_auth->user()->row();
		$user_identity = $user->user_identity;

		$this->data = [];
		$this->data['title'] = "Divisi";
		$this->data['btn_add_divisi'] = '<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambah"><i class="fas fa-plus"></i> Tambah Divisi</button>';

		if ($this->db->table_exists('division_' . $user_identity) ){
			$this->db->from('division_' . $user_identity);
			$current = $this->db->get();
		}else{
			$this->db->query("CREATE TABLE division_" . $user_identity . " LIKE division");
			$this->db->query("INSERT INTO division_" . $user_identity . " SELECT * FROM division");
			$this->db->from('division_' . $user_identity);
			$current = $this->db->get();
		}
		
		$this->data['division'] = $current;

		return view('divisi/index', $this->data);
	}

	public function ajax_list_division()
	{
		$user = $this->ion_auth->user()->row();
		$user_identity = $user->user_identity;

		$list = $this->KelolaDivision_model->get_datatables($user_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			$no++;
			$row = array();
			$row[] = $no;

			if ($value->is_default == 1) {

				$row[] = '<i class="fa fa-bookmark text-primary" aria-hidden="true"></i> <b>' . $value->division_name . '</b>';
				$row[] = '';
				$row[] = '';
			} else {

				$row[] = $value->division_name;
				$row[] = '<button type="button" class="btn btn-light-primary btn-sm" data-toggle="modal" data-target="#edit' . $value->id_division . '"><i class="fa fa-edit"></i> Edit</button>';
				$row[] = '<a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus ' . $value->division_name . '" onclick="delete_divisi(' . "'" . $value->id_division . "'" . ')"><i class="fa fa-trash"></i> Delete</a>';
			};
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->KelolaDivision_model->count_all($user_identity),
			"recordsFiltered" => $this->KelolaDivision_model->count_filtered($user_identity),
			"data" => $data,
		);

		echo json_encode($output);
	}

	public function add_division()
	{
		$user = $this->ion_auth->user()->row();
		$user_identity = $user->user_identity;

		$object = [
			'id_berlangganan' => $this->input->post('id_berlangganan'),
			'division_name' => $this->input->post('division_name'),
			'is_default' => '0',
		];
		// var_dump($object);
		$this->db->insert('division_' . $user_identity, $object);

		$this->session->set_flashdata('message_success', 'Berhasil menambah data');
		redirect(base_url() . $this->session->userdata('username') . '/divisi', 'refresh');
	}

	public function edit_division()
	{
		$user = $this->ion_auth->user()->row();
		$user_identity = $user->user_identity;

		$object = [
			'division_name' => $this->input->post('division_name')
		];
		// var_dump($object);
		$this->db->where('id',  $this->input->post('id'));
		$this->db->update('division_' . $user_identity, $object);

		$this->session->set_flashdata('message_success', 'Berhasil mengubah data');
		redirect(base_url() . $this->session->userdata('username') . '/divisi', 'refresh');
	}

	public function delete_division()
	{
		$user = $this->ion_auth->user()->row();
		$user_identity = $user->user_identity;

		$cek_spv = $this->db->get_where('supervisor', array('id_division' => $this->uri->segment(4)));
		if ($cek_spv->num_rows() > 0) {
			// ubah divisi ke default terlebih dahulu
			$object = [
				'id_division' => 1,
			];
			$this->db->where('id_division', $this->uri->segment(4));
			$this->db->update('supervisor', $object);
		}
		$this->db->where('id', $this->uri->segment(4));
		$this->db->delete('division_' . $user_identity);

		echo json_encode(array("status" => TRUE));
	}
}

/* End of file DivisiController.php */
/* Location: ./application/controllers/DivisiController.php */