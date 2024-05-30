<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DataPerolehanPerAnakController extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}
		$this->load->model('DataPerolehanPerAnak_model', 'models');
		$this->load->model('DataPerolehanKeseluruhan_model', 'modelss');
	}

	public function index()
	{
		$this->data = [];
		$this->data['title'] = 'Data Perolehan Survei Per Anak';


		return view('data_perolehan_per_anak/index', $this->data);
	}

	public function ajax_list()
	{
		//CEK GROUP USER
        if($this->ion_auth->in_group('parent_klien_induk')){
            $user_id = $this->ion_auth->user()->row()->id_parent_klien_induk;
        } else{
            $user_id = $this->session->userdata('user_id');
        }


		$list = $this->models->get_datatables($user_id);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			$no++;
			$row = array();


			$array_lengkap[$no] = [];
			$lengkap[$no] = 0;
			foreach($this->db->get_where("manage_survey", ['id_user' => $value->id])->result() as $get){

				$array_lengkap[$no][] = $this->db->get_where("survey_$get->table_identity", ['is_submit' => 1])->num_rows();
				$lengkap[$no] = array_sum($array_lengkap[$no]);
			}
			
			$color[$no] = $lengkap[$no] > 0 ? 'success' : 'danger';
			// var_dump($lengkap[$no]);



			$row[] = $no;
			$row[] = $value->company;
			$row[] = '<span class="badge badge-' . $color[$no] . '"><b>' . $lengkap[$no] . '</b></span>';
			$row[] = '<a href="' . base_url() . 'data-perolehan-per-anak/' . $value->username . '" class="btn btn-info btn-sm font-weight-bold"><i class="fa fa-info-circle"></i> Detail Perolehan</a>';

			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->models->count_all($user_id),
			"recordsFiltered" => $this->models->count_filtered($user_id),
			"data" => $data,
		);
		echo json_encode($output);
	}



	public function detail()
	{
		$this->data = [];
		$this->data['title'] = 'Detail Perolehan Survei';

		$this->data['users'] = $this->db->get_where("users", ['username' => $this->uri->segment(2)])->row();


		return view('data_perolehan_per_anak/form_detail', $this->data);
	}


	public function ajax_list_detail()
	{
		$users = $this->db->get_where("users", ['username' => $this->uri->segment(2)])->row();

		$data = array();
		foreach ($this->db->query("SELECT *, (SELECT first_name FROM users WHERE id = manage_survey.id_user) AS nama_depan, (SELECT last_name FROM users WHERE id = manage_survey.id_user) AS nama_belakang FROM manage_survey WHERE id_user = $users->id")->result() as $key => $value) {

			$data[$key] = "UNION SELECT
						id_responden,
						'' AS nama_lengkap,
						saran,
						waktu_isi,
						is_submit,
						responden_$value->table_identity.uuid,
						(SELECT nama_layanan FROM layanan_survei_$value->table_identity WHERE responden_$value->table_identity.id_layanan_survei = layanan_survei_$value->table_identity.id),
						'$value->slug',
						'$value->table_identity',
						survey_$value->table_identity.id_surveyor,
						survey_$value->table_identity.is_end,
						surveyor.kode_surveyor,
						(SELECT first_name FROM users WHERE surveyor.id_user = id),
						(SELECT last_name FROM users WHERE surveyor.id_user = id),
						'$value->nama_depan',
						'$value->nama_belakang'

						FROM responden_$value->table_identity
						JOIN survey_$value->table_identity ON responden_$value->table_identity.id = survey_$value->table_identity.id_responden
						LEFT JOIN surveyor ON survey_$value->table_identity.id_surveyor = surveyor.id
						WHERE is_submit = 1";
		}
		$tabel_union = implode(" ", $data);

		$list = $this->modelss->get_datatables($tabel_union);
		$data = array();
		$no = $_POST['start'];

		foreach ($list as $value) {

			if ($value->is_submit == 1) {
				$status = '<span class="badge badge-primary">Lengkap</span>';
			} else {
				$status = '<span class="badge badge-danger">Tidak Lengkap</span><br>
				<small class="text-dark-50 font-italic">' . $value->is_end . '</small>';
			}

			$no++;
			$row = array();
		

			$row[] = $no;
			$row[] = $value->nama_depan_user . ' '. $value->nama_belakang_user;
			$row[] = $status;
			$row[] = anchor($value->slug . '/hasil-survei/' . $value->uuid_responden, '<i class="fas fa-file-pdf text-danger"></i>', ['target' => '_blank']);
			$row[] = $value->nama_layanan; //'<b>' . $value->kode_surveyor . '</b>--' . $value->first_name . ' ' . $value->last_name;
			//$row[] = $value->nama_lengkap;
			$row[] = $value->nama_lengkap;

		
			$data[] = $row;
		}
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->modelss->count_all($tabel_union),
			"recordsFiltered" => $this->modelss->count_filtered($tabel_union),
			"data" => $data,
		);
		echo json_encode($output);
	}

	public function export()
	{
		$this->data = [];
		$this->data['title'] = 'Export Perolehan Survei';


		header("Content-type: application/vnd-ms-excel");
		header("Content-Disposition: attachment; filename=djp-perolehan.xls");

		return view('data_perolehan_per_anak/cetak', $this->data);
	}
}

/* End of file DataPerolehanSurveiController.php */
/* Location: ./application/controllers/DataPerolehanSurveiController.php */