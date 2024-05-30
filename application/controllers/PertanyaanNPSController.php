<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PertanyaanNPSController extends Client_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}
		$this->load->model('PertanyaanNPS_model', 'models');
		$this->load->library('form_validation');
	}

	public function index($id1 = NULL, $id2 = NULL)
	{
		$this->data = [];
		$this->data['title'] = "Pertanyaan Net Promoter Score (NPS)";

		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);
		$this->data['manage_survey'] = $this->db->get_where('manage_survey', ['slug' => $this->uri->segment(2)])->row();


		return view('pertanyaan_nps/index', $this->data);
	}

	public function ajax_list()
	{
		$slug = $this->uri->segment(2);

		$get_identity = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $get_identity->table_identity;

		$list = $this->models->get_datatables($table_identity);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			if ($value->is_required == 1) {
				$is_required = '<span class="badge badge-secondary">Wajib diiisi</span>';
			} else {
				$is_required = '<span class="badge badge-secondary">Tidak Wajib diiisi</span>';
			}

			$array_pilihan = [];
			foreach ($this->db->get_where("pilihan_jawaban_nps_$table_identity", ['id_pertanyaan_nps' => $value->id])->result() as $get) {
				$array_pilihan[] = '<label><input type="radio">&ensp;<img src="' . base_url() . 'assets/img/emoji/' . $get->nama_kategori . '" width="20">&emsp;</label>';
			}

			$no++;
			$row = array();
			$row[] = $no;
			$row[] = $value->isi_pertanyaan;
			$row[] = $is_required;
			$row[] = implode("", $array_pilihan);

			$row[] = anchor($this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-nps/edit/' . $value->id, '<i class="fa fa-edit"></i> Edit', ['class' => 'btn btn-light-primary btn-sm font-weight-bold shadow']);

			if ($get_identity->is_question == 1) {
				$row[] = '<a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus ' . $value->isi_pertanyaan . '" onclick="delete_pertanyaan_nps(' . $value->id . ')"><i class="fa fa-trash"></i> Delete</a>';
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

	public function add($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = "Tambah Pertanyaan NPS";
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);
		$manage_survey = $this->db->get_where('manage_survey', ['slug' => $this->uri->segment(2)])->row();


		$this->form_validation->set_rules('isi_pertanyaan', 'Isi Pertanyaan', 'trim|required');
		if ($this->form_validation->run() == FALSE) {

			return view('pertanyaan_nps/add', $this->data);
		} else {

			$input 	= $this->input->post(NULL, TRUE);

			$object = [
				'isi_pertanyaan' 	=> $input['isi_pertanyaan'],
				'is_required' 	=> $input['is_required']
			];
			$this->db->insert("pertanyaan_nps_$manage_survey->table_identity", $object);

			$insert_id = $this->db->insert_id();
			$this->db->query("INSERT INTO pilihan_jawaban_nps_$manage_survey->table_identity
			(id_pertanyaan_nps, nama_kategori, bobot) VALUES
			($insert_id, 'e-0.png', 0),
			($insert_id, 'e-1.png', 1),
			($insert_id, 'e-2.png', 2),
			($insert_id, 'e-3.png', 3),
			($insert_id, 'e-4.png', 4),
			($insert_id, 'e-5.png', 5),
			($insert_id, 'e-6.png', 6),
			($insert_id, 'e-7.png', 7),
			($insert_id, 'e-8.png', 8),
			($insert_id, 'e-9.png', 9),
			($insert_id, 'e-10.png', 10)");


			if ($this->db->affected_rows() > 0) {
				$this->session->set_flashdata('message_success', 'Berhasil menambah data');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-nps', 'refresh');
			} else {

				$this->data['message_data_danger'] = "Gagal menambah data";
				return view('pertanyaan_nps/add', $this->data);
			}
		}
	}



	public function edit($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = "Edit Pertanyaan NPS";
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);
		$manage_survey = $this->db->get_where('manage_survey', ['slug' => $this->uri->segment(2)])->row();

		$this->data['pertanyaan_nps'] = $this->db->get_where("pertanyaan_nps_$manage_survey->table_identity", ['id' => $this->uri->segment(5)])->row();

		$this->form_validation->set_rules('isi_pertanyaan', 'Isi Pertanyaan', 'trim|required');

		if ($this->form_validation->run() == FALSE) {

			return view('pertanyaan_nps/edit', $this->data);
		} else {

			$input 	= $this->input->post(NULL, TRUE);
			$object = [
				'isi_pertanyaan' 	=> $input['isi_pertanyaan'],
				'is_required' 	=> $input['is_required']
			];
			$this->db->where('id', $this->uri->segment(5));
			$this->db->update('pertanyaan_nps_' . $manage_survey->table_identity, $object);

			if ($this->db->affected_rows() > 0) {

				$this->session->set_flashdata('message_success', 'Berhasil mengubah data');
				redirect(base_url() . $this->session->userdata('username') . '/' . $this->uri->segment(2) . '/pertanyaan-nps', 'refresh');
			}
		}
	}


	public function delete($id1, $id2, $id3)
	{
		$manage_survey = $this->db->get_where('manage_survey', ['slug' => $id2])->row();
		$this->db->delete('pertanyaan_nps_' . $manage_survey->table_identity, array('id' => $id3));
		$this->db->delete('pilihan_jawaban_nps_' . $manage_survey->table_identity, array('id' => $id3));

		echo json_encode(array("status" => TRUE));
	}
}

/* End of file PertanyaanUnsurSurveiController.php */
/* Location: ./application/controllers/PertanyaanUnsurSurveiController.php */
