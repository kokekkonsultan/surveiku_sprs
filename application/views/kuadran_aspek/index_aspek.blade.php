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

    .flex {
        display: flex;
        width: 100%;
        flex-direction: row;
    }

    .chart {
        min-width: 200px;
        height: 100%;
        width: 100%;
    }

    .highcharts-credits {
        display: none;
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
                    </div>
                </div>
            </div>




            @php
            $n = 1;
            @endphp
            @foreach($ci->db->get("aspek_$table_identity")->result() as $value)
            <h4 class="font-weight-bolder text-primary mt-5">A{{$n++ . '. ' . $value->nama_aspek}}</h4>
            <hr>
            <div class="accordion accordion-solid accordion-panel accordion-svg-toggle mb-10" id="aspek_{{$value->id}}">


                @foreach($ci->db->get_where("dimensi_$table_identity", ['id_aspek' => $value->id])->result() as $get)
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
                    <div id="dimensi_{{$get->id}}" class="collapse" aria-labelledby="faqHeading1" data-parent="#aspek_{{$value->id}}">
                        <div class="card-body pt-3 font-weight-normal text-dark-50">

                            @foreach($ci->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => 0,
                            'id_dimensi' => $get->id])->result() as $key => $row)

                            @php
                            $unsur = $ci->db->query("SELECT
                            pertanyaan_unsur_pelayanan_$table_identity.id,
                            nomor_unsur,
                            IF(id_parent = 0, id_unsur_pelayanan, id_parent) AS id_parent,

                            (SELECT AVG(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN
                            survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden =
                            survey_$table_identity.id_responden WHERE is_submit = 1 && id_pertanyaan_unsur =
                            pertanyaan_unsur_pelayanan_$table_identity.id) AS skor_persepsi,

                            (SELECT AVG(skor_jawaban) FROM jawaban_pertanyaan_harapan_$table_identity JOIN
                            survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden =
                            survey_$table_identity.id_responden WHERE is_submit = 1 && id_pertanyaan_unsur =
                            pertanyaan_unsur_pelayanan_$table_identity.id) AS skor_harapan

                            FROM pertanyaan_unsur_pelayanan_$table_identity
                            JOIN unsur_pelayanan_$table_identity ON
                            pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan =
                            unsur_pelayanan_$table_identity.id
                            WHERE IF(id_parent = 0, id_unsur_pelayanan, id_parent) = $row->id");
                            @endphp

                            <div class="card card-body mb-5 shadow" data-aos="fade-down">
                                <span class="font-weight-bolder text-primary mt-5">{{strtoupper($row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan)}}</span>
                                <hr>

                                <div id="kuadran_{{$row->nomor_unsur}}"></div>


                                <div class="table-responsive mt-3">
                                    <div class="table-responsive mt-5">
                                        <table width="100%" class="table table-bordered" style="font-size: 12px;">

                                            <tr align="center">
                                                <th></th>
                                                <th colspan="{{$unsur->num_rows()}}" class="text-center bg-dark text-white">PERSEPSI</th>
                                                <th colspan="{{$unsur->num_rows()}}" class="text-center bg-dark text-white">HARAPAN</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-secondary">Unsur & Harapan</th>
                                                @foreach ($unsur->result() as $val1)
                                                <th class="bg-secondary text-center">{{$val1->nomor_unsur}}</th>
                                                @endforeach

                                                @foreach ($unsur->result() as $val2)
                                                <th class="bg-secondary text-center">H{{substr($val1->nomor_unsur,1)}}</th>
                                                @endforeach
                                            </tr>

                                            <tr>
                                                <th class="bg-secondary">Rata-Rata</th>

                                                <?php
                                                $nilai_tertimbang_persepsi[$key] = 0;
                                                $nilai_bobot_persepsi[$key] = [];
                                                foreach ($unsur->result() as $val3) {
                                                    $nilai_bobot_persepsi[$key][] = $val3->skor_persepsi;
                                                    $nilai_tertimbang_persepsi[$key] = array_sum($nilai_bobot_persepsi[$key]) / count($nilai_bobot_persepsi[$key]);
                                                    ?>
                                                    <td class="text-center">{{ROUND($val3->skor_persepsi,3)}}</td>
                                                <?php } ?>


                                                <?php
                                                $nilai_tertimbang_harapan[$key] = 0;
                                                $nilai_bobot_harapan[$key] = [];
                                                foreach ($unsur->result() as $val4) {
                                                    $nilai_bobot_harapan[$key][] = $val4->skor_harapan;
                                                    $nilai_tertimbang_harapan[$key] = array_sum($nilai_bobot_harapan[$key]) / count($nilai_bobot_harapan[$key]);
                                                    ?>
                                                    <td class="text-center">{{ROUND($val4->skor_harapan,3)}}</td>
                                                <?php } ?>
                                            </tr>

                                            <tr>
                                                <th class="bg-secondary">Rata-Rata Akhir</th>
                                                <th colspan="{{$unsur->num_rows()}}" class="text-center">
                                                    {{ROUND($nilai_tertimbang_persepsi[$key], 3)}}</th>

                                                <th colspan="{{$unsur->num_rows()}}" class="text-center">
                                                    {{ROUND($nilai_tertimbang_harapan[$key], 3)}}</th>
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
            @endforeach




        </div>
    </div>

</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/highcharts/7.1.1/highcharts.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/react/16.8.6/umd/react.production.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/react-dom/16.8.6/umd/react-dom.production.min.js'></script>


@foreach($ci->db->get_where("unsur_pelayanan_$table_identity", ['id_parent' => 0])->result() as $obj => $row)

<?php
$nilai_persepsi[$obj] = [];
$nilai_harapan[$obj] = [];
$total_persepsi[$obj] = 0;
$total_harapan[$obj] = 0;
$data_chart[$obj] = [];

foreach ($ci->db->query("SELECT
    pertanyaan_unsur_pelayanan_$table_identity.id,
    nomor_unsur,
    nama_unsur_pelayanan,
    IF(id_parent = 0, id_unsur_pelayanan, id_parent) AS id_parent,

    (SELECT AVG(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity JOIN
    survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden =
    survey_$table_identity.id_responden WHERE is_submit = 1 && id_pertanyaan_unsur =
    pertanyaan_unsur_pelayanan_$table_identity.id) AS skor_persepsi,

    (SELECT AVG(skor_jawaban) FROM jawaban_pertanyaan_harapan_$table_identity JOIN
    survey_$table_identity ON jawaban_pertanyaan_harapan_$table_identity.id_responden =
    survey_$table_identity.id_responden WHERE is_submit = 1 && id_pertanyaan_unsur =
    pertanyaan_unsur_pelayanan_$table_identity.id) AS skor_harapan

    FROM pertanyaan_unsur_pelayanan_$table_identity
    JOIN unsur_pelayanan_$table_identity ON
    pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan =
    unsur_pelayanan_$table_identity.id
    WHERE IF(id_parent = 0, id_unsur_pelayanan, id_parent) = $row->id")->result() as $val0) {

$nilai_persepsi[$obj][] = $val0->skor_persepsi;
$nilai_harapan[$obj][] = $val0->skor_harapan;

$total_persepsi[$obj] = array_sum($nilai_persepsi[$obj]) / count($nilai_persepsi[$obj]);
$total_harapan[$obj] = array_sum($nilai_harapan[$obj]) / count($nilai_harapan[$obj]);

$data_chart[$obj][] = '{
                            x: ' . $val0->skor_persepsi . ',
                            y: ' . $val0->skor_harapan . ',
                            name: "' . $val0->nomor_unsur . '",
                            counter: "' . $val0->nomor_unsur . '. ' . $val0->nama_unsur_pelayanan . '<br>Persepsi = ' . $val0->skor_persepsi . ', Harapan = ' . $val0->skor_harapan . '"

                        }';

}
?>



<script>
    Highcharts.chart('kuadran_{{$row->nomor_unsur}}', {
        chart: {
            type: 'scatter',
            plotBorderWidth: 1,
            zoomType: 'xy',
            height: 350,
        },

        title: 'quadrant',
        legend: {
            enabled: false
        },
        tooltip: {
            pointFormat: '<span>{point.counter}</span>',
        },

        xAxis: {
            gridLineWidth: 0,
            startOnTick: true,
            endOnTick: true,
            crosshair: true,
            title: {
                text: 'PERFORMANCE'
            },
            plotLines: [{
                color: 'black',
                dashStyle: 'dot',
                width: 2,
                value: <?= $total_persepsi[$obj] ?>, //GARIS BATAS NILAI X
                zIndex: 3
            }],
        },

        yAxis: {
            gridLineWidth: 0,
            startOnTick: true,
            endOnTick: true,
            crosshair: true,
            title: {
                text: 'IMPORTANCE'
            },
            maxPadding: 0.2,
            plotLines: [{
                color: 'black',
                dashStyle: 'dot',
                width: 2,
                value: <?= $total_harapan[$obj] ?>, //GARIS BATAS NILAI Y
                label: {
                    align: 'right',
                    style: {
                        fontStyle: 'italic'
                    },
                    x: -10
                },
                zIndex: 3
            }],
        },
        plotOptions: {
            series: {
                dataLabels: {
                    defer: true,
                    enabled: true,
                    format: '{point.name}',
                    style: {
                        fontSize: '14px',
                        fontFamily: 'monospace',
                        color: 'black'
                        //  fontStyle: 'italic'
                    },
                }
            }
        },
        series: [{
            data: [<?= implode(", ", $data_chart[$obj]) ?>]
        }, {
            enableMouseTracking: false,
            linkedTo: 0,
            marker: {
                enabled: false
            },
            dataLabels: {
                defer: false,
                enabled: true,
                //  y: 20,
                style: {
                    fontSize: '14px',
                    fontFamily: 'monospace',
                    color: 'black',
                    fontStyle: 'italic'
                },
                format: 'Kuadran {point.name}'
            },
            keys: ['x', 'y', 'name'],
            data: [
                [<?php echo $total_persepsi[$obj] - 0.5 ?>,
                    <?php echo $total_harapan[$obj] + 0.5 ?>, 'I'
                ],
                [<?php echo $total_persepsi[$obj] + 0.5 ?>,
                    <?php echo $total_harapan[$obj] + 0.5 ?>, 'II'
                ],
                [<?php echo $total_persepsi[$obj] - 0.5 ?>,
                    <?php echo $total_harapan[$obj] - 0.5 ?>, 'III'
                ],
                [<?php echo $total_persepsi[$obj] + 0.5 ?>,
                    <?php echo $total_harapan[$obj] - 0.5 ?>, 'IV'
                ]
            ]
        }],

    });
</script>
@endforeach

@endsection