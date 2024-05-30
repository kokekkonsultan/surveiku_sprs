<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OlahDataPerBagianController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('ion_auth');

        if (!$this->ion_auth->logged_in()) {
            $this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
            redirect('auth', 'refresh');
        }
        $this->load->library('form_validation');
        $this->load->model('DataPerolehanPerBagian_model', 'models');
        $this->load->model('OlahData_model', 'models');
        $this->load->model('OlahData_model');
    }

    public function index()
    {
        $this->data = [];
        $this->data['title'] = 'Olah Data';

        // $klien_induk = $this->db->get_where("pengguna_klien_induk", array('id_user' => $this->session->userdata('user_id')))->row();
        // $parent = implode(", ", unserialize($klien_induk->cakupan_induk));

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

        $manage_survey = $this->db->get();

        if ($manage_survey->num_rows() > 0) {
            $no = 1;
            foreach ($manage_survey->result() as $value) {

                $skala_likert = (100 / ($value->skala_likert == 5 ? 5 : 4));
                $this->data['tahun_awal'] = $value->survey_year;

                if ($this->db->get_where("survey_$value->table_identity", array('is_submit' => 1))->num_rows() > 0) {
                    

                    $nilai_per_unsur[$no] = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$value->table_identity.id, unsur_pelayanan_$value->table_identity.id_parent) AS id_sub,
					((SUM(skor_jawaban)/COUNT(DISTINCT survey_$value->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$value->table_identity.id_responden))) AS nilai_per_unsur, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$value->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$value->table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$value->table_identity WHERE id_parent = 0)) AS rata_rata_bobot

					FROM jawaban_pertanyaan_unsur_$value->table_identity
					JOIN pertanyaan_unsur_pelayanan_$value->table_identity ON jawaban_pertanyaan_unsur_$value->table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$value->table_identity.id
					JOIN unsur_pelayanan_$value->table_identity ON pertanyaan_unsur_pelayanan_$value->table_identity.id_unsur_pelayanan = unsur_pelayanan_$value->table_identity.id
					JOIN survey_$value->table_identity ON jawaban_pertanyaan_unsur_$value->table_identity.id_responden = survey_$value->table_identity.id_responden
					WHERE survey_$value->table_identity.is_submit = 1
					GROUP BY id_sub");

                    $nilai_bobot[$no] = [];
                    foreach ($nilai_per_unsur[$no]->result() as $get) {
                        $nilai_bobot[$no][] = $get->rata_rata_bobot;
                        $nilai_tertimbang[$no] = array_sum($nilai_bobot[$no]);
                    }

                    $data_chart[] = '{"label": "' . $value->survey_name ." - ". $value->organisasi . '",
						"value": "' . ROUND($nilai_tertimbang[$no] * $skala_likert, 2) . '"}';
                } else {
                    $data_chart[] = '{"label": "' . $value->survey_name ." - ". $value->organisasi . '", "value": "0"}';
                };
                $no++;
            }
            $this->data['get_data_chart'] = implode(", ", $data_chart);
        } else {
            $this->data['get_data_chart'] = '{"label": "", "value": "0"}';
        }

        return view('olah_data_per_bagian/index', $this->data);
    }





    public function ajax_list()
    {
        // $klien_induk = $this->db->get_where("pengguna_klien_induk", array('id_user' => $this->session->userdata('user_id')))->row();
        // $parent = implode(", ", unserialize($klien_induk->cakupan_induk));

        $parent = '';
        $n = 0;
        foreach($this->db->query("SELECT id FROM users WHERE id_parent_induk = '".$this->session->userdata('user_id')."'")->result() as $data){
            $n++;
            if($n!=1){
                $parent .= ', ';
            }
            $parent .= $data->id;
        }

        $list = $this->models->get_datatables($parent);
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $value) {

            $klien_user = $this->db->get_where("users", array('id' => $value->id_user))->row();
            $skala_likert = (100 / ($value->skala_likert == 5 ? 5 : 4));

            if ($value->is_privacy == 1) {
                $status = '<span class="badge badge-info" width="40%">Public</span>';
            } else {
                $status = '<span class="badge badge-danger" width="40%">Private</span>';
            };

            if ($this->db->get_where("survey_$value->table_identity", array('is_submit' => 1))->num_rows() > 0) {

                $nilai_per_unsur[$no] = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$value->table_identity.id, unsur_pelayanan_$value->table_identity.id_parent) AS id_sub,
					((SUM(skor_jawaban)/COUNT(DISTINCT survey_$value->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$value->table_identity.id_responden))) AS nilai_per_unsur, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$value->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$value->table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$value->table_identity WHERE id_parent = 0)) AS rata_rata_bobot

					FROM jawaban_pertanyaan_unsur_$value->table_identity
					JOIN pertanyaan_unsur_pelayanan_$value->table_identity ON jawaban_pertanyaan_unsur_$value->table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$value->table_identity.id
					JOIN unsur_pelayanan_$value->table_identity ON pertanyaan_unsur_pelayanan_$value->table_identity.id_unsur_pelayanan = unsur_pelayanan_$value->table_identity.id
					JOIN survey_$value->table_identity ON jawaban_pertanyaan_unsur_$value->table_identity.id_responden = survey_$value->table_identity.id_responden
					WHERE survey_$value->table_identity.is_submit = 1
					GROUP BY id_sub");

                $nilai_bobot[$no] = [];
                foreach ($nilai_per_unsur[$no]->result() as $get) {
                    $nilai_bobot[$no][] = $get->rata_rata_bobot;
                    $nilai_tertimbang[$no] = array_sum($nilai_bobot[$no]);
                }
                $nilai_ikk = ROUND($nilai_tertimbang[$no] * $skala_likert, 2);
            } else {
                $nilai_ikk = 0;
            };

            foreach ($this->db->query("SELECT * FROM definisi_skala_$value->table_identity ORDER BY id DESC")->result() as $obj) {
                if ($nilai_ikk <= $obj->range_bawah && $nilai_ikk >= $obj->range_atas) {
                    $kualitas_pelayanan = $obj->kategori;
                }
            }
            if ($nilai_ikk <= 0) {
                $kualitas_pelayanan = '-';
            }

            $no++;
            $row = array();
            $row[] = '
			<a href="' . base_url() . 'olah-data-per-bagian/' . $klien_user->username . '/' . $value->slug . '" title="">
			<div class="card mb-5 shadow" style="background-color: SeaShell;">
				<div class="card-body">
					<div class="row">
						<div class="col sm-10">
							<strong style="font-size: 17px;">' . $value->survey_name . '</strong><br>
							<span class="text-dark">Nama Akun : <b>' . $value->first_name . ' ' . $value->last_name . '</b></span><br>
                            <span class="text-dark">Nilai IKP : <b>' . $nilai_ikk . '</b></span><br>
                            <span class="text-dark">Kategori : <b>' . $kualitas_pelayanan . '</b></span><br>
						</div>
						<div class="col sm-2 text-right"><span class="badge badge-info" width="40%">Detail</span>
							<div class="mt-3 text-dark font-weight-bold" style="font-size: 11px;">
                            Periode Survei : ' . date('d-m-Y', strtotime($value->survey_start)) . ' s/d ' . date('d-m-Y', strtotime($value->survey_end)) . '
							</div>

						</div>
					</div>
					<!--small class="text-secondary">' . $value->description . '</small><br-->
					
				</div>
			</div>
		</a>';

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->models->count_all($parent),
            "recordsFiltered" => $this->models->count_filtered($parent),
            "data" => $data,
        );
        echo json_encode($output);
    }


    

    public function detail($id1, $id2)
    {
        $this->data = [];
        $this->data['title'] = 'Olah Data';
        $this->data['profiles'] = $this->_get_data_profile($id1, $id2);

        $slug = $this->uri->segment(3);

        $manage_survey = $this->db->get_where('manage_survey', ['slug' => "$slug"])->row();
        $table_identity = $manage_survey->table_identity;
        $this->data['nama_survey'] = $manage_survey->survey_name;


        $this->data['skala_likert'] = 100 / ($manage_survey->skala_likert == 5 ? 5 : 4);
		$this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");

        //JUMLAH KUISIONER
        $this->data['jumlah_kuesioner_terisi'] = $this->db->query("SELECT COUNT(id) AS total_kuesioner
        FROM survey_$table_identity WHERE is_submit = 1")->row()->total_kuesioner;


		$this->data['unsur'] = $this->db->query("SELECT *, SUBSTR(nomor_unsur,2) AS nomor_harapan
		FROM unsur_pelayanan_$table_identity
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan
		");

		//TOTAL
		$this->data['total'] = $this->db->query("SELECT SUM(skor_jawaban) AS sum_skor_jawaban
		FROM jawaban_pertanyaan_unsur_$table_identity
		JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
		JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
		WHERE is_submit = 1
		GROUP BY id_pertanyaan_unsur");

		
		//RATA-RATA
		$this->db->select("(SUM(skor_jawaban)/COUNT(DISTINCT jawaban_pertanyaan_unsur_$table_identity.id_responden)) AS rata_rata");
		$this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
		$this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
		$this->db->where("survey_$table_identity.is_submit = 1");
		$this->db->group_by('id_pertanyaan_unsur');
		$this->data['rata_rata'] = $this->db->get();

		//RATA-RATA HARAPAN
		$this->db->select("(SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS rata_rata");
		$this->db->from('jawaban_pertanyaan_harapan_' . $table_identity);
		$this->db->join("survey_$table_identity", "jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden");
		$this->db->where("survey_$table_identity.is_submit = 1");
		$this->db->group_by('id_pertanyaan_unsur');
		$this->data['rata_rata_harapan'] = $this->db->get();

		//NILAI PER UNSUR
		$this->db->select("nama_unsur_pelayanan, IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur");
		$this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
		$this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
		$this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
		$this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
		$this->db->where("survey_$table_identity.is_submit = 1");
		$this->db->group_by('id_sub');
		$this->data['nilai_per_unsur'] = $this->db->get();


		//RATA-RATA BOBOT
		$this->db->select("nama_unsur_pelayanan, IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$table_identity WHERE id_parent = 0)) AS rata_rata_bobot");
		$this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
		$this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
		$this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
		$this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
		$this->db->where("survey_$table_identity.is_submit = 1");
		$this->db->group_by('id_sub');
		$this->data['rata_rata_bobot'] = $this->db->get();

		//TERTIMBANG
		$this->db->select("IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub,  (COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)))) AS tertimbang, ((((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))))*25) AS skm");
		$this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
		$this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
		$this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
		$this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
		$this->db->where("survey_$table_identity.is_submit = 1");
		$this->data['tertimbang'] = $this->db->get()->row();

        // var_dump($total_biaya);

        return view('olah_data_per_bagian/detail', $this->data);
    }

    public function _get_data_profile($id1, $id2)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('users_groups', 'users.id = users_groups.user_id');
        $this->db->where('users.username', $id1);
        $data_user = $this->db->get()->row();
        // $user_identity = 'drs' . $data_user->is_parent;

        $this->db->select('users.username, manage_survey.survey_name, manage_survey.slug, manage_survey.description, manage_survey.is_privacy, manage_survey.table_identity, manage_survey.atribut_pertanyaan_survey');
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

    public function ajax_detail()
    {
        $slug = $this->uri->segment(3);

        $manage_survey = $this->db->get_where('manage_survey', ['slug' => "$slug"])->row();
        $id_manage_survey = $manage_survey->id;
        $table_identity = $manage_survey->table_identity;

        $jawaban_unsur = $this->db->get("jawaban_pertanyaan_unsur_cst$id_manage_survey");

        $list = $this->models->get_datatables($id_manage_survey);
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $value) {

            if ($value->is_submit == 1) {
                $status = '<span class="badge badge-primary">Valid</span>';
            } else {
                $status = '<span class="badge badge-danger">Tidak Valid</span>';
            }

            $no++;
            $row = array();
            $row[] = $no;
            // $row[] = $status;
            // $row[] = '<b>' . $value->kode_surveyor . '</b>--' . $value->first_name . ' ' . $value->last_name;
            $row[] = $value->nama_lengkap;

            foreach ($jawaban_unsur->result() as $get_unsur) {
                if ($get_unsur->id_survey == $value->id_survey) {
                    $row[] = $get_unsur->skor_jawaban;
                }
            }

            foreach ($jawaban_unsur->result() as $get_unsur) {
                if ($get_unsur->id_survey == $value->id_survey) {
                    $row[] = $get_unsur->alasan_pilih_jawaban;
                }
            }

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->models->count_all($id_manage_survey),
            "recordsFiltered" => $this->models->count_filtered($id_manage_survey),
            "data" => $data,
        );

        echo json_encode($output);
    }
}

/* End of file OlahDataPerBagianController.php */
/* Location: ./application/controllers/OlahDataPerBagianController.php */
