@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

<style type="text/css">
    [pointer-events="bounding-box"] {
        display: none
    }
</style>
@endsection

@section('content')

<div class="container-fluid">

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            <div class="card card-custom bgi-no-repeat gutter-b" style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)" data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            {{strtoupper($title)}}
                        </h3>
                        <a class="btn btn-secondary font-weight-bolder" data-toggle="collapse" href="#collapse1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bar-chart-line-fill" viewBox="0 0 16 16">
                                <path d="M11 2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h1V7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7h1V2z" />
                            </svg> Lihat Chart
                        </a>
                    </div>
                </div>
            </div>


            <div class="card card-body mb-10 collapse" id="collapse1">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="benner-tab" data-toggle="tab" href="#tab-chart-dimensi">Chart Dimensi</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="benner-tab" data-toggle="tab" href="#tab-chart-unsur">Chart Unsur</a>
                    </li>
                </ul>


                <div class=" tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="tab-chart-dimensi" role="tabpanel">
                        <div class="mt-5 mb-5">
                            <div id="chart-dimensi"></div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-chart-unsur" role="tabpanel">
                        <div class="mt-5 mb-5">
                            <div id="chart-unsur"></div>
                        </div>
                    </div>

                </div>
            </div>


            <div class="accordion accordion-solid accordion-panel accordion-svg-toggle mb-10" id="aspek">


                @foreach($ci->db->get("dimensi_$table_identity")->result() as $get)
                <div class="card shadow p-6">
                    <div class="card-header" id="faqHeading1">
                        <div class="card-title font-size-h5 text-dark collapsed" data-toggle="collapse" data-target="#dimensi_{{$get->id}}" role="button">
                            <div class="card-label font-weight-bold">{{$get->kode . '. ' . $get->dimensi}}</div>
                            <span class="svg-icon svg-icon-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                        <polygon points="0 0 24 0 24 24 0 24" />
                                        <path d="M12.2928955,6.70710318 C11.9023712,6.31657888 11.9023712,5.68341391 12.2928955,5.29288961 C12.6834198,4.90236532 13.3165848,4.90236532 13.7071091,5.29288961 L19.7071091,11.2928896 C20.085688,11.6714686 20.0989336,12.281055 19.7371564,12.675721 L14.2371564,18.675721 C13.863964,19.08284 13.2313966,19.1103429 12.8242777,18.7371505 C12.4171587,18.3639581 12.3896557,17.7313908 12.7628481,17.3242718 L17.6158645,12.0300721 L12.2928955,6.70710318 Z" fill="#000000" fill-rule="nonzero" />
                                        <path d="M3.70710678,15.7071068 C3.31658249,16.0976311 2.68341751,16.0976311 2.29289322,15.7071068 C1.90236893,15.3165825 1.90236893,14.6834175 2.29289322,14.2928932 L8.29289322,8.29289322 C8.67147216,7.91431428 9.28105859,7.90106866 9.67572463,8.26284586 L15.6757246,13.7628459 C16.0828436,14.1360383 16.1103465,14.7686056 15.7371541,15.1757246 C15.3639617,15.5828436 14.7313944,15.6103465 14.3242754,15.2371541 L9.03007575,10.3841378 L3.70710678,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.000003, 11.999999) rotate(-270.000000) translate(-9.000003, -11.999999)" />
                                    </g>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div id="dimensi_{{$get->id}}" class="collapse" aria-labelledby="faqHeading1" data-parent="#aspek">
                        <div class="card-body pt-3 font-weight-normal text-dark-50">

                            @foreach($ci->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => 0, 'id_dimensi' => $get->id])->result() as $key => $row)

                            @php
                            $unsur = $ci->db->query("SELECT
                            pertanyaan_unsur_pelayanan_$table_identity.id,
                            nomor_unsur,
                            IF(id_parent = 0, id_unsur_pelayanan, id_parent) AS id_parent,

                            (SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) AS sum_skor,
                            (SELECT AVG(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden WHERE is_submit = 1 && id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id) AS avg_skor

                            FROM pertanyaan_unsur_pelayanan_$table_identity
                            JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan =
                            unsur_pelayanan_$table_identity.id
                            WHERE IF(id_parent = 0, id_unsur_pelayanan, id_parent) = $row->id");
                            @endphp

                            <div class="card card-body mb-5 shadow" data-aos="fade-down">
                                <span class="font-weight-bolder text-primary mt-5">{{strtoupper($row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan)}}</span>
                                <hr>

                                <div class="table-responsive mt-5">
                                    <table id="table_{{$row->nomor_unsur}}" class="table table-bordered table-hover" cellspacing="0" width="100%" style="font-size: 12px;">
                                        <thead class="bg-secondary">
                                            <tr>
                                                <th width="5%">No.</th>
                                                <th>Responden</th>

                                                @foreach ($unsur->result() as $val4)
                                                <th>{{$val4->nomor_unsur}}</th>
                                                @endforeach

                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>


                                <div class="table-responsive mt-3">
                                    <div class="table-responsive mt-5">
                                        <table width="100%" class="table table-bordered" style="font-size: 12px;">
                                            <tr align="center">
                                                <th></th>
                                                @foreach ($unsur->result() as $val1)
                                                <th class="bg-secondary">{{$val1->nomor_unsur}}</th>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <th class="bg-secondary">TOTAL</th>
                                                @foreach ($unsur->result() as $val2)
                                                <td class="text-center">{{ROUND($val2->sum_skor)}}</td>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <th class="bg-secondary">Rata-Rata</th>

                                                <?php
                                                $nilai_tertimbang[$key] = 0;
                                                $nilai_bobot[$key] = [];
                                                foreach ($unsur->result() as $val3) {
                                                    $nilai_bobot[$key][] = $val3->avg_skor;
                                                    $nilai_tertimbang[$key] = array_sum($nilai_bobot[$key]) / count($nilai_bobot[$key]);
                                                    $ikp[$key] = $nilai_tertimbang[$key] * $skala_likert;
                                                    ?>
                                                    <td class="text-center">{{ROUND($val3->avg_skor,3)}}</td>
                                                <?php } ?>
                                            </tr>


                                            <tr>
                                                <th class="bg-secondary">Nilai Rata2 Tertimbang</th>
                                                <th colspan="{{$unsur->num_rows()}}">{{ROUND($nilai_tertimbang[$key], 3)}}</th>
                                            </tr>

                                            <tr>
                                                <th class="bg-secondary">IKP</th>
                                                <th colspan="{{$unsur->num_rows()}}">{{ROUND($ikp[$key], 2)}}</th>
                                            </tr>


                                            <?php
                                            foreach ($definisi_skala->result() as $obj) {
                                                if ($ikp[$key] <= $obj->range_bawah && $ikp[$key] >= $obj->range_atas) {
                                                    $kategori[$key] = $obj->kategori;
                                                    $mutu[$key] = $obj->mutu;
                                                }
                                            }

                                            if ($ikp[$key] <= 0) {
                                                $kategori[$key] = 'NULL';
                                                $mutu[$key] = 'NULL';
                                            }
                                            ?>


                                            <tr>
                                                <th class="bg-secondary">MUTU PELAYANAN</th>
                                                <th colspan="{{$unsur->num_rows()}}">{{$mutu[$key]}}</th>
                                            </tr>

                                            <tr>
                                                <th class="bg-secondary">KATEGORI</th>
                                                <th colspan="{{$unsur->num_rows()}}">{{$kategori[$key]}}</th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach





                        </div>
                    </div>
                </div>
                @endforeach


            </div>




        </div>
    </div>

</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>

<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.accessibility.js">
</script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.candy.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.carbon.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fint.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.gammel.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.ocean.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.umber.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.zune.js"></script>




<script>
    FusionCharts.ready(function() {
        var myChart = new FusionCharts({
            type: "column3d",
            renderAt: "chart-dimensi",
            "width": "100%",
            // "height": "100%",
            dataFormat: "json",
            dataSource: {
                chart: {
                    caption: "Indeks Per Dimensi Survei",
                    // yaxisname: "Annual Income",
                    showvalues: "1",
                    "decimals": "2",
                    theme: "umber",
                    "bgColor": "#ffffff",
                },
                data: [

                    <?php foreach ($chart_dimensi->result() as $row) { ?> {
                            label: "<?= $row->dimensi ?>",
                            value: <?= $row->skor * $skala_likert ?>
                        },
                    <?php } ?>
                ]
            }
        });
        myChart.render();
    });
</script>


<script>
    FusionCharts.ready(function() {
        var myChart = new FusionCharts({
            type: "column3d",
            renderAt: "chart-unsur",
            "width": "100%",
            // "height": "100%",
            dataFormat: "json",
            dataSource: {
                chart: {
                    caption: "Indeks Per Unsur Survei",
                    // yaxisname: "Annual Income",
                    showvalues: "1",
                    "decimals": "2",
                    theme: "umber",
                    "bgColor": "#ffffff",
                },
                data: [

                    <?php foreach ($chart_unsur->result() as $row) { ?> {
                            label: "<?= $row->nomor_unsur ?>",
                            value: <?= $row->rata_rata * $skala_likert ?>
                        },
                    <?php } ?>
                ]
            }
        });
        myChart.render();
    });
</script>


@foreach($ci->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => 0])->result() as $row)
<script>
    $(document).ready(function() {
        table = $('#table_{{$row->nomor_unsur}}').DataTable({

            "processing": true,
            "serverSide": true,

            "lengthMenu": [
                [5, 10, 25, 50, 100, -1],
                [5, 10, 25, 50, 100, "Semua data"]
            ],
            "pageLength": 5,
            "order": [],
            "language": {
                "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> ',
            },
            "ajax": {
                "url": "{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/tabulasi-aspek/ajax-list/' . $row->id}}",
                "type": "POST",
                "data": function(data) {}
            },

            "columnDefs": [{
                "targets": [-1],
                "orderable": false,
            }, ],

        });
    });
</script>
@endforeach
@endsection