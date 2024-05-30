<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> <?= 'Laporan ' . $manage_survey->survey_name ?></title>

    <style>
    /* @page {
        margin: 0.2in 0.5in 0.2in 0.5in;
    } */

    /* body {
        padding: .4in;
    } */

    @page {
        margin: 100px 20px;
    }

    .content-paragraph {
        text-indent: 5%;
        text-align: justify;
        text-justify: inter-word;
        line-height: 1.5;
        margin-left: 76px;
        margin-right: 76px;

    }

    .content-narasi {
        text-align: justify;
        text-justify: inter-word;
        line-height: 1.5;
        margin-left: 76px;
        margin-right: 76px;

    }

    .content-list {
        text-indent: 10%;
        text-align: justify;
        text-justify: inter-word;
        line-height: 1.5;

    }

    .page-session {
        page-break-after: always;
        font-family: Calibri, sans-serif;
        margin: 0.2in 0.5in 0.2in 0.5in;
    }

    .page-session:last-child {
        page-break-after: never;
    }

    .table-list {
        border-collapse: collapse;
        font-family: sans-serif;

        text-align: center;
    }

    table,
    th,
    td {
        font-size: 13px;
        padding: 3px;
        line-height: 1.5;
    }

    li {
        padding: 4px;
        text-align: justify;
        font-family: sans-serif;
        font-size: 13px;
    }

    .td-th-list {
        border: 1px solid black;
        height: 20px;
    }

    header {
        position: fixed;
        top: -90px;
        left: 0px;
        right: 0px;
        /* background-color: lightblue; */
        height: 50px;
    }

    footer {
        position: fixed;
        bottom: -60px;
        left: 0px;
        right: 0px;
        /* background-color: lightblue; */
        height: 50px;
    }

    footer .page:after {
        content: counter(page, decimal);
    }

    input[type=checkbox] {
        display: inline;
    }

    .th-td-draf {
        border: 1px solid black;
        font-size: 11px;
        /* text-align:left; */
        height: 15px;
    }
    </style>
</head>

<body>

    <!-- COVER -->
    <!-- <div class="page-session">
        <div style="text-align:center;">
        </div>
    </div> -->

    <header>
        <table style="width: 90%; margin-left: auto; margin-right: auto;" class="table-list">
            <tr>
                <td style="width: 10%;">
                    <img src="<?= base_url() . 'assets/klien/foto_profile/200px.jpg' ?>" alt="Logo" width="70">
                </td>
                <td>
                    <div style="color:#DE2226; font-size:16px;">
                        <b>L A P O R A N</b>
                    </div>
                    Survei Perilaku Ruang Siber
                </td>
            </tr>
        </table>
        <hr>
    </header>

    <footer>
        <div style="text-align:center;">
            <hr>
            <?= strtoupper($manage_survey->organisasi) ?> -
            <?= date("Y") ?>
            <br>
            <p class="page"></p>
        </div>
    </footer>

    <main>
        <div class="page-session">

            <table style="width: 100%;" class="">
                <tr>
                    <td style=" font-weight: bold;">1. Profil Responden</td>
                </tr>
                <tr>
                    <td style="padding-left:1em;">Berikut merupakan karakteristik responden yang diperoleh dari Survei:
                    </td>
                </tr>
            </table>


            <ul>
                <?php
                foreach ($this->db->query("SELECT * FROM profil_responden_$table_identity WHERE jenis_isian = 1")->result() as $i => $row) {

                    $a = 1;
                    foreach ($this->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$row->nama_alias && is_submit = 1) AS perolehan, 
                    (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah_survei

                    FROM kategori_profil_responden_$table_identity
                    WHERE id_profil_responden = $row->id")->result() as $kpr) {


                        $nama_kpr[$i][] = '%27' . str_replace(' ', '+', $kpr->nama_kategori_profil_responden) . '(' . $kpr->perolehan . '),+' .  ROUND(($kpr->perolehan / $kpr->jumlah_survei) * 100, 2) . '%%27';
                        $persentase_kpr[$i][] = $kpr->perolehan;

                        $array_kpr[$i][] = '<tr>
                                                <td width="5%" class="td-th-list">' . $a++ . '</td>
                                                <td width="55%" class="td-th-list" align="left">' . $kpr->nama_kategori_profil_responden . '</td>
                                                <td width="20%" class="td-th-list">' . $kpr->perolehan . '</td>
                                                <td width="20%" class="td-th-list">' . ROUND(($kpr->perolehan / $kpr->jumlah_survei) * 100, 2) . '%</td>
                                            </tr>';
                    }?>


                <li><b><?= $row->nama_profil_responden ?></b>
                    <div style="text-align: center;">
                        <img src="https://quickchart.io/chart?c={type:%27outlabeledPie%27,data:{labels:[<?= implode(",", $nama_kpr[$i]) ?>],datasets:[{backgroundColor:[%27rgb(255,55,132)%27,%27rgb(54,162,235)%27,%27rgb(75,192,192)%27,%27rgb(247,120,37)%27,%27rgb(153,102,255)%27,%27rgb(247,77,82)%27,%27rgb(247,37,230)%27,%27rgb(37,247,156)%27,%27rgb(84,54,235)%27,%27rgb(247,255,102)%27],data:[<?= implode(",", $persentase_kpr[$i]) ?>],},],},options:{plugins:{legend:false,outlabels:{text:%27%l%27,color:%27white%27,stretch:35,font:{resizable:true,minSize:12,maxSize:18,},},},},}"
                            alt="" width="70%">

                    </div>
                    <br>
                    <table style="width: 100%;" class="table-list">
                        <tr style="background-color:#E4E6EF;">
                            <th width="5%" class="td-th-list">No</th>
                            <th width="55%" class="td-th-list">Kategori</th>
                            <th width="20%" class="td-th-list">Perolehan</th>
                            <th width="20%" class="td-th-list">Persentase</th>
                        </tr>
                        <?= implode("", $array_kpr[$i]) ?>
                    </table>
                    <br>
                </li>

                <?php } ?>
            </ul>

        </div>




        <div class="page-session">
            <table style="width: 100%;" class="">
                <tr>
                    <td width="3%"><b>2.</b></td>
                    <td><b>Dimensi</b></td>
                </tr>
                <tr>
                    <td width="3%"></td>
                    <td>Berikut merupakan Dimensi yang diperoleh dari Survei:</td>
                </tr>
            </table>




            <?php
            foreach ($this->db->query("SELECT * FROM dimensi_$table_identity")->result() as $j => $get) { ?>

            <table style="width: 100%;" class="">
                <tr>
                    <td width="3%"></td>
                    <td width="3%"><b><?= $get->kode ?>.</b></td>
                    <td><b><?= $get->dimensi ?></b></td>
                </tr>
            </table>

            <?php  
                foreach ($this->db->query("SELECT * FROM unsur_pelayanan_$table_identity
                JOIN pertanyaan_unsur_pelayanan_$table_identity ON unsur_pelayanan_$table_identity.id = pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan
                WHERE id_dimensi = $get->id")->result() as $k => $row) {


                $b = 1;
                foreach ($this->db->query("SELECT *, 
                (SELECT COUNT(skor_jawaban) FROM lap_jawaban_pertanyaan_unsur_$table_identity
                JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden 
                WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && kategori_unsur_pelayanan_$table_identity.nomor_kategori_unsur_pelayanan = lap_jawaban_pertanyaan_unsur_$table_identity.skor_jawaban && is_submit = 1) AS perolehan,
                
                (SELECT COUNT(IF(skor_jawaban != 0, 1, NULL))
                FROM lap_jawaban_pertanyaan_unsur_$table_identity
                JOIN survey_$table_identity ON lap_jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
                WHERE lap_jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = kategori_unsur_pelayanan_$table_identity.id_pertanyaan_unsur && is_submit = 1) AS total_survei
                
                FROM kategori_unsur_pelayanan_$table_identity
                WHERE id_unsur_pelayanan = $row->id_unsur_pelayanan")->result() as $value) {

                    $kup_persentase[$j][$k] = $value->perolehan > 0 ? ROUND(($value->perolehan / $value->total_survei) * 100, 2) : 0;

                    $nama_kup[$j][$k][] = '%27' . str_replace(' ', '+', $value->nama_kategori_unsur_pelayanan) . '(' . $value->perolehan . '),+' .  $kup_persentase[$j][$k] . '%%27';
                    $persentase_kup[$j][$k][] = $value->perolehan;

                    $array_kup[$j][$k][] = '<tr>
                                            <td width="5%" class="td-th-list">' . $b++ . '</td>
                                            <td width="55%" class="td-th-list" align="left">' . $value->nama_kategori_unsur_pelayanan . '</td>
                                            <td width="20%" class="td-th-list">' . $value->perolehan . '</td>
                                            <td width="20%" class="td-th-list">' . $kup_persentase[$j][$k] . '%</td>
                                        </tr>';
                }?>


            <table style="width: 100%;" class="">
                <tr>
                    <td width="3%"></td>
                    <td width="3%"></td>
                    <td width="3%"><b><?= $row->nomor_unsur ?>.</b></td>
                    <td><b><?= $row->nama_unsur_pelayanan ?></b></td>
                </tr>

                <tr>
                    <td width="3%"></td>
                    <td width="3%"></td>
                    <td width="3%"></td>
                    <td>
                        <div style="text-align: center;">
                            <img src="https://quickchart.io/chart?c={type:%27outlabeledPie%27,data:{labels:[<?= implode(",", $nama_kup[$j][$k]) ?>],datasets:[{backgroundColor:[%27rgb(255,55,132)%27,%27rgb(54,162,235)%27,%27rgb(75,192,192)%27,%27rgb(247,120,37)%27,%27rgb(153,102,255)%27,%27rgb(247,77,82)%27,%27rgb(247,37,230)%27,%27rgb(37,247,156)%27,%27rgb(84,54,235)%27,%27rgb(247,255,102)%27],data:[<?= implode(",", $persentase_kup[$j][$k]) ?>],},],},options:{plugins:{legend:false,outlabels:{text:%27%l%27,color:%27white%27,stretch:35,font:{resizable:true,minSize:12,maxSize:18,},},},},}"
                                alt="" width="70%">
                        </div>
                        <br>
                    </td>
                </tr>

                <tr>
                    <td width="3%"></td>
                    <td width="3%"></td>
                    <td width="3%"></td>
                    <td>
                        <table style="width: 100%;" class="table-list">
                            <tr style="background-color:#E4E6EF;">
                                <th width="5%" class="td-th-list">No</th>
                                <th width="55%" class="td-th-list">Kategori</th>
                                <th width="20%" class="td-th-list">Perolehan</th>
                                <th width="20%" class="td-th-list">Persentase</th>
                            </tr>
                            <?= implode("", $array_kup[$j][$k]) ?>
                        </table>
                    </td>
                </tr>
            </table>





            <?php } ?>
            <?php } ?>

        </div>
    </main>


</body>

</html>