<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DrafKuesionerController extends Client_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->library('Pdf');
    }


    public function index($id1, $id2)
    {

        $this->data = [];
        $this->data['title'] = 'Detail Pertanyaan Unsur';
        $this->data['profiles'] = Client_Controller::_get_data_profile($id1, $id2);

        // get tabel identity
        $this->db->select('*, manage_survey.id AS id_manage_survey');
        $this->db->from('manage_survey');
        $this->db->where('manage_survey.slug', $this->uri->segment(2));
        $this->data['manage_survey'] = $this->db->get()->row();
        $table_identity = $this->data['manage_survey']->table_identity;

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id=' . $this->data['manage_survey']->id_user);
        $this->data['user'] = $this->db->get()->row();

        $this->data['profil_responden'] = $this->db->query("SELECT *, (SELECT COUNT(id) FROM kategori_profil_responden_$table_identity WHERE id_profil_responden = profil_responden_$table_identity.id) AS total_kategori FROM profil_responden_$table_identity")->result();

        //PERTANYAAN UNSUR
        $this->data['pertanyaan_unsur'] = $this->db->query("SELECT *, (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 1 ) AS pilihan_1,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 2 ) AS pilihan_2,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 3 ) AS pilihan_3,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 4 ) AS pilihan_4
        FROM pertanyaan_unsur_pelayanan_$table_identity
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id");


        // //PERTANYAAN HARAPAN
        // $this->data['pertanyaan_harapan'] = $this->db->query("SELECT *, (SELECT nama_tingkat_kepentingan FROM nilai_tingkat_kepentingan_$table_identity WHERE id_pertanyaan_unsur_pelayanan = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_tingkat_kepentingan = 1 ) AS pilihan_1,
        // (SELECT nama_tingkat_kepentingan FROM nilai_tingkat_kepentingan_$table_identity WHERE id_pertanyaan_unsur_pelayanan = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_tingkat_kepentingan = 2 ) AS pilihan_2,
        // (SELECT nama_tingkat_kepentingan FROM nilai_tingkat_kepentingan_$table_identity WHERE id_pertanyaan_unsur_pelayanan = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_tingkat_kepentingan = 3 ) AS pilihan_3,
        // (SELECT nama_tingkat_kepentingan FROM nilai_tingkat_kepentingan_$table_identity WHERE id_pertanyaan_unsur_pelayanan = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_tingkat_kepentingan = 4 ) AS pilihan_4, (SELECT nomor_unsur FROM unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = unsur_pelayanan_$table_identity.id) AS nomor_unsur
        // FROM pertanyaan_unsur_pelayanan_$table_identity");


        // //PERTANYAAN KUALITATIF
        // $this->data['pertanyaan_kualitatif'] = $this->db->get_where("pertanyaan_kualitatif_$table_identity", array('is_active' => 1));

        if ($this->data['pertanyaan_unsur']->num_rows() > 0) {

            $this->load->library('pdfgenerator');
            $this->data['title_pdf'] = 'Draf Kuesioner';
            $file_pdf = 'Draf Kuesioner';

            $paper = 'A4';
            $orientation = "potrait";

            $html = $this->load->view('draf_kuesioner/cetak', $this->data, true);
            $this->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);

            // $this->load->view('draf_kuesioner/cetak', $this->data);
        } else {
            $this->data['pesan'] = 'Pertanyaan Belum di Isi !';
            return view('not_questions/index', $this->data);
            exit();
        }
    }



    public function tcpdf()
    {
        //START QUERY
        $this->db->select('*');
        $this->db->from('manage_survey');
        $this->db->where('manage_survey.slug', $this->uri->segment(2));
        $manage_survey = $this->db->get()->row();
        $table_identity = $manage_survey->table_identity;
        $skala_likert = $manage_survey->skala_likert == 5 ? 5 : 4;

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id', $manage_survey->id_user);
        $user = $this->db->get()->row();



        //============================================= START NEW PDF BY TCPDF =============================================
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hanif');
        $pdf->SetTitle('Draf Kuesioner ' . $manage_survey->survey_name);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage('P', 'A4');





        //========================================  USER PROFIL =============================================
        if ($user->foto_profile == NULL) {
            $profil = '<img src="' . URL_AUTH . 'assets/klien/foto_profile/200px.jpg" height="75" alt="">';
        } else {
            $profil = '<img src="' . URL_AUTH . 'assets/klien/foto_profile/' . $user->foto_profile . '" height="75" alt="">';
        };

        $title_header = unserialize($manage_survey->title_header_survey);
        $title_1 = $title_header[0];
        $title_2 = $title_header[1];



        //========================================  PROFIL RESPONDEN =============================================
        $profil_responden = $this->db->query("SELECT *,  REPLACE(LOWER(nama_profil_responden), ' ', '_') AS nama_alias FROM profil_responden_$table_identity ORDER BY IF(urutan != '',urutan,id) ASC")->result();
        $nama_profil = [];
        foreach ($profil_responden as $get_profil) {

            if ((isset($get_profil->posisi_label_isian)) && ($get_profil->posisi_label_isian == 1)) {
                $label_kiri = $get_profil->label_isian . ' ';
            } else {
                $label_kiri = '';
            }
            if ((isset($get_profil->posisi_label_isian)) && ($get_profil->posisi_label_isian == 2)) {
                $label_kanan = ' ' . $get_profil->label_isian;
            } else {
                $label_kanan = '';
            }

            if ($get_profil->jenis_isian == 1) {
                $kategori = [];
                foreach ($this->db->get_where("kategori_profil_responden_$table_identity", array('id_profil_responden' => $get_profil->id))->result() as $value) {
                    $kategori[] = '<li>' . $value->nama_kategori_profil_responden . '</li>';
                }
                $get_kategori = implode("", $kategori);
            } else {
                $get_kategori = '';
            };

            if ((!isset($get_profil->id_profil)) || ($get_profil->id_profil == 0)) {
                $namaalias = $get_profil->nama_profil_responden;
            } else {
                $namaalias = '&nbsp;';
            }

            $nama_profil[] = '<tr style="font-size: 11px;"><td width="30%" style="height:15px;" valign="top">' . $namaalias . ' </td><td width="70%">';
            $nama_profil[] = '<ul style="list-style-type:img|png|3|3|' . base_url() . 'assets/img/site/vector/check.png">';
            $nama_profil[] = $label_kiri . $get_kategori . $label_kanan;
            $nama_profil[] = '</ul>';
            $nama_profil[] = '</td></tr>';
        }
        $get_nama = implode("", $nama_profil);



        //CEK MENGGUNAKAN JENIS LAYANAN ATAU TIDAK
        if ($manage_survey->is_layanan_survei != 0) {
            $nama_layanan = [];
            foreach ($this->db->get_where("layanan_survei_$table_identity", array('is_active' => 1))->result() as $row) {
                $nama_layanan[] = '<li>' . $row->nama_layanan . '</li>';
            }
            $get_layanan = '<ul style="list-style-type:img|png|3|3|' . base_url() . 'assets/img/site/vector/check.png">' . implode("", $nama_layanan) . '</ul>';


            $layanan_survei = '<tr style="font-size: 11px;">
            <td width=" 30%" style="height:15px;">Jenis Pelayanan yang diterima</td>
            <td width="70%">' . $get_layanan . '</td>
            </tr>';
        } else {
            $layanan_survei = '';
        }





        //=================================== PERTANYAAN TERBUKA ATAS ==========================================
        if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {

            $pertanyaan_terbuka_atas = $this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka, (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
            FROM pertanyaan_terbuka_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            WHERE pertanyaan_terbuka_$table_identity.is_letak_pertanyaan = 1
            ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");

            if ($pertanyaan_terbuka_atas->num_rows() > 0) {

                $per_terbuka_atas = [];
                foreach ($pertanyaan_terbuka_atas->result() as $value) {


                    if ($value->id_jenis_pilihan_jawaban == 2) {

                        $per_terbuka_atas[] = '
                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                        <tr>
                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                            <td width="60%"></td>
                        </tr>
                    </table>';
                    } else {

                        $pilihan_terbuka_atas = [];
                        foreach ($this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->result() as $get) {

                            $pilihan_terbuka_atas[] = '<tr>
                        <td width="5%"></td>
                        <td width="55%">' . $get->pertanyaan_ganda . '</td>
                        </tr>';
                        }


                        $get_pilihan_terbuka_atas = implode("", $pilihan_terbuka_atas);
                        $isi_terbuka_atas = $this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->num_rows() + 1;


                        $per_terbuka_atas[] = '
                        <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                            <tr>
                                <td rowspan="' . $isi_terbuka_atas . '" width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>

                                <td width="33%" rowspan="' . $isi_terbuka_atas . '" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>

                                <td colspan="2" width="60%" style="background-color:#C7C6C1; text-align:center;">PILIHAN JAWABAN</td>
                                        
                            </tr>' . $get_pilihan_terbuka_atas . '
                    </table>';
                    }
                }
                $get_pertanyaan_terbuka_atas = implode("", $per_terbuka_atas);
            } else {
                $get_pertanyaan_terbuka_atas = '';
            }
        } else {
            $get_pertanyaan_terbuka_atas = '';
        };





        //============================================= PERTANYAAN UNSUR =============================================
        //JIKA MENGGUNAKAN DIMENSI MAKA PROSES INI
        if ($manage_survey->is_dimensi == 1) {

            $per_dimensi = [];
            foreach ($this->db->query("SELECT *
            FROM (SELECT *, (SELECT COUNT(id) FROM unsur_pelayanan_$table_identity WHERE dimensi_$table_identity.id = unsur_pelayanan_$table_identity.id_dimensi) AS jumlah FROM dimensi_$table_identity) dms_$table_identity
            WHERE jumlah > 0
            ORDER BY id ASC")->result() as $dms) {


                $per_unsur = [];
                foreach ($this->db->query("SELECT *, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur
                FROM pertanyaan_unsur_pelayanan_$table_identity
                JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
                WHERE unsur_pelayanan_$table_identity.id_dimensi = $dms->id")->result() as $row) {


                    //PERTANYAAN TERBUKA =======================================================================
                    if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {


                        $per_terbuka = [];
                        foreach ($this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka, (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
                        FROM pertanyaan_terbuka_$table_identity
                        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        WHERE  id_unsur_pelayanan = $row->id_unsur_pelayanan
                        ORDER BY SUBSTR(nomor_pertanyaan_terbuka, 2) + 0")->result() as $value) {


                            if ($value->id_jenis_pilihan_jawaban == 2) {

                                $per_terbuka[] = '
                                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                        <tr>
                                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                                            <td width="60%"></td>
                                        </tr>
                                    </table>';
                            } else {


                                $pilihan_terbuka = $this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka));
                                $rowspan_terbuka = $pilihan_terbuka->num_rows() + 1;


                                $array_pilihan_terbuka = [];
                                foreach ($pilihan_terbuka->result() as $get) {

                                    $array_pilihan_terbuka[] = '
                                    <tr>
                                        <td width="5%"></td>
                                        <td width="55%">' . $get->pertanyaan_ganda . '</td>
                                    </tr>';
                                }
                                $get_pilihan_terbuka = implode("", $array_pilihan_terbuka);


                                $per_terbuka[] = '
                                <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                    <tr>
                                        <td rowspan="' . $rowspan_terbuka . '" width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>

                                        <td width="33%" rowspan="' . $rowspan_terbuka . '" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>

                                        <td colspan="2" width="60%" style="text-align:center; background-color:#C7C6C1;">PILIHAN JAWABAN</td>
                                    </tr>' . $get_pilihan_terbuka . '
                                </table>';
                            }
                        }
                        $get_pertanyaan_terbuka = implode("", $per_terbuka);
                    } else {
                        $get_pertanyaan_terbuka = '';
                    }
                    // END PERTANYAAN TERBUKA =======================================================================



                    $kategori_unsur = $this->db->get_where("kategori_unsur_pelayanan_$table_identity", ['id_pertanyaan_unsur' => $row->id_pertanyaan_unsur]);
                    $rowspan = $kategori_unsur->num_rows() + 1;

                    $array_kategori_unsur = [];
                    foreach ($kategori_unsur->result() as $val) {
                        $array_kategori_unsur[] = '
                        <tr>
                            <td width="5%"></td>
                            <td width="55%">' . $val->nama_kategori_unsur_pelayanan . '</td>
                        </tr>';
                    }

                    $per_unsur[] = '<table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                        <tr>
                            <td rowspan="' . $rowspan . '" width="7%" style="text-align:center; font-size: 11px;">' . $row->nomor_unsur . '</td>

                            <td rowspan="' . $rowspan . '" width="33%"  style="text-align:left; font-size: 11px;">' . $row->isi_pertanyaan_unsur . '</td>

                            <td colspan="2" width="60%" style="text-align:center; background-color:#C7C6C1;">PILIHAN JAWABAN</td>
                        </tr>' . implode("", $array_kategori_unsur) . '
                    </table>' . $get_pertanyaan_terbuka;
                }
                $get_pertanyaan_unsur = implode("", $per_unsur);


                $keterangan = $dms->keterangan != '' ? '<br>' . $dms->keterangan : '';
                $per_dimensi[] = '
                <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                        <tr>
                            <td><b>' . $dms->kode . '. ' . $dms->dimensi . '</b>' . $keterangan . '</td>
                        </tr>
                </table>' . $get_pertanyaan_unsur;
            }
            $get_dimensi = implode("", $per_dimensi);




            //JIKA TIDAK MENGGUNAKAN DIMENSI
        } else {

            $per_unsur = [];
            foreach ($this->db->query("SELECT *, pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur
                FROM pertanyaan_unsur_pelayanan_$table_identity
                JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id")->result() as $row) {


                //PERTANYAAN TERBUKA =======================================================================
                if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {


                    $per_terbuka = [];
                    foreach ($this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka, (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
                        FROM pertanyaan_terbuka_$table_identity
                        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        WHERE  id_unsur_pelayanan = $row->id_unsur_pelayanan
                        ORDER BY SUBSTR(nomor_pertanyaan_terbuka, 2) + 0")->result() as $value) {


                        if ($value->id_jenis_pilihan_jawaban == 2) {

                            $per_terbuka[] = '
                                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                        <tr>
                                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                                            <td width="60%"></td>
                                        </tr>
                                    </table>';
                        } else {


                            $pilihan_terbuka = $this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka));
                            $rowspan_terbuka = $pilihan_terbuka->num_rows() + 1;


                            $array_pilihan_terbuka = [];
                            foreach ($pilihan_terbuka->result() as $get) {

                                $array_pilihan_terbuka[] = '
                                    <tr>
                                        <td width="5%"></td>
                                        <td width="55%">' . $get->pertanyaan_ganda . '</td>
                                    </tr>';
                            }
                            $get_pilihan_terbuka = implode("", $array_pilihan_terbuka);


                            $per_terbuka[] = '
                                <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                    <tr>
                                        <td rowspan="' . $rowspan_terbuka . '" width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>

                                        <td width="33%" rowspan="' . $rowspan_terbuka . '" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>

                                        <td colspan="2" width="60%" style="text-align:center; background-color:#C7C6C1;">PILIHAN JAWABAN</td>
                                    </tr>' . $get_pilihan_terbuka . '
                                </table>';
                        }
                    }
                    $get_pertanyaan_terbuka = implode("", $per_terbuka);
                } else {
                    $get_pertanyaan_terbuka = '';
                }
                // END PERTANYAAN TERBUKA =======================================================================



                $kategori_unsur = $this->db->get_where("kategori_unsur_pelayanan_$table_identity", ['id_pertanyaan_unsur' => $row->id_pertanyaan_unsur]);
                $rowspan = $kategori_unsur->num_rows() + 1;

                $array_kategori_unsur = [];
                foreach ($kategori_unsur->result() as $val) {
                    $array_kategori_unsur[] = '
                        <tr>
                            <td width="5%"></td>
                            <td width="55%">' . $val->nama_kategori_unsur_pelayanan . '</td>
                        </tr>';
                }

                $per_unsur[] = '<table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                        <tr>
                            <td rowspan="' . $rowspan . '" width="7%" style="text-align:center; font-size: 11px;">' . $row->nomor_unsur . '</td>

                            <td rowspan="' . $rowspan . '" width="33%"  style="text-align:left; font-size: 11px;">' . $row->isi_pertanyaan_unsur . '</td>

                            <td colspan="2" width="60%" style="text-align:center; background-color:#C7C6C1;">PILIHAN JAWABAN</td>
                        </tr>' . implode("", $array_kategori_unsur) . '
                    </table>' . $get_pertanyaan_terbuka;
            }

            $get_dimensi = implode("", $per_unsur);
        }



        //============================================= PERTANYAAN TERBUKA BAWAH =========================================
        if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {

            $pertanyaan_terbuka_bawah = $this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka, (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
    FROM pertanyaan_terbuka_$table_identity
    JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
    WHERE pertanyaan_terbuka_$table_identity.is_letak_pertanyaan = 2
    ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");

            if ($pertanyaan_terbuka_bawah->num_rows() > 0) {

                $per_terbuka_bawah = [];
                foreach ($pertanyaan_terbuka_bawah->result() as $value) {

                    if ($value->id_jenis_pilihan_jawaban == 2) {

                        $per_terbuka_bawah[] = '
                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                        <tr>
                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                            <td width="60%"></td>
                        </tr>
                    </table>';
                    } else {

                        $pilihan_terbuka_bawah = [];
                        foreach ($this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->result() as $get) {

                            $pilihan_terbuka_bawah[] = '
                    <tr>
                        <td width="5%"></td>
                        <td width="55%">' . $get->pertanyaan_ganda . '</td>
                    </tr>';
                        }


                        $get_pilihan_terbuka_bawah = implode("", $pilihan_terbuka_bawah);
                        $isi_terbuka_bawah = $this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->num_rows() + 1;



                        $per_terbuka_bawah[] = '
                            <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                <tr>
                                    <td rowspan="' . $isi_terbuka_bawah . '" width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>

                                    <td width="33%" rowspan="' . $isi_terbuka_bawah . '" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>

                                    <td colspan="2" width="60%" style="background-color:#C7C6C1; text-align:center;">PILIHAN JAWABAN</td>
                                </tr>' . $get_pilihan_terbuka_bawah . '
                            </table>';
                    }
                }
                $get_pertanyaan_terbuka_bawah = implode("", $per_terbuka_bawah);
            } else {
                $get_pertanyaan_terbuka_bawah = '';
            }
        } else {
            $get_pertanyaan_terbuka_bawah = '';
        };






        // =============================================== STATUS SARAN ================================================
        if ($manage_survey->is_saran == 1) {
            $is_saran = '<tr>
                            <td colspan="2" style="text-align:left; font-size: 11px;"><b>SARAN :</b>
                                <br>
                                <br>
                                <br>
                                <br>
                                <br>
                                <br>
                            </td>
                        </tr>';
        } else {
            $is_saran = '';
        }



        //GET WILAYAH SURVEI
        $nama_wilayah = [];
        foreach ($this->db->get("wilayah_survei_$table_identity")->result() as $row) {
            $nama_wilayah[] = '<li>' . $row->nama_wilayah . '</li>';
        }
        $get_wilayah = '<ul style="list-style-type:img|png|3|3|' . base_url() . 'assets/img/site/vector/check.png">' . implode("", $nama_wilayah) . '</ul>';
        $wilayah_survei = '<tr style="font-size: 11px;">
            <td width=" 30%" style="height:15px;">Wilayah Survei</td>
            <td width="70%">' . $get_wilayah . '</td>
            </tr>';





        // ============================================= GET HTML VIEW =============================================
        $html = '
        <table border="1" style="width: 100%;">
            <tr>
                <td>
                    <table border="0" style="width: 100%;" cellpadding="7">
                    <tr>
                        <td width="11%">' . $profil . '</td>
                        <td width="89%" style="font-size:12px; font-weight:bold;">' . strtoupper($title_1) . '<br>' . strtoupper($title_2) . '</td>
                    </tr>
                </table>
                </td>
            </tr>
        </table>
        

        <table  border="1" style="width: 100%;" cellpadding="7">
            <tr>
                <td style="text-align:center; font-size: 11px; font-family:Arial, Helvetica, sans-serif; height:35px;">Dalam rangka pemetaan perilaku pengguna ruang siber, Saudara dipercaya menjadi responden pada kegiatan survei
                    ini.<br>
                    Atas kesediaan Saudara kami sampaikan terima kasih dan penghargaan sedalam-dalamnya.</td>
            </tr>
        </table>


        <table border="1" style="width: 100%;" cellpadding="3">
            <tr>
                <td style="font-size: 11px; background-color: black; color:white; height:15px;"><b>DATA RESPONDEN</b> (Berikan tanda silang (x) sesuai jawaban Saudara pada kolom yang tersedia)
                </td>
            </tr>
        </table>
        <table border="1" style="width: 100%;" cellpadding="4">' . $wilayah_survei . $layanan_survei
            . $get_nama . '
        </table>
        
        
        <table style="width: 100%;" border="1" cellpadding="3">
            <tr>
                <td colspan="2" style="text-align:left; font-size: 11px; background-color: black; color:white;"><b>PENILAIAN TERHADAP UNSUR-UNSUR</b></td>
            </tr>

            <tr>
                <td colspan="2" style="text-align:left; font-size: 11px; background-color: black; color:white;">Berikan tanda silang (x) sesuai jawaban Saudara<!-- dan berikan alasan jika jawaban Saudara negatif(Tidak
                    atau Kurang Baik)--></td>
            </tr>
        </table>' . $get_pertanyaan_terbuka_atas . $get_dimensi . $get_pertanyaan_terbuka_bawah . '

        <table style="width: 100%;" border="1" cellpadding="5">' . $is_saran . '
            

            <tr>
                <td colspan="2" style="text-align:center; font-size: 11px;">Terima kasih atas kesediaan Saudara mengisi kuesioner tersebut di atas.
                Saran dan penilaian Saudara memberikan konstribusi yang sangat berarti dalam kegiatan pemetaan perilaku pengguna ruang siber.
                </td>
            </tr>
        </table>
    ';
        // var_dump($html);
        $pdf->writeHTML($html, true, false, true, false, '');


        $pdf->lastPage();
        $pdf->Output('Draf Kuesioner ' . $manage_survey->survey_name . '.pdf', 'I');
    }
}
