<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RekapitulasiIndeksPerProfilRespondenController extends Client_Controller{

	public function index($id1, $id2)
	{
		$this->data = [];
		$this->data['title'] = 'Rekapitulasi Indeks Per Profil Responden';
		$this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

		$slug = $this->uri->segment(2);
		$manage_survey = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
		$table_identity = $manage_survey->table_identity;
		$this->data['manage_survey'] = $manage_survey;
		$this->data['table_identity'] = $table_identity;

		// $this->db->select("*");
		// $this->db->from("layanan_survei_$table_identity");
		// $this->db->order_by("layanan_survei_$table_identity.urutan asc");
		// $this->data['layanan'] = $this->db->get();

		$profil_responden = $this->db->query("SELECT *,  REPLACE(LOWER(nama_profil_responden), ' ', '_') AS nama_alias, (SELECT COUNT(id) FROM kategori_profil_responden_$table_identity WHERE id_profil_responden = profil_responden_$table_identity.id) AS jumlah_pilihan
		FROM profil_responden_$table_identity
		WHERE jenis_isian = 1");

		if ($profil_responden->num_rows() == 0) {
			$this->data['pesan'] = 'Profil responden survei anda tidak memiliki data yang bisa di olah.';
			return view('not_questions/index', $this->data);
		}
		$this->data['profil_responden'] = $profil_responden->result();

        $this->db->select('COUNT(id) AS id');
        $this->db->from('survey_' . $manage_survey->table_identity);
        $this->db->where("is_submit = 1");
        $this->data['jumlah_kuisioner'] = $this->db->get()->row()->id;

		if ($this->data['jumlah_kuisioner'] == 0) {
			$this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
			return view('not_questions/index', $this->data);
		}
		
		return view('rekapitulasi_indeks_per_profil_responden/index', $this->data);
	}
}
