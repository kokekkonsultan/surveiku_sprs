<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Writer\Word2007;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Element\Chart;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use Dompdf\Dompdf;
use Dompdf\Options;


class LaporanSurveyController extends Client_Controller
{

    function  __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            $this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
            redirect('auth', 'refresh');
        }
        $this->load->helper('url');
    }

    public function index($id1, $id2)
    {
        $this->data = [];
        $this->data['title'] = "Laporan Survei";
        $this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

        $this->data['query'] = $this->db->get_where('manage_survey', array('slug' => $id2))->row();
        $this->data['atribut_pertanyaan'] =  unserialize($this->data['query']->atribut_pertanyaan_survey);
        $table_identity = $this->data['query']->table_identity;

        $cek_survey = $this->db->get_where("survey_$table_identity", array('is_submit' => 1))->num_rows();

        if (date("Y-m-d") < $this->data['query']->survey_end) {
            $this->data['pesan'] = 'Halaman ini hanya bisa dikelola jika periode survei sudah diselesai atau survei sudah ditutup.';
            return view('not_questions/index', $this->data);
        }

        if ($cek_survey == 0) {
            $this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
            return view('not_questions/index', $this->data);
        }

        if ($this->data['query']->atribut_kuadran != null) {
            $atribut_kuadran = unserialize($this->data['query']->atribut_kuadran);
            $this->data['nama_file'] = $atribut_kuadran[0];
            $this->data['tgl_convert'] = $atribut_kuadran[1];
        } else {
            $this->data['nama_file'] = '';
            $this->data['tgl_convert'] = '';
        }

        $this->_get_kuadran($table_identity);

        return view('laporan_survey/index', $this->data);
    }

    public function generate($id1, $id2)
    {
        $this->data = [];
        $this->data['title'] = "Laporan";
        $this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

        $this->data['manage_survey'] = $this->db->get_where('manage_survey', array('slug' => $id2))->row();
        $table_identity = $this->data['manage_survey']->table_identity;
        $this->data['table_identity'] = $this->data['manage_survey']->table_identity;
        $this->data['atribut_pertanyaan'] =  unserialize($this->data['manage_survey']->atribut_pertanyaan_survey);

        //PROFIL RESPONDEN
        $this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity WHERE jenis_isian = 1");

        //PENDEFINISIAN SKALA LIKERT
        $this->data['skala_likert'] = 100 / ($this->data['manage_survey']->skala_likert == 5 ? 5 : 4);
        $this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");

        //SARAN
        $this->data['saran_res'] = $this->db->query("SELECT * FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE is_active = 1 && is_submit = 1 && saran != ''");

        $this->data['profil_urutan'] = $this->db->query("SELECT GROUP_CONCAT(nama_profil_responden) AS nama FROM profil_responden_$table_identity")->row()->nama;


        //ANALISA
        $this->db->select("*");
        $this->db->from("analisa_$table_identity");
        $this->db->join("unsur_pelayanan_$table_identity", "unsur_pelayanan_$table_identity.id = analisa_$table_identity.id_unsur_pelayanan");
        $this->data['analisa'] = $this->db->get();


        $jawaban_ganda = $this->db->query("SELECT *,
            (SELECT COUNT(*) FROM survey_$table_identity
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = jawaban_pertanyaan_terbuka_$table_identity.id_responden
            WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,

            (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_survei

            FROM isi_pertanyaan_ganda_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
            WHERE perincian_pertanyaan_terbuka_$table_identity.id_jenis_pilihan_jawaban = 1");

        $jawaban_isian = $this->db->query("SELECT *
            FROM jawaban_pertanyaan_terbuka_$table_identity
            JOIN pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN survey_$table_identity ON jawaban_pertanyaan_terbuka_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE id_jenis_pilihan_jawaban = 2 && survey_$table_identity.is_submit = 1");




        $this->_get_data_laporan($table_identity, $this->data['skala_likert']);
        $this->_get_unsur_tertinggi_terendah($table_identity);
        $this->_get_chart_unsur($this->data['manage_survey'], $this->data['manage_survey']->skala_likert, $this->data['definisi_skala'], $this->data['atribut_pertanyaan']);
        $this->_get_rekap_tambahan_atas($table_identity, $jawaban_ganda, $jawaban_isian, $this->data['atribut_pertanyaan']);
        $this->_get_rekap_tambahan_bawah($table_identity, $jawaban_ganda, $jawaban_isian, $this->data['atribut_pertanyaan']);
        $this->_get_rekap_alasan_jawaban($table_identity);



        if (in_array(1, $this->data['atribut_pertanyaan'])) {
            $this->_get_kuadran_laporan($table_identity);
        }

        if (in_array(3, $this->data['atribut_pertanyaan'])) {
            $this->_get_rekap_kualitatif($table_identity);
        }


        $this->load->library('uuid');
        $filename = $this->data['manage_survey']->table_identity . '-' . $this->uuid->v4();
        $this->generate_word($this->data['profiles']->username, $this->data['manage_survey']->slug, $filename);


        require_once 'application/libraries/dompdf-master/autoload.inc.php';
        $pdfroot  = dirname(dirname(dirname(__FILE__)));
        $pdfroot .= '/assets/klien/file/laporan/pdf/' . $filename . '.pdf';

        $options = new Options();
        $options->set('isRemoteEnabled', TRUE);
        $dompdf = new Dompdf($options);
        $html = $this->load->view('laporan_survey/cetak', $this->data, true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'potrait');
        $dompdf->render();
        $output =  $dompdf->output();
        file_put_contents($pdfroot, $output); 

        $obj = [
            'file' => $filename,
            'created_at' => date("Y-m-d H:i:s"),
            'keterangan' => $this->input->post('keterangan')
        ];
        $this->db->insert('log_report_' . $this->data['manage_survey']->table_identity, $obj);
        

        $pesan = 'Data berhasil disimpan';
        $msg = ['sukses' => $pesan];
        echo json_encode($msg);
    }

    public function cetak($id1, $id2)
    {
        $this->data = [];
        $this->data['title'] = "Laporan";
        $this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

        $this->data['manage_survey'] = $this->db->get_where('manage_survey', array('slug' => $id2))->row();
        $table_identity = $this->data['manage_survey']->table_identity;
        $this->data['table_identity'] = $this->data['manage_survey']->table_identity;
        $this->data['atribut_pertanyaan'] =  unserialize($this->data['manage_survey']->atribut_pertanyaan_survey);

        //PROFIL RESPONDEN
        $this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity WHERE jenis_isian = 1");

        //PENDEFINISIAN SKALA LIKERT
        $this->data['skala_likert'] = 100 / ($this->data['manage_survey']->skala_likert == 5 ? 5 : 4);
        $this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");

        //SARAN
        $this->data['saran_res'] = $this->db->query("SELECT * FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE is_active = 1 && is_submit = 1 && saran != ''");

        $this->data['profil_urutan'] = $this->db->query("SELECT GROUP_CONCAT(nama_profil_responden) AS nama FROM profil_responden_$table_identity")->row()->nama;


        //ANALISA
        $this->db->select("*");
        $this->db->from("analisa_$table_identity");
        $this->db->join("unsur_pelayanan_$table_identity", "unsur_pelayanan_$table_identity.id = analisa_$table_identity.id_unsur_pelayanan");
        $this->data['analisa'] = $this->db->get();


        $jawaban_ganda = $this->db->query("SELECT *,
            (SELECT COUNT(*) FROM survey_$table_identity
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = jawaban_pertanyaan_terbuka_$table_identity.id_responden
            WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,

            (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_survei

            FROM isi_pertanyaan_ganda_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
            WHERE perincian_pertanyaan_terbuka_$table_identity.id_jenis_pilihan_jawaban = 1");

        $jawaban_isian = $this->db->query("SELECT *
            FROM jawaban_pertanyaan_terbuka_$table_identity
            JOIN pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN survey_$table_identity ON jawaban_pertanyaan_terbuka_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE id_jenis_pilihan_jawaban = 2 && survey_$table_identity.is_submit = 1");




        $this->_get_data_laporan($table_identity, $this->data['skala_likert']);
        $this->_get_unsur_tertinggi_terendah($table_identity);
        $this->_get_chart_unsur($this->data['manage_survey'], $this->data['manage_survey']->skala_likert, $this->data['definisi_skala'], $this->data['atribut_pertanyaan']);
        $this->_get_rekap_tambahan_atas($table_identity, $jawaban_ganda, $jawaban_isian, $this->data['atribut_pertanyaan']);
        $this->_get_rekap_tambahan_bawah($table_identity, $jawaban_ganda, $jawaban_isian, $this->data['atribut_pertanyaan']);
        $this->_get_rekap_alasan_jawaban($table_identity);



        if (in_array(1, $this->data['atribut_pertanyaan'])) {
            $this->_get_kuadran_laporan($table_identity);
        }

        if (in_array(3, $this->data['atribut_pertanyaan'])) {
            $this->_get_rekap_kualitatif($table_identity);
        }



        $this->load->library('pdfgenerator');
        $this->data['title_pdf'] = 'Laporan';
        $file_pdf = 'Laporan';
        $paper = 'A4';
        $orientation = "potrait";
        // $this->load->view('laporan_survey/cetak', $this->data);
        $html = $this->load->view('laporan_survey/cetak', $this->data, true);
        $this->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);
    }


    public function _get_kuadran_laporan($table_identity)
    {
        //Unsur Prioritas Perbaikan 
        $nilai_per_unsur_asc = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT id_responden))/(COUNT(id_parent)/COUNT(DISTINCT id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan
        FROM jawaban_pertanyaan_harapan_$table_identity
        JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
        GROUP BY id_sub
        ORDER BY nilai_per_unsur ASC
        LIMIT 3");

        $asc = [];
        foreach ($nilai_per_unsur_asc->result() as $value) {
            $asc[] = $value->nomor_unsur . '. ' . $value->nama_unsur_pelayanan;
        }
        $this->data['asc_harapan'] = implode("<br>", $asc);



        //JUDUL PERSEPSI
        $this->db->select("unsur_pelayanan_$table_identity.nomor_unsur AS nomor,
		SUBSTRING(nomor_unsur, 2, 4) AS nomor_harapan, nama_unsur_pelayanan");
        $this->db->from("unsur_pelayanan_$table_identity");
        $this->db->where('id_parent = 0');
        $this->data['persepsi'] = $this->db->get();
        $jumlah_unsur = $this->data['persepsi']->num_rows();


        $object_unsur = $this->data['nilai_per_unsur'];

        $nilai_unsur = 0;
        foreach ($object_unsur->result() as $values) {
            $nilai_unsur += $values->nilai_per_unsur;
        }

        //NILAI PER HARAPAN
        $this->db->select("((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur");
        $this->db->from("jawaban_pertanyaan_harapan_$table_identity");
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->group_by("IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent)");
        $object_harapan = $this->db->get();
        $this->data['nilai_per_unsur_harapan'] = $object_harapan;

        $nilai_harapan = 0;
        foreach ($object_harapan->result() as $rows) {
            $nilai_harapan += $rows->nilai_per_unsur;
        }

        $total_rata_unsur = $nilai_unsur / $jumlah_unsur;
        $total_rata_harapan = $nilai_harapan / $jumlah_unsur;


        $this->data['kuadran_unsur'] =  $this->db->query("SELECT *,
            (CASE
                WHEN kup.skor_unsur <= $total_rata_unsur && kup.skor_harapan >= $total_rata_harapan
                        THEN 1
                WHEN kup.skor_unsur >= $total_rata_unsur && kup.skor_harapan >= $total_rata_harapan
                        THEN 2
                    WHEN kup.skor_unsur <= $total_rata_unsur && kup.skor_harapan <= $total_rata_harapan
                        THEN 3
                    WHEN kup.skor_unsur >= $total_rata_unsur && kup.skor_harapan <= $total_rata_harapan
                        THEN 4
                ELSE 0
            END) AS kuadran

            FROM (SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) AS nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) AS nama_unsur_pelayanan, 

            (SUM((SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1 && pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur))/(SELECT COUNT(survey_$table_identity.id_responden) FROM jawaban_pertanyaan_unsur_$table_identity 
            JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur && survey_$table_identity.is_submit = 1)/COUNT(id_parent)) AS skor_unsur,

            (SUM((SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_harapan_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1 && pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur))/(SELECT COUNT(survey_$table_identity.id_responden) FROM jawaban_pertanyaan_unsur_$table_identity 
            JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur && survey_$table_identity.is_submit = 1)/COUNT(id_parent)) AS skor_harapan

            FROM pertanyaan_unsur_pelayanan_$table_identity
            JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
            GROUP BY id_sub) AS kup");

        $this->data['total_rata_unsur'] = $total_rata_unsur;
    }



    public function _get_rekap_tambahan_atas($table_identity, $jawaban_ganda, $jawaban_isian, $atribut_pertanyaan)
    {
        if (in_array(2, $atribut_pertanyaan)) {
            $pertanyaan_tambahan_atas = $this->db->query("SELECT *,
            (SELECT DISTINCT dengan_isian_lainnya FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS is_lainnya
            
            FROM pertanyaan_terbuka_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            WHERE is_letak_pertanyaan = 1");


            if ($pertanyaan_tambahan_atas->num_rows() > 0) {
                $get_rekap_tambahan = [];
                foreach ($pertanyaan_tambahan_atas->result() as $row) {

                    if ($row->id_jenis_pilihan_jawaban == 1) {

                        $nt = 1;
                        $add_table_terbuka = [];
                        foreach ($jawaban_ganda->result() as $value) {
                            if ($value->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {

                                $add_table_terbuka[] = '
                            <tr>
                                <td class="td-th-list" width="6%">' . $nt++ . '</td>
                                <td class="td-th-list">' . $value->pertanyaan_ganda . '</td>
                                <td class="td-th-list">' . $value->perolehan . '</td>
                                <td class="td-th-list">' . str_replace('.', ',', ROUND(($value->perolehan / $value->jumlah_survei) * 100, 2)) . ' %</td>
                            </tr>';
                            }
                        }
                        $t_terbuka = implode(" ", $add_table_terbuka);

                        if ($row->is_lainnya == 1) {
                            $add_table_terbuka_lainnya = '
                            <tr>
                                <th class="td-th-list" width="6%">' . $nt++ . '</th>
                                <td class="td-th-list">Lainnya</td>
                                <td class="td-th-list">' . $row->perolehan . '</td>
                                <td class="td-th-list">' . str_replace('.', ',', ROUND($row->persentase, 2)) . ' %</td>
                            </tr>';
                        } else {
                            $add_table_terbuka_lainnya = '';
                        }

                        $get_terbuka_pilihan = '
                    <table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                        <tr style="background-color:#E4E6EF;">
                            <th class="td-th-list" width="6%">No</th>
                            <th class="td-th-list">Kelompok</th>
                            <th class="td-th-list">Jumlah</th>
                            <th class="td-th-list">Persentase</th>
                        </tr>' . $t_terbuka . ' ' . $add_table_terbuka_lainnya .
                            '</table>
                    ';
                    } else {
                        $ns = 1;
                        $add_table_terbuka_isian = [];
                        foreach ($jawaban_isian->result() as $get) {
                            if ($get->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {
                                $add_table_terbuka_isian[] = '
                            <tr>
                                <td class="td-th-list" width="6%">' . $ns++ . '</th>
                                <td class="td-th-list" style="text-align: left;">' . $get->jawaban . '</td>
                            </tr>';
                            }
                        }
                        $t_isian = implode(" ", $add_table_terbuka_isian);

                        $get_terbuka_pilihan = '<table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                        <tr style="background-color:#E4E6EF;">
                            <th class="td-th-list" width="6%">No</th>
                            <th class="td-th-list">Jawaban</th>
                        </tr>' . $t_isian .
                            '</table>';
                    }

                    $get_rekap_tambahan[] = '<li><div><b>' . $row->nomor_pertanyaan_terbuka . '. ' . $row->nama_pertanyaan_terbuka . '</b></div><br>' . $get_terbuka_pilihan .
                        '<br><br></li>';
                }
                $this->data['html_rekap_tambahan_atas'] = implode(" ", $get_rekap_tambahan);
            } else {
                $this->data['html_rekap_tambahan_atas'] = '';
            }
        } else {
            $this->data['html_rekap_tambahan_atas'] = '';
        }
        // var_dump($this->data['html_rekap_tambahan_atas']);
    }





    public function _get_rekap_tambahan_bawah($table_identity, $jawaban_ganda, $jawaban_isian, $atribut_pertanyaan)
    {

        if (in_array(2, $atribut_pertanyaan)) {
            $pertanyaan_tambahan_bawah = $this->db->query("SELECT *,
        (SELECT DISTINCT dengan_isian_lainnya FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS is_lainnya
        
        FROM pertanyaan_terbuka_$table_identity
        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
        WHERE is_letak_pertanyaan = 2");


            if ($pertanyaan_tambahan_bawah->num_rows() > 0) {
                $get_rekap_tambahan = [];
                foreach ($pertanyaan_tambahan_bawah->result() as $row) {

                    if ($row->id_jenis_pilihan_jawaban == 1) {

                        $nt = 1;
                        $add_table_terbuka = [];
                        foreach ($jawaban_ganda->result() as $value) {
                            if ($value->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {

                                $add_table_terbuka[] = '
                        <tr>
                            <td class="td-th-list" width="6%">' . $nt++ . '</td>
                            <td class="td-th-list">' . $value->pertanyaan_ganda . '</td>
                            <td class="td-th-list">' . $value->perolehan . '</td>
                            <td class="td-th-list">' . str_replace('.', ',', ROUND(($value->perolehan / $value->jumlah_survei) * 100, 2)) . ' %</td>
                        </tr>';
                            }
                        }
                        $t_terbuka = implode(" ", $add_table_terbuka);

                        if ($row->is_lainnya == 1) {
                            $add_table_terbuka_lainnya = '
                        <tr>
                            <td class="td-th-list" width="6%">' . $nt++ . '</td>
                            <td class="td-th-list">Lainnya</td>
                            <td class="td-th-list">' . $row->perolehan . '</td>
                            <td class="td-th-list">' . str_replace('.', ',', ROUND($row->persentase, 2)) . ' %</td>
                        </tr>';
                        } else {
                            $add_table_terbuka_lainnya = '';
                        }

                        $get_terbuka_pilihan = '
                <table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                    <tr style="background-color:#E4E6EF;">
                        <th class="td-th-list" width="6%">No</th>
                        <th class="td-th-list">Kelompok</th>
                        <th class="td-th-list">Jumlah</th>
                        <th class="td-th-list">Persentase</th>
                    </tr>' . $t_terbuka . ' ' . $add_table_terbuka_lainnya .
                            '</table>
                ';
                    } else {
                        $ns = 1;
                        $add_table_terbuka_isian = [];
                        foreach ($jawaban_isian->result() as $get) {
                            if ($get->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {
                                $add_table_terbuka_isian[] = '
                        <tr>
                            <td class="td-th-list" width="6%">' . $ns++ . '</th>
                            <td class="td-th-list" style="text-align: left;">' . $get->jawaban . '</td>
                        </tr>';
                            }
                        }
                        $t_isian = implode(" ", $add_table_terbuka_isian);

                        $get_terbuka_pilihan = '<table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                     <tr style="background-color:#E4E6EF;">
                        <th class="td-th-list" width="6%">No</th>
                        <th class="td-th-list">Jawaban</th>
                    </tr>' . $t_isian .
                            '</table>';
                    }

                    $get_rekap_tambahan[] = '<li><div><b>' . $row->nomor_pertanyaan_terbuka . '. ' . $row->nama_pertanyaan_terbuka . '</b></div><br>' . $get_terbuka_pilihan .
                        '<br><br></li>';
                }
                $this->data['html_rekap_tambahan_bawah'] = implode(" ", $get_rekap_tambahan);
            } else {
                $this->data['html_rekap_tambahan_bawah'] = '';
            }
        } else {
            $this->data['html_rekap_tambahan_bawah'] = '';
        }
        // var_dump($this->data['html_rekap_tambahan_bawah']);
    }



    public function _get_chart_unsur($manage_survey, $skala_likert, $definisi_skala, $atribut_pertanyaan)
    {
        // $this->data['skala_likert'] = 100 / ($this->data['manage_survey']->skala_likert == 5 ? 5 : 4);
        // $this->data['definisi_skala'] = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");

        $table_identity = $manage_survey->table_identity;


        $unsur_pelayanan = $this->db->query("SELECT *, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan, (SELECT isi_pertanyaan_unsur FROM pertanyaan_unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = unsur_pelayanan_$table_identity.id) as isi_pertanyaan_unsur
        FROM unsur_pelayanan_$table_identity
        WHERE id_parent = 0 ");

        $get_pilihan_jawaban = $this->db->query("SELECT *, (SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan, (SELECT COUNT(id) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_pengisi
        FROM kategori_unsur_pelayanan_$table_identity");


        $rekap_turunan_unsur = $this->db->query("SELECT *, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur_pelayanan,
        (SELECT COUNT(skor_jawaban)
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
        WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 1) AS perolehan_1,
        (SELECT COUNT(skor_jawaban)
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
        WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 2) AS perolehan_2,
        (SELECT COUNT(skor_jawaban)
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
        WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 3) AS perolehan_3,
        (SELECT COUNT(skor_jawaban)
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
        WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 4) AS perolehan_4,
        (SELECT COUNT(skor_jawaban)
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
        WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 5) AS perolehan_5,
        (SELECT COUNT(id) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_pengisi,
        (SELECT AVG(skor_jawaban)
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
        WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) AS rata_rata
        FROM unsur_pelayanan_$table_identity
        JOIN pertanyaan_unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");

        $no_img = $this->db->get_where("profil_responden_$table_identity", array('jenis_isian' => 1))->num_rows() + 2;
        $no_tabel = $this->db->get_where("profil_responden_$table_identity", array('is_lainnya' => 1))->num_rows() + 2;

        
        foreach ($unsur_pelayanan->result() as $key => $row) {


    if (in_array(2, $atribut_pertanyaan)) {
        $pertanyaan_tambahan[$key] = $this->db->query("SELECT *,
        (SELECT DISTINCT dengan_isian_lainnya FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS is_lainnya,
        (SELECT COUNT(*) FROM responden_$table_identity
        JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
        jawaban_pertanyaan_terbuka_$table_identity.id_responden
        JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
        WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
        perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
        'Lainnya') AS perolehan,
        
        (((SELECT COUNT(*) FROM responden_$table_identity
        JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
        jawaban_pertanyaan_terbuka_$table_identity.id_responden
        JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
        WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
        perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
        'Lainnya') / (SELECT COUNT(*) FROM survey_$table_identity JOIN responden_$table_identity ON survey_$table_identity.id_responden = responden_$table_identity.id JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban != '' )) * 100) AS persentase

        FROM pertanyaan_terbuka_$table_identity
        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
        WHERE id_unsur_pelayanan = $row->id_unsur_pelayanan");


                    if ($pertanyaan_tambahan[$key]->num_rows() > 0) {
                        $get_rekap_tambahan[$key] = [];
                        foreach ($pertanyaan_tambahan[$key]->result() as $pt) {
                            if ($pt->id_jenis_pilihan_jawaban == 1) {

                                $nt = 1;
                                $add_table_terbuka = [];
                                foreach ($this->db->query("SELECT *,
                                (SELECT COUNT(*) FROM survey_$table_identity
                                JOIN jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = jawaban_pertanyaan_terbuka_$table_identity.id_responden
                                WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,
                    
                                (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_survei
                    
                                FROM isi_pertanyaan_ganda_$table_identity
                                JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
                                WHERE perincian_pertanyaan_terbuka_$table_identity.id_jenis_pilihan_jawaban = 1 && id_pertanyaan_terbuka = $pt->id_pertanyaan_terbuka")->result() as $value) {

                                        $add_table_terbuka[] = '
                                    <tr>
                                        <td class="td-th-list" width="6%">' . $nt++ . '</td>
                                        <td class="td-th-list">' . $value->pertanyaan_ganda . '</td>
                                        <td class="td-th-list">' . $value->perolehan . '</td>
                                        <td class="td-th-list">' . str_replace('.', ',', ROUND(($value->perolehan / $value->jumlah_survei) * 100, 2)) . ' %</td>
                                    </tr>';
                                }
                                $t_terbuka = implode(" ", $add_table_terbuka);

                                if ($pt->is_lainnya == 1) {
                                    $add_table_terbuka_lainnya = '
                                <tr>
                                    <td class="td-th-list" width="6%">' . $nt++ . '</td>
                                    <td class="td-th-list">Lainnya</td>
                                    <td class="td-th-list">' . $pt->perolehan . '</td>
                                    <td class="td-th-list">' . str_replace('.', ',', ROUND($pt->persentase, 2)) . ' %</td>
                                </tr>';
                                } else {
                                    $add_table_terbuka_lainnya = '';
                                }

                                $get_terbuka_pilihan = '
                                <table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                                <tr style="background-color:#E4E6EF;">
                                    <th class="td-th-list" width="6%">No</th>
                                    <th class="td-th-list">Kelompok</th>
                                    <th class="td-th-list">Jumlah</th>
                                    <th class="td-th-list">Persentase</th>
                                </tr>' . $t_terbuka . ' ' . $add_table_terbuka_lainnya .
                                    '</table>
                            ';
                            } else {
                                $ns = 1;
                                $add_table_terbuka_isian = [];
                                foreach ($this->db->query("SELECT *
                                FROM jawaban_pertanyaan_terbuka_$table_identity
                                JOIN pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                                JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                                JOIN survey_$table_identity ON jawaban_pertanyaan_terbuka_$table_identity.id_responden = survey_$table_identity.id_responden
                                WHERE id_jenis_pilihan_jawaban = 2 && survey_$table_identity.is_submit = 1 && pertanyaan_terbuka_$table_identity.id = $pt->id_pertanyaan_terbuka")->result() as $get) {

                                        $add_table_terbuka_isian[] = '
                                    <tr>
                                        <td class="td-th-list" width="6%">' . $ns++ . '</th>
                                        <td class="td-th-list" style="text-align: left;">' . $get->jawaban . '</td>
                                    </tr>';
                                }
                                $t_isian = implode(" ", $add_table_terbuka_isian);

                                $get_terbuka_pilihan = '<table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                                    <tr style="background-color:#E4E6EF;">
                                        <th class="td-th-list" width="6%">No</th>
                                        <th class="td-th-list">Jawaban</th>
                                    </tr>' . $t_isian .
                                    '</table>';
                            }

                            $get_rekap_tambahan[$key][] = '<li><div><b>' . $pt->nomor_pertanyaan_terbuka . '. ' . $pt->nama_pertanyaan_terbuka . '</b></div><br>' . $get_terbuka_pilihan .
                                '<br><br></li>';
                        }
                        $html_rekap_tambahan[$key] = implode(" ", $get_rekap_tambahan[$key]);
                    } else {
                        $html_rekap_tambahan[$key] = '';
                    }


            } else {
                $html_rekap_tambahan[$key] = '';
            }











            $cek_sub = $this->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => $row->id_unsur_pelayanan]);

            // UNSUR YANG TIDAK MEMILIKI TURUNAN
            if ($cek_sub->num_rows() == 0) {
                $no = 1;
                $t_perolehan = 0;
                $t_persentase = 0;
                $add_table = [];
                $nama_kategori_unsur_pelayanan = [];
                $persentase = [];
                foreach ($get_pilihan_jawaban->result() as $value) {
                    if ($value->id_unsur_pelayanan == $row->id_unsur_pelayanan) {

                        $nama_kategori_unsur_pelayanan[] = '%27' . $value->nama_kategori_unsur_pelayanan . '+=+' . ROUND(($value->perolehan / $value->jumlah_pengisi) * 100, 2) . '%25%27';
                        $persentase[] = ROUND(($value->perolehan / $value->jumlah_pengisi) * 100, 2);

                        $add_table[] = '<tr>
                                            <td class="td-th-list">' . $no++ . '</td>
                                            <td class="td-th-list">' . $value->nama_kategori_unsur_pelayanan . '</td>
                                            <td class="td-th-list">' . $value->perolehan . '</td>
                                            <td class="td-th-list">
                                                ' . ROUND(($value->perolehan / $value->jumlah_pengisi) * 100, 2) . ' %
                                            </td>
                                        </tr>';
                        $t_perolehan += $value->perolehan;
                        $t_persentase += ($value->perolehan / $value->jumlah_pengisi) * 100;
                    }
                }
                $get_table = implode(" ", $add_table);
                $get_persentase = implode(",", $persentase);
                $get_nama_kategori = implode(", ", $nama_kategori_unsur_pelayanan);



                $alasan = $this->db->query("SELECT *
                FROM jawaban_pertanyaan_unsur_$table_identity
                JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
                JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
                WHERE is_submit = 1 && id_unsur_pelayanan = $row->id && alasan_pilih_jawaban != ''
                && jawaban_pertanyaan_unsur_$table_identity.is_active = 1
                ");



                if($alasan->num_rows() > 0){
                    $val_alasan = [];
                    foreach($alasan->result() as $get){
                        $val_alasan[] = '<li>' . $get->alasan_pilih_jawaban . '</li>';
                    }
                    $data_alasan = '
                    <tr>
                        <td style="text-align: left; padding-top:1em;">
                        Alasan yang diberikan responden pada unsur ' . $row->nama_unsur_pelayanan . ':

                        <ul>' . implode(" ", $val_alasan).'</ul>
                        </td>
                    </tr>';
                } else {
                    $data_alasan = '';
                }


                $html5[] = '<table style="width: 100%; padding-left: 2em;" class="table-list">
                    <tr>
                        <td>
                            <div style="text-align: left; font-weight:bold; padding-top:1em;">' . $row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan. '</div>
                            <br>
                            <div style="outline: dashed 1px black;">
                                <img src="https://quickchart.io/chart?c={%20type:%20%27horizontalBar%27,%20data:%20{%20labels:%20[' . $get_nama_kategori . '],%20datasets:%20[{%20label:%20%27Dataset%201%27,%20backgroundColor:%20%27rgb(255,%20159,%2064)%27,%20stack:%20%27Stack%200%27,%20data:%20[' . $get_persentase . '],%20},%20],%20},%20options:%20{%20title:%20{%20display:%20false,%20text:%20%27Chart.js%20Bar%20Chart%20-%20Stacked%27%20},%20legend:%20{%20display:%20false%20},%20plugins:%20{%20roundedBars:%20true%20},%20responsive:%20true,%20},%20}"
                                    alt="" width="70%">
                            </div>
                            <br>
                            Gambar ' . $no_img++ . '. Grafik Unsur ' . $row->nama_unsur_pelayanan . '
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-left: 2em;">
                            <br>
                            <div style="text-align: center;">Tabel ' . $no_tabel .'. Persentase Responden pada Unsur ' . $row->nama_unsur_pelayanan . '</div>
                                <table style="width: 90%; margin-left: auto; margin-right: auto;" class="table-list">
                                            <tr style="background-color:#E4E6EF;">
                                                <th class="td-th-list">No</th>
                                                <th class="td-th-list">Kategori</th>
                                                <th class="td-th-list">Jumlah</th>
                                                <th class="td-th-list">Persentase</th>
                                            </tr>' . $get_table . '
                                            <tr>
                                                <td class="td-th-list" style="text-align: center;" colspan="2"><b>TOTAL</b></td>
                                                <td class="td-th-list">' .  $t_perolehan . '</td>
                                                <td class="td-th-list">' . str_replace('.', ',', $t_persentase) . ' %</td>
                                            </tr>
                                            </table>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%; padding-left: 2em;" class="table-list">' .$data_alasan.'</table>
                ' . $html_rekap_tambahan[$key];

                $html5[] = '';

                //UNSUR MEMILIKI TURUNAN
            } else {

                $html_turunan = [];
                $sub_no_table = 1;
                foreach ($cek_sub->result() as $get) {

                    $s = 1;
                    $add_table_turunan = [];
                    $t_perolehan_turunan = 0;
                    $t_persentase_turunan = 0;
                    foreach ($get_pilihan_jawaban->result() as $value) {
                        if ($value->id_unsur_pelayanan == $get->id) {

                            $add_table_turunan[] = '<tr>
                                            <td class="td-th-list">' . $s++ . '</td>
                                            <td class="td-th-list">' . $value->nama_kategori_unsur_pelayanan . '</td>
                                            <td class="td-th-list">' . $value->perolehan . '</td>
                                            <td class="td-th-list">
                                                ' . ROUND(($value->perolehan / $value->jumlah_pengisi) * 100, 2) . ' %
                                            </td>
                                        </tr>';

                            $t_perolehan_turunan += $value->perolehan;
                            $t_persentase_turunan += ($value->perolehan / $value->jumlah_pengisi) * 100;
                        }
                    }
                    $get_table_turunan = implode(" ", $add_table_turunan);


                    $alasan = $this->db->query("SELECT *
                    FROM jawaban_pertanyaan_unsur_$table_identity
                    JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
                    JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
                    WHERE is_submit = 1 && id_unsur_pelayanan = $get->id && alasan_pilih_jawaban != '' && jawaban_pertanyaan_unsur_$table_identity.is_active = 1
                    ");


                    if($alasan->num_rows() > 0){
                        $val_alasan = [];
                        foreach($alasan->result() as $val){
                            $val_alasan[] = '<li>' . $val->alasan_pilih_jawaban . '</li>';
                        }
                        $data_alasan = '
                        <div style="font-size:13px;">Alasan yang diberikan responden pada unsur ' . $get->nama_unsur_pelayanan . ':</div>
                        <ul style="font-size:13px;">' . implode(" ", $val_alasan) . '</ul>';
                    } else {
                        $data_alasan = '';
                    }
                

                    $html_turunan[] = '
                        <li>
                            <div style="font-size:13px;"><b>' . $get->nomor_unsur . '. ' . $get->nama_unsur_pelayanan . '</b></div>
                            <div style="text-align: center; font-size:13px; padding-top:1em;">Tabel ' . $no_tabel . '.' .  $sub_no_table++ . '. Persentase Responden pada Unsur ' . $get->nama_unsur_pelayanan . '</div>
                            <table style="width: 90%; margin-left: auto; margin-right: auto;" class="table-list">
                                <tr style="background-color:#E4E6EF;">
                                    <th class="td-th-list">No</th>
                                    <th class="td-th-list">Kategori</th>
                                    <th class="td-th-list">Jumlah</th>
                                    <th class="td-th-list">Persentase</th>
                                </tr>' . $get_table_turunan . '
                                <tr>
                                    <td class="td-th-list" style="text-align: center;" colspan="2"><b>TOTAL</b></td>
                                    <td class="td-th-list">' .  $t_perolehan_turunan . '</td>
                                    <td class="td-th-list">' . str_replace('.', ',', $t_persentase_turunan) . ' %</td>
                                </tr>
                            </table>
                            <br>' .$data_alasan.'</li>';
                }
                $get_html_turunan = implode(" ", $html_turunan);


                $pilihan_jawaban_turunan = $this->db->query("SELECT * FROM kategori_unsur_pelayanan_$table_identity
                JOIN unsur_pelayanan_$table_identity ON kategori_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id WHERE id_parent = $row->id_unsur_pelayanan
                GROUP BY nomor_kategori_unsur_pelayanan")->result_array();

                if ($skala_likert == 5) {
                    $skala_5 = '<th class="td-th-list">' . $pilihan_jawaban_turunan[4]['nama_kategori_unsur_pelayanan'] . '</th>';
                    $cols = 5;
                } else {
                    $skala_5 = '';
                    $cols = 4;
                };


                $add_table_kesimpulan = [];
                $jum_persentase_1 = 0;
                $jum_persentase_2 = 0;
                $jum_persentase_3 = 0;
                $jum_persentase_4 = 0;
                $jum_persentase_5 = 0;
                $jum_indeks = 0;
                $j = 0;
                foreach ($rekap_turunan_unsur->result() as $obj) {
                    if ($obj->id_parent == $row->id_unsur_pelayanan) {

                        $predikat = $obj->rata_rata * ($skala_likert == 5 ? 20 : 25);
                        foreach ($definisi_skala->result() as $val) {
                            if ($predikat <= $val->range_bawah && $predikat >= $val->range_atas) {
                                $k_kategori = $val->kategori;
                            }
                        }
                        if ($predikat <= 0) {
                            $k_kategori = 'NULL';
                        }


                        if ($skala_likert == 5) {
                            $k_skala_5 = '<td class="td-th-list">' . ROUND(($obj->perolehan_5 / $obj->jumlah_pengisi) * 100, 2) . ' %</td>';
                        } else {
                            $k_skala_5 = '';
                        }



                        $add_table_kesimpulan[] = '<tr>
                                            <td class="td-th-list">' . $obj->nomor_unsur . '. ' . $obj->nama_unsur_pelayanan . '</th>
                                            <td class="td-th-list">' . ROUND(($obj->perolehan_1 / $obj->jumlah_pengisi) * 100, 2) . ' %</td>
                                            <td class="td-th-list">' . ROUND(($obj->perolehan_2 / $obj->jumlah_pengisi) * 100, 2) . ' %</td>
                                            <td class="td-th-list">' . ROUND(($obj->perolehan_3 / $obj->jumlah_pengisi) * 100, 2) . ' %</td>
                                            <td class="td-th-list">' . ROUND(($obj->perolehan_4 / $obj->jumlah_pengisi) * 100, 2) . ' %</td>'
                            . $k_skala_5 .
                            '<td class="td-th-list">' . ROUND($obj->rata_rata, 2) . '</td>
                                            <td class="td-th-list">
                                                ' . $k_kategori . '</td>
                                        </tr>';


                        $jum_persentase_1 += ($obj->perolehan_1 / $obj->jumlah_pengisi) * 100;
                        $jum_persentase_2 += ($obj->perolehan_2 / $obj->jumlah_pengisi) * 100;
                        $jum_persentase_3 += ($obj->perolehan_3 / $obj->jumlah_pengisi) * 100;
                        $jum_persentase_4 += ($obj->perolehan_4 / $obj->jumlah_pengisi) * 100;
                        $jum_persentase_5 += ($obj->perolehan_5 / $obj->jumlah_pengisi) * 100;
                        $jum_indeks += $obj->rata_rata;
                        $j++;
                    }
                }
                $get_html_table_kesimpulan = implode(" ", $add_table_kesimpulan);


                if ($skala_likert == 5) {
                    $jum_5 = '<th class="td-th-list">' . ROUND($jum_persentase_5 / $j, 2) . '%</th>';

                    $nama_chart = [
                        $pilihan_jawaban_turunan[0]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_1 / $j, 2) . '%25%27',
                        $pilihan_jawaban_turunan[1]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_2 / $j, 2) . '%25%27',
                        $pilihan_jawaban_turunan[2]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_3 / $j, 2) . '%25%27',
                        $pilihan_jawaban_turunan[3]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_4 / $j, 2) . '%25%27',
                        $pilihan_jawaban_turunan[4]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_5 / $j, 2) . '%25%27'
                    ];

                    $persen_chart = [ ROUND($jum_persentase_1 / $j, 2),  ROUND($jum_persentase_2 / $j, 2),  ROUND($jum_persentase_3 / $j, 2),  ROUND($jum_persentase_4 / $j, 2),  ROUND($jum_persentase_5 / $j, 2)];
                } else {
                    $jum_5 =  '';
                    $nama_chart = [
                    '%27' . $pilihan_jawaban_turunan[0]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_1 / $j, 2) . '%25%27',
                    '%27' . $pilihan_jawaban_turunan[1]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_2 / $j, 2) . '%25%27',
                    '%27' . $pilihan_jawaban_turunan[2]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_3 / $j, 2) . '%25%27',
                    '%27' . $pilihan_jawaban_turunan[3]['nama_kategori_unsur_pelayanan'] . '+=+' . ROUND($jum_persentase_4 / $j, 2) . '%25%27'
                    ];

                    $persen_chart = [ROUND($jum_persentase_1 / $j, 2),  ROUND($jum_persentase_2 / $j, 2),  ROUND($jum_persentase_3 / $j, 2),  ROUND($jum_persentase_4 / $j, 2)];
                }
                $get_series = implode(",", $persen_chart);
                $get_nama_opsi = implode(", ", $nama_chart);



                $rata_predikat = ($jum_indeks / $j) * ($skala_likert == 5 ? 20 : 25);
                foreach ($definisi_skala->result() as $val) {
                    if ($rata_predikat <= $val->range_bawah && $rata_predikat >= $val->range_atas) {
                        $rata_kategori = $val->kategori;
                    }
                }
                if ($rata_predikat <= 0) {
                    $rata_kategori = 'NULL';
                }


                $html5[] = '<table style="width: 100%;" class="table-list">
                    <tr>
                        <td>
                            <div style="text-align: left; font-weight:bold; padding-top:1em;">' . $row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan. '</div>
                            <br>
                            <div style="outline: dashed 1px black;">
                                <img src="https://quickchart.io/chart?c={%20type:%20%27horizontalBar%27,%20data:%20{%20labels:%20[' . $get_nama_opsi . '],%20datasets:%20[{%20label:%20%27Dataset%201%27,%20backgroundColor:%20%27rgb(255,%20159,%2064)%27,%20stack:%20%27Stack%200%27,%20data:%20[' . $get_series . '],%20},%20],%20},%20options:%20{%20title:%20{%20display:%20false,%20text:%20%27Chart.js%20Bar%20Chart%20-%20Stacked%27%20},%20legend:%20{%20display:%20false%20},%20plugins:%20{%20roundedBars:%20true%20},%20responsive:%20true,%20},%20}"
                                    alt="" width="70%">
                            </div>
                            <br>
                            Gambar ' . $no_img++ . '. Grafik Unsur ' . $row->nama_unsur_pelayanan . '
                        </td>
                    </tr>

                    <tr>
                        <td style="padding-left: 2em;">
                            <br>
                            <div style="text-align: center;">Tabel ' . $no_tabel . '. Persentase Responden pada Unsur ' . $row->nama_unsur_pelayanan . '</div>
                            <table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                                        <tr style="background-color:#E4E6EF;">
                                            <th class="td-th-list" rowspan="2">Unsur</th>
                                            <th class="td-th-list" colspan="' . $cols . '">Persentase</th>
                                            <th class="td-th-list" rowspan="2">Indeks</th>
                                            <th class="td-th-list" rowspan="2">Predikat</th>
                                        </tr>
            
                                        <tr style="background-color:#E4E6EF;">
                                            <th class="td-th-list">' . $pilihan_jawaban_turunan[0]['nama_kategori_unsur_pelayanan'] . '</th>
                                                <th class="td-th-list">' . $pilihan_jawaban_turunan[1]['nama_kategori_unsur_pelayanan'] . '</th>
                                                <th class="td-th-list">' . $pilihan_jawaban_turunan[2]['nama_kategori_unsur_pelayanan'] . '</th>
                                                <th class="td-th-list">' . $pilihan_jawaban_turunan[3]['nama_kategori_unsur_pelayanan'] . '</th>'
                                .  $skala_5 .
                                '</tr>' . $get_html_table_kesimpulan . '
                                        <tr style="background-color:#E4E6EF;">
                                            <th class="td-th-list">Rata-rata</th>
                                            <th class="td-th-list">' . ROUND($jum_persentase_1 / $j, 2) . '%</th>
                                            <th class="td-th-list">' . ROUND($jum_persentase_2 / $j, 2) . '%</th>
                                            <th class="td-th-list">' . ROUND($jum_persentase_3 / $j, 2) . '%</th>
                                            <th class="td-th-list">' . ROUND($jum_persentase_4 / $j, 2) . '%</th>' . $jum_5 .
                                '<th class="td-th-list">' .  ROUND($jum_indeks/$j, 2) . '</th>
                                            <th class="td-th-list">' .  $rata_kategori . '</th>
                                        </tr>
                                        </table>
                        </td>
                    </tr>
                </table>
                
                <ul>' . $get_html_turunan .'</ul>' . $html_rekap_tambahan[$key];
                        //$get_html_turunan
            }

            $no_tabel++;
            $this->data['get_html'] = implode(" ", $html5);

        }



    }




    public function _get_rekap_kualitatif($table_identity)
    {
        foreach ($this->db->get("pertanyaan_kualitatif_$table_identity")->result() as $value) {

            $jawaban_kualitatif = $this->db->query("SELECT * FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden JOIN jawaban_pertanyaan_kualitatif_$table_identity ON responden_$table_identity.id = jawaban_pertanyaan_kualitatif_$table_identity.id_responden
            WHERE is_submit = 1 && id_pertanyaan_kualitatif = $value->id");

            $kl = 1;
            $add_table_kualitatif = [];
            foreach ($jawaban_kualitatif->result() as $row) {
                $add_table_kualitatif[] = '
                <tr>
                    <td class="td-th-list" width="6%">' . $kl++ . '</th>
                    <td class="td-th-list" style="text-align: left;">' . $row->isi_jawaban_kualitatif . '</td>
                </tr>';
            }
            $get_kualitatif = implode(" ", $add_table_kualitatif);


            $get_rekap_kualitatif[] = '<li style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">' . $value->isi_pertanyaan .
                '<table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                     <tr style="background-color:#E4E6EF;">
                        <th class="td-th-list" width="6%">No</th>
                        <th class="td-th-list">Jawaban</th>
                    </tr>' . $get_kualitatif .
                '</table></li>';
        }

        $this->data['html_rekap_kualitatif'] = implode(" ", $get_rekap_kualitatif);
    }





    public function _get_rekap_alasan_jawaban($table_identity)
    {
        $this->db->select("*, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = unsur_pelayanan_$table_identity.id) AS nomor_unsur");
        $this->db->from("pertanyaan_unsur_pelayanan_$table_identity");
        $unsur = $this->db->get();


        $get_rekap_alasan = [];
        foreach ($unsur->result() as $value) {
            $this->db->select("*");
            $this->db->from("jawaban_pertanyaan_unsur_$table_identity");
            $this->db->join("responden_$table_identity", "responden_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_responden");
            $this->db->join("survey_$table_identity", "responden_$table_identity.id = survey_$table_identity.id_responden");
            $this->db->where("survey_$table_identity.is_submit", 1);
            $this->db->where("jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur", $value->id_pertanyaan_unsur);
            $this->db->where("jawaban_pertanyaan_unsur_$table_identity.is_active", 1);
            $this->db->where("jawaban_pertanyaan_unsur_$table_identity.alasan_pilih_jawaban !=", "");
            $jawaban_p_unsur = $this->db->get();


            $q = 1;
            $add_table_alasan = [];
            foreach ($jawaban_p_unsur->result() as $values) {
                $add_table_alasan[] = '<tr>
                    <td class="td-th-list" width="6%">' . $q++ . '</td>
                    <td class="td-th-list" style="text-align: left;">' . $values->alasan_pilih_jawaban . '</td>
                    </tr>';
            }
            $get_table_alasan = implode(" ", $add_table_alasan);

            $get_rekap_alasan[] = '<li style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">' . $value->nomor_unsur . ' ' . $value->isi_pertanyaan_unsur .
                ' <table style="width: 100%; margin-left: auto; margin-right: auto;" class="table-list">
                    <tr style="background-color:#E4E6EF;">
                        <th class="td-th-list" width="6%">No</th>
                        <th class="td-th-list">Alasan Jawaban</th>
                    </tr>' . $get_table_alasan . '
            </table>
            <br>
            <br>
            </li>';
        }
        $this->data['html_rekap_alasan'] = implode(" ", $get_rekap_alasan);
    }




    public function _get_data_laporan($table_identity, $skala_likert)
    {
        $this->data['survey'] = $this->db->get_where("survey_$table_identity", array("is_submit", 1));

        //HASIL SURVEI KEPUASAN MASYARAKAT Bar Chart Nilai SKM Per Unsur Pelayanan
        $this->data['nilai_per_unsur'] = $this->db->query("SELECT IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, 
		(SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS rata_rata, 
		(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan, 
		((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur, 
		(SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, 
		(SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan, unsur_pelayanan_$table_identity.id AS id_unsur
		
		FROM jawaban_pertanyaan_unsur_$table_identity 
		JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id 
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE survey_$table_identity.is_submit = 1
		GROUP BY id_sub
		ORDER BY unsur_pelayanan_$table_identity.id");

        // $this->data['nama_per_unsur'] = $this->db->query("SELECT GROUP_CONCAT(nomor_unsur ORDER BY unsur_pelayanan_$table_identity.id DESC SEPARATOR '|') AS nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_parent = 0")->row()->nomor_unsur;

        $bobot_per_unsur = [];
        foreach ($this->data['nilai_per_unsur']->result() as $value) {
            $nama_per_unsur[] = "'" . str_replace(' ', '+', $value->nomor_unsur) . "+=+" . ROUND($value->nilai_per_unsur, 2) . "'";
            $bobot_per_unsur[] = $value->nilai_per_unsur;
        }
        $this->data['nama_per_unsur'] = implode(", ", $nama_per_unsur);
        $this->data['bobot_per_unsur'] = implode(", ", $bobot_per_unsur);


        //NILAI INDEX
        $this->db->select("nama_unsur_pelayanan, IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$table_identity WHERE id_parent = 0)) AS rata_rata_bobot");
        $this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->group_by('id_sub');
        $rata_rata_bobot = $this->db->get();

        foreach ($rata_rata_bobot->result() as $rata_rata_bobot) {
            $nilai_bobot[] = $rata_rata_bobot->rata_rata_bobot;
            $ikm_nilai_tertimbang = array_sum($nilai_bobot);
            $ikm = ROUND($ikm_nilai_tertimbang * $skala_likert, 10);
        }

        foreach ($this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC")->result() as $obj) {
            if ($ikm <= $obj->range_bawah && $ikm >= $obj->range_atas) {
                $this->data['ketegori'] = $obj->kategori;
                $this->data['mutu_pelayanan'] = $obj->mutu;
            }
        }
        if ($ikm <= 0) {
            $this->data['ketegori'] = 'NULL';
            $this->data['mutu_pelayanan'] = 'NULL';
        }

        // if ($ikm <= 100 && $ikm >= 88.31) {
        //     $this->data['index'] = 'Sangat Baik';
        //     $this->data['mutu_pelayanan'] = 'A';
        // } elseif ($ikm <= 88.40 && $ikm >= 76.61) {
        //     $this->data['index'] = 'Baik';
        //     $this->data['mutu_pelayanan'] = 'B';
        // } elseif ($ikm <= 76.60 && $ikm >= 65) {
        //     $this->data['index'] = 'Kurang Baik';
        //     $this->data['mutu_pelayanan'] = 'C';
        // } elseif ($ikm <= 64.99 && $ikm >= 25) {
        //     $this->data['index'] = 'Tidak Baik';
        //     $this->data['mutu_pelayanan'] = 'D';
        // } else {
        //     $this->data['index'] = 'NULL';
        //     $this->data['mutu_pelayanan'] = 'NULL';
        // }
        $this->data['nilai_tertimbang'] = $ikm_nilai_tertimbang;
        $this->data['nilai_skm'] = $ikm;


        //GRAFIK UNSUR
        // $this->db->select("*, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan");
        // $this->db->from("unsur_pelayanan_$table_identity");
        // $this->db->where(['id_parent' => 0]);
        // $this->data['unsur_pelayanan'] = $this->db->get();
    }



    public function _get_unsur_tertinggi_terendah($table_identity)
    {
        //UNSUR TERENDAH DAN TERTINGGI
        $nilai_per_unsur_desc = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT id_responden))/(COUNT(id_parent)/COUNT(DISTINCT id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
        GROUP BY id_sub
        ORDER BY nilai_per_unsur DESC
        LIMIT 3");

        $nilai_per_unsur_asc = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT id_responden))/(COUNT(id_parent)/COUNT(DISTINCT id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
        GROUP BY id_sub
        ORDER BY nilai_per_unsur ASC
        LIMIT 3");

        $asc = [];
        foreach ($nilai_per_unsur_asc->result() as $value) {
            $asc[] = $value->nomor_unsur . '. ' . $value->nama_unsur_pelayanan;
        }
        $this->data['asc'] = implode("<br>", $asc);

        $desc = [];
        foreach ($nilai_per_unsur_desc->result() as $get) {
            $desc[] = $get->nomor_unsur . '. ' . $get->nama_unsur_pelayanan;
        }
        $this->data['desc'] = implode("<br>", $desc);
    }




    public function _get_kuadran($table_identity)
    {
        $this->db->select('COUNT(id) AS jumlah_unsur');
        $this->db->from('unsur_pelayanan_' . $table_identity);
        $this->db->where('id_parent = 0');
        $jumlah_unsur = $this->db->get()->row()->jumlah_unsur;
        $this->data['jumlah_unsur'] = $jumlah_unsur;

        //SKOR JAWABAN UNSUR
        $this->db->select('*');
        $this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->data['skor'] = $this->db->get();

        //NILAI PER UNSUR
        $this->db->select("IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan");
        $this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->group_by('id_sub');
        $object_unsur = $this->db->get();
        $this->data['nilai_per_unsur'] = $object_unsur;


        $nilai_unsur = 0;
        foreach ($object_unsur->result() as $values) {
            $nilai_unsur += $values->nilai_per_unsur;
        }

        //NILAI PER HARAPAN
        $this->db->select("((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur");
        $this->db->from("jawaban_pertanyaan_harapan_$table_identity");
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->group_by("IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent)");
        $object_harapan = $this->db->get();
        $this->data['nilai_per_unsur_harapan'] = $object_harapan;

        $nilai_harapan = 0;
        foreach ($object_harapan->result() as $rows) {
            $nilai_harapan += $rows->nilai_per_unsur;
        }


        $query =  $this->db->query("SELECT nama_unsur_pelayanan, IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub,

		ROUND((SUM(
		(SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur))/(SELECT COUNT(survey_$table_identity.id_responden) FROM jawaban_pertanyaan_unsur_$table_identity 
		JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur && survey_$table_identity.is_submit = 1)/COUNT(id_parent)),2) AS skor_unsur,
		
		ROUND((SUM(
		(SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_harapan_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur))/(SELECT COUNT(survey_$table_identity.id_responden) FROM jawaban_pertanyaan_harapan_$table_identity 
		JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden
		WHERE pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur && survey_$table_identity.is_submit = 1)/COUNT(id_parent)),2) AS skor_harapan,
		
		IF(is_sub_unsur_pelayanan = 1,SUBSTR(nomor_unsur,1, 3), nomor_unsur) AS nomor
		
		FROM pertanyaan_unsur_pelayanan_$table_identity
		JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		GROUP BY id_sub");
        $this->data['grafik'] = $query;
        // var_dump($this->data['grafik']->result());


        if ($this->data['skor']->num_rows() > 0) {
            $this->data['skor'] = $this->data['skor'];
            $this->data['total_rata_unsur'] = $nilai_unsur / $jumlah_unsur;
            $this->data['total_rata_harapan'] = $nilai_harapan / $jumlah_unsur;
        } else {
            $this->data['pesan'] = 'survei belum dimulai atau belum ada responden !';
            return view('not_questions/index', $this->data);
            exit();
        }
    }

    public function _get_data_profile($id1, $id2)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->join('users_groups', 'users.id = users_groups.user_id');
        $this->db->where('users.username', $this->session->userdata('username'));
        $data_user = $this->db->get()->row();
        // $user_identity = 'drs' . $data_user->is_parent;

        $this->db->select('*');
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



    public function generate_word($username, $slug, $filename)
    {

        $manage_survey = $this->db->get_where('manage_survey', array('slug' => "$slug"))->row();
        $table_identity = $manage_survey->table_identity;

        $atribut_pertanyaan = unserialize($manage_survey->atribut_pertanyaan_survey);

        $user = $this->ion_auth->user()->row();
        $data_user = [
            'foto_profile' => ($user->foto_profile != '') ? $user->foto_profile : '200px.jpg',
        ];

        $data_survei = [
            'nama_survei' => $manage_survey->survey_name,
            'tahun_survei' => $manage_survey->survey_year,
            'survei_dimulai' => date("d-m-Y", strtotime($manage_survey->survey_start)),
            'survei_selesai' => date("d-m-Y", strtotime($manage_survey->survey_end)),
            'nama_organisasi' => $manage_survey->organisasi,
            'alamat_organisasi' => $manage_survey->alamat,
            'telp_organisasi' => $manage_survey->no_tlpn,
            'email_organisasi' => $manage_survey->email,
            'executive_summary' => $manage_survey->executive_summary,
            'visi' => strip_tags($manage_survey->visi),
            'misi' => strip_tags($manage_survey->misi)
        ];

        //PENDEFINISIAN SKALA LIKERT
        $skala_likert = 100 / ($manage_survey->skala_likert == 5 ? 5 : 4);
        $definisi_skala = $this->db->query("SELECT * FROM definisi_skala_$table_identity ORDER BY id DESC");




        $this->db->select("nama_unsur_pelayanan, IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai, (((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden)))/(SELECT COUNT(id) FROM unsur_pelayanan_$table_identity WHERE id_parent = 0)) AS rata_rata_bobot");
        $this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->group_by('id_sub');
        $rata_rata_bobot = $this->db->get();

        foreach ($rata_rata_bobot->result() as $rata_rata_bobot) {
            $nilai_bobot[] = $rata_rata_bobot->rata_rata_bobot;
            $ikm_nilai_tertimbang = array_sum($nilai_bobot);
            $ikm = ROUND($ikm_nilai_tertimbang * $skala_likert, 10);
        }

        foreach ($definisi_skala->result() as $obj) {
            if ($ikm <= $obj->range_bawah && $ikm >= $obj->range_atas) {
                $index  = $obj->kategori;
                $mutu_pelayanan = $obj->mutu;
            }
        }
        if ($ikm <= 0) {
            $index = 'NULL';
            $mutu_pelayanan = 'NULL';
        }


        // if ($ikm <= 100 && $ikm >= 88.31) {
        //     $index = 'Sangat Baik';
        //     $mutu_pelayanan = 'A';
        // } elseif ($ikm <= 88.40 && $ikm >= 76.61) {
        //     $index = 'Baik';
        //     $mutu_pelayanan = 'B';
        // } elseif ($ikm <= 76.60 && $ikm >= 65) {
        //     $index = 'Kurang Baik';
        //     $mutu_pelayanan = 'C';
        // } elseif ($ikm <= 64.99 && $ikm >= 25) {
        //     $index = 'Tidak Baik';
        //     $mutu_pelayanan = 'D';
        // } else {
        //     $index = 'NULL';
        //     $mutu_pelayanan = 'NULL';
        // }
        $nilai_tertimbang = $ikm_nilai_tertimbang;
        $nilai_skm = $ikm;


        // UNSUR TERENDAH DAN TERTINGGI
        $nilai_per_unsur_desc = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT id_responden))/(COUNT(id_parent)/COUNT(DISTINCT id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
        GROUP BY id_sub
        ORDER BY nilai_per_unsur DESC
        LIMIT 3");

        $nilai_per_unsur_asc = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT id_responden))/(COUNT(id_parent)/COUNT(DISTINCT id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan
        FROM jawaban_pertanyaan_unsur_$table_identity
        JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
        GROUP BY id_sub
        ORDER BY nilai_per_unsur ASC
        LIMIT 3");

        $asc = [];
        foreach ($nilai_per_unsur_asc->result() as $value) {
            $asc[] = $value->nomor_unsur . '. ' . $value->nama_unsur_pelayanan;
        }
        $unsur_terendah = implode("<br />", $asc);

        $desc = [];
        foreach ($nilai_per_unsur_desc->result() as $get) {
            $desc[] = $get->nomor_unsur . '. ' . $get->nama_unsur_pelayanan;
        }
        $unsur_tertinggi = implode("<br />", $desc);

        $total_survey = $this->db->get_where("survey_$table_identity", array('is_submit' => 1))->num_rows();




        $bulan = array (1 =>   'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $split1 = explode('-', $manage_survey->survey_start);
        $split2 = explode('-', $manage_survey->survey_end);
        if((int)$split1[0] != (int)$split2[0]){
            $periode =  strtoupper($bulan[ (int)$split1[1] ] . ' ' . $split1[0] . ' - ' . $bulan[ (int)$split2[1] ] . ' ' . $split2[0]);
        }else{
            if($bulan[ (int)$split1[1] ] == $bulan[ (int)$split2[1] ]){
                $periode =  strtoupper($bulan[ (int)$split2[1] ] . ' ' . $split1[0]);
            }else{
                $periode =  strtoupper($bulan[ (int)$split1[1] ] . ' - ' . $bulan[ (int)$split2[1] ] . ' ' . $split1[0]);
            }
        }





        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        PhpOffice\PhpWord\Settings::setDefaultFontSize(11);

        $phpWord->addParagraphStyle('Heading2', array('alignment' => 'center'));

        $fontStyleName = 'rStyle';
        $phpWord->addFontStyle($fontStyleName, array('name' => 'Arial', 'size' => 11, 'allCaps' => true));

        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));


        $section = $phpWord->addSection();

        // Add first page header
        $header = $section->addHeader();
        $header->firstPage();

        // $header->addWatermark(base_url().'assets/img/carousel/header-1.jpg', ['marginTop' => 200, 'marginLeft' => 55]);
        $header->addWatermark(base_url().'assets/img/site/logo/logo-dark2.png');

        // Add header for all other pages
        $subsequent = $section->addHeader();
        $subsequent->addImage(
            base_url() . 'assets/klien/foto_profile/' . $data_user['foto_profile'],
            array(
                'positioning'        => 'relative',
                'marginTop'          => -5,
                'marginLeft'         => 0,
                'width'              => 55,
                'height'             => 55,
                'wrappingStyle'      => 'behind',
                'wrapDistanceRight'  => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(),
                'wrapDistanceBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(),
            )
        );
        $subsequent->addText('L A P O R A N', array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'DE2226'), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $subsequent->addText('SURVEI KEPUASAN PELANGGAN', array('name' => 'Arial', 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $subsequent->addText(strtoupper($data_survei['nama_organisasi']), array('name' => 'Arial', 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $subsequent->addLine(['weight' => 1, 'width' => 450, 'height' => 0]);

        // Add footer
        $footer = $section->addFooter();
        $footer->addLine(['weight' => 1, 'width' => 450, 'height' => 0]);
        // $footer->addText($data_survei['nama_organisasi'] . ' - ' . $data_survei['tahun_survei'], array('name' => 'Arial', 'size' => 10), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $footer->addText('SKM ' . $data_survei['tahun_survei'] . ' - Generate by SurveiKu.com', array('name' => 'Arial', 'size' => 10), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $footer->addPreserveText('{PAGE}', null, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        // HALAMAN COVER LAPORAN
        $section->addTextBreak(3);

        $section->addImage(base_url() . 'assets/klien/foto_profile/' . $data_user['foto_profile'], array('width' => 140, 'height' => 140, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $section->addTextBreak(3);

        $section->addText('LAPORAN', array('bold' => true, 'size' => 24), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $section->addTextBreak();
        $section->addText('SURVEI KEPUASAN PELANGGAN', array('name' => 'Arial', 'size' => 18, 'bold' => true), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $section->addTextBreak();
        $section->addText(strtoupper($data_survei['nama_organisasi']), array('name' => 'Arial', 'size' => 16, 'bold' => true), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $section->addText(strtoupper($user->company), array('name' => 'Arial', 'size' => 16, 'bold' => true), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $section->addTextBreak();
        // $section->addText('Periode ' . $data_survei['survei_dimulai'] . ' - ' . $data_survei['survei_selesai'], array('name' => 'Arial', 'size' => 14, 'bold' => true), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $section->addText('PERIODE ' . $periode, array('name' => 'Arial', 'size' => 14, 'bold' => true), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));

        $section->addTextBreak(7);

        // $section->addText($data_survei['nama_organisasi'], array('name' => 'Arial', 'size' => 10, 'allCaps' => true), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        // $section->addText($data_survei['alamat_organisasi'], array('name' => 'Arial', 'size' => 10), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        // $section->addText($data_survei['telp_organisasi'], array('name' => 'Arial', 'size' => 10), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        // $section->addText($data_survei['email_organisasi'], array('name' => 'Arial', 'size' => 10), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));



        $section->addPageBreak();



        // HALAMAN PROFIL ORGANISASI

        $section->addText('BAB I', array('bold' => true, 'size' => 16), $paragraphStyleName);
        //$section->addTextBreak();
        $section->addText('PENDAHULUAN', array('bold' => true, 'size' => 16), $paragraphStyleName);
        $section->addTextBreak();

        $texthtmlbab1 = '<table>
        <tr>
            <td width="5%"><b>1.</b></td>
            <td width="95%"><b>Latar Belakang</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify" style="text-indent: 50pt; ">Seiring dengan kemajuan teknologi dan tuntutan pelanggan dalam hal pelayanan, maka unit penyelenggara pelayanan publik dituntut untuk memenuhi harapan pelanggan dalam melakukan pelayanan.</p>
        
            <p align="justify" style="text-indent: 50pt; ">Pelayanan publik yang dilakukan oleh aparatur pemerintah saat ini dirasakan belum memenuhi harapan pelanggan. Hal ini dapat diketahui dari berbagai keluhan pelanggan yang disampaikan melalui media massa dan jejaring sosial. Tentunya keluhan tersebut jika tidak ditangani akan memberikan dampak buruk terhadap pemerintah. Lebih jauh lagi adalah dapat menimbulkan ketidakpercayaan dari pelanggan.</p>
            
            <p align="justify" style="text-indent: 50pt; ">Salah satu upaya yang harus dilakukan dalam perbaikan pelayanan publik adalah melakukan survei kepuasan pelanggan kepada pengguna layanan dengan mengukur kepuasan pelanggan pengguna layanan.</p></td>
        </tr>
        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>
        <tr>
            <td width="5%"><b>2.</b></td>
            <td width="95%"><b>Tujuan Survei Kepuasan Pelanggan</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify" style="text-indent: 50pt; ">Kegiatan Survei Kepuasan Pelanggan terhadap pelayanan publik bertujuan untuk mendapatkan feedback/umpan balik atas kinerja pelayanan yang diberikan kepada pelanggan guna perbaikan dan peningkatan kinerja pelayanan secara berkesinambungan.</p></td>
        </tr>
        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>

        <tr>
            <td width="5%"><b>3.</b></td>
            <td width="95%"><b>Metodologi</b></td>
        </tr>
        <tr>
            <td width="5%">3.1.</td>
            <td width="95%">Populasi</td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Populasi dari kegiatan Survei Kepuasan Pelanggan adalah penyelenggara pelayanan publik, yaitu instansi pemerintah pusat dan pemerintah daerah, termasuk BUMN/BUMD dan BHMN menyesuaikan dengan lingkup yang akan disurvei.</p></td>
        </tr>
        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>
        <tr>
            <td width="5%">3.2.</td>
            <td width="95%">Sampel</td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Sampel kegiatan Survei Kepuasan Pelanggan ditentukan dengan menggunakan perhitungan rumus Krejcie dan Morgan sebagai berikut:</p>
            <b>Rumus Krejcie dan Morgan:</b>
            </td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%">
                <table width="50%" align="center" style="border: 1px #000 solid;">
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S = {λ². N. P. Q}/ {d² (N-1) + λ². P. Q</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%">
                <table>
                    <tr>
                        <td width="25%">Keterangan</td>
                        <td width="75%">
                        <p>S = Jumlah sampel</p>
                        <p>λ² = Lamda (faktor pengali) dengan dk = 1, (taraf kesalahan yang digunakan 5%, sehingga nilai lamba 3,841)</p>
                        <p>N = Populasi sebanyak '.$manage_survey->jumlah_populasi.'</p>
                        <p>P = Q = 0,5 (populasi menyebar normal)</p>
                        <p>d = 0,05</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%">Sehingga dari perhitungan di atas, jumlah responden minimal yang harus diperoleh adalah '.$manage_survey->jumlah_sampling.' responden.</td>
        </tr>
        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>
        <tr>
            <td width="5%">3.3.</td>
            <td width="95%">Responden</td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Responden adalah penerima pelayanan publik yang pada saat pencacahan sedang berada di lokasi unit pelayanan, atau yang pernah menerima pelayanan dari aparatur penyelenggara pelayanan publik.</p></td>
        </tr>

        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>
        <tr>
            <td width="5%"><b>4.</b></td>
            <td width="95%"><b>Tim Survei Kepuasan Pelanggan</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Survei Kepuasan Pelanggan ini dilakukan oleh Tim Survei Kepuasan Pelanggan yang telah ditetapkan.</p></td>
        </tr>

        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>

        <tr>
            <td width="5%"><b>5.</b></td>
            <td width="95%"><b>Jadwal Survei Kepuasan Pelanggan</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Jadwal Survei Kepuasan Pelanggan dilakukan sesuai dengan jadwal yang telah ditentukan.</p></td>
        </tr>

        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab1, false, false);
        $section->addTextBreak();

        // $section->addText('Profil Organisasi', array('bold' => true, 'size' => 18), $paragraphStyleName);

        // $section->addTextBreak();

        // $section->addText('Survei Kepuasan Pelanggan di ' . $data_survei['nama_organisasi'] . ' dilaksanakan pada seluruh layanan. Survei ini mendapat respon positif dari pelanggan yang mengharapkan adanya perbaikan kinerja pelayanan. Berikut merupakan profil organisasi unit penyelenggara pelayanan publik', array('name' => 'Arial', 'size' => 11), array('keepNext' => true, 'indentation' => array('firstLine' => 500), 'align' => 'both'));

        // $section->addTextBreak();

        $fancyTableStyleName = 'Profil Organisasi';
        $fancyTableStyle = array('borderSize' => 6, 'borderColor' => 'FFFFFF', 'cellMargin' => 30, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => 'FFFFFF');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('bold' => true);
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);

        /*$table->addRow();
        $table->addCell(20)->addText("•");
        $table->addCell(3500)->addText("Nama Instansi", array('name' => 'Arial', 'size' => 11));
        $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        $table->addCell(8500)->addText($data_survei['nama_organisasi'], array('name' => 'Arial', 'size' => 11));

        $table->addRow();
        $table->addCell(20)->addText("•");
        $table->addCell(3500)->addText("Alamat", array('name' => 'Arial', 'size' => 11));
        $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        $table->addCell(8500)->addText($data_survei['alamat_organisasi'], array('name' => 'Arial', 'size' => 11));

        $table->addRow();
        $table->addCell(20)->addText("•");
        $table->addCell(3500)->addText("No.Telp/Fax", array('name' => 'Arial', 'size' => 11));
        $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        $table->addCell(8500)->addText($data_survei['telp_organisasi'], array('name' => 'Arial', 'size' => 11));

        $table->addRow();
        $table->addCell(20)->addText("•");
        $table->addCell(3500)->addText("Email", array('name' => 'Arial', 'size' => 11));
        $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        $table->addCell(8500)->addText($data_survei['email_organisasi'], array('name' => 'Arial', 'size' => 11));

        $table->addRow();
        $table->addCell(20)->addText("•");
        $table->addCell(3500)->addText("Visi", array('name' => 'Arial', 'size' => 11));
        $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        $table->addCell(8500)->addText($data_survei['visi'], array('name' => 'Arial', 'size' => 11));

        $table->addRow();
        $table->addCell(20)->addText("•");
        $table->addCell(3500)->addText("Misi", array('name' => 'Arial', 'size' => 11));
        $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        $table->addCell(8500)->addText($data_survei['misi'], array('name' => 'Arial', 'size' => 11));*/

        /*$visi = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si","<$1$2>", $data_survei['visi']);
        $misi = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si","<$1$2>", $data_survei['misi']);*/
        $visi = ($data_survei['visi']) ? $data_survei['visi'] : '-';
        $misi = ($data_survei['misi']) ? $data_survei['misi'] : '-';
        $someHTMLcode = '<table>
        <tr>
            <td width="41%">• Nama Instansi</td>
            <td width="4%">:</td>
            <td width="55%">' . $data_survei['nama_organisasi'] . '</td>
        </tr>
        <tr>
            <td width="41%">• Alamat</td>
            <td width="4%">:</td>
            <td width="55%">' . $data_survei['alamat_organisasi'] . '</td>
        </tr>
        <tr>
            <td width="41%">• No.Telp/Fax</td>
            <td width="4%">:</td>
            <td width="55%">' . $data_survei['telp_organisasi'] . '</td>
        </tr>
        <tr>
            <td width="41%">• Email</td>
            <td width="4%">:</td>
            <td width="55%">' . $data_survei['email_organisasi'] . '</td>
        </tr>
        <tr>
            <td width="41%">• Visi</td>
            <td width="4%">:</td>
            <td width="55%">' . $visi . '</td>
        </tr>
        <tr>
            <td width="41%">• Misi</td>
            <td width="4%">:</td>
            <td width="55%">' . $misi . '</td>
        </tr>
        </table>';

        //\PhpOffice\PhpWord\Shared\Html::addHtml($section, $someHTMLcode, false, false);

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Waktu pelayanan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Jenis pelayanan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Persyaratan setiap jenis pelayanan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Jangka waktu penyelesaian setiap jenis pelayanan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Biaya pelayanan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Jumlah pegawai", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Fasilitas penunjang", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Sarana pengaduan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Dokumentasi fasilitas pelayanan", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));

        // $table->addRow();
        // $table->addCell(20)->addText("•");
        // $table->addCell(3500)->addText("Struktur organisasi", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(30)->addText(":", array('name' => 'Arial', 'size' => 11));
        // $table->addCell(8500)->addText('', array('name' => 'Arial', 'size' => 11));


        $section->addPageBreak();

        // HALAMAN EXECUTIVE SUMMARY
        // $section->addText('Executive Summary', array('bold' => true, 'size' => 18), $paragraphStyleName);
        // $section->addTextBreak();
        // \PhpOffice\PhpWord\Shared\Html::addHtml($section, $data_survei['executive_summary'], false, false);
        // $section->addPageBreak();

        // HALAMAN HASIL SURVEI KEPUASAN PELANGGAN

        $section->addText('BAB II', array('bold' => true, 'size' => 16), $paragraphStyleName);
        //$section->addTextBreak();
        $section->addText('ANALISIS', array('bold' => true, 'size' => 16), $paragraphStyleName);
        $section->addTextBreak();
        // $section->addText('Hasil Survei Kepuasan Pelanggan', array('bold' => true, 'size' => 18), $paragraphStyleName);
        // $section->addTextBreak();
        // $section->addText('Hasil Survei Kepuasan Pelanggan ' . $data_survei['nama_organisasi'] . ' Periode ' . $data_survei['survei_dimulai'] . ' s/d ' . $data_survei['survei_selesai'] . ' dengan total ' . $total_survey . ' responden seperti pada tabel 1 menghasilkan Indeks Kepuasan Pelanggan (IKP) sebesar ' . ROUND($nilai_tertimbang, 2) . '. Dengan demikian pelayanan publik pada ' . $data_survei['nama_organisasi'] . ' berada pada kategori ' . $index . ' atau dengan nilai konversi IKP sebesar ' . ROUND($nilai_skm, 2) . '.', array('name' => 'Arial', 'size' => 11), array('keepNext' => true, 'indentation' => array('firstLine' => 500), 'align' => 'both'));
        // $section->addTextBreak(1);

        $texthtmlbab2 = '<table>
        <tr>
            <td width="5%"><b>1.</b></td>
            <td width="95%"><b>Jenis Layanan</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Berikut merupakan jenis layanan yang diperoleh dari Survei Kepuasan Pelanggan:</p></td>
        </tr>
        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab2, false, false);
        $section->addTextBreak();

        $section->addText('Tabel 1. Persentase Jenis Pelayanan', array('size' => 11), $paragraphStyleName);

        $fancyTableStyleName = 'Tabel Jenis Layanan';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        $table->addRow();
        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
        $table->addCell(5000, $fancyTableCellStyle)->addText('Jenis Pelayanan', $fancyTableFontStyle);
        $table->addCell(1000, $fancyTableCellStyle)->addText('Jumlah', $fancyTableFontStyle);
        $table->addCell(1000, $fancyTableCellStyle)->addText('Persentase Responden', $fancyTableFontStyle);


        
        $responden = $this->db->query("SELECT * FROM responden_$table_identity
                JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                WHERE is_submit = 1");

                $data = [];
                foreach ($responden->result() as $key => $value) {
                    //$id_layanan_survei = implode(", ", unserialize($value->id_layanan_survei));
                    $id_layanan_survei = $value->id_layanan_survei;
                    $data[$key] = "UNION ALL SELECT *
                                FROM layanan_survei_$table_identity
                                WHERE id IN ($id_layanan_survei)";
                }
                $tabel_layanan = implode(" ", $data);

                $layanan = $this->db->query("
                SELECT id, nama_layanan, COUNT(id) - 1 AS perolehan,
                SUM(Count(id)) OVER () - (SELECT COUNT(id) FROM layanan_survei_$table_identity WHERE is_active = 1) as total_survei
                FROM (
                    SELECT * FROM layanan_survei_$table_identity
                    $tabel_layanan
                    ) ls
                WHERE is_active = 1
                GROUP BY id
                ");

        $no = 1;

        foreach ($layanan->result() as $row) {
            $perolehan[] = $row->perolehan;
            $total_perolehan = array_sum($perolehan);

            $persentase[] = ($row->perolehan/$row->total_survei) * 100;
            $total_persentase  = array_sum($persentase);

            $table->addRow();
            $table->addCell(150)->addText($no++, $cellTableFontStyle);
            $table->addCell(5000)->addText($row->nama_layanan, $cellTableFontStyle);
            $table->addCell(1000)->addText($row->perolehan, $cellTableFontStyle);
            $table->addCell(1000)->addText(ROUND(($row->perolehan/$row->total_survei) * 100, 2).'%', $cellTableFontStyle);
        }

        $table->addRow();
        $table->addCell(150)->addText('', $cellTableFontStyle);
        $table->addCell(5000)->addText('Total', array('bold' => true), $cellTableFontStyle);
        $table->addCell(1000)->addText($total_perolehan, array('bold' => true), $cellTableFontStyle);
        $table->addCell(1000)->addText(ROUND($total_persentase).'%', array('bold' => true), $cellTableFontStyle);

        $section->addTextBreak();

        /*$fancyTableStyleName = 'Unsur Survei';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        $table->addRow();
        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
        $table->addCell(4000, $fancyTableCellStyle)->addText('Unit Pelayanan', $fancyTableFontStyle);
        $table->addCell(1200, $fancyTableCellStyle)->addText('Nilai IKP', $fancyTableFontStyle);
        $table->addCell(1200, $fancyTableCellStyle)->addText('Nilai Konversi', $fancyTableFontStyle);
        $table->addCell(1200, $fancyTableCellStyle)->addText('Kualitas Pelayanan', $fancyTableFontStyle);
        $table->addRow();
        $table->addCell(150)->addText('1.', $cellTableFontStyle);
        $table->addCell(4000)->addText($data_survei['nama_organisasi'], $cellTableFontStyle);
        $table->addCell(1200)->addText(str_replace('.', ',', ROUND($nilai_tertimbang, 3)), $cellTableFontStyle);
        $table->addCell(1200)->addText(str_replace('.', ',', ROUND($nilai_skm, 2)), $cellTableFontStyle);
        $table->addCell(1200)->addText($mutu_pelayanan, $cellTableFontStyle);*/

        $section->addTextBreak(1);

        

        // HALAMAN Karakteristik Responden
        $texthtmlbab22 = '<table>
        <tr>
            <td width="5%"><b>2.</b></td>
            <td width="95%"><b>Profil Responden</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Berikut merupakan karakteristik responden yang diperoleh dari Survei Kepuasan Pelanggan pada ' . $data_survei['nama_organisasi'] . '.</p></td>
        </tr>
        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab22, false, false);
        $section->addTextBreak();
        // $section->addText('Karakteristik Responden', array('bold' => true, 'size' => 18), $paragraphStyleName);

        // $section->addTextBreak();

        // Karakteristik Responden
        $profil_responden = $this->db->query("SELECT * FROM profil_responden_$table_identity WHERE jenis_isian = 1");

        $arr_profil_responden = [];
        foreach ($profil_responden->result() as $get) {
            $arr_profil_responden[] = $get->nama_profil_responden;
        }
        $arr_profil_responden = implode(", ", $arr_profil_responden);

        // $section->addText('Responden merupakan pihak yang dipakai sebagai sampel dalam sebuah penelitian. Karakteristik responden akan mempengaruhi teknik sampling yang digunakan dalam penelitian. Responden dipilih secara acak yang ditentukan sesuai dengan karakteristik di ' . $data_survei['nama_organisasi'] . ' dan diambil jumlah minimal responden yang telah ditetapkan. Peran responden ialah memberikan tanggapan dan informasi terkait data yang dibutuhkan oleh peneliti, serta memberikan masukan kepada peneliti, baik secara langsung maupun tidak langsung.', array('name' => 'Arial', 'size' => 11), array('keepNext' => true, 'indentation' => array('firstLine' => 500), 'align' => 'both'));
        // $section->addText('Secara umum responden dibagi dalam karakteristik ' . $arr_profil_responden . '. Secara rinci dapat dilihat pada pie chart dan tabel dibawah ini.', array('name' => 'Arial', 'size' => 11), array('keepNext' => true, 'indentation' => array('firstLine' => 500), 'align' => 'both'));

        // $section->addTextBreak();

        if ($profil_responden->num_rows() > 0) {
            $no_p = 1;
            $no_pg = 1;
            foreach ($profil_responden->result() as $get) {

                $kategori_profil_responden = $this->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$get->nama_alias && is_submit = 1) AS perolehan, ROUND((((SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$get->nama_alias && is_submit = 1) / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1)) * 100), 2) AS persentase
                FROM kategori_profil_responden_$table_identity
                WHERE id_profil_responden = $get->id");

                $jumlah = [];
                $nama_kelompok = [];
                $jumlah_persentase = [];
                foreach ($kategori_profil_responden->result() as $kpr) {
                    $jumlah[] = $kpr->perolehan;
                    $nama_kelompok[] = str_replace(' ', '+', $kpr->nama_kategori_profil_responden) . '+=+' . $kpr->persentase . '%';  //'%27' . str_replace(' ', '+', $kpr->nama_kategori_profil_responden) . '%27';
                    $jumlah_persentase[] = $kpr->persentase;
                }
                $total_rekap_responden = implode(",", $jumlah);
                $kelompok_rekap_responden = implode("|", $nama_kelompok);
                $persentase_kelompok = implode(",", $jumlah_persentase);
                // var_dump($persentase_kelompok);

                $kategori_profil_responden2 = $this->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$get->nama_alias && is_submit = 1) AS perolehan, ROUND((((SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$get->nama_alias && is_submit = 1) / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1)) * 100), 2) AS persentase
                FROM kategori_profil_responden_$table_identity
                WHERE id_profil_responden = $get->id ORDER BY id DESC");

                $nama_kelompok2 = [];
                foreach ($kategori_profil_responden2->result() as $kpr) {
                    $nama_kelompok2[] = str_replace(' ', '+', $kpr->nama_kategori_profil_responden) . '+=+' . $kpr->persentase . '%';  //'%27' . str_replace(' ', '+', $kpr->nama_kategori_profil_responden) . '%27';
                }
                $kelompok_rekap_responden2 = implode("|", $nama_kelompok2);

                $section->addText('2.'.$no_p++.'. '.$get->nama_profil_responden, array('bold' => true, 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START, 'spaceAfter' => 100));

                $section->addTextBreak(1);


                // if ($kategori_profil_responden->num_rows() < 10) {
                //     $section->addImage('https://image-charts.com/chart?chd=t:' . $persentase_kelompok . '&chdlp=b&chdl=' . $kelompok_rekap_responden . '&chf=ps0-0%2Clg%2C45%2Cfc3dd6%2C0.2%2Cfc3d3d7C%2C1%7Cps0-1%2Clg%2C45%2C2b4fc4%2C0.2%2C32c9c47C%2C1%7Cps0-2%2Clg%2C45%2CEA469E%2C0.2%2C03A9F47C%2C1%7Cps0-3%2Clg%2C45%2Cfacc00%2C0.2%2Cffca477C%2C1%7Cps0-4%2Clg%2C45%2Cf2fa05%2C0.2%2C2fa36f7C%2C1%7Cps0-4%2Clg%2C45%2C098d9c%2C0.2%2C840ccf7C%2C1&chs=500x200&cht=pc&chxt=x%2Cy', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                // } else {
                if ($kategori_profil_responden->num_rows() > 20) {
                    $htmlchart = '<img src="https://image-charts.com/chart?chbh=20&chbr=10&chd=t:' . $persentase_kelompok . '&chs=600x300&cht=bhs&chxr=1,0,100&chxt=y,x&chxl=0%3A|' . $kelompok_rekap_responden2 . '&chco=ff9f40"/>';
                    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlchart, false, false);
                } else {
                    $section->addImage('https://image-charts.com/chart?chbh=20&chbr=10&chd=t:' . $persentase_kelompok . '&chs=600x300&cht=bhs&chxr=1,0,100&chxt=y,x&chxl=0%3A|' . $kelompok_rekap_responden2 . '&chco=ff9f40', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                }
                // }

                $section->addText('Gambar '.$no_pg++.'. Persentase Responden Berdasarkan '.$get->nama_profil_responden, array('size' => 11), $paragraphStyleName);



                // $section->addImage("https://quickchart.io/chart?width=500&height=500&bkg=white&c={%27type%27:%27outlabeledPie%27,%27data%27:{%27labels%27:[" . $kelompok_rekap_responden ."],%27datasets%27:[{%27backgroundColor%27:[%27rgb(255,55,132)%27,%27rgb(54,%20162,%20235)%27,%27rgb(75,192,192)%27,%27rgb(255,221,0)%27,%27rgb(247,120,37)%27,%27rgb(153,102,255)%27],%27data%27:[" . $total_rekap_responden . "]}]},%27options%27:{%27plugins%27:{%27legend%27:false,%27outlabels%27:{%27color%27:%27white%27,%27stretch%27:35,%27font%27:{%27resizable%27:true,%27minSize%27:12,%27maxSize%27:18}}}}}", array('width' => 300, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));



                
                $section->addTextBreak(1);

                $no_pl = 1;
                if($get->is_lainnya == 1) {
                    $lainnya = $get->nama_alias . '_lainnya';
                    $cek_lainnya = $this->db->query("SELECT *
                    FROM responden_$table_identity
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                    WHERE is_submit = 1 && responden_$table_identity.$lainnya != ''");

                    if($cek_lainnya->num_rows() > 0){

                        // $section->addText('Tabel '.$no_pl++.'. Persentase Responden pada '.$get->nama_profil_responden . ' Lainnya', array('size' => 11), $paragraphStyleName);

                        $fancyTableStyleName = 'Profil Responden Lainnya ' . $no_p;
                        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
                        $fancyTableCellStyle = array('valign' => 'center');
                        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                        $table = $section->addTable($fancyTableStyleName);
                        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                        $table->addRow();
                        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                        $table->addCell(5000, $fancyTableCellStyle)->addText($get->nama_profil_responden. ' Lainnya', $fancyTableFontStyle);

                        $no_pr2 = 1;
                        $profil_lainnya = $this->db->query("SELECT *
                                        FROM responden_$table_identity
                                        JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                                        WHERE is_submit = 1");

                        foreach ($profil_lainnya->result() as $value) {
                            if($value->$lainnya != ''){
                                $table->addRow();
                                $table->addCell(150)->addText($no_pr2++, $cellTableFontStyle);
                                $table->addCell(5000)->addText($value->$lainnya, $cellTableFontStyle);
                            }
                        }
                    }

                }

                /*
                $fancyTableStyleName = 'Profil Responden ' . $no_p;
                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                $fancyTableCellStyle = array('valign' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                $table->addRow();
                $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                $table->addCell(4000, $fancyTableCellStyle)->addText('Kelompok', $fancyTableFontStyle);
                $table->addCell(1000, $fancyTableCellStyle)->addText('Jumlah', $fancyTableFontStyle);
                $table->addCell(1000, $fancyTableCellStyle)->addText('Persentase', $fancyTableFontStyle);


                $no_pr = 1;
                foreach ($kategori_profil_responden->result() as $key) {
                    if ($key->id_profil_responden == $get->id) {

                        $table->addRow();
                        $table->addCell(150)->addText($no_pr++, $cellTableFontStyle);
                        $table->addCell(4000)->addText($key->nama_kategori_profil_responden, $cellTableFontStyle);
                        $table->addCell(1000)->addText($key->perolehan, $cellTableFontStyle);
                        $table->addCell(1000)->addText(str_replace('.', ',', $key->persentase) . ' %', $cellTableFontStyle);
                    }
                }

                $section->addTextBreak(3);
                */
            }
        }

        $texthtmlbab23 = '<table>
        <tr>
            <td width="5%"><b>3.</b></td>
            <td width="95%"><b>Nilai Indeks Kepuasan Pelanggan</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Hasil Survei Kepuasan Pelanggan ' . $data_survei['nama_organisasi'] . ' mendapatkan nilai Indeks Kepuasan Pelanggan (IKP) sebesar <b>' . ROUND($nilai_tertimbang, 3) . '</b>, dengan mutu pelayanan <b>' . $index . '</b>. Nilai Indeks Kepuasan Pelanggan (IKP) tersebut didapat dari nilai rata-rata seluruh unsur pada tabel berikut..</p></td>
        </tr>
        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab23, false, false);
        $section->addTextBreak();

        $section->addText('Tabel 2. Nilai Unsur ' . $data_survei['nama_organisasi'], array('size' => 11), $paragraphStyleName);

        $this->db->select("IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan");
        $this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->group_by('id_sub');
        $nilai_per_unsur = $this->db->get();
        // var_dump($nilai_per_unsur->result());


        $grafik_nama_per_unsur = $this->db->query("SELECT GROUP_CONCAT(nomor_unsur ORDER BY unsur_pelayanan_$table_identity.id DESC SEPARATOR '|') AS nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_parent = 0")->row()->nomor_unsur;

        // $nama_per_unsur = [];
        $bobot_per_unsur = [];
        foreach ($nilai_per_unsur->result() as $value) {
            // $nama_per_unsur[] = $value->nomor_unsur; //. '. ' . $value->nama_unsur_pelayanan;
            $bobot_per_unsur[] = $value->nilai_per_unsur;
        }
        // $grafik_nama_per_unsur = implode("|", $nama_per_unsur);
        $grafik_bobot_per_unsur = implode(",", $bobot_per_unsur);

        $this->db->select("IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$table_identity.id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan");
        $this->db->from('jawaban_pertanyaan_unsur_' . $table_identity);
        $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id");
        $this->db->join("unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
        $this->db->join("survey_$table_identity", "jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden");
        $this->db->where("survey_$table_identity.is_submit = 1");
        $this->db->order_by('nomor_unsur', 'desc');
        $this->db->group_by('id_sub');
        $nilai_per_unsur2 = $this->db->get();

        $nama_per_unsur2 = [];
        foreach ($nilai_per_unsur2->result() as $value) {
            $nama_per_unsur2[] = str_replace(' ', '+', $value->nama_unsur_pelayanan) . '+=+' . ROUND($value->nilai_per_unsur, 3);
        }
        $grafik_nama_per_unsur = implode("|", $nama_per_unsur2);


        $fancyTableStyleName = 'Nilai SKM Per Unsur Pelayanan';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        $table->addRow();
        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
        $table->addCell(5000, $fancyTableCellStyle)->addText('Unsur', $fancyTableFontStyle);
        $table->addCell(1200, $fancyTableCellStyle)->addText('Indeks', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Kategori', $fancyTableFontStyle);

        $no = 1;
        foreach ($nilai_per_unsur->result() as $row) {

            $nilai_unsur = ROUND($row->nilai_per_unsur * $skala_likert, 10);
            foreach ($definisi_skala->result() as $obj) {
                if ($nilai_unsur <= $obj->range_bawah && $nilai_unsur >= $obj->range_atas) {
                    $ktg = $obj->kategori;
                }
            }
            if ($nilai_unsur <= 0) {
                $ktg = 'NULL';
            }

            $table->addRow();
            $table->addCell(150)->addText($no++, $cellTableFontStyle);
            $table->addCell(5000)->addText($row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan, $cellTableFontStyle);
            $table->addCell(1200)->addText(str_replace('.', ',', ROUND($row->nilai_per_unsur, 3)), $cellTableFontStyle);
            $table->addCell(2000)->addText($ktg, $cellTableFontStyle);
        }

        $table->addRow();
        $table->addCell(150)->addText('', $cellTableFontStyle);
        $table->addCell(5000)->addText('Nilai IKP', array('bold' => true), $cellTableFontStyle);
        $table->addCell(1200)->addText(str_replace('.', ',', ROUND($nilai_tertimbang, 3)), array('bold' => true), $cellTableFontStyle);
        $table->addCell(2000)->addText($index, array('bold' => true), $cellTableFontStyle);

        $table->addRow();
        $table->addCell(150)->addText('', $cellTableFontStyle);
        $table->addCell(5000)->addText('Nilai Konversi' , array('bold' => true), $cellTableFontStyle);
        $table->addCell(1200)->addText(str_replace('.', ',', ROUND($nilai_skm, 3)), array('bold' => true), $cellTableFontStyle);
        $table->addCell(2000)->addText($index, array('bold' => true), $cellTableFontStyle);

        $section->addTextBreak(1);

        $texthtmlbab23 = '<table>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Nilai unsur Survei Kepuasan Pelanggan pada ' . $data_survei['nama_organisasi'] . ' apabila diurutkan berdasarkan nilai tertinggi sampai terendah dapat dilihat pada gambar di bawah ini.</p></td>
        </tr>
        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab23, false, false);
        $section->addTextBreak();

        

        $section->addImage('https://image-charts.com/chart?chbh=20&chbr=10&chd=t:' . $grafik_bobot_per_unsur . '&chs=600x300&cht=bhs&chxr=1,0,5&chxt=y,x&chxl=0%3A|' . $grafik_nama_per_unsur . '&chco=ff9f40', array('width' => 450, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
        $section->addText('Gambar '.($no_pg).'. Grafik Unsur ' . $data_survei['nama_organisasi'], array('size' => 11), $paragraphStyleName);

        $section->addTextBreak(1);


        $texthtmlbab24 = '<table>
        <tr>
            <td width="5%"><b>4.</b></td>
            <td width="95%"><b>Pembahasan Unsur</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Unsur yang dipakai dalam Survei Kepuasan Pelanggan dapat dijadikan sebagai acuan untuk mengetahui kondisi Unit Pelayanan Publik pada ' . $data_survei['nama_organisasi'] . ' yang nantinya dijadikan suatu pedoman perbaikan kinerja. Pada pembahasan ini akan dijelaskan terkait persentase jawaban pada masing-masing unsur dalam Survei Kepuasan Pelanggan.</p></td>
        </tr>
        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab24, false, false);
        $section->addTextBreak();


        // $section->addPageBreak();










        // HALAMAN CHART UNSUR SKM
        // $section->addText('Chart Unsur SKM', array('bold' => true, 'size' => 18), $paragraphStyleName);

        // $section->addTextBreak();

        $this->db->select("*, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan");
        $this->db->from("unsur_pelayanan_$table_identity");
        $this->db->where(['id_parent' => 0]);
        $unsur_pelayanan = $this->db->get();

        $no_u = 1;
        $no_t = 3;
        $no_pgu = $no_pg+1;
        foreach ($unsur_pelayanan->result() as $value) {
            
            $section->addText('4.'.$no_u++ . '.  ' . $value->nomor_unsur .'. '. $value->nama_unsur_pelayanan, array('bold' => true, 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START, 'spaceAfter' => 100));

            $sub_unsur = $this->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => $value->id_unsur_pelayanan]);

            //JIKA MEMPUNYAI TURUNAN
            if ($sub_unsur->num_rows() > 0) {

                $this->db->select("(SELECT id FROM pertanyaan_unsur_pelayanan_$table_identity WHERE pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id) AS id_pertanyaan_unsur");
                $this->db->from("unsur_pelayanan_$table_identity");
                $this->db->where('id_parent', $value->id_unsur_pelayanan);
                $this->db->order_by('id', 'desc');
                $get_opsi = $this->db->get()->row();

                $this->db->select('nama_kategori_unsur_pelayanan');
                $this->db->from("kategori_unsur_pelayanan_$table_identity");
                $this->db->where('id_pertanyaan_unsur', $get_opsi->id_pertanyaan_unsur);
                $get_data_opsi = $this->db->get()->result_array();

                $rel_data = $this->db->query(" SELECT *, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur_pelayanan,
                    ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur =
                    pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 1) AS jumlah_1,
                    ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur =
                    pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 2) AS jumlah_2,
                    ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur =
                    pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 3) AS jumlah_3,
                    ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur =
                    pertanyaan_unsur_pelayanan_$table_identity.id AND skor_jawaban = 4) AS jumlah_4,

                    ( SELECT ROUND(COUNT(skor_jawaban) / ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) * 100, 2) FROM
                    jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND
                    skor_jawaban = 1 ) AS persentase_1,
                    ( SELECT ROUND(COUNT(skor_jawaban) / ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 &&
                    id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) * 100, 2) FROM
                    jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND
                    skor_jawaban = 2 ) AS persentase_2,
                    ( SELECT ROUND(COUNT(skor_jawaban) / ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 &&
                    id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) * 100, 2) FROM
                    jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND
                    skor_jawaban = 3 ) AS persentase_3,
                    ( SELECT ROUND(COUNT(skor_jawaban) / ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 &&
                    id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) * 100, 2) FROM
                    jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND
                    skor_jawaban = 4 ) AS persentase_4,

                    ( SELECT ROUND(COUNT(skor_jawaban) / ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 &&
                    id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) * 100, 2) FROM
                    jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id AND
                    skor_jawaban = 5 ) AS persentase_5,

                    ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur =
                    pertanyaan_unsur_pelayanan_$table_identity.id) AS jumlah_pengisi,
                    ( SELECT AVG(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
                    WHERE is_submit = 1 && id_pertanyaan_unsur =
                    pertanyaan_unsur_pelayanan_$table_identity.id) AS rata_rata

                    FROM unsur_pelayanan_$table_identity
                    JOIN pertanyaan_unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan =
                    unsur_pelayanan_$table_identity.id
                    WHERE unsur_pelayanan_$table_identity.id_parent = $value->id_unsur_pelayanan
                    ");



                $ij = 0;
                $jumlah_persentase_1 = 0;
                $jumlah_persentase_2 = 0;
                $jumlah_persentase_3 = 0;
                $jumlah_persentase_4 = 0;
                $jumlah_persentase_5 = 0;
                foreach ($rel_data->result() as $elements) {
                    $jumlah_persentase_1 += $elements->persentase_1;
                    $jumlah_persentase_2 += $elements->persentase_2;
                    $jumlah_persentase_3 += $elements->persentase_3;
                    $jumlah_persentase_4 += $elements->persentase_4;
                    $jumlah_persentase_5 += $elements->persentase_5;
                    $ij++;

                    $f_persentase_1 = round(($jumlah_persentase_1 / $ij), 2);
                    $f_persentase_2 = round(($jumlah_persentase_2 / $ij), 2);
                    $f_persentase_3 = round(($jumlah_persentase_3 / $ij), 2);
                    $f_persentase_4 = round(($jumlah_persentase_4 / $ij), 2);
                    $f_persentase_5 = round(($jumlah_persentase_5 / $ij), 2);
                }

                $get_data_opsi_1 = str_replace(' ', '%20', $get_data_opsi[0]['nama_kategori_unsur_pelayanan']);
                $get_data_opsi_2 = str_replace(' ', '%20', $get_data_opsi[1]['nama_kategori_unsur_pelayanan']);
                $get_data_opsi_3 = str_replace(' ', '%20', $get_data_opsi[2]['nama_kategori_unsur_pelayanan']);
                $get_data_opsi_4 = str_replace(' ', '%20', $get_data_opsi[3]['nama_kategori_unsur_pelayanan']);


                if ($manage_survey->skala_likert == 5) {
                    $get_data_opsi_5 = str_replace(' ', '%20', $get_data_opsi[4]['nama_kategori_unsur_pelayanan']);

                    $series = [$f_persentase_1, $f_persentase_2, $f_persentase_3, $f_persentase_4, $f_persentase_5];
                    $labels = [$get_data_opsi_1 . '%20=%20' . $f_persentase_1 . '%', $get_data_opsi_2 . '%20=%20' . $f_persentase_2 . '%',  $get_data_opsi_3 . '%20=%20' . $f_persentase_3 . '%', $get_data_opsi_4 . '%20=%20' . $f_persentase_4 . '%', $get_data_opsi_5 . '%20=%20' . $f_persentase_5 . '%'];

                    $identitas = [$f_persentase_1 . '%', $f_persentase_2 . '%', $f_persentase_3 . '%', $f_persentase_4 . '%', $f_persentase_5 . '%'];
                } else {

                    $series = [$f_persentase_1, $f_persentase_2, $f_persentase_3, $f_persentase_4];
                    $labels = [$get_data_opsi_1 . '%20=%20' . $f_persentase_1 . '%', $get_data_opsi_2 . '%20=%20' . $f_persentase_2 . '%',  $get_data_opsi_3 . '%20=%20' . $f_persentase_3 . '%', $get_data_opsi_4 . '%20=%20' . $f_persentase_4 . '%'];
                    $identitas = [$f_persentase_1 . '%', $f_persentase_2 . '%', $f_persentase_3 . '%', $f_persentase_4 . '%'];
                }



                $get_series = implode(",", $series);
                $get_nama_opsi = implode("|", $labels);
                $get_identitas = implode("|", $identitas);
                // var_dump($get_series);



                // JIKA UNSUR MEMILIKI TURUNAN
                // $section->addImage('https://image-charts.com/chart?chd=t:' . $get_series . '&chdlp=b&chdl=' . $get_nama_opsi . '&chf=ps0-0%2Clg%2C45%2Cfc3dd6%2C0.2%2Cfc3d3d7C%2C1%7Cps0-1%2Clg%2C45%2C2b4fc4%2C0.2%2C32c9c47C%2C1%7Cps0-2%2Clg%2C45%2CEA469E%2C0.2%2C03A9F47C%2C1%7Cps0-3%2Clg%2C45%2Cfacc00%2C0.2%2Cffca477C%2C1%7Cps0-4%2Clg%2C45%2Cf2fa05%2C0.2%2C2fa36f7C%2C1%7Cps0-4%2Clg%2C45%2C098d9c%2C0.2%2C840ccf7C%2C1&chl=' . $get_identitas . '&chs=500x200&cht=pc&chxt=x%2Cy', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

                $total_rekap_responden = implode(",", $series);
                $kelompok_rekap_responden = implode("|", $labels);
                $section->addImage('https://image-charts.com/chart?chbh=20&chbr=10&chd=t:' . $total_rekap_responden . '&chs=600x300&cht=bhs&chxr=1,0,100&chxt=y,x&chxl=0%3A|' . $kelompok_rekap_responden . '&chco=ff9f40', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                // $section->addImage('https://image-charts.com/chart?chd=t:' . $get_series . '&chdlp=b&chdl=' . $get_nama_opsi . '&chf=ps0-0%2Clg%2C45%2Cfc3dd6%2C0.2%2Cfc3d3d7C%2C1%7Cps0-1%2Clg%2C45%2C2b4fc4%2C0.2%2C32c9c47C%2C1%7Cps0-2%2Clg%2C45%2CEA469E%2C0.2%2C03A9F47C%2C1%7Cps0-3%2Clg%2C45%2Cfacc00%2C0.2%2Cffca477C%2C1%7Cps0-4%2Clg%2C45%2Cf2fa05%2C0.2%2C2fa36f7C%2C1%7Cps0-4%2Clg%2C45%2C098d9c%2C0.2%2C840ccf7C%2C1&chs=500x200&cht=pc&chxt=x%2Cy', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                $section->addText('Gambar '.$no_pgu++.'. Grafik Unsur ' . $value->nama_unsur_pelayanan, array('size' => 11), $paragraphStyleName);
                $section->addTextBreak(1);

                $section->addText('Tabel '.$no_t++.'. Persentase Responden pada Unsur ' . $value->nama_unsur_pelayanan, array('size' => 11), $paragraphStyleName);
                $fancyTableStyleName = 'Chart Unsur';
                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 100, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                $fancyTableCellStyle = array('valign' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                $table->addRow();
                $table->addCell(2000, $fancyTableCellStyle)->addText('Unsur', $fancyTableFontStyle);
                $table->addCell(800, $fancyTableCellStyle)->addText($get_data_opsi[0]['nama_kategori_unsur_pelayanan'], $fancyTableFontStyle);
                $table->addCell(800, $fancyTableCellStyle)->addText($get_data_opsi[1]['nama_kategori_unsur_pelayanan'], $fancyTableFontStyle);
                $table->addCell(800, $fancyTableCellStyle)->addText($get_data_opsi[2]['nama_kategori_unsur_pelayanan'], $fancyTableFontStyle);
                $table->addCell(800, $fancyTableCellStyle)->addText($get_data_opsi[3]['nama_kategori_unsur_pelayanan'], $fancyTableFontStyle);

                if ($manage_survey->skala_likert == 5) {
                    $table->addCell(800, $fancyTableCellStyle)->addText($get_data_opsi[4]['nama_kategori_unsur_pelayanan'], $fancyTableFontStyle);
                }

                $table->addCell(525, $fancyTableCellStyle)->addText('Indeks', $fancyTableFontStyle);
                $table->addCell(525, $fancyTableCellStyle)->addText('Predikat', $fancyTableFontStyle);


                $no = 0;
                $jum_persentase_1 = 0;
                $jum_persentase_2 = 0;
                $jum_persentase_3 = 0;
                $jum_persentase_4 = 0;
                $jum_persentase_5 = 0;
                $jum_indeks = 0;
                $nama_sub_unsur = [];

                foreach ($rel_data->result() as $elements) {

                    foreach ($definisi_skala->result() as $obj) {
                        if (($elements->rata_rata * $skala_likert) <= $obj->range_bawah && ($elements->rata_rata * $skala_likert) >= $obj->range_atas) {
                            $predikat = $obj->kategori;
                        }
                    }
                    if (($elements->rata_rata * $skala_likert) <= 0) {
                        $predikat = 'NULL';
                    }

                    $table->addRow();
                    $table->addCell(2000)->addText($elements->nama_unsur_pelayanan, $cellTableFontStyle);
                    $table->addCell(800)->addText(str_replace('.', ',', round($elements->persentase_1, 2)) . ' %', $cellTableFontStyle);
                    $table->addCell(800)->addText(str_replace('.', ',', round($elements->persentase_2, 2)) . ' %', $cellTableFontStyle);
                    $table->addCell(800)->addText(str_replace('.', ',', round($elements->persentase_3, 2)) . ' %', $cellTableFontStyle);
                    $table->addCell(800)->addText(str_replace('.', ',', round($elements->persentase_4, 2)) . ' %', $cellTableFontStyle);

                    if ($manage_survey->skala_likert == 5) {
                        $table->addCell(800)->addText(str_replace('.', ',', round($elements->persentase_5, 2)) . ' %', $cellTableFontStyle);
                    }

                    $table->addCell(525)->addText(str_replace('.', ',', round($elements->rata_rata, 2)), $cellTableFontStyle);
                    $table->addCell(525)->addText($predikat, $cellTableFontStyle);


                    $nama_sub_unsur[] = $elements->nama_unsur_pelayanan;
                    $jum_persentase_1 += $elements->persentase_1;
                    $jum_persentase_2 += $elements->persentase_2;
                    $jum_persentase_3 += $elements->persentase_3;
                    $jum_persentase_4 += $elements->persentase_4;
                    $jum_persentase_5 += $elements->persentase_5;
                    $jum_indeks += $elements->rata_rata;
                    $no++;

                    $f_indeks = round(($jum_indeks / $no), 2);


                    foreach ($definisi_skala->result() as $obj) {
                        if (($f_indeks * $skala_likert) <= $obj->range_bawah && ($f_indeks * $skala_likert) >= $obj->range_atas) {
                            $h_indeks = $obj->kategori;
                        }
                    }
                    if (($f_indeks * $skala_likert) <= 0) {
                        $h_indeks = 'NULL';
                    }

                    // if (($f_indeks * 25) <= 100 &&  ($f_indeks * 25) >= 88.31) {
                    //     $h_indeks = 'Sangat Baik';
                    // } elseif (($f_indeks * 25) <= 88.40 &&  ($f_indeks * 25) >= 76.61) {
                    //     $h_indeks = 'Baik';
                    // } elseif (($f_indeks * 25) <= 76.60 &&  ($f_indeks * 25) >= 65) {
                    //     $h_indeks = 'Kurang Baik';
                    // } elseif (($f_indeks * 25) <= 64.99 &&  ($f_indeks * 25) >= 25) {
                    //     $h_indeks = 'Tidak Baik';
                    // } else {
                    //     $h_indeks = 'NULL';
                    // }
                }

                $table->addRow();
                $table->addCell(2000)->addText('Rata-rata', $cellTableFontStyle);
                $table->addCell(800)->addText(str_replace('.', ',', round(($jum_persentase_1 / $no), 2)) . ' %', $cellTableFontStyle);
                $table->addCell(800)->addText(str_replace('.', ',', round(($jum_persentase_2 / $no), 2)) . ' %', $cellTableFontStyle);
                $table->addCell(800)->addText(str_replace('.', ',', round(($jum_persentase_3 / $no), 2)) . ' %', $cellTableFontStyle);
                $table->addCell(800)->addText(str_replace('.', ',', round(($jum_persentase_4 / $no), 2)) . ' %', $cellTableFontStyle);

                if ($manage_survey->skala_likert == 5) {
                    $table->addCell(800)->addText(str_replace('.', ',', round(($jum_persentase_5 / $no), 2)) . ' %', $cellTableFontStyle);
                }

                $table->addCell(525)->addText(str_replace('.', ',', $f_indeks), $cellTableFontStyle);
                $table->addCell(525)->addText($h_indeks, $cellTableFontStyle);

                $section->addTextBreak(1);



                //TURUNAN
                $this->db->select("*, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur_pelayanan");
                $this->db->from("unsur_pelayanan_$table_identity");
                $this->db->join("pertanyaan_unsur_pelayanan_$table_identity", "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");
                $this->db->where(['id_parent' => $value->id_unsur_pelayanan]);
                $unsur_pelayanan_a = $this->db->get();

                foreach ($unsur_pelayanan_a->result() as $element_a) {

                    $this->db->select("*, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur_pelayanan");
                    $this->db->from("unsur_pelayanan_$table_identity");
                    $this->db->join(
                        "pertanyaan_unsur_pelayanan_$table_identity",
                        "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id"
                    );
                    $this->db->where(["unsur_pelayanan_$table_identity.id" => $element_a->id_unsur_pelayanan]);
                    $unsur_pelayanan_aa = $this->db->get()->row();


                    $id_pertanyaan_unsur_pelayanan = $unsur_pelayanan_aa->id_pertanyaan_unsur_pelayanan;
                    $persentase_detail = $this->db->query(" SELECT id AS id_kup, nama_kategori_unsur_pelayanan,
                        ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur =
                        $id_pertanyaan_unsur_pelayanan AND skor_jawaban = nomor_kategori_unsur_pelayanan) AS jumlah,
                        ( SELECT ROUND(( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur =
                        $id_pertanyaan_unsur_pelayanan AND skor_jawaban = nomor_kategori_unsur_pelayanan) / ( SELECT COUNT(*) FROM
                        jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan ) * 100,2) ) AS
                        persentase
                        FROM kategori_unsur_pelayanan_$table_identity
                        WHERE id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan
                        ");

                    $section->addText('4.'.$element_a->nomor_unsur . '. ' . $element_a->nama_unsur_pelayanan, array('bold' => true, 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START, 'spaceAfter' => 100));

                    $fancyTableStyleName = 'Chart Unsur 1';
                    $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                    $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                    $fancyTableCellStyle = array('valign' => 'center');
                    $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                    $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                    $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                    $table = $section->addTable($fancyTableStyleName);
                    $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                    $table->addRow();
                    $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                    $table->addCell(4000, $fancyTableCellStyle)->addText('Kelompok', $fancyTableFontStyle);
                    $table->addCell(1000, $fancyTableCellStyle)->addText('Jumlah', $fancyTableFontStyle);
                    $table->addCell(1000, $fancyTableCellStyle)->addText('Persentase', $fancyTableFontStyle);


                    $no = 1;
                    $t_jum = 0;
                    $t_persen = 0;

                    foreach ($persentase_detail->result() as $val_p) {

                        $table->addRow();
                        $table->addCell(150)->addText($no++, $cellTableFontStyle);
                        $table->addCell(4000)->addText($val_p->nama_kategori_unsur_pelayanan, $cellTableFontStyle);
                        $table->addCell(1000)->addText($val_p->jumlah, $cellTableFontStyle);
                        $table->addCell(1000)->addText(str_replace('.', ',', $val_p->persentase) . ' %', $cellTableFontStyle);

                        $t_jum += $val_p->jumlah;
                        $t_persen += $val_p->persentase;
                    }
                    $table->addRow();
                    $table->addCell(150)->addText('', $cellTableFontStyle);
                    $table->addCell(4000)->addText('TOTAL', array('bold' => true), $cellTableFontStyle);
                    $table->addCell(1000)->addText($t_jum, array('bold' => true), $cellTableFontStyle);
                    $table->addCell(1000)->addText(str_replace('.', ',', $t_persen) . ' %', array('bold' => true), $cellTableFontStyle);
                    $section->addTextBreak(1);

                    $alasan = $this->db->query("SELECT *
                    FROM jawaban_pertanyaan_unsur_$table_identity
                    JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
                    JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
                    WHERE is_submit = 1 && id_unsur_pelayanan = $id_pertanyaan_unsur_pelayanan && alasan_pilih_jawaban != '' && jawaban_pertanyaan_unsur_$table_identity.is_active = 1
                    ");


                    if($alasan->num_rows() > 0){
                        $val_alasan = [];
                        foreach($alasan->result() as $val){
                            $val_alasan[] = '<li>' . $val->alasan_pilih_jawaban . '</li>';
                        }
                        $data_alasan = '
                            <div align="left">Alasan yang diberikan responden pada unsur ' . $element_a->nama_unsur_pelayanan . ':</div>
                            <ul>' . implode(" ", $val_alasan).'</ul>';
                    } else {
                        $data_alasan = '';
                    }

                    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $data_alasan, false, false);
                    $section->addTextBreak();

                    // HALAMAN REKAPITULASI PERTANYAAN TAMBAHAN
                    if (in_array(2, $atribut_pertanyaan)) {
                        // $section->addText('Rekapitulasi Pertanyaan Tambahan', array('bold' => true, 'size' => 18), $paragraphStyleName);
                        // $section->addTextBreak(2);

                        $pertanyaan_tambahan = $this->db->query("SELECT *, (SELECT DISTINCT dengan_isian_lainnya FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS is_lainnya,
                    (SELECT COUNT(*) FROM responden_$table_identity
                    JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
                    jawaban_pertanyaan_terbuka_$table_identity.id_responden
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                    WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
                    perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
                    'Lainnya') AS perolehan,
                    (((SELECT COUNT(*) FROM responden_$table_identity
                    JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
                    jawaban_pertanyaan_terbuka_$table_identity.id_responden
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                    WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
                    perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
                    'Lainnya') / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit =
                    1)) * 100) AS persentase

                    FROM pertanyaan_terbuka_$table_identity
                    JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka 
                    WHERE id_unsur_pelayanan = $value->id");

                        $jawaban_ganda = $this->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity
                    JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
                    jawaban_pertanyaan_terbuka_$table_identity.id_responden
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                    WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
                    perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
                    isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,
                    (((SELECT COUNT(*) FROM responden_$table_identity
                    JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
                    jawaban_pertanyaan_terbuka_$table_identity.id_responden
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                    WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
                    perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
                    isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit =
                    1)) * 100) AS persentase
                    FROM isi_pertanyaan_ganda_$table_identity
                    JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka
                    = perincian_pertanyaan_terbuka_$table_identity.id
                    WHERE perincian_pertanyaan_terbuka_$table_identity.id_jenis_pilihan_jawaban = 1");

                        $jawaban_isian = $this->db->query("SELECT *
                    FROM jawaban_pertanyaan_terbuka_$table_identity
                    JOIN pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                    JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                    JOIN responden_$table_identity ON jawaban_pertanyaan_terbuka_$table_identity.id_responden = responden_$table_identity.id
                    JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
                    WHERE id_jenis_pilihan_jawaban = 2 && survey_$table_identity.is_submit = 1");


                        foreach ($pertanyaan_tambahan->result() as $row) {
                            $table = $section->addTable('Judul Pertanyaan Tambahan');
                            $table->addRow();
                            $table->addCell(500)->addText($row->nomor_pertanyaan_terbuka . '.', array('name' => 'Arial', 'size' => 11, 'bold' => true, 'valign' => 'center'));
                            $table->addCell(9000)->addText(strip_tags($row->nama_pertanyaan_terbuka), array('name' => 'Arial', 'size' => 11, 'bold' => true, 'valign' => 'center'));

                            if ($row->id_jenis_pilihan_jawaban == 1) {

                                $fancyTableStyleName = 'Pertanyaan Tambahan 1';
                                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                                $fancyTableCellStyle = array('valign' => 'center');
                                $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                                $table = $section->addTable($fancyTableStyleName);
                                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                                $table->addRow();
                                $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                                $table->addCell(3000, $fancyTableCellStyle)->addText('Kelompok', $fancyTableFontStyle);
                                $table->addCell(2500, $fancyTableCellStyle)->addText('Jumlah', $fancyTableFontStyle);
                                $table->addCell(2500, $fancyTableCellStyle)->addText('Persentase', $fancyTableFontStyle);

                                $nt = 1;
                                foreach ($jawaban_ganda->result() as $value) {
                                    if ($value->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {
                                        $table->addRow();
                                        $table->addCell(150)->addText($nt++, $cellTableFontStyle);
                                        $table->addCell(3000)->addText($value->pertanyaan_ganda, $cellTableFontStyle);
                                        $table->addCell(2500)->addText($value->perolehan, $cellTableFontStyle);
                                        $table->addCell(2500)->addText(str_replace('.', ',', ROUND($value->persentase, 2)) . '%', $cellTableFontStyle);
                                    }
                                }
                                if ($row->is_lainnya == 1) {
                                    $table->addRow();
                                    $table->addCell(150)->addText($nt++, $cellTableFontStyle);
                                    $table->addCell(3000)->addText('Lainnya', $cellTableFontStyle);
                                    $table->addCell(2500)->addText($row->perolehan, $cellTableFontStyle);
                                    $table->addCell(2500)->addText(str_replace('.', ',', ROUND($row->persentase, 2)) . '%', $cellTableFontStyle);
                                }
                            } else {
                                $fancyTableStyleName = 'Pertanyaan Tambahan 2';
                                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                                $fancyTableCellStyle = array('valign' => 'center');
                                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                                $table = $section->addTable($fancyTableStyleName);
                                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                                $table->addRow();
                                $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                                $table->addCell(8200, $fancyTableCellStyle)->addText('Jawaban', $fancyTableFontStyle);


                                $i = 1;
                                foreach ($jawaban_isian->result() as $get) {
                                    if ($get->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {
                                        $table->addRow();
                                        $table->addCell(150)->addText($i++, $cellTableFontStyle);
                                        $table->addCell(8200)->addText($get->jawaban, $cellTableFontStyle);
                                    }
                                }
                            }
                            $section->addTextBreak();
                        }

                        $section->addTextBreak();
                    }



                }
            } else {

                //JIKA TIDAK MEMPUNYAI TURUNAN
                $this->db->select("*, unsur_pelayanan_$table_identity.id AS id_unsur_pelayanan, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur_pelayanan");
                $this->db->from("unsur_pelayanan_$table_identity");
                $this->db->join(
                    "pertanyaan_unsur_pelayanan_$table_identity",
                    "pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id"
                );
                $this->db->where(["unsur_pelayanan_$table_identity.id" => $value->id_unsur_pelayanan]);
                $unsur_pelayanan_b = $this->db->get()->row();

                $id_pertanyaan_unsur_pelayanan = $unsur_pelayanan_b->id_pertanyaan_unsur_pelayanan;
                $persentase_detail = $this->db->query(" SELECT nama_kategori_unsur_pelayanan,
                    ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur =
                    $id_pertanyaan_unsur_pelayanan AND skor_jawaban = nomor_kategori_unsur_pelayanan) AS jumlah,
                    ( SELECT ROUND(( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur =
                    $id_pertanyaan_unsur_pelayanan AND skor_jawaban = nomor_kategori_unsur_pelayanan) / ( SELECT COUNT(*) FROM
                    jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan ) * 100,2) ) AS
                    persentase
                    FROM kategori_unsur_pelayanan_$table_identity
                    WHERE id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan
                    ")->result_array();

                $nama_kategori_unsur_pelayanan = [];
                $persentase = [];
                $nama_identitas = [];
                foreach ($persentase_detail as $element) {
                    $nama_kategori_unsur_pelayanan[] = str_replace(' ', '%20', $element['nama_kategori_unsur_pelayanan']) . '%20=%20' . $element['persentase'] . '%';
                    $persentase[] = $element['persentase'];
                    $nama_identitas[] = $element['persentase'] . '%';
                }
                $get_persentase = implode(",", $persentase);
                $get_nama_kategori = implode("|", $nama_kategori_unsur_pelayanan);
                $get_nama_identitas = implode("|", $nama_identitas);

                //JIKA UNSUR TIDAK MEMILIKI TURUNAN
                // $section->addImage('https://image-charts.com/chart?chd=t:' . $get_persentase . '&chdlp=b&chdl=' . $get_nama_kategori . '&chf=ps0-0%2Clg%2C45%2Cfc3dd6%2C0.2%2Cfc3d3d7C%2C1%7Cps0-1%2Clg%2C45%2C2b4fc4%2C0.2%2C32c9c47C%2C1%7Cps0-2%2Clg%2C45%2CEA469E%2C0.2%2C03A9F47C%2C1%7Cps0-3%2Clg%2C45%2Cfacc00%2C0.2%2Cffca477C%2C1%7Cps0-4%2Clg%2C45%2Cf2fa05%2C0.2%2C2fa36f7C%2C1%7Cps0-4%2Clg%2C45%2C098d9c%2C0.2%2C840ccf7C%2C1&chl=' . $get_nama_identitas . '&chs=500x200&cht=pc&chxt=x%2Cy', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

                $persentase_detail2 = $this->db->query(" SELECT nama_kategori_unsur_pelayanan,
                ( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur =
                $id_pertanyaan_unsur_pelayanan AND skor_jawaban = nomor_kategori_unsur_pelayanan) AS jumlah,
                ( SELECT ROUND(( SELECT COUNT(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur =
                $id_pertanyaan_unsur_pelayanan AND skor_jawaban = nomor_kategori_unsur_pelayanan) / ( SELECT COUNT(*) FROM
                jawaban_pertanyaan_unsur_$table_identity JOIN responden_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id
JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id
WHERE is_submit = 1 && id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan ) * 100,2) ) AS
                persentase
                FROM kategori_unsur_pelayanan_$table_identity
                WHERE id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan ORDER BY ID DESC
                ")->result_array();

                $nama_kategori_unsur_pelayanan2 = [];
                foreach ($persentase_detail2 as $element) {
                    $nama_kategori_unsur_pelayanan2[] = str_replace(' ', '%20', $element['nama_kategori_unsur_pelayanan']) . '%20=%20' . $element['persentase'] . '%';
                }

                $total_rekap_responden = implode(",", $persentase);
                $kelompok_rekap_responden = implode("|", $nama_kategori_unsur_pelayanan2);
                $section->addImage('https://image-charts.com/chart?chbh=20&chbr=10&chd=t:' . $total_rekap_responden . '&chs=600x300&cht=bhs&chxr=1,0,100&chxt=y,x&chxl=0%3A|' . $kelompok_rekap_responden . '&chco=ff9f40', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                // $section->addImage('https://image-charts.com/chart?chd=t:' . $get_persentase . '&chdlp=b&chdl=' . $get_nama_kategori . '&chf=ps0-0%2Clg%2C45%2Cfc3dd6%2C0.2%2Cfc3d3d7C%2C1%7Cps0-1%2Clg%2C45%2C2b4fc4%2C0.2%2C32c9c47C%2C1%7Cps0-2%2Clg%2C45%2CEA469E%2C0.2%2C03A9F47C%2C1%7Cps0-3%2Clg%2C45%2Cfacc00%2C0.2%2Cffca477C%2C1%7Cps0-4%2Clg%2C45%2Cf2fa05%2C0.2%2C2fa36f7C%2C1%7Cps0-4%2Clg%2C45%2C098d9c%2C0.2%2C840ccf7C%2C1&chs=500x200&cht=pc&chxt=x%2Cy', array('width' => 350, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                $section->addText('Gambar '.$no_pgu++.'. Grafik Unsur ' . $value->nama_unsur_pelayanan, array('size' => 11), $paragraphStyleName);
                $section->addTextBreak(1);


                $section->addText('Tabel '.$no_t++.'. Persentase Responden pada Unsur ' . $value->nama_unsur_pelayanan, array('size' => 11), $paragraphStyleName);
                $fancyTableStyleName = 'Chart Unsur tidak memiliki turunan';
                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                $fancyTableCellStyle = array('valign' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                $table->addRow();
                $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                $table->addCell(4000, $fancyTableCellStyle)->addText('Kategori', $fancyTableFontStyle);
                $table->addCell(1000, $fancyTableCellStyle)->addText('Jumlah', $fancyTableFontStyle);
                $table->addCell(1000, $fancyTableCellStyle)->addText('Persentase', $fancyTableFontStyle);

                $t = 1;
                $t_jum = 0;
                $t_persen = 0;
                foreach ($persentase_detail as $element) {
                    $table->addRow();
                    $table->addCell(150)->addText($t++, $cellTableFontStyle);
                    $table->addCell(4000)->addText($element['nama_kategori_unsur_pelayanan'], $cellTableFontStyle);
                    $table->addCell(1000)->addText($element['jumlah'], $cellTableFontStyle);
                    $table->addCell(1000)->addText(str_replace('.', ',', $element['persentase']) . ' %', $cellTableFontStyle);

                    $t_jum += $element['jumlah'];
                    $t_persen += $element['persentase'];
                }
                $table->addRow();
                $table->addCell(150)->addText('', $cellTableFontStyle);
                $table->addCell(4000)->addText('TOTAL', array('bold' => true), $cellTableFontStyle);
                $table->addCell(1000)->addText($t_jum, array('bold' => true), $cellTableFontStyle);
                $table->addCell(1000)->addText(str_replace('.', ',', $t_persen) . ' %', array('bold' => true), $cellTableFontStyle);
            };
            $section->addTextBreak(1);


            $alasan = $this->db->query("SELECT * 
            FROM jawaban_pertanyaan_unsur_$table_identity
            JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
            JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE is_submit = 1 && id_pertanyaan_unsur = $id_pertanyaan_unsur_pelayanan && alasan_pilih_jawaban != '' && jawaban_pertanyaan_unsur_$table_identity.is_active = 1
            ");

            if($alasan->num_rows() > 0){
                $val_alasan = [];
                foreach($alasan->result() as $get){
                    $val_alasan[] = '<li>' . $get->alasan_pilih_jawaban . '</li>';
                }
                $data_alasan = '
                    <div align="left">Alasan yang diberikan responden pada unsur ' . $value->nama_unsur_pelayanan . ':</div>
                    <ul>' . implode(" ", $val_alasan).'</ul>';
            } else {
                $data_alasan = '';
            }
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $data_alasan, false, false);
            $section->addTextBreak();

            // HALAMAN REKAPITULASI PERTANYAAN TAMBAHAN
            if (in_array(2, $atribut_pertanyaan)) {
                // $section->addText('Rekapitulasi Pertanyaan Tambahan', array('bold' => true, 'size' => 18), $paragraphStyleName);
                // $section->addTextBreak(2);

                $pertanyaan_tambahan = $this->db->query("SELECT *, (SELECT DISTINCT dengan_isian_lainnya FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS is_lainnya,
            (SELECT COUNT(*) FROM responden_$table_identity
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
            jawaban_pertanyaan_terbuka_$table_identity.id_responden
            JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
            WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
            perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
            'Lainnya') AS perolehan,
            (((SELECT COUNT(*) FROM responden_$table_identity
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
            jawaban_pertanyaan_terbuka_$table_identity.id_responden
            JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
            WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
            perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
            'Lainnya') / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit =
            1)) * 100) AS persentase

            FROM pertanyaan_terbuka_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka 
            WHERE id_unsur_pelayanan = $value->id");

                $jawaban_ganda = $this->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
            jawaban_pertanyaan_terbuka_$table_identity.id_responden
            JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
            WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
            perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
            isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,
            (((SELECT COUNT(*) FROM responden_$table_identity
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id =
            jawaban_pertanyaan_terbuka_$table_identity.id_responden
            JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
            WHERE survey_$table_identity.is_submit = 1 && jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka =
            perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && jawaban_pertanyaan_terbuka_$table_identity.jawaban =
            isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit =
            1)) * 100) AS persentase
            FROM isi_pertanyaan_ganda_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka
            = perincian_pertanyaan_terbuka_$table_identity.id
            WHERE perincian_pertanyaan_terbuka_$table_identity.id_jenis_pilihan_jawaban = 1");

                $jawaban_isian = $this->db->query("SELECT *
            FROM jawaban_pertanyaan_terbuka_$table_identity
            JOIN pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN responden_$table_identity ON jawaban_pertanyaan_terbuka_$table_identity.id_responden = responden_$table_identity.id
            JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
            WHERE id_jenis_pilihan_jawaban = 2 && survey_$table_identity.is_submit = 1");


                foreach ($pertanyaan_tambahan->result() as $row) {
                    $table = $section->addTable('Judul Pertanyaan Tambahan');
                    $table->addRow();
                    $table->addCell(500)->addText($row->nomor_pertanyaan_terbuka . '.', array('name' => 'Arial', 'size' => 11, 'bold' => true, 'valign' => 'center'));
                    $table->addCell(9000)->addText(strip_tags($row->nama_pertanyaan_terbuka), array('name' => 'Arial', 'size' => 11, 'bold' => true, 'valign' => 'center'));

                    if ($row->id_jenis_pilihan_jawaban == 1) {

                        $fancyTableStyleName = 'Pertanyaan Tambahan 1';
                        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                        $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                        $fancyTableCellStyle = array('valign' => 'center');
                        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                        $table = $section->addTable($fancyTableStyleName);
                        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                        $table->addRow();
                        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                        $table->addCell(3000, $fancyTableCellStyle)->addText('Kelompok', $fancyTableFontStyle);
                        $table->addCell(2500, $fancyTableCellStyle)->addText('Jumlah', $fancyTableFontStyle);
                        $table->addCell(2500, $fancyTableCellStyle)->addText('Persentase', $fancyTableFontStyle);

                        $nt = 1;
                        foreach ($jawaban_ganda->result() as $value) {
                            if ($value->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {
                                $table->addRow();
                                $table->addCell(150)->addText($nt++, $cellTableFontStyle);
                                $table->addCell(3000)->addText($value->pertanyaan_ganda, $cellTableFontStyle);
                                $table->addCell(2500)->addText($value->perolehan, $cellTableFontStyle);
                                $table->addCell(2500)->addText(str_replace('.', ',', ROUND($value->persentase, 2)) . '%', $cellTableFontStyle);
                            }
                        }
                        if ($row->is_lainnya == 1) {
                            $table->addRow();
                            $table->addCell(150)->addText($nt++, $cellTableFontStyle);
                            $table->addCell(3000)->addText('Lainnya', $cellTableFontStyle);
                            $table->addCell(2500)->addText($row->perolehan, $cellTableFontStyle);
                            $table->addCell(2500)->addText(str_replace('.', ',', ROUND($row->persentase, 2)) . '%', $cellTableFontStyle);
                        }
                    } else {
                        $fancyTableStyleName = 'Pertanyaan Tambahan 2';
                        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                        $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                        $fancyTableCellStyle = array('valign' => 'center');
                        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                        $table = $section->addTable($fancyTableStyleName);
                        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                        $table->addRow();
                        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                        $table->addCell(8200, $fancyTableCellStyle)->addText('Jawaban', $fancyTableFontStyle);


                        $i = 1;
                        foreach ($jawaban_isian->result() as $get) {
                            if ($get->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka) {
                                $table->addRow();
                                $table->addCell(150)->addText($i++, $cellTableFontStyle);
                                $table->addCell(8200)->addText($get->jawaban, $cellTableFontStyle);
                            }
                        }
                    }
                    $section->addTextBreak();
                }

                $section->addTextBreak();
            }








        }

        $texthtmlbab25 = '<table>
        <tr>
            <td width="5%"><b>5.</b></td>
            <td width="95%"><b>Diagram Persepsi dan Harapan </b></td>
        </tr>
        <tr>
            <td width="5%">&nbsp;</td>
            <td width="95%">&nbsp;</td>
        </tr>
        </table>';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab25, false, false);
        //$section->addTextBreak();
        
        // HALAMAN KUADRAN UNSUR SKM
        if (in_array(1, $atribut_pertanyaan)) {
            // $section->addText('Kuadran Unsur SKM', array('bold' => true, 'size' => 18), $paragraphStyleName);

            // $section->addTextBreak(1);

            $section->addImage('assets/klien/img_kuadran/kuadran-' . $table_identity . '.png', array('width' => 450, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

            // $section->addTextBreak();
            $section->addText('Gambar '.($no_pgu).'. Diagram Persepsi dan Harapan', array('size' => 11), $paragraphStyleName);
            $section->addTextBreak(1);


            $section->addText('Tabel '.($no_t).'. Kuadran Perbaikan Unsur', array('size' => 11), $paragraphStyleName);

            $fancyTableStyleName = 'Tabel Kuadran Unsur SKM';
            $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
            $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
            $fancyTableCellStyle = array('valign' => 'center');
            $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
            $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
            $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
            $table = $section->addTable($fancyTableStyleName);
            $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');






            $this->db->select('COUNT(id) AS jumlah_unsur');
            $this->db->from('unsur_pelayanan_' . $manage_survey->table_identity);
            $this->db->where('id_parent = 0');
            $jumlah_unsur = $this->db->get()->row()->jumlah_unsur;

            //NILAI PER UNSUR
            $this->db->select("IF(id_parent = 0,unsur_pelayanan_$manage_survey->table_identity.id, unsur_pelayanan_$manage_survey->table_identity.id_parent) AS id_sub, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))) AS nilai_per_unsur");
            $this->db->from('jawaban_pertanyaan_unsur_' . $manage_survey->table_identity);
            $this->db->join("pertanyaan_unsur_pelayanan_$manage_survey->table_identity", "jawaban_pertanyaan_unsur_$manage_survey->table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id");
            $this->db->join("unsur_pelayanan_$manage_survey->table_identity", "pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id_unsur_pelayanan = unsur_pelayanan_$manage_survey->table_identity.id");
            $this->db->join("survey_$manage_survey->table_identity", "jawaban_pertanyaan_unsur_$manage_survey->table_identity.id_responden = survey_$manage_survey->table_identity.id_responden");
            $this->db->where("survey_$manage_survey->table_identity.is_submit = 1");
            $this->db->group_by('id_sub');
            $object_unsur = $this->db->get();
            $this->data['nilai_per_unsur'] = $object_unsur;

            $nilai_unsur = 0;
            foreach ($object_unsur->result() as $values) {
                $nilai_unsur += $values->nilai_per_unsur;
            }

            //NILAI PER HARAPAN
            $this->db->select("((SUM(skor_jawaban)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))) AS nilai_per_unsur");
            $this->db->from("jawaban_pertanyaan_harapan_$manage_survey->table_identity");
            $this->db->join("pertanyaan_unsur_pelayanan_$manage_survey->table_identity", "jawaban_pertanyaan_harapan_$manage_survey->table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id");
            $this->db->join("unsur_pelayanan_$manage_survey->table_identity", "pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id_unsur_pelayanan = unsur_pelayanan_$manage_survey->table_identity.id");
            $this->db->join("survey_$manage_survey->table_identity", "jawaban_pertanyaan_harapan_$manage_survey->table_identity.id_responden = survey_$manage_survey->table_identity.id_responden");
            $this->db->where("survey_$manage_survey->table_identity.is_submit = 1");
            $this->db->group_by("IF(id_parent = 0,unsur_pelayanan_$manage_survey->table_identity.id, unsur_pelayanan_$manage_survey->table_identity.id_parent)");
            $object_harapan = $this->db->get();
            $this->data['nilai_per_unsur_harapan'] = $object_harapan;

            $nilai_harapan = 0;
            foreach ($object_harapan->result() as $rows) {
                $nilai_harapan += $rows->nilai_per_unsur;
            }

            $total_rata_unsur = $nilai_unsur / $jumlah_unsur;
            $total_rata_harapan = $nilai_harapan / $jumlah_unsur;


            // $section->addText('PENENTUAN KUADRAN', array('bold' => true, 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START, 'spaceAfter' => 100));

            $data_kuadran_unsur =  $this->db->query("SELECT *,
            (CASE
                WHEN kup.skor_unsur <= $total_rata_unsur && kup.skor_harapan >= $total_rata_harapan
                        THEN 1
                WHEN kup.skor_unsur >= $total_rata_unsur && kup.skor_harapan >= $total_rata_harapan
                        THEN 2
                    WHEN kup.skor_unsur <= $total_rata_unsur && kup.skor_harapan <= $total_rata_harapan
                        THEN 3
                    WHEN kup.skor_unsur >= $total_rata_unsur && kup.skor_harapan <= $total_rata_harapan
                        THEN 4
                ELSE 0
            END) AS kuadran

            FROM (SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) AS nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) AS nama_unsur_pelayanan, 

            (SUM((SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1 && pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur))/(SELECT COUNT(survey_$table_identity.id_responden) FROM jawaban_pertanyaan_unsur_$table_identity 
            JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur && survey_$table_identity.is_submit = 1)/COUNT(id_parent)) AS skor_unsur,

            (SUM((SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_harapan_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1 && pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur))/(SELECT COUNT(survey_$table_identity.id_responden) FROM jawaban_pertanyaan_unsur_$table_identity 
            JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
            WHERE pertanyaan_unsur_pelayanan_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur && survey_$table_identity.is_submit = 1)/COUNT(id_parent)) AS skor_harapan

            FROM pertanyaan_unsur_pelayanan_$table_identity
            JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
            GROUP BY id_sub) AS kup");

            foreach ($data_kuadran_unsur->result() as $row) {
                if ($row->kuadran == 1) {
                    $kuadran_1[] = '<li>' . $row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan . '</li>';
                }
            }
            $set_kuadran_1 = implode("", $kuadran_1);


            foreach ($data_kuadran_unsur->result() as $row) {
                if ($row->kuadran == 2) {
                    $kuadran_2[] = '<li>' . $row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan . '</li>';
                }
            }
            $set_kuadran_2 = implode("", $kuadran_2);


            foreach ($data_kuadran_unsur->result() as $row) {
                if ($row->kuadran == 3) {
                    $kuadran_3[] = '<li>' . $row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan . '</li>';
                }
            }
            $set_kuadran_3 = implode("", $kuadran_3);


            foreach ($data_kuadran_unsur->result() as $row) {
                if ($row->kuadran == 4) {
                    $kuadran_4[] = '<li>' . $row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan . '</li>';
                }
            }
            $set_kuadran_4 = implode("", $kuadran_4);


            $html = '
            <table align="left" style="width: 100%; border: 1px #A5A5A5 solid;">
                    <tr>
                        <th width="30%" style="font-weight: bold; text-align:center; ">Kuadran</th>
                        <th style="font-weight: bold; text-align:center; ">Unsur</th>
                    </tr>
                    <tr>
                        <td width="30%" style="background-color: #F3F6F9; font-weight: bold; ">KUADRAN I</td>
                        <td><ul>' .  $set_kuadran_1 . '</ul></td>
                    </tr>
                     <tr>
                        <td width="30%" style="background-color: #F3F6F9; font-weight: bold; ">KUADRAN II</td>
                        <td><ul>' .  $set_kuadran_2 . '</ul></td>
                    </tr>
                     <tr>
                        <td width="30%" style="background-color: #F3F6F9; font-weight: bold; ">KUADRAN III</td>
                        <td><ul>' .  $set_kuadran_3 . '</ul></td>
                    </tr>
                     <tr>
                        <td width="30%" style="background-color: #F3F6F9; font-weight: bold; ">KUADRAN IV</td>
                        <td><ul>' .  $set_kuadran_4 . '</ul></td>
                    </tr>
            </table>
            ';
            // var_dump($html);
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);
            $section->addTextBreak();





            /*$section->addText('Nilai Persepsi.', array('bold' => true, 'size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START, 'spaceAfter' => 100));

            $persepsi = $this->db->query("SELECT *, SUBSTR(nomor_unsur,2) AS nomor_harapan
            FROM unsur_pelayanan_$table_identity
            WHERE id_parent = 0");
            $jumlah_unsur = $persepsi->num_rows();
            $colspan_unsur = ($jumlah_unsur + 1);

            $nomor_unsur = [];
            $nomor_harapan = [];
            foreach ($persepsi->result() as $row_unsur) {
                $nomor_unsur[] = '<th>' . $row_unsur->nomor_unsur . '</th>';
                $nomor_harapan[] = '<th>H' . $row_unsur->nomor_harapan . '</th>';
            }
            $no_unsur = implode("", $nomor_unsur);
            $no_harapan = implode("", $nomor_harapan);


            //NILAI PER UNSUR
            $this->db->select("IF(id_parent = 0,unsur_pelayanan_$manage_survey->table_identity.id, unsur_pelayanan_$manage_survey->table_identity.id_parent) AS id_sub, ((SUM(skor_jawaban)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))/(COUNT(id_parent)/COUNT(DISTINCT survey_$manage_survey->table_identity.id_responden))) AS nilai_per_unsur");
            $this->db->from('jawaban_pertanyaan_unsur_' . $manage_survey->table_identity);
            $this->db->join("pertanyaan_unsur_pelayanan_$manage_survey->table_identity", "jawaban_pertanyaan_unsur_$manage_survey->table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id");
            $this->db->join("unsur_pelayanan_$manage_survey->table_identity", "pertanyaan_unsur_pelayanan_$manage_survey->table_identity.id_unsur_pelayanan = unsur_pelayanan_$manage_survey->table_identity.id");
            $this->db->join("survey_$manage_survey->table_identity", "jawaban_pertanyaan_unsur_$manage_survey->table_identity.id_responden = survey_$manage_survey->table_identity.id_responden");
            $this->db->where("survey_$manage_survey->table_identity.is_submit = 1");
            $this->db->group_by('id_sub');
            $object_unsur = $this->db->get();

            $nilai_unsur = 0;
            $rata_unsur = [];
            foreach ($object_unsur->result() as $values) {
                $rata_unsur[] = '<td style="text-align: center;">' . ROUND($values->nilai_per_unsur, 3) . '</td>';
                $nilai_unsur += $values->nilai_per_unsur;
            }
            $get_rata_unsur = implode("", $rata_unsur);
            $total_rata_unsur = $nilai_unsur / $jumlah_unsur;

            $html = '
            
            <table align="left" style="width: 100%; border: 1px #A5A5A5 solid;">
                <thead>
                    <tr style="background-color: #A5A5A5; text-align: center; color: #FFFFFF; font-weight: bold; ">
                        <th></th>' . $no_unsur . '</tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Rata-Rata per Unsur</th>' . $get_rata_unsur . '</tr>
                    <tr>
                        <th>Rata-Rata Akhir</th>
                        <td colspan="' . $colspan_unsur . '">' . ROUND($total_rata_unsur, 3) . '</td>
                    </tr>
                </tbody>
            </table>
            ';
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html, false, false);

            //$section->addPageBreak();
            $section->addTextBreak();*/
        }

        $texthtmlbab26 = '<table>
        <tr>
            <td width="5%"><b>6.</b></td>
            <td width="95%"><b>Saran Responden</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Saran responden mengenai Survei Kepuasan Pelanggan pada ' . $data_survei['nama_organisasi'] . ' sebagai berikut:</p></td>
        </tr>
        </table>';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab26, false, false);
        $section->addTextBreak();

        // HALAMAN REKAP SARAN/ OPINI RESPONDEN
        if ($manage_survey->is_saran == 1) {
            /*$section->addText('Rekapitulasi Saran/ Opini Responden', array('bold' => true, 'size' => 18), $paragraphStyleName);

            $section->addTextBreak(1);

            $section->addText('Saran atau opini responden mengenai survei kepuasan pelanggan ' . $data_survei['nama_organisasi'] . '.', array('size' => 11), array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START, 'spaceAfter' => 100));

            $section->addTextBreak();*/
            $section->addText('Tabel '.($no_t+1).'. Saran Masukan Responden', array('size' => 11), $paragraphStyleName);

            $fancyTableStyleName = 'Jawaban Saran 1';
            $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
            $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
            $fancyTableCellStyle = array('valign' => 'center');
            $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
            $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
            $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
            $table = $section->addTable($fancyTableStyleName);
            $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

            $this->db->select("*");
            $this->db->from("survey_$table_identity");
            $this->db->join("responden_$table_identity", "responden_$table_identity.id = survey_$table_identity.id_responden");
            $this->db->where("survey_$table_identity.is_active", 1);
            $this->db->where("survey_$table_identity.is_submit", 1);
            $this->db->where("survey_$table_identity.saran != ''");
            $rekap_saran = $this->db->get();

            $table->addRow();
            $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
            // $table->addCell(4000, $fancyTableCellStyle)->addText('Nama Responden', $fancyTableFontStyle);
            $table->addCell(8200, $fancyTableCellStyle)->addText('Saran', $fancyTableFontStyle);

            $no = 1;
            foreach ($rekap_saran->result() as $value) {

                $table->addRow();
                $table->addCell(150)->addText($no++, $cellTableFontStyle);
                // $table->addCell(4000)->addText($value->nama_lengkap, $cellTableFontStyle);
                $table->addCell(200)->addText($value->saran, $cellTableFontStyle);
            }
            //$section->addPageBreak();
            $section->addTextBreak();
        }

        $section->addPageBreak();





        $section->addText('BAB III', array('bold' => true, 'size' => 16), $paragraphStyleName);
        //$section->addTextBreak();
        $section->addText('PENUTUP', array('bold' => true, 'size' => 16), $paragraphStyleName);
        $section->addTextBreak();

        if (in_array(1, $atribut_pertanyaan)) { 
            //Unsur Prioritas Perbaikan 
            $nilai_per_unsur_p_asc = $this->db->query("SELECT IF(id_parent = 0,unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent) AS id_sub, (SUM(skor_jawaban)/COUNT(DISTINCT id_responden)) AS rata_rata,  (COUNT(id_parent)/COUNT(DISTINCT id_responden)) AS colspan, ((SUM(skor_jawaban)/COUNT(DISTINCT id_responden))/(COUNT(id_parent)/COUNT(DISTINCT id_responden))) AS nilai_per_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nomor_unsur, (SELECT nama_unsur_pelayanan FROM unsur_pelayanan_$table_identity WHERE id_sub = unsur_pelayanan_$table_identity.id) as nama_unsur_pelayanan
            FROM jawaban_pertanyaan_harapan_$table_identity
            JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
            JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
            GROUP BY id_sub
            ORDER BY nilai_per_unsur ASC");

            $p_asc = [];
            foreach ($nilai_per_unsur_p_asc->result() as $values) {
                $p_asc[] = $values->nomor_unsur . '. ' . $values->nama_unsur_pelayanan;
            }
            $asc_harapan = implode("<br />", $p_asc);

            $prioritas_perbaikan = '<tr>
            <td width="34%">Unsur Prioritas Perbaikan</td>
            <td width="4%">:</td>
            <td width="62%">'.$asc_harapan.'</td>
        </tr>';
        }else{
            $prioritas_perbaikan = '';
        }

        $texthtmlbab3 = '<table>
        <tr>
            <td width="5%"><b>1.</b></td>
            <td width="95%"><b>Kesimpulan</b></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%"><p align="justify">Berdasarkan hasil Survei Kepuasan Pelanggan pada ' . $data_survei['nama_organisasi'] . ' diperoleh hasil sebagai berikut:</p></td>
        </tr>
        <tr>
            <td width="5%"></td>
            <td width="95%">
            
            <table>
        <tr>
            <td width="34%">Nilai IKP</td>
            <td width="4%">:</td>
            <td width="62%">'.str_replace('.', ',', ROUND($nilai_tertimbang, 3)).'</td>
        </tr>
        <tr>
            <td width="34%">Nilai IKP Konversi</td>
            <td width="4%">:</td>
            <td width="62%">'.str_replace('.', ',', ROUND($nilai_skm, 2)).'</td>
        </tr>
        <tr>
            <td width="34%">Kategori</td>
            <td width="4%">:</td>
            <td width="62%">'.$index.'</td>
        </tr>
        <tr>
            <td width="34%">Unsur Tertinggi</td>
            <td width="4%">:</td>
            <td width="62%">'.$unsur_tertinggi.'</td>
        </tr>
        <tr>
            <td width="34%">Unsur Terendah</td>
            <td width="4%">:</td>
            <td width="62%">'.$unsur_terendah.'</td>
        </tr>
        '.$prioritas_perbaikan.'
        </table>

            </td>
        </tr>
        </table>';

        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab3, false, false);
        $section->addTextBreak();

        /*$section->addText('Hasil SKM tersebut di atas, terdiri dari 16 unsur pelayanan, sebagaimana tersebut dalam tabel 2 di bawah ini.', array('name' => 'Arial', 'size' => 11), array('keepNext' => true, 'indentation' => array('firstLine' => 500), 'align' => 'both'));

        $section->addTextBreak(1);*/

        

        

        // $section->addText('Tabel 3. Ringkasan Hasil Survei Kepuasan Pelanggan', array('size' => 11), $paragraphStyleName);

        $fancyTableStyleName = 'SKM Unsur Tertinggi Terendah';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        /*$table->addRow();
        $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
        $table->addCell(3000, $fancyTableCellStyle)->addText('Kesimpulan', $fancyTableFontStyle);
        $table->addCell(5000, $fancyTableCellStyle)->addText('Keterangan', $fancyTableFontStyle);
        $table->addRow();
        $table->addCell(150)->addText('1', $cellTableFontStyle);
        $table->addCell(3000)->addText('Nilai IKP', $cellTableFontStyle);
        $table->addCell(5000)->addText(str_replace('.', ',', ROUND($nilai_tertimbang, 3)), $cellTableFontStyle);
        $table->addRow();
        $table->addCell(150)->addText('2', $cellTableFontStyle);
        $table->addCell(3000)->addText('Nilai IKP Konversi', $cellTableFontStyle);
        $table->addCell(5000)->addText(str_replace('.', ',', ROUND($nilai_skm, 2)), $cellTableFontStyle);
        $table->addRow();
        $table->addCell(150)->addText('3', $cellTableFontStyle);
        $table->addCell(3000)->addText('Kategori', $cellTableFontStyle);
        $table->addCell(5000)->addText($index, $cellTableFontStyle);
        $table->addRow();
        $table->addCell(150)->addText('4', $cellTableFontStyle);
        $table->addCell(3000)->addText('Unsur Tertinggi', $cellTableFontStyle);
        $table->addCell(5000)->addText($unsur_tertinggi, $cellTableFontStyle);
        $table->addRow();
        $table->addCell(150)->addText('5', $cellTableFontStyle);
        $table->addCell(3000)->addText('Unsur Terendah', $cellTableFontStyle);
        $table->addCell(5000)->addText($unsur_terendah, $cellTableFontStyle);*/

        $texthtmlkesimpulan = '<table>
        <tr>
            <td width="45%">Nilai IKP</td>
            <td width="5%">:</td>
            <td width="50%">'.str_replace('.', ',', ROUND($nilai_tertimbang, 3)).'</td>
        </tr>
        <tr>
            <td width="45%">Nilai IKP Konversi</td>
            <td width="5%">:</td>
            <td width="50%">'.str_replace('.', ',', ROUND($nilai_skm, 2)).'</td>
        </tr>
        <tr>
            <td width="45%">Kategori</td>
            <td width="5%">:</td>
            <td width="50%">'.$index.'</td>
        </tr>
        <tr>
            <td width="45%">Unsur Tertinggi</td>
            <td width="5%">:</td>
            <td width="50%">'.$unsur_tertinggi.'</td>
        </tr>
        <tr>
            <td width="45%">Unsur Terendah</td>
            <td width="5%">:</td>
            <td width="50%">'.$unsur_terendah.'</td>
        </tr>
        </table>';

        // \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlkesimpulan, false, false);
        
        $section->addTextBreak();
        


        $texthtmlbab32 = '<table>
        <tr>
            <td width="5%"><b>2.</b></td>
            <td width="95%"><b>Rekomendasi</b></td>
        </tr>
        </table>';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab32, false, false);

        $this->db->select("*");
        $this->db->from("analisa_$table_identity");
        $this->db->join("unsur_pelayanan_$table_identity", "unsur_pelayanan_$table_identity.id = analisa_$table_identity.id_unsur_pelayanan");
        $data_analisa = $this->db->get();
              
        if($data_analisa->num_rows() > 0 ) {   
            $texthtmlbab32 = '<table>';
            foreach ($data_analisa->result() as $value) {
                $texthtmlbab32 .= '<tr>
                    <td width="25%"><b>Unsur</b></td>
                    <td width="5%">:</td>
                    <td width="70%">'.$value->nomor_unsur.'. '.$value->nama_unsur_pelayanan.'</td>
                </tr>
                <tr>
                    <td width="25%"><b>Faktor-faktor yang mempengaruhi</b></td>
                    <td width="5%">:</td>
                    <td width="70%">'.$value->faktor_penyebab.'</td>
                </tr>
                <tr>
                    <td width="25%"><b>Rencana tindak lanjut</b></td>
                    <td width="5%">:</td>
                    <td width="70%">'.$value->rencana_perbaikan.'</td>
                </tr>
                <tr>
                    <td width="25%"><b>Waktu</b></td>
                    <td width="5%">:</td>
                    <td width="70%">'.$value->waktu.'</td>
                </tr>
                <tr>
                    <td width="25%"><b>Penanggung jawab</b></td>
                    <td width="5%">:</td>
                    <td width="70%">'.$value->penanggung_jawab.'</td>
                </tr>
                <tr>
                    <td width="25%">&nbsp;</td>
                    <td width="5%">&nbsp;</td>
                    <td width="70%">&nbsp;</td>
                </tr>';
            }
            $texthtmlbab32 .= '</table>';
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $texthtmlbab32, false, false);

        }else{
            $section->addText('Belum ada data rekomendasi.', array('italic'=>true, 'size' => 11), $paragraphStyleName);
        }
        
        // $section->addTextBreak();


        /*$fancyTableStyleName = 'Rekomendasi 1';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        $table->addRow();
        $table->addCell(3000, $fancyTableCellStyle)->addText('Unsur Prioritas Perbaikan', $fancyTableFontStyle);
        $table->addCell(3000, $fancyTableCellStyle)->addText('Rekomendasi', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Target Waktu', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('PIC', $fancyTableFontStyle);
        $table->addRow();
        $table->addCell(3000)->addText('U1', $cellTableFontStyle);
        $table->addCell(3000)->addText('R1', $cellTableFontStyle);
        $table->addCell(2000)->addText('T1', $cellTableFontStyle);
        $table->addCell(2000)->addText('P1', $cellTableFontStyle);
        $table->addRow();
        $table->addCell(3000)->addText('U2', $cellTableFontStyle);
        $table->addCell(3000)->addText('R2', $cellTableFontStyle);
        $table->addCell(2000)->addText('T2', $cellTableFontStyle);
        $table->addCell(2000)->addText('P2', $cellTableFontStyle);
        $section->addTextBreak(1);

        $fancyTableStyleName = 'Rekomendasi 2';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        $table->addRow();
        $table->addCell(3000, $fancyTableCellStyle)->addText('Unsur Terendah', $fancyTableFontStyle);
        $table->addCell(3000, $fancyTableCellStyle)->addText('Rekomendasi', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('Target Waktu', $fancyTableFontStyle);
        $table->addCell(2000, $fancyTableCellStyle)->addText('PIC', $fancyTableFontStyle);
        $table->addRow();
        $table->addCell(3000)->addText('U1', $cellTableFontStyle);
        $table->addCell(3000)->addText('R1', $cellTableFontStyle);
        $table->addCell(2000)->addText('T1', $cellTableFontStyle);
        $table->addCell(2000)->addText('P1', $cellTableFontStyle);
        $table->addRow();
        $table->addCell(3000)->addText('U2', $cellTableFontStyle);
        $table->addCell(3000)->addText('R2', $cellTableFontStyle);
        $table->addCell(2000)->addText('T2', $cellTableFontStyle);
        $table->addCell(2000)->addText('P2', $cellTableFontStyle);
        $section->addTextBreak(1);*/
        

        

        // HALAMAN REKAPITULASI ALASAN JAWABAN PERTANYAAN UNSUR
        /*$section->addText('Rekapitulasi Alasan Jawaban Pertanyaan Unsur', array('bold' => true, 'size' => 18), $paragraphStyleName);

        $section->addTextBreak(2);

        $this->db->select("*, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = unsur_pelayanan_$table_identity.id) AS nomor_unsur");
        $this->db->from("pertanyaan_unsur_pelayanan_$table_identity");
        $unsur = $this->db->get();

        foreach ($unsur->result() as $value) {

            // CEK DATA RESPONDEN UNSUR
            $this->db->select("*");
            $this->db->from("jawaban_pertanyaan_unsur_$table_identity");
            $this->db->join("responden_$table_identity", "responden_$table_identity.id = jawaban_pertanyaan_unsur_$table_identity.id_responden");
            $this->db->join("survey_$table_identity", "responden_$table_identity.id = survey_$table_identity.id_responden");
            $this->db->where("survey_$table_identity.is_submit", 1);
            $this->db->where("jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur", $value->id_pertanyaan_unsur);
            $this->db->where("jawaban_pertanyaan_unsur_$table_identity.is_active", 1);
            $this->db->where("jawaban_pertanyaan_unsur_$table_identity.alasan_pilih_jawaban !=", "");
            $jawaban_p_unsur = $this->db->get();

            if ($jawaban_p_unsur->num_rows() > 0) {

                $table = $section->addTable('Alasan Jawaban U1');
                $table->addRow();
                $table->addCell(500)->addText($value->nomor_unsur . '.', array('name' => 'Arial', 'size' => 11, 'valign' => 'center'));
                $table->addCell(9000)->addText(strip_tags($value->isi_pertanyaan_unsur), array('name' => 'Arial', 'size' => 11, 'valign' => 'center'));


                $fancyTableStyleName = 'Rekapitulasi Alasan 1';
                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                $fancyTableCellStyle = array('valign' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                $table->addRow();
                $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                // $table->addCell(4000, $fancyTableCellStyle)->addText('Nama Responden', $fancyTableFontStyle);
                $table->addCell(8200, $fancyTableCellStyle)->addText('Alasan Jawaban', $fancyTableFontStyle);


                if ($jawaban_p_unsur->num_rows() > 0) {

                    $no = 1;
                    foreach ($jawaban_p_unsur->result() as $values) {
                        $table->addRow();
                        $table->addCell(150)->addText($no++, $cellTableFontStyle);
                        // $table->addCell(4000)->addText($values->nama_lengkap, $cellTableFontStyle);
                        $table->addCell(8200)->addText($values->alasan_pilih_jawaban, $cellTableFontStyle);
                    }
                } else {
                    // echo '<span style="color: red;">Tidak ada alasan jawaban yang diisi</span>';
                }
                $section->addTextBreak();
            }
        }
        $section->addPageBreak();*/







        








        // HALAMAN REKAPITULASI JAWABAN PERTANYAAN KUALITATIF\
        /*if (in_array(3, $atribut_pertanyaan)) {
            // $section->addText('Rekapitulasi Jawaban Pertanyaan Kualitatif', array('bold' => true, 'size' => 18), $paragraphStyleName);

            // $section->addTextBreak(2);


            $this->db->select("*");
            $this->db->from("pertanyaan_kualitatif_$table_identity");
            $this->db->where("pertanyaan_kualitatif_$table_identity.is_active", 1);
            $rekap_kualitatif = $this->db->get();


            $no = 1;
            foreach ($rekap_kualitatif->result() as $value) {

                $table = $section->addTable('Pertanyaan Kualitatif 1');
                $table->addRow();
                $table->addCell(500)->addText($no++, array('name' => 'Arial', 'size' => 11, 'valign' => 'center'));
                $table->addCell(9000)->addText(strip_tags($value->isi_pertanyaan), array('name' => 'Arial', 'size' => 11, 'valign' => 'center'));

                $this->db->select("*");
                $this->db->from("jawaban_pertanyaan_kualitatif_$table_identity");
                $this->db->join("responden_$table_identity", "responden_$table_identity.id = jawaban_pertanyaan_kualitatif_$table_identity.id_responden");
                $this->db->join("survey_$table_identity", "responden_$table_identity.id = survey_$table_identity.id_responden");
                $this->db->where("survey_$table_identity.is_submit", 1);
                $this->db->where("jawaban_pertanyaan_kualitatif_$table_identity.id_pertanyaan_kualitatif", $value->id);
                $this->db->where("jawaban_pertanyaan_kualitatif_$table_identity.is_active", 1);
                $rekap_jawaban_kualitatif = $this->db->get();

                $fancyTableStyleName = 'Jawaban Pertanyaan Kualitatif 1';
                $fancyTableStyle = array('borderSize' => 5, 'borderColor' => 'A5A5A5', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
                $fancyTableFirstRowStyle = array('bgColor' => 'A5A5A5');
                $fancyTableCellStyle = array('valign' => 'center');
                $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
                $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

                $table->addRow();
                $table->addCell(150, $fancyTableCellStyle)->addText('No', $fancyTableFontStyle);
                // $table->addCell(4000, $fancyTableCellStyle)->addText('Nama Responden', $fancyTableFontStyle);
                $table->addCell(8200, $fancyTableCellStyle)->addText('Jawaban', $fancyTableFontStyle);

                $no_sub = 1;
                foreach ($rekap_jawaban_kualitatif->result() as $values) {

                    $table->addRow();
                    $table->addCell(150)->addText($no_sub++, $cellTableFontStyle);
                    // $table->addCell(4000)->addText($values->nama_lengkap, $cellTableFontStyle);
                    $table->addCell(8200)->addText($values->isi_jawaban_kualitatif, $cellTableFontStyle);
                }

                $section->addTextBreak();
            }
            // $section->addPageBreak();
        }*/




        





        







        // HALAMAN ANALISA HIDDEN SEMENTARA
        /*$section->addText('Analisa', array('bold' => true, 'size' => 18), $paragraphStyleName);

        $section->addTextBreak();

        $fancyTableStyleName = 'Tabel Analisa SKM';
        $fancyTableStyle = array('borderSize' => 5, 'borderColor' => '4472C4', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $fancyTableFirstRowStyle = array('bgColor' => '4472C4');
        $fancyTableCellStyle = array('valign' => 'center');
        $fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
        $fancyTableFontStyle = array('name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => 'FFFFFF');
        $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
        $table = $section->addTable($fancyTableStyleName);
        $cellTableFontStyle = array('name' => 'Arial', 'size' => 11, 'valign' => 'center');

        $table->addRow();
        $table->addCell(150, $fancyTableCellStyle)->addText('Unsur', $fancyTableFontStyle);
        // $table->addCell(2000, $fancyTableCellStyle)->addText('Saran dan Masukan', $fancyTableFontStyle);
        $table->addCell(3000, $fancyTableCellStyle)->addText('Faktor-faktor Yang Mempengaruhi', $fancyTableFontStyle);
        $table->addCell(2500, $fancyTableCellStyle)->addText('Rencana Tindak Lanjut', $fancyTableFontStyle);
        $table->addCell(1000, $fancyTableCellStyle)->addText('Waktu', $fancyTableFontStyle);
        // $table->addCell(1000, $fancyTableCellStyle)->addText('Kegiatan', $fancyTableFontStyle);
        $table->addCell(1500, $fancyTableCellStyle)->addText('Penanggung Jawab', $fancyTableFontStyle);

        $this->db->select("*");
        $this->db->from("analisa_$table_identity");
        $this->db->join("unsur_pelayanan_$table_identity", "unsur_pelayanan_$table_identity.id = analisa_$table_identity.id_unsur_pelayanan");
        $data_analisa = $this->db->get();

        foreach ($data_analisa->result() as $value) {
            $table->addRow();
            $table->addCell(150)->addText($value->nomor_unsur, $cellTableFontStyle);
            // $table->addCell(2000)->addText(strip_tags($value->saran_masukan), $cellTableFontStyle);
            $table->addCell(3000)->addText(strip_tags($value->faktor_penyebab), $cellTableFontStyle);
            $table->addCell(2500)->addText(strip_tags($value->rencana_perbaikan), $cellTableFontStyle);
            $table->addCell(1000)->addText($value->waktu, $cellTableFontStyle);
            // $table->addCell(1000)->addText(strip_tags($value->kegiatan), $cellTableFontStyle);
            $table->addCell(1500)->addText($value->penanggung_jawab, $cellTableFontStyle);
        }*/


        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $file = $filename . '.docx';
        $somePathToUpload = 'assets/klien/file/laporan/word/';
        $objWriter->save($somePathToUpload.$file);
    }

    public function download_pdf($slug, $filename) {
        $this->load->helper('download');
        $path    =   file_get_contents(base_url() . "assets/klien/file/laporan/pdf/" . $filename . '.pdf');
        $name    =   "laporan-" . $slug . '.pdf';
        force_download($name, $path);  
    }

    public function download_word($slug, $filename) {
        $this->load->helper('download');
        $path    =   file_get_contents(base_url() . "assets/klien/file/laporan/word/" . $filename . '.docx');
        $name    =   "laporan-" . $slug . '.docx';
        force_download($name, $path);  
    }
    
}