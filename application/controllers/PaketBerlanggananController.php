<?php
use Carbon\Carbon;

class PaketBerlanggananController extends CI_Controller{

	public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in() || !$this->ion_auth->in_group(['client_induk'])) {
            $this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
            redirect('auth', 'refresh');
        }
        $this->load->library('form_validation');
    }

	public function index()
	{
		$this->data = [];
        $this->data['title'] = 'Paket Berlangganan';
        // $this->data['induk'] = $this->db->get_where("pengguna_klien_induk", array('id_user' => $this->session->userdata('user_id')))->row();
        
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
		$get_data = $this->db->get();
        // $this->data['data_pelanggan'] = $this->db->get();

        $last_payment = $get_data->last_row();
		$this->data['last_payment'] = $last_payment;

		$tanggal_mulai = $last_payment->tanggal_mulai;
		$tanggal_selesai = $last_payment->tanggal_selesai;

		$this->data['tanggal_sekarang'] = $tanggal_mulai;
		$this->data['tanggal_expired'] = $tanggal_selesai;

		$now = Carbon::now();
		$start_date = Carbon::parse($tanggal_mulai);
		$end_date = Carbon::parse($tanggal_selesai);
		$due_date = $now->diffInDays($end_date); // Tanggal jatuh tempo

        if ((strtotime($tanggal_mulai) <= time() AND time() >= strtotime($tanggal_selesai))) {
			$this->data['status_jatuh_tempo'] = 'Packet is Expired';
			$this->data['status_paket'] = 'Expired';
		} else {
			$this->data['status_jatuh_tempo'] = 'Paket berakhir dalam ' . $due_date . ' hari lagi';
			$this->data['status_paket'] = 'Aktif';
		}

        $this->load->library('table');

		$template = array(
			'table_open'            => '<table id="table" class="table table-bordered" cellspacing="0" width="100%">',
			'table_close'           => '</table>'
		);

		$this->table->set_template($template);

		$this->table->set_heading('No', 'Nama Paket', 'Panjang Hari', 'Harga Paket (Rp.)', 'Tanggal Aktif', 'Tanggal Kedaluarsa');

		$no = 1;
		foreach ($get_data->result() as $value) {
			$this->table->add_row(
				$no++,
				$value->nama_paket,
				$value->panjang_hari,
				number_format($value->harga_paket, 2, ',', '.'),
				date('d-m-Y', strtotime($value->tanggal_mulai)),
				date('d-m-Y', strtotime($value->tanggal_selesai))
			);
		}

		$this->data['table'] = $this->table->generate();
        
        return view('paket_berlangganan/index', $this->data);
	}
}
