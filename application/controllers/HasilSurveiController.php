<?php
defined('BASEPATH') or exit('No direct script access allowed');

class HasilSurveiController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->data = [];
        $this->data['title'] = 'Detail Pertanyaan Unsur';

        $manage_survey = $this->db->get_where('manage_survey', array('slug' => $this->uri->segment(1)))->row();
        $this->data['manage_survey'] = $manage_survey;
        $table_identity = $manage_survey->table_identity;

        $this->data['user'] = $this->db->get_where('users', array('id' => $manage_survey->id_user))->row();
        $uuid_responden = $this->uri->segment(3);



        $this->_data_responden($table_identity, $uuid_responden); //LOAD DATA RESPONDEN
        $id_responden = $this->data['responden']->id_responden;

        $this->_pertanyaan_unsur($table_identity, $id_responden); //LOAD PERTANYAAN UNSUR
        $this->_pertanyaan_kualitatif($table_identity, $id_responden); //LOAD PERTANYAAN KUALITATIF

        $this->load->library('pdfgenerator');
        $this->data['title_pdf'] = $this->data['responden']->uuid_responden;
        $file_pdf = $this->data['responden']->uuid_responden;
        $paper = 'A4';
        $orientation = "potrait";

        $html = $this->load->view('hasil_survei/cetak', $this->data, true);

        $this->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);
    }

    public function _data_responden($table_identity, $uuid_responden)
    {
        $this->data['profil_responden'] = $this->db->query("SELECT * FROM profil_responden_$table_identity")->result();

        $data_profil = [];
        foreach ($this->data['profil_responden'] as $get) {
            if ($get->jenis_isian == 1) {

                $data_profil[] = "(SELECT nama_kategori_profil_responden FROM kategori_profil_responden_$table_identity WHERE responden_$table_identity.$get->nama_alias = kategori_profil_responden_$table_identity.id) AS $get->nama_alias";
            } else {
                $data_profil[] = $get->nama_alias;
            }
        }
        $query_profil = implode(",", $data_profil);

        $this->db->select("id_responden, waktu_isi, saran, responden_$table_identity.uuid AS uuid_responden, $query_profil");
        $this->db->from("responden_$table_identity");
        $this->db->join("survey_$table_identity", "responden_$table_identity.id = survey_$table_identity.id_responden");
        $this->db->where("responden_$table_identity.uuid = '$uuid_responden'");
        $this->data['responden'] = $this->db->get()->row();
    }

    public function _pertanyaan_unsur($table_identity, $id_responden)
    {
        $this->data['pertanyaan_unsur'] = $this->db->query("SELECT *, (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 1 ) AS pilihan_1,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 2 ) AS pilihan_2,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 3 ) AS pilihan_3,
        (SELECT nama_kategori_unsur_pelayanan FROM kategori_unsur_pelayanan_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && nomor_kategori_unsur_pelayanan = 4 ) AS pilihan_4 , (SELECT skor_jawaban FROM jawaban_pertanyaan_unsur_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && id_responden = $id_responden) AS skor_jawaban, (SELECT alasan_pilih_jawaban FROM jawaban_pertanyaan_unsur_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && id_responden = $id_responden) AS alasan_jawaban
        FROM pertanyaan_unsur_pelayanan_$table_identity
        JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
		");
    }

    public function _pertanyaan_kualitatif($table_identity, $id_responden)
    {
        $this->data['jawaban_kualitatif'] = $this->db->query("SELECT * FROM pertanyaan_kualitatif_$table_identity JOIN jawaban_pertanyaan_kualitatif_$table_identity ON pertanyaan_kualitatif_$table_identity.id = jawaban_pertanyaan_kualitatif_$table_identity.id_pertanyaan_kualitatif WHERE id_responden = $id_responden and pertanyaan_kualitatif_$table_identity.is_active = 1");
    }





    public function tcpdf()
    {
        //============================================= START NEW PDF BY TCPDF =============================================
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hanif');
        $pdf->SetTitle($this->uri->segment(3));
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage('P', 'A4');
        $uuid_responden = $this->uri->segment(3);

        //START QUERY
        $this->db->select('*');
        $this->db->from('manage_survey');
        $this->db->where('manage_survey.slug', $this->uri->segment(1));
        $manage_survey = $this->db->get()->row();
        $table_identity = $manage_survey->table_identity;
        $skala_likert = $manage_survey->skala_likert == 5 ? 5 : 4;

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('id=' . $manage_survey->id_user);
        $user = $this->db->get()->row();


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

        $profil_responden = $this->db->query("SELECT * FROM profil_responden_$table_identity ORDER BY IF(urutan != '',urutan,id) ASC")->result();

        $data_profil = [];
        foreach ($profil_responden as $get) {
            if ($get->jenis_isian == 1) {

                $data_profil[] = "(SELECT nama_kategori_profil_responden FROM kategori_profil_responden_$table_identity WHERE responden_$table_identity.$get->nama_alias = kategori_profil_responden_$table_identity.id) AS $get->nama_alias";
            } else {
                $data_profil[] = $get->nama_alias;
            }
        }
        $query_profil = implode(",", $data_profil);


        $responden = $this->db->query("SELECT responden_$table_identity.uuid AS uuid, id_responden, waktu_isi, saran, (SELECT nama_layanan FROM layanan_survei_$table_identity WHERE responden_$table_identity.id_layanan_survei = layanan_survei_$table_identity.id) AS nama_layanan,
        (SELECT nama_wilayah FROM wilayah_survei_$table_identity WHERE id_wilayah = wilayah_survei_$table_identity.id) AS nama_wilayah,
        $query_profil
        
        FROM responden_$table_identity
        JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden
        WHERE responden_$table_identity.uuid = '$uuid_responden'")->row();
        $id_responden = $responden->id_responden;


        $nama_profil = [];
        foreach ($profil_responden as $get_profil) {
            $isi_profil = $get_profil->nama_alias;

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

            if ((!isset($get_profil->id_profil)) || ($get_profil->id_profil == 0)) {
                $namaalias = $get_profil->nama_profil_responden;
            } else {
                $namaalias = '&nbsp;';
            }

            $nama_profil[] = '<tr style="font-size: 11px;"><td width="30%" style="height:15px;" valign="top">' . $namaalias . ' </td><td width="70%">' . $label_kiri . $responden->$isi_profil . $label_kanan . '</td></tr>';
        }
        $get_nama = implode("", $nama_profil);




        // CEK MENGGUNAKAN JENIS LAYANAN ATAU TIDAK
        if ($manage_survey->is_layanan_survei != 0) {
            $layanan_survei = '<tr style="font-size: 11px;">
                <td width=" 30%" style="height:15px;">Jenis Pelayanan yang diterima</td>
                <td width="70%">' . $responden->nama_layanan . '</td>
                </tr>';
        } else {
            $layanan_survei = '';
        }





        //======================================== PERTANYAAN TERBUKA ATAS =========================================
        if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {

            $pertanyaan_terbuka_atas = $this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka, (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
            FROM pertanyaan_terbuka_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            WHERE pertanyaan_terbuka_$table_identity.is_letak_pertanyaan = 1 && id_responden = $id_responden
            ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");

            if ($pertanyaan_terbuka_atas->num_rows() > 0) {

                $per_terbuka_atas = [];
                foreach ($pertanyaan_terbuka_atas->result() as $value) {

                    if ($value->id_jenis_pilihan_jawaban == 2) {

                        $jawaban_atas = $value->jawaban != '' ? implode("", unserialize($value->jawaban)) : '';
                        $per_terbuka_atas[] = '
                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                        <tr>
                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                            <td width="60%">' . $jawaban_atas . '</td>
                        </tr>
                    </table>';
                    } else {

                        $pilihan_terbuka_atas = [];
                        foreach ($this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->result() as $get) {

                            if (in_array($get->pertanyaan_ganda, unserialize($value->jawaban))) {
                                $jawaban_terbuka = '<b>X</b>';
                            } else {
                                $jawaban_terbuka = '';
                            };


                            $pilihan_terbuka_atas[] = '<tr>
                        <td width="5%" style="text-align:center">' . $jawaban_terbuka . '</td>
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

                            <td colspan="2" width="60%" style="text-align:center; background-color:#C7C6C1;"></td>
                                    
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
                foreach ($this->db->query("SELECT *,
                pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur,
                (SELECT skor_jawaban FROM jawaban_pertanyaan_unsur_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && id_responden = $responden->id_responden) AS skor_jawaban
                FROM pertanyaan_unsur_pelayanan_$table_identity
                JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
                WHERE unsur_pelayanan_$table_identity.id_dimensi = $dms->id")->result() as $row) {



                    //PERTANYAAN TERBUKA START============================================
                    if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {

                        $per_terbuka = [];
                        foreach ($this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka,
                        (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
                        FROM pertanyaan_terbuka_$table_identity
                        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        JOIN jawaban_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        WHERE id_unsur_pelayanan = $row->id_unsur_pelayanan && id_responden = $id_responden && jawaban_pertanyaan_terbuka_$table_identity.jawaban != ''
                        ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0")->result() as $value) {

                            if ($value->id_jenis_pilihan_jawaban == 2) {
                                $jawaban = $value->jawaban != '' ? implode("", unserialize($value->jawaban)) : '';
                                $per_terbuka[] = '
                                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                        <tr>
                                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                                            <td width="60%">' . $jawaban . '</td>
                                        </tr>
                                    </table>';
                            } else {


                                $pilihan_terbuka = [];
                                foreach ($this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->result() as $get) {

                                    if (in_array($get->pertanyaan_ganda, unserialize($value->jawaban))) {
                                        $jawaban_terbuka = '<b>X</b>';
                                    } else {
                                        $jawaban_terbuka = '';
                                    };

                                    $pilihan_terbuka[] = '<tr>
                                        <td width="5%" style="text-align:center;">' . $jawaban_terbuka . '</td>
                                        <td width="55%">' . $get->pertanyaan_ganda . '</td>
                                        </tr>';
                                }


                                $get_pilihan_terbuka = implode("", $pilihan_terbuka);
                                $isi = $this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->num_rows() + 1;


                                $per_terbuka[] = '
                                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                        <tr>
                                            <td rowspan="' . $isi . '" width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
            
                                            <td width="33%" rowspan="' . $isi . '" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
            
                                            <td colspan="2" width="60%" style="background-color:#C7C6C1; text-align:center;">PILIHAN JAWABAN</td>
                                            
                                        </tr>' . $get_pilihan_terbuka . '
                                    </table>';
                            }
                        }

                        $get_pertanyaan_terbuka = implode("", $per_terbuka);
                    } else {
                        $get_pertanyaan_terbuka = '';
                    }
                    //PERTANYAAN TERBUKA END============================================




                    $kategori_unsur = $this->db->get_where("kategori_unsur_pelayanan_$table_identity", ['id_pertanyaan_unsur' => $row->id_pertanyaan_unsur]);
                    $rowspan = $kategori_unsur->num_rows() + 1;

                    $array_kategori_unsur = [];
                    foreach ($kategori_unsur->result() as $val) {

                        $jawaban_unsur = in_array($val->nomor_kategori_unsur_pelayanan, unserialize($row->skor_jawaban)) ? '<b>X</b>' : '';
                        $array_kategori_unsur[] = '
                        <tr>
                            <td width="5%" align="center">' . $jawaban_unsur . '</td>
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
            foreach ($this->db->query("SELECT *,
                pertanyaan_unsur_pelayanan_$table_identity.id AS id_pertanyaan_unsur,
                (SELECT skor_jawaban FROM jawaban_pertanyaan_unsur_$table_identity WHERE id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id && id_responden = $responden->id_responden) AS skor_jawaban
                FROM pertanyaan_unsur_pelayanan_$table_identity
                JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id")->result() as $row) {



                //PERTANYAAN TERBUKA START============================================
                if (in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey))) {

                    $per_terbuka = [];
                    foreach ($this->db->query("SELECT *, perincian_pertanyaan_terbuka_$table_identity.id AS id_perincian_pertanyaan_terbuka,
                        (SELECT DISTINCT(dengan_isian_lainnya) FROM isi_pertanyaan_ganda_$table_identity WHERE isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id) AS dengan_isian_lainnya
                        FROM pertanyaan_terbuka_$table_identity
                        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        JOIN jawaban_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        WHERE id_unsur_pelayanan = $row->id_unsur_pelayanan && id_responden = $id_responden && jawaban_pertanyaan_terbuka_$table_identity.jawaban != ''
                        ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0")->result() as $value) {

                        if ($value->id_jenis_pilihan_jawaban == 2) {
                            $jawaban = $value->jawaban != '' ? implode("", unserialize($value->jawaban)) : '';
                            $per_terbuka[] = '
                                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                        <tr>
                                            <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                                            <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                                            <td width="60%">' . $jawaban . '</td>
                                        </tr>
                                    </table>';
                        } else {


                            $pilihan_terbuka = [];
                            foreach ($this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->result() as $get) {

                                if (in_array($get->pertanyaan_ganda, unserialize($value->jawaban))) {
                                    $jawaban_terbuka = '<b>X</b>';
                                } else {
                                    $jawaban_terbuka = '';
                                };

                                $pilihan_terbuka[] = '<tr>
                                        <td width="5%" style="text-align:center;">' . $jawaban_terbuka . '</td>
                                        <td width="55%">' . $get->pertanyaan_ganda . '</td>
                                        </tr>';
                            }


                            $get_pilihan_terbuka = implode("", $pilihan_terbuka);
                            $isi = $this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->num_rows() + 1;


                            $per_terbuka[] = '
                                    <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                                        <tr>
                                            <td rowspan="' . $isi . '" width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
            
                                            <td width="33%" rowspan="' . $isi . '" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
            
                                            <td colspan="2" width="60%" style="background-color:#C7C6C1; text-align:center;">PILIHAN JAWABAN</td>
                                            
                                        </tr>' . $get_pilihan_terbuka . '
                                    </table>';
                        }
                    }

                    $get_pertanyaan_terbuka = implode("", $per_terbuka);
                } else {
                    $get_pertanyaan_terbuka = '';
                }
                //PERTANYAAN TERBUKA END============================================




                $kategori_unsur = $this->db->get_where("kategori_unsur_pelayanan_$table_identity", ['id_pertanyaan_unsur' => $row->id_pertanyaan_unsur]);
                $rowspan = $kategori_unsur->num_rows() + 1;

                $array_kategori_unsur = [];
                foreach ($kategori_unsur->result() as $val) {

                    $jawaban_unsur = in_array($val->nomor_kategori_unsur_pelayanan, unserialize($row->skor_jawaban)) ? '<b>X</b>' : '';
                    $array_kategori_unsur[] = '
                        <tr>
                            <td width="5%" align="center">' . $jawaban_unsur . '</td>
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
            JOIN jawaban_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
            WHERE pertanyaan_terbuka_$table_identity.is_letak_pertanyaan = 2 && id_responden = $id_responden
            ORDER BY SUBSTR(nomor_pertanyaan_terbuka,2) + 0");

            if ($pertanyaan_terbuka_bawah->num_rows() > 0) {

                $per_terbuka_bawah = [];
                foreach ($pertanyaan_terbuka_bawah->result() as $value) {

                    if ($value->id_jenis_pilihan_jawaban == 2) {

                        $jawaban_bawah = $value->jawaban != '' ? implode("", unserialize($value->jawaban)) : '';
                        $per_terbuka_bawah[] = '
                        <table width="100%" style="font-size: 11px;" border="1" cellpadding="3">
                            <tr>
                                <td width="7%" style="text-align:center; font-size: 11px;">' . $value->nomor_pertanyaan_terbuka . '</td>
                                <td width="33%" style="text-align:left; font-size: 11px;">' . $value->isi_pertanyaan_terbuka . '</td>
                                <td width="60%">' . $jawaban_bawah . '</td>
                            </tr>
                        </table>';
                    } else {

                        $pilihan_terbuka_bawah = [];
                        foreach ($this->db->get_where("isi_pertanyaan_ganda_$table_identity", array('id_perincian_pertanyaan_terbuka' => $value->id_perincian_pertanyaan_terbuka))->result() as $get) {

                            if (in_array($get->pertanyaan_ganda, unserialize($value->jawaban))) {
                                $jawaban_terbuka = '<b>X</b>';
                            } else {
                                $jawaban_terbuka = '';
                            };


                            $pilihan_terbuka_bawah[] = '<tr>
                            <td width="5%" style="text-align:center;">' . $jawaban_terbuka . '</td>
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






        // ============================================ STATUS SARAN ============================================
        if ($manage_survey->is_saran == 1) {
            $is_saran = '<tr>
            <td colspan="2" style="text-align:left; font-size: 11px;"><b>SARAN :</b>
                <br><br>' . $responden->saran . '
                <br>
                </td>
        </tr>';
        } else {
            $is_saran = '';
        }





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
        <table border="1" style="width: 100%;" cellpadding="4">
            <tr style="font-size: 11px;">
                <td width=" 30%" style="height:15px;">Wilayah Survei</td>
                <td width="70%">' . $responden->nama_wilayah . '</td>
            </tr>' . $layanan_survei . $get_nama . '

			<--<tr style="font-size: 11px;">
                <td width=" 30%" style="height:15px;">Waktu Isi</td>
            <td width="70%">' . date("d-m-Y", strtotime($responden->waktu_isi)) . '</td>
            </tr>-->
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
        $pdf->Output("$responden->uuid.pdf", 'I');
    }
}

/* End of file DataPerolehanSurveiController.php */
/* Location: ./application/controllers/DataPerolehanSurveiController.php */
