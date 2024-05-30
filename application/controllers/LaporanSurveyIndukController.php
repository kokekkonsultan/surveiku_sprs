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


class LaporanSurveyIndukController extends CI_Controller
{

    function  __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            $this->session->set_flashdata('message_warning', 'You must be an admin to view this page');
            redirect('auth', 'refresh');
        }
        $this->load->helper('url');

        $this->load->model('DataPerolehanPerBagian_model', 'models');
    }

    public function index()
    {
        $this->data = [];
        $this->data['title'] = 'Laporan Survey';

        return view('laporan_survey_induk/index', $this->data);
    }

    public function ajax_list()
    {
        // $klien_induk = $this->db->get_where("pengguna_klien_induk", array('id_user' => $this->session->userdata('user_id')))->row();
        // $parent = implode(", ", unserialize($klien_induk->cakupan_induk));
        $parent = '';
        $n = 0;
        foreach ($this->db->query("SELECT id FROM users WHERE id_parent_induk = '" . $this->session->userdata('user_id') . "'")->result() as $data) {
            $n++;
            if ($n != 1) {
                $parent .= ', ';
            }
            $parent .= $data->id;
        }

        $list = $this->models->get_datatables($parent);
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $value) {

            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $value->first_name . ' ' . $value->last_name;
            // $row[] = '<a class="btn btn-primary" href="' . base_url() . 'laporan-induk/' . $value->slug . '/download-docx" target="_blank"><i class="fa fa-file-word"></i></a>';
            $row[] = '<a class="btn btn-danger" href="' . base_url() . 'laporan-induk/' . $value->slug . '/download-pdf" target="_blank"><i class="fa fa-file-pdf"></i></a>
            <a class="btn btn-primary" href="' . base_url() . 'laporan-induk/' . $value->slug . '/download-docx" target="_blank"><i class="fa fa-file-word"></i></a>';

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


    public function download_pdf($id)
    {
        $this->data = [];
        $this->data['title'] = "Laporan Survey";

        $this->data['manage_survey'] = $this->db->get_where("manage_survey", ['slug' => $id])->row();
        $this->data['table_identity'] = $this->data['manage_survey']->table_identity;
        $table_identity = $this->data['table_identity'];


        foreach ($this->db->query("SELECT * FROM jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1")->result() as $get) {

            $array_query[] = "UNION ALL
            SELECT * FROM kategori_unsur_pelayanan_$table_identity WHERE nomor_kategori_unsur_pelayanan IN (" . implode(",", unserialize($get->skor_jawaban)) . ") && id_pertanyaan_unsur = $get->id_pertanyaan_unsur";
        }
        $this->data['union_tabel'] = implode(" ", $array_query);



        $this->load->library('pdfgenerator');
        $file_pdf = 'Laporan ' . $this->data['manage_survey']->organisasi;
        $paper = 'A4';
        $orientation = "potrait";
        $html = $this->load->view('laporan_survey_induk/cetak', $this->data, true);
        $this->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);
        // $this->load->view('laporan_survey_induk/cetak', $this->data);
    }


    public function download_docx($id)
    {
        $this->data = [];
        $this->data['title'] = "Laporan Survey";

        $manage_survey = $this->db->get_where("manage_survey", ['slug' => $id])->row();
        $users = $this->db->get_where("users", ['id' => $manage_survey->id_user])->row();
        $table_identity = $manage_survey->table_identity;



        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        PhpOffice\PhpWord\Settings::setDefaultFontSize(11);
        // $phpWord->addParagraphStyle('Heading2', array('alignment' => 'center'));
        // $fontStyleName = 'rStyle';
        // $phpWord->addFontStyle($fontStyleName, array('name' => 'Calibri', 'size' => 11, 'allCaps' => true));
        $paragraphStyleName = 'pStyle';
        $phpWord->addParagraphStyle($paragraphStyleName, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 100));
        $section = $phpWord->addSection();




        // Add header for all other pages
        $subsequent = $section->addHeader();
        $subsequent->addImage(
            base_url() . 'assets/klien/foto_profile/profil_40.png',
            array(
                'positioning'        => 'relative',
                'marginTop'          => 0,
                'marginLeft'         => 0,
                'height'             => 44,
                'wrappingStyle'      => 'behind',
                'wrapDistanceRight'  => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(),
                'wrapDistanceBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToPoint(),
            )
        );
        $subsequent->addText('                      L A P O R A N  A K H I R', array('name' => 'Arial', 'size' => 9, 'bold' => true, 'color' => '91C991'), array('spaceAfter' => 50));
        $subsequent->addText(strtoupper('                            Survei Pemetaan Perilaku Pengguna Ruang Siber'), array('name' => 'Arial', 'size' => 7), array('spaceAfter' => 100));
        $subsequent->addLine(['weight' => 1, 'width' => 450, 'height' => 0]);
        //  //END ==========================================================================================================



        // // Add footer ==========================================================================================================
        $footer = $section->addFooter();
        $footer->addLine(['weight' => 1, 'width' => 450, 'height' => 0]);
        $footer->addPreserveText('{PAGE}', null, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
        // //END ==================================================================================================================



        // // CSS HTML ==========================================================================================================
        $content_paragraph = 'text-align: justify; text-indent: 30pt;';
        $abjad = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $no_table = 1;
        $no_gambar = 1;



        $htmllabel = '
         <table style="width: 100%;" cellpadding="3">
             <tr>
                 <td style="text-align: center; font-weight: bold; font-size:16px;">LAPORAN SURVEI PEMETAAN PERILAKU PENGGUNA RUANG SIBER<br/>PROVINSI ' . strtoupper($users->last_name) . '<br/><br/></td>
             </tr>
        </table>';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmllabel, false, false);


        $htmlA = '
         <table style="width: 100%; font-size:13px; line-height: 1.4;">
            <tr>
                <td width="3%"><b>A.</b></td>
                <td><b>Hasil Analisis Survei Pemetaan Perilaku Pengguna Ruang Siber</b></td>
            </tr>

            <tr>
                <td width="3%"></td>
                <td>
                    <p style="' . $content_paragraph . '">Berikut merupakan hasil analisis Survei Pemetaan Perilaku Pengguna Ruang Siber di ' . $users->last_name . ' berdasarkan beberapa dimensi:</p>
                    <br/>
                </td>
            </tr>
            <tr>
                <td width="3%"><b>1.</b></td>
                <td><b>Karakteristik Responden</b></td>
            </tr>
        </table>
        ';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlA, false, false);



        foreach ($this->db->query("SELECT * FROM profil_responden_$table_identity WHERE jenis_isian = 1 && nama_alias != 'profil_13'")->result() as $prf => $prores) {

            $a = 1;
            foreach ($this->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$prores->nama_alias && is_submit = 1) AS perolehan,
                ROUND((((SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$prores->nama_alias && is_submit = 1) / (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1)) * 100), 2) AS persentase
                FROM kategori_profil_responden_$table_identity
                WHERE id_profil_responden = $prores->id")->result() as $kpr) {

                $nama_kelompok[$prf][] = '%27' . str_replace(' ', '+', $kpr->nama_kategori_profil_responden) . '+=+' . $kpr->persentase . '%%27';
                $jumlah_persentase[$prf][] = $kpr->persentase;
                $jumlah_perolehan[$prf][] = $kpr->perolehan;

                $array_profil[$prf][] = '<tr>
                        <td width="5%" align="center">' . $a++ . '</td>
                        <td width="55%">' . $kpr->nama_kategori_profil_responden . '</td>
                        <td width="20%" align="center">' . $kpr->perolehan . '</td>
                        <td width="20%" align="center">' . $kpr->persentase . '%</td>
                    </tr>';

                $array_text_profil[$prf][] = $kpr->persentase . '% atau ' . $kpr->perolehan . ' siswa ber' . strtolower($prores->nama_profil_responden) . ' ' . $kpr->nama_kategori_profil_responden;
            }




            $htmlprofilres1[$prf] = '
         <table style="width: 100%; font-size:13px; line-height: 1.4;">
            <tr>
                <td width="3%"></td>
                <td width="3%">' . $abjad[$prf] . '.</td>
                <td>' . $prores->nama_profil_responden . '</td>
            </tr>
            <tr>
                <td width="3%"></td>
                <td width="3%"></td>
                <td style="text-align:center;">Tabel ' . $no_table++ . '. Persentase ' . $prores->nama_profil_responden . '
                    <table width="100%" align="center" style="font-size:13px; border: 1px #000 solid;">
                        <tr>
                            <th width="5%" align="center" style="font-weight: bold;">No</th>
                            <th width="55%" align="center" style="font-weight: bold;">Kategori</th>
                            <th width="20%" align="center" style="font-weight: bold;">Jumlah</th>
                            <th width="20%" align="center" style="font-weight: bold;">Persentase</th>
                        </tr>
                        ' . implode("", $array_profil[$prf]) . '
                    </table>
                </td>
            </tr>
        </table>
        ';
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlprofilres1[$prf], false, false);


            $section->addImage("https://quickchart.io/chart?c={type:%27horizontalBar%27,data:{labels:[" . implode(",", $nama_kelompok[$prf]) . "],datasets:[{label:%27Dataset1%27,backgroundColor:%27rgb(255,159,64)%27,stack:%27Stack0%27,data:[" . implode(",", $jumlah_persentase[$prf]) . "],},],},options:{title:{display:false,text:%27Chart.jsBarChart-Stacked%27},legend:{display:false},plugins:{roundedBars:true},responsive:true,},}", array('width' => 300, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
            $section->addText('Grafik ' . $no_gambar++ . '. Persentase ' . $prores->nama_profil_responden, array('size' => 10), $paragraphStyleName);


            $htmlprofilres2[$prf] = '
         <table style="width: 100%; font-size:13px; line-height: 1.4;">
            <tr>
                <td width="3%"></td>
                <td width="3%"></td>
                <td><br/><p style="text-align: justify;">Berdasarkan tabel di atas, diketahui bahwa jumlah responden Survei Pemetaan Perilaku Pengguna Siber yaitu ' .  array_sum($jumlah_perolehan[$prf]) . ' siswa dengan rincian sebanyak ' . implode(" dan ", $array_text_profil[$prf]) . '.</p><br/></td>
            </tr>
        </table>';
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlprofilres2[$prf], false, false);
        }








        $no_dimensi = 2;
        foreach ($this->db->get("dimensi_$table_identity")->result() as $dm => $dmns) {

            $htmldimensi[$dm] = '
            <table style="width: 100%; font-size:13px; line-height: 1.4;">
                <tr>
                    <td width="3%"><b>' . $no_dimensi++ . '.</b></td>
                    <td><b>' . $dmns->dimensi . '</b></td>
                </tr>
            </table>
            ';
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmldimensi[$dm], false, false);


            foreach ($this->db->query("SELECT * FROM unsur_pelayanan_$table_identity WHERE id_dimensi = $dmns->id && id_parent = 0")->result() as $up => $unspel) {

                $htmlunsur[$dm][$up] = '
                <table style="width: 100%; font-size:13px; line-height: 1.4;">
                    <tr>
                        <td width="3%"></td>
                        <td width="3%">' . $abjad[$up] . '.</td>
                        <td>' . $unspel->nama_unsur_pelayanan . '</td>
                    </tr>
                </table>
                ';
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlunsur[$dm][$up], false, false);


                //JIKA TIDAK MEMILIKI SUB
                if ($this->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => $unspel->id])->num_rows() == 0) {

                    $c = 1;
                    foreach ($this->db->query("SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity
                        JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,
                        (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL))
                        FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

                        FROM kategori_unsur_pelayanan_$table_identity
                        WHERE id_unsur_pelayanan = $unspel->id")->result() as $katunsur) {

                        $array_nama_unsur_kup[$dm][$up][] = '%27' . str_replace(' ', '+', $katunsur->nama_kategori_unsur_pelayanan) .
                            '+=+' . ROUND(($katunsur->perolehan / $katunsur->jumlah_pengisi) * 100, 2) . '%%27';
                        $array_persentase_kup[$dm][$up][] = ROUND(($katunsur->perolehan / $katunsur->jumlah_pengisi) * 100, 2);

                        $array_kup[$dm][$up][] = '<tr>
                                            <td width="5%" align="center">' . $c++ . '</td>
                                            <td width="55%">' . $katunsur->nama_kategori_unsur_pelayanan . '</td>
                                            <td width="20%" align="center">' . $katunsur->perolehan . '</td>
                                            <td width="20%" align="center">' . ROUND(($katunsur->perolehan / $katunsur->jumlah_pengisi) * 100, 2) . '%</td>
                                        </tr>';
                    }


                    $htmlunsur1[$dm][$up] = '
                    <table style="width: 100%; font-size:13px; line-height: 1.4;">
                        <tr>
                            <td width="3%"></td>
                            <td width="3%"></td>
                            <td style="text-align:center;">Tabel ' . $no_table++ . '. Persentase ' . $unspel->nama_unsur_pelayanan . '
                                <table width="100%" align="center" style="font-size:13px; border: 1px #000 solid;">
                                    <tr>
                                        <th width="5%" align="center" style="font-weight: bold;">No</th>
                                        <th width="55%" align="center" style="font-weight: bold;">Kategori</th>
                                        <th width="20%" align="center" style="font-weight: bold;">Jumlah</th>
                                        <th width="20%" align="center" style="font-weight: bold;">Persentase</th>
                                    </tr>'
                        . implode("", $array_kup[$dm][$up]) .
                        '</table>
                            </td>
                        </tr>
                    </table>
                    ';
                    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlunsur1[$dm][$up], false, false);


                    $section->addImage("https://quickchart.io/chart?c={type:%27horizontalBar%27,data:{labels:[" . implode(",", $array_nama_unsur_kup[$dm][$up]) . "],datasets:[{label:%27Dataset1%27,backgroundColor:%27rgb(255,159,64)%27,stack:%27Stack0%27,data:[" . implode(",", $array_persentase_kup[$dm][$up]) . "],},],},options:{title:{display:false,text:%27Chart.jsBarChart-Stacked%27},legend:{display:false},plugins:{roundedBars:true},responsive:true,},}", array('width' => 300, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                    $section->addText('Grafik ' . $no_gambar++ . '. Persentase ' . $unspel->nama_unsur_pelayanan, array('size' => 10), $paragraphStyleName);



                    $kategori_tertinggi[$dm][$up] = $this->db->query("SELECT *
                    FROM (
                    SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,

                    (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL)) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

                    FROM kategori_unsur_pelayanan_$table_identity
                    WHERE id_unsur_pelayanan = $unspel->id
                    ) ktg_$table_identity ORDER BY perolehan DESC LIMIT 1")->row();


                    $kategori_terendah[$dm][$up] = $this->db->query("SELECT *
                    FROM (
                    SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,

                    (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL)) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

                    FROM kategori_unsur_pelayanan_$table_identity
                    WHERE id_unsur_pelayanan = $unspel->id
                    ) ktg_$table_identity ORDER BY perolehan ASC LIMIT 1")->row();



                    $htmlunsur2[$dm][$up] = '
                     <table style="width: 100%; font-size:13px; line-height: 1.4;">
                        <tr>
                            <td width="3%"></td>
                            <td width="3%"></td>
                            <td><br/><p style="text-align: justify;">' .
                        $unspel->text_1 . $kategori_tertinggi[$dm][$up]->nama_kategori_unsur_pelayanan .
                        $unspel->text_2 . ROUND(($kategori_tertinggi[$dm][$up]->perolehan / $kategori_tertinggi[$dm][$up]->jumlah_pengisi) * 100, 2) .
                        $unspel->text_3 . $kategori_tertinggi[$dm][$up]->perolehan .
                        $unspel->text_4 . $kategori_terendah[$dm][$up]->nama_kategori_unsur_pelayanan .
                        $unspel->text_5 . ROUND(($kategori_terendah[$dm][$up]->perolehan / $kategori_terendah[$dm][$up]->jumlah_pengisi) * 100, 2) .
                        $unspel->text_6 . $kategori_terendah[$dm][$up]->perolehan .
                        ' siswa.</p><br/></td>
                        </tr>
                    </table>';
                    \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlunsur2[$dm][$up], false, false);




                    //CEK TERBUKA ADA ATAU TIDAK
                    if ($this->db->get_where("pertanyaan_terbuka_$table_identity", ['id_unsur_pelayanan' => $unspel->id])->num_rows() > 0) {

                        foreach ($this->db->query("SELECT * FROM pertanyaan_terbuka_$table_identity
                        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
                        WHERE id_unsur_pelayanan = $unspel->id")->result() as $pt => $p_ter) {

                            //CEK TERBUKA PILIHAN GANDA ATAU TIDAK
                            if ($p_ter->id_jenis_pilihan_jawaban == 1) {

                                $d = 1;
                                foreach ($this->db->query("SELECT *,
                                    (SELECT COUNT(*) FROM survey_$table_identity JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE survey_$table_identity.is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,

                                    (SELECT COUNT(*) FROM survey_$table_identity JOIN responden_$table_identity ON survey_$table_identity.id_responden = responden_$table_identity.id JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban != '' ) AS jumlah_survei

                                    FROM isi_pertanyaan_ganda_$table_identity
                                    JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
                                    WHERE id_pertanyaan_terbuka = $p_ter->id_pertanyaan_terbuka && perincian_pertanyaan_terbuka_$table_identity.id_jenis_pilihan_jawaban = 1")->result() as $ipg) {


                                    $array_nama_ipg[$dm][$up][$pt][] = '%27' . str_replace(' ', '+', $ipg->pertanyaan_ganda) .
                                        '+=+' . ROUND(($ipg->perolehan / $ipg->jumlah_survei) * 100, 2) . '%%27';
                                    $array_persentase_ipg[$dm][$up][$pt][] = ROUND(($ipg->perolehan / $ipg->jumlah_survei) * 100, 2);

                                    $array_ipg[$dm][$up][$pt][] = '<tr>
                                                <td width="5%" align="center">' . $d++ . '</td>
                                                <td width="55%">' . $ipg->pertanyaan_ganda . '</td>
                                                <td width="20%" align="center">' . $ipg->perolehan . '</td>
                                                <td width="20%" align="center">' . ROUND(($ipg->perolehan / $ipg->jumlah_survei) * 100, 2) . '%</td>
                                            </tr>';
                                }



                                $htmlterbuka1[$dm][$up][$pt] = '
                                <table style="width: 100%; font-size:13px; line-height: 1.4;">
                                    <tr>
                                        <td width="3%"></td>
                                        <td width="3%"></td>
                                        <td style="text-align:center;">Tabel ' . $no_table++ . '. Persentase ' . $p_ter->nama_pertanyaan_terbuka . '
                                            <table width="100%" align="center" style="font-size:13px; border: 1px #000 solid;">
                                                <tr>
                                                    <th width="5%" align="center" style="font-weight: bold;">No</th>
                                                    <th width="55%" align="center" style="font-weight: bold;">Kategori</th>
                                                    <th width="20%" align="center" style="font-weight: bold;">Jumlah</th>
                                                    <th width="20%" align="center" style="font-weight: bold;">Persentase</th>
                                                </tr>
                                                ' . implode("", $array_ipg[$dm][$up][$pt]) . '
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                ';
                                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlterbuka1[$dm][$up][$pt], false, false);


                                $section->addImage("https://quickchart.io/chart?c={type:%27horizontalBar%27,data:{labels:[" . implode(",", $array_nama_ipg[$dm][$up][$pt]) . "],datasets:[{label:%27Dataset1%27,backgroundColor:%27rgb(255,159,64)%27,stack:%27Stack0%27,data:[" . implode(",", $array_persentase_ipg[$dm][$up][$pt]) . "],},],},options:{title:{display:false,text:%27Chart.jsBarChart-Stacked%27},legend:{display:false},plugins:{roundedBars:true},responsive:true,},}", array('width' => 300, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                                $section->addText('Grafik ' . $no_gambar++ . '. Persentase ' . $p_ter->nama_pertanyaan_terbuka, array('size' => 10), $paragraphStyleName);


                                $ipg_tertinggi[$dm][$up][$pt] = $this->db->query("SELECT *
                                FROM (
                                SELECT pertanyaan_ganda,
                                (SELECT COUNT(*) FROM survey_$table_identity JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE survey_$table_identity.is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,

                                (SELECT COUNT(*) FROM survey_$table_identity JOIN responden_$table_identity ON survey_$table_identity.id_responden = responden_$table_identity.id JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban != '' ) AS jumlah_survei

                                FROM isi_pertanyaan_ganda_$table_identity
                                JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
                                WHERE id_pertanyaan_terbuka = $p_ter->id_pertanyaan_terbuka
                                ) ipg_k
                                ORDER BY perolehan DESC LIMIT 1")->row();


                                $ipg_terendah[$dm][$up][$pt] = $this->db->query("SELECT *
                                FROM (
                                SELECT pertanyaan_ganda,
                                (SELECT COUNT(*) FROM survey_$table_identity JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE survey_$table_identity.is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,

                                (SELECT COUNT(*) FROM survey_$table_identity JOIN responden_$table_identity ON survey_$table_identity.id_responden = responden_$table_identity.id JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban != '' ) AS jumlah_survei

                                FROM isi_pertanyaan_ganda_$table_identity
                                JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
                                WHERE id_pertanyaan_terbuka = $p_ter->id_pertanyaan_terbuka
                                ) ipg_k
                                ORDER BY perolehan ASC LIMIT 1")->row();



                                $htmlterbuka2[$dm][$up][$pt] = '
                                <table style="width: 100%; font-size:13px; line-height: 1.4;">
                                    <tr>
                                        <td width="3%"></td>
                                        <td width="3%"></td>
                                        <td><br/><p style="text-align: justify;">' .
                                    $p_ter->text_1 . $ipg_tertinggi[$dm][$up][$pt]->pertanyaan_ganda .
                                    $p_ter->text_2 . ROUND(($ipg_tertinggi[$dm][$up][$pt]->perolehan / $ipg_tertinggi[$dm][$up][$pt]->jumlah_survei) * 100, 2) .
                                    $p_ter->text_3 . $ipg_tertinggi[$dm][$up][$pt]->perolehan .

                                    $p_ter->text_4 . $ipg_terendah[$dm][$up][$pt]->pertanyaan_ganda .
                                    $p_ter->text_5 . ROUND(($ipg_terendah[$dm][$up][$pt]->perolehan / $ipg_terendah[$dm][$up][$pt]->jumlah_survei) * 100, 2) .
                                    $p_ter->text_6 . $ipg_terendah[$dm][$up][$pt]->perolehan .
                                    ' siswa.</p><br/></td>
                                    </tr>
                                </table>';
                                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlterbuka2[$dm][$up][$pt], false, false);
                            } else {

                                $htmlterbuka1[$dm][$up][$pt] = '
                                <table style="width: 100%; font-size:13px; line-height: 1.4;">
                                    <tr>
                                        <td width="3%"></td>
                                        <td width="3%"></td>
                                        <td><p style="text-align: justify;">Hal-hal yang diketahui oleh siswa terkait larangan yang dilakukan di internet berdasarkan Undang-Undang Informasi dan Transaksi Elektronik (UU ITE) dapat dilihat pada tabel berikut.</p></td>
                                    </tr>
                                    <tr>
                                        <td width="3%"></td>
                                        <td width="3%"></td>
                                        <td style="text-align:center;">Tabel ' . $no_table++ . '. Persentase ' . $p_ter->nama_pertanyaan_terbuka . '
                                            <table width="100%" align="center" style="font-size:13px; border: 1px #000 solid;">
                                                <tr>
                                                    <th width="5%" align="center" style="font-weight: bold;">No</th>
                                                    <th width="70%" align="center" style="font-weight: bold;">Jawaban</th>
                                                    <th width="25%" align="center" style="font-weight: bold;">Persentase</th>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">1</td>
                                                    <td width="70%">Menyebarkan Video Asusila (Kesusilaan)</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">2</td>
                                                    <td width="70%">Judi Online (perjudian)</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">3</td>
                                                    <td width="70%">Penghinaan dan/atau pencemaran nama baik</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">4</td>
                                                    <td width="70%">Pemerasan dan/atau pengancaman</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">5</td>
                                                    <td width="70%">Menyebarkan berita bohong dan menyesatkan </td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">6</td>
                                                    <td width="70%">Ujaran kebencian atau permusuhan</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">7</td>
                                                    <td width="70%">Teror Online</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                                <tr>
                                                    <td width="5%" align="center">8</td>
                                                    <td width="70%">Meretas Akun Media Sosial Orang lain</td>
                                                    <td width="25%" align="center"></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                ';
                                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlterbuka1[$dm][$up][$pt], false, false);


                                $htmlterbuka2[$dm][$up][$pt] = '
                                <table style="width: 100%; font-size:13px; line-height: 1.4;">
                                    <tr>
                                        <td width="3%"></td>
                                        <td width="3%"></td>
                                        <td><br/><p style="text-align: justify;">Hal-hal yang siswa ketahui dilarang untuk dilakukan di internet berdasarkan UU ITE paling tinggi yaitu (.....) sebanyak (....) %, sedangkan paling rendah yaitu (.....) sebanyak (.....)%.</p><br/></td>
                                    </tr>
                                </table>';
                                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlterbuka2[$dm][$up][$pt], false, false);
                            }
                        }
                    }


                } else {


                    foreach ($this->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => $unspel->id])->result() as $sub_up => $sub_unsur) {



                        $e = 1;
                        foreach ($this->db->query("SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity
                        JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,
                        (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL))
                        FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

                        FROM kategori_unsur_pelayanan_$table_identity
                        WHERE id_unsur_pelayanan = $sub_unsur->id")->result() as $katsubunsur) {

                            $array_nama_subkup[$dm][$up][$sub_up][] = '%27' . str_replace(' ', '+', $katsubunsur->nama_kategori_unsur_pelayanan) .
                                '+=+' . ROUND(($katsubunsur->perolehan / $katsubunsur->jumlah_pengisi) * 100, 2) . '%%27';
                            $array_persentase_subkup[$dm][$up][$sub_up][] = ROUND(($katsubunsur->perolehan / $katsubunsur->jumlah_pengisi) * 100, 2);

                            $array_subkup[$dm][$up][$sub_up][] = '<tr>
                                            <td width="5%" align="center">' . $e++ . '</td>
                                            <td width="55%">' . $katsubunsur->nama_kategori_unsur_pelayanan . '</td>
                                            <td width="20%" align="center">' . $katsubunsur->perolehan . '</td>
                                            <td width="20%" align="center">' . ROUND(($katsubunsur->perolehan / $katsubunsur->jumlah_pengisi) * 100, 2) . '%</td>
                                        </tr>';
                        }

                        $htmlsubunsur[$dm][$up][$sub_up] = '
                        <table style="width: 100%; font-size:13px; line-height: 1.4;">
                            <tr>
                                <td width="3%"></td>
                                <td width="3%"></td>
                                <td style="text-align:center;">Tabel ' . $no_table++ . '. Persentase ' . $sub_unsur->nama_unsur_pelayanan . '
                                    <table width="100%" align="center" style="font-size:13px; border: 1px #000 solid;">
                                        <tr>
                                            <th width="5%" align="center" style="font-weight: bold;">No</th>
                                            <th width="55%" align="center" style="font-weight: bold;">Kategori</th>
                                            <th width="20%" align="center" style="font-weight: bold;">Jumlah</th>
                                            <th width="20%" align="center" style="font-weight: bold;">Persentase</th>
                                        </tr>
                                        ' . implode("", $array_subkup[$dm][$up][$sub_up]) . '
                                    </table>
                                </td>
                            </tr>
                        </table>';
                        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlsubunsur[$dm][$up][$sub_up], false, false);


                        $section->addImage("https://quickchart.io/chart?c={type:%27horizontalBar%27,data:{labels:[" . implode(",", $array_nama_subkup[$dm][$up][$sub_up]) . "],datasets:[{label:%27Dataset1%27,backgroundColor:%27rgb(255,159,64)%27,stack:%27Stack0%27,data:[" . implode(",", $array_persentase_subkup[$dm][$up][$sub_up]) . "],},],},options:{title:{display:false,text:%27Chart.jsBarChart-Stacked%27},legend:{display:false},plugins:{roundedBars:true},responsive:true,},}", array('width' => 300, 'ratio' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
                        $section->addText('Grafik ' . $no_gambar++ . '. Persentase ' . $sub_unsur->nama_unsur_pelayanan, array('size' => 10), $paragraphStyleName);



                        $kategori_tertinggi[$dm][$up][$sub_up] = $this->db->query("SELECT *
                        FROM (
                        SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,

                        (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL)) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

                        FROM kategori_unsur_pelayanan_$table_identity
                        WHERE id_unsur_pelayanan = $sub_unsur->id
                        ) ktg_$table_identity ORDER BY perolehan DESC LIMIT 1")->row();


                        $kategori_terendah[$dm][$up][$sub_up] = $this->db->query("SELECT *
                        FROM (
                        SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,

                        (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL)) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

                        FROM kategori_unsur_pelayanan_$table_identity
                        WHERE id_unsur_pelayanan = $sub_unsur->id
                        ) ktg_$table_identity ORDER BY perolehan ASC LIMIT 1")->row();



                        $htmlunsur2[$dm][$up] = '
                         <table style="width: 100%; font-size:13px; line-height: 1.4;">
                            <tr>
                                <td width="3%"></td>
                                <td width="3%"></td>
                                <td><br/><p style="text-align: justify;">' .
                            $sub_unsur->text_1 . $kategori_tertinggi[$dm][$up][$sub_up]->nama_kategori_unsur_pelayanan .
                            $sub_unsur->text_2 . ROUND(($kategori_tertinggi[$dm][$up][$sub_up]->perolehan / $kategori_tertinggi[$dm][$up][$sub_up]->jumlah_pengisi) * 100, 2) .
                            $sub_unsur->text_3 . $kategori_tertinggi[$dm][$up][$sub_up]->perolehan .
                            $sub_unsur->text_4 . $kategori_terendah[$dm][$up][$sub_up]->nama_kategori_unsur_pelayanan .
                            $sub_unsur->text_5 . ROUND(($kategori_terendah[$dm][$up][$sub_up]->perolehan / $kategori_terendah[$dm][$up][$sub_up]->jumlah_pengisi) * 100, 2) .
                            $sub_unsur->text_6 . $kategori_terendah[$dm][$up][$sub_up]->perolehan .
                            ' siswa.</p><br/></td>
                            </tr>
                        </table>';
                        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlunsur2[$dm][$up], false, false);
                    }
                }
            }
        }




        $htmlB = '
         <table style="width: 100%; font-size:13px; line-height: 1.4;">
            <tr>
                <td width="3%"><b>B.</b></td>
                <td><b>Kesimpulan</b></td>
            </tr>

            <tr>
                <td width="3%"></td>
                <td>
                    <p style="' . $content_paragraph . '">Berdasarkan pengumpulan data dan analisis pada hasil Survei Pemetaan Perilaku Pengguna Ruang Siber di ' . $users->last_name . ', maka diambil kesimpulan sebagai berikut:</p>
                    <br/>
                </td>
            </tr>
            <tr>
                <td width="3%"><b>1.</b></td>
                <td><b>Perilaku Pengguna Ruang Siber</b></td>
            </tr>
        </table>
        ';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlB, false, false);


        $f = 1;
        foreach ($this->db->query("SELECT *
         FROM unsur_pelayanan_$table_identity
         JOIN pertanyaan_unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan")->result() as $pup) {

            $k_tertinggi = $this->db->query("SELECT *
            FROM (
            SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,

            (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL)) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

            FROM kategori_unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = $pup->id_unsur_pelayanan
            ) ktg_$table_identity ORDER BY perolehan DESC LIMIT 1")->row();


            $k_terendah = $this->db->query("SELECT *
            FROM (
            SELECT *, (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,

            (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL)) FROM lap_jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS jumlah_pengisi

            FROM kategori_unsur_pelayanan_$table_identity WHERE id_unsur_pelayanan = $pup->id_unsur_pelayanan
            ) ktg_$table_identity ORDER BY perolehan ASC LIMIT 1")->row();


            $array_b1[] = '<tr>
                            <td width="6%" align="center">' . $f++ . '</td>
                            <td width="28%">' . $pup->nama_unsur_pelayanan . '</td>
                            <td width="22%" align="center">' . $k_tertinggi->nama_kategori_unsur_pelayanan . '</td>
                            <td width="11%" align="center">' . ROUND(($k_tertinggi->perolehan / $k_tertinggi->jumlah_pengisi) * 100, 2) . '%</td>
                            <td width="22%" align="center">' . $k_terendah->nama_kategori_unsur_pelayanan . '</td>
                            <td width="11%" align="center">' . ROUND(($k_terendah->perolehan / $k_terendah->jumlah_pengisi) * 100, 2) . '%</td>
                        </tr>';
        }



        $htmlB1 = '<table width="100%" style="font-size:13px; border: 1px #000 solid;">
            <tr>
                <th width="6%" align="center" style="font-weight: bold;">No</th>
                <th width="26%" align="center" style="font-weight: bold;">Pertanyaan</th>
                <th width="22%" align="center" style="font-weight: bold;">Jawaban Tertinggi</th>
                <th width="12%" align="center" style="font-weight: bold;">Persentase</th>
                <th width="22%" align="center" style="font-weight: bold;">Jawaban Terendah</th>
                <th width="12%" align="center" style="font-weight: bold;">Persentase</th>
            </tr>
            ' . implode("", $array_b1) . '
        </table>';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlB1, false, false);



        $htmlB2 = '
         <table style="width: 100%; font-size:13px; line-height: 1.4;">
            <tr>
                <td width="3%"></td>
                <td></td>
            </tr>
            <tr>
                <td width="3%"><b>2.</b></td>
                <td><b>Pertanyaan Tambahan</b></td>
            </tr>
        </table>
        ';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlB2, false, false);







        $g = 1;
        foreach ($this->db->query("SELECT * FROM pertanyaan_terbuka_$table_identity
        JOIN perincian_pertanyaan_terbuka_$table_identity ON pertanyaan_terbuka_$table_identity.id = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka
        WHERE id_jenis_pilihan_jawaban = 1")->result() as $ppt) {


            $ppt_tertinggi = $this->db->query("SELECT *
            FROM (
            SELECT pertanyaan_ganda,
            (SELECT COUNT(*) FROM survey_$table_identity JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE survey_$table_identity.is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,
            
            (SELECT COUNT(*) FROM survey_$table_identity JOIN responden_$table_identity ON survey_$table_identity.id_responden = responden_$table_identity.id JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban != '' ) AS jumlah_pengisi

            FROM isi_pertanyaan_ganda_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
            WHERE id_pertanyaan_terbuka = $ppt->id_pertanyaan_terbuka
            ) ipg_k ORDER BY perolehan DESC LIMIT 1")->row();


            $ppt_terendah = $this->db->query("SELECT *
            FROM ( SELECT pertanyaan_ganda,
            
            (SELECT COUNT(*) FROM survey_$table_identity JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON survey_$table_identity.id_responden = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE survey_$table_identity.is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban = isi_pertanyaan_ganda_$table_identity.pertanyaan_ganda) AS perolehan,
            
            (SELECT COUNT(*) FROM survey_$table_identity JOIN responden_$table_identity ON survey_$table_identity.id_responden = responden_$table_identity.id JOIN lap_jawaban_pertanyaan_terbuka_$table_identity ON responden_$table_identity.id = lap_jawaban_pertanyaan_terbuka_$table_identity.id_responden WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$table_identity.jawaban != '' ) AS jumlah_pengisi
            
            FROM isi_pertanyaan_ganda_$table_identity
            JOIN perincian_pertanyaan_terbuka_$table_identity ON isi_pertanyaan_ganda_$table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$table_identity.id
            WHERE id_pertanyaan_terbuka = $ppt->id_pertanyaan_terbuka
            ) ipg_k ORDER BY perolehan ASC LIMIT 1")->row();



            $array_b2[] = '<tr>
                            <td width="6%" align="center">' . $g++ . '</td>
                            <td width="28%">' . $ppt->nama_pertanyaan_terbuka . '</td>
                            <td width="22%" align="center">' . $ppt_tertinggi->pertanyaan_ganda . '</td>
                            <td width="11%" align="center">' . ROUND(($ppt_tertinggi->perolehan / $ppt_tertinggi->jumlah_pengisi) * 100, 2) . '%</td>
                            <td width="22%" align="center">' . $ppt_terendah->pertanyaan_ganda . '</td>
                            <td width="11%" align="center">' . ROUND(($ppt_terendah->perolehan / $ppt_terendah->jumlah_pengisi) * 100, 2) . '%</td>
                        </tr>';
        }





        $htmlB2tabel = '<table width="100%" style="font-size:13px; border: 1px #000 solid;">
            <tr>
                <th width="6%" align="center" style="font-weight: bold;">No</th>
                <th width="26%" align="center" style="font-weight: bold;">Pertanyaan</th>
                <th width="22%" align="center" style="font-weight: bold;">Jawaban Tertinggi</th>
                <th width="12%" align="center" style="font-weight: bold;">Persentase</th>
                <th width="22%" align="center" style="font-weight: bold;">Jawaban Terendah</th>
                <th width="12%" align="center" style="font-weight: bold;">Persentase</th>
            </tr>
            ' . implode("", $array_b2) . '
            <tr>
                <td width="6%" align="center">4</td>
                <td width="28%">Hal-hal yang siswa ketahui dilarang untuk dilakukan di internet berdasarkan UU ITE</td>
                <td width="22%" align="center"></td>
                <td width="11%" align="center"></td>
                <td width="22%" align="center"></td>
                <td width="11%" align="center"></td>
            </tr>
        </table>';
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlB2tabel, false, false);





        $filename = 'Laporan ' .  $manage_survey->organisasi;
        header('Content-Type: application/msword');
        header('Content-Disposition: attachment;filename="' . $filename . '.docx"');
        header('Cache-Control: max-age=0');
        $phpWord->save('php://output');
    }
}
