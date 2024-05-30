<?php

class KuotaJumlahRespondenController extends CI_Controller{

	public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group(['client_induk'])) {
            $this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
            redirect('auth', 'refresh');
        }
        $this->load->library('form_validation');
        $this->load->model('DataPerolehanPerBagian_model', 'models');
    }

	public function index()
	{
		$this->data = [];
        $this->data['title'] = 'Kuota Jumlah Responden';
        // $this->data['induk'] = $this->db->get_where("pengguna_klien_induk", array('id_user' => $this->session->userdata('user_id')))->row();
        
        $parent = '';
        $n = 0;
        foreach($this->db->query("SELECT id FROM users WHERE id_parent_induk = '".$this->session->userdata('user_id')."'")->result() as $data){
			$n++;
            if($n!=1){
                $parent .= ', ';
            }
            $parent .= $data->id;
        }

        $this->db->select('*, manage_survey.slug AS slug_manage_survey, (SELECT first_name FROM users WHERE users.id = manage_survey.id_user) AS first_name, (SELECT last_name FROM users WHERE users.id = manage_survey.id_user) AS last_name');
		$this->db->from('manage_survey');
		$this->db->where("id_user IN ($parent)");
        $this->data['data_survey'] = $this->db->get();

        $this->db->select('*, berlangganan.id AS id_berlangganan');
		$this->db->from('berlangganan');
		$this->db->join('users', 'users.id = berlangganan.id_user');
		$this->db->join('produk', 'berlangganan.id_produk = produk.id');
		$this->db->join('paket', 'paket.id = berlangganan.id_paket');
		$this->db->join('status_berlangganan', 'status_berlangganan.id = berlangganan.id_status_berlangganan');
		$this->db->join('metode_pembayaran', 'metode_pembayaran.id = berlangganan.id_metode_pembayaran');
		$this->db->where('berlangganan.id_user', $this->session->userdata('user_id'));
		$this->db->where('berlangganan.id_produk', '9');
		$this->db->order_by('berlangganan.id', 'asc');
		$this->data['data_langganan'] = $this->db->get()->row();	

        return view('kuota_jumlah_responden/index', $this->data);
	}

}
