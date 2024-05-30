<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TingkatanPertanyaanSurveiController extends Client_Controller
{

	public $ion_auth;
	public $session;
	public $uri;
	public $db;
	private $data;

	public function __construct()
	{
		parent::__construct();

		if (!$this->ion_auth->logged_in()) {
			$this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
			redirect('auth', 'refresh');
		}

	}

	public function index($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Tingkatan Pertanyaan Survei';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$slug = $this->uri->segment('2');

		$this->db->select('*');
		$this->db->from('manage_survey');
		$this->db->where("slug = '$slug'");
		$this->data['manage_survey'] = $this->db->get()->row();

		$this->data['id_manage_survey'] = $this->data['manage_survey']->id;
		$this->data['atribut_pertanyaan_survey'] = unserialize($this->data['manage_survey']->atribut_pertanyaan_survey);

		return view('tingkatan_pertanyaan_survei/index', $this->data);
	}


	public function update($id1, $id2)
    {

        $this->db->select('*');
        $this->db->from('manage_survey');
        $this->db->where("slug = '$id2'");
        $current = $this->db->get()->row();


        $this->db->empty_table("isi_pertanyaan_ganda_$current->table_identity");
        $this->db->empty_table("perincian_pertanyaan_terbuka_$current->table_identity");
        $this->db->empty_table("pertanyaan_terbuka_$current->table_identity");
		$this->db->empty_table("kategori_pertanyaan_terbuka_$current->table_identity");
		$this->db->empty_table("nilai_tingkat_kepentingan_$current->table_identity");
		$this->db->empty_table("kategori_unsur_pelayanan_$current->table_identity");
        $this->db->empty_table("pertanyaan_unsur_pelayanan_$current->table_identity");
        $this->db->empty_table("unsur_pelayanan_$current->table_identity");
		$this->db->empty_table("dimensi_$current->table_identity");
        $this->db->empty_table("aspek_$current->table_identity");



        if($this->input->post('is_dimensi') != ''){
            $is_dimensi = $this->input->post('is_dimensi');
        }else{
            if($this->input->post('is_aspek') != ''){
                $is_dimensi = $this->input->post('is_aspek');
            }else{
                $is_dimensi = '2';
            }
        }

        $object = [
            'is_aspek' => ($this->input->post('is_aspek') != '') ? $this->input->post('is_aspek') : '2',
            'is_dimensi' => $is_dimensi,
        ];
        $this->db->where('slug', "$id2");
        $this->db->update('manage_survey', $object);

        $pesan = 'Data berhasil disimpan';
        $msg = ['sukses' => $pesan];
        echo json_encode($msg);
    }
}