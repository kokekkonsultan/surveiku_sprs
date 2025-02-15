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

.dataTables_length {
    display: none
}

.dataTables_filter {
    display: none
}
</style>
@endsection

@section('content')

<div class="container-fluid">
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5" data-aos="fade-down">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            <div class="card card-custom bgi-no-repeat gutter-b"
                style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)"
                data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            REKAPITULASI JAWABAN PERTANYAAN TAMBAHAN
                        </h3>

                        <!-- <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn
                        btn-primary btn-sm font-weight-bold dropdown-toggle" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-print"></i> Download Rekap
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">

                                <a class="dropdown-item text-secondary"
                                    href="{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/rekapitulasi-pertanyaan-tambahan/download-docx"
                                    target="_blank"><i class="fas fa-file-word text-dark"></i>&nbsp;&nbsp;
                                    <b>DOCX</b></a>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>


            @foreach($pertanyaan_tambahan->result() as $row)
            <div class="card card-body mb-5">

                <!-- <div class="alert alert-secondary text-center" role="alert">
                    <h5>{{$row->nomor_pertanyaan_terbuka . '. ' . $row->nama_pertanyaan_terbuka}}</h5>
                    <div class="text">
                        <?php echo $row->isi_pertanyaan_terbuka ?>
                    </div>
                </div> -->


                <div class="d-flex justify-content-center" id="<?php echo $row->nomor_pertanyaan_terbuka ?>"></div>
                <br>

                @if($row->id_jenis_pilihan_jawaban == 1)
                <table class="table table-bordered table-striped table-hover mt-5 example">
                    <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kelompok</th>
                        <th>Jumlah</th>
                        <th>Persentase</th>
                    </tr>
                    </thead>


                    <tbody>
                    @php
                    $no = 1;
                    @endphp
                    @foreach ($jawaban_ganda->result() as $value)
                    @if ($value->id_perincian_pertanyaan_terbuka == $row->id_perincian_pertanyaan_terbuka)
                    
                    <tr>
                        <td><?php echo $no++ ?></td>
                        <td><?php echo $value->pertanyaan_ganda ?></td>
                        <td><?php echo $value->perolehan ?></th>
                        <td><?php echo ROUND(($value->perolehan / $value->total_survei) * 100, 2) ?> %</td>
                    </tr>
                    @endif
                    @endforeach
                    </tbody>
                </table>

                @else

                <div>
                    <span style="font-size: 14px;">
                        <b>{{$row->nomor_pertanyaan_terbuka . '. ' . $row->nama_pertanyaan_terbuka}}</b>
                    </span>
                    <p style="font-size: 11px;"><?php echo strip_tags($row->isi_pertanyaan_terbuka) ?></p>
                </div>

                <table class="table table-bordered table-striped table-hover mt-7 example">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Jawaban</th>
                        </tr>
                    </thead>

                    <tbody>
                    @php
                    $i = 1;
                    @endphp
                    @foreach ($jawaban_isian->result() as $get)
                    @if ($get->id_pertanyaan_terbuka == $row->id_pertanyaan_terbuka)
                    <tr>
                        <td><?php echo $i++ ?></td>
                        <td><?php echo implode("", unserialize($get->jawaban)) ?></td>
                    </tr>
                    @endif
                    @endforeach
                    </tbody>
                </table>

            
                @endif

            </div>
            @endforeach

        </div>
    </div>
</div>


@endsection

@section('javascript')
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


<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>



<script>
$(document).ready(function() {
    $('.example').DataTable();
});
</script>

@foreach($pertanyaan_tambahan->result() as $row)
@if($row->id_jenis_pilihan_jawaban == 1)
@php
$jumlah = [];
$nama_kelompok = [];
$data_chart = [];
foreach ($jawaban_ganda->result() as $value) {
    if ($value->id_perincian_pertanyaan_terbuka == $row->id_perincian_pertanyaan_terbuka) {
        $jumlah[] = $value->perolehan;
        $nama_kelompok[] = "'" . $value->pertanyaan_ganda . "'";

        $data_chart[] = str_word_count($value->pertanyaan_ganda) > 2 ? '{label:"' . substr($value->pertanyaan_ganda, 0, 7) . ' [...](' . $value->perolehan . ')", value:"' . $value->perolehan . '"}' : '{label:"' . $value->pertanyaan_ganda . '(' . $value->perolehan . ')", value:"' . $value->perolehan . '"}';
    }
}


$total = implode(", ", $jumlah);
$kelompok = implode(", ", $nama_kelompok);
$get_chart = implode(", ", $data_chart);
// var_dump($total);
@endphp



<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        "type": "pie3d",
        "renderAt": "<?php echo $row->nomor_pertanyaan_terbuka ?>",
        "width": "100%",
        "height": "350",
        "dataFormat": "json",
        dataSource: {
            "chart": {
                caption: "{{$row->nomor_pertanyaan_terbuka . '. ' . $row->nama_pertanyaan_terbuka}}",
                subcaption: "<?php echo strip_tags($row->isi_pertanyaan_terbuka) ?>",
                "enableSmartLabels": "1",
                "startingAngle": "0",
                "showpercentvalues": "1",
                "decimals": "2",
                "useDataPlotColorForLabels": "1",
                "theme": "umber",
                "bgColor": "#ffffff",
            },
            "data": [<?php echo $get_chart ?>]
        }

    });
    myChart.render();
});
</script>

@endif
@endforeach

@endsection