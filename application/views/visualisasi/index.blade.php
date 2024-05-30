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
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">


            @foreach($pertanyaan_unsur->result() as $row)
            <div class="card card-custom card-sticky mb-5" data-aos="fade-down">
                <div class="card-body">

                    <div class="d-flex justify-content-center" id="{{$row->nomor_unsur}}"></div>
                    <br>

                    <table class="table table-bordered example mt-5" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th class="text-center">Pilihan Jawaban</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                        $no = 1;
                        @endphp
                        @foreach ($jawaban_ganda->result() as $value)
                        @if ($value->id_pertanyaan_unsur == $row->id_pertanyaan_unsur)
                        
                        <tr>
                            <td align="center"><?php echo $no++ ?></td>
                            <td><?php echo $value->nama_kategori_unsur_pelayanan ?></td>
                            <td align="center"><?php echo $value->perolehan ?></th>
                            <td align="center"><?php echo ROUND(($value->perolehan / $value->total_survei) * 100, 2) ?> %</td>
                        </tr>
                        @endif
                        @endforeach
                        </tbody>
                    </table>





                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>


@endsection

@section('javascript')
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
    $(document).ready(function() {
        $('.example').DataTable({
            "lengthMenu": [
            [5, 10, 25, -1],
            [5, 10, 25, "Semua data"]
        ],
        "pageLength": 5,});
    });
</script>



@foreach($pertanyaan_unsur->result() as $row)
@php
$jumlah = [];
$nama_kelompok = [];
$data_chart = [];
foreach ($jawaban_ganda->result() as $value) {
    if ($value->id_pertanyaan_unsur == $row->id_pertanyaan_unsur) {
        $jumlah[] = $value->perolehan;
        $nama_kelompok[] = "'" . $value->nama_kategori_unsur_pelayanan . "'";

        $data_chart[] = str_word_count($value->nama_kategori_unsur_pelayanan) > 2 ? '{label:"' . substr($value->nama_kategori_unsur_pelayanan, 0, 7) . ' [...](' . $value->perolehan . ')", value:"' . $value->perolehan . '"}' : '{label:"' . $value->nama_kategori_unsur_pelayanan . '(' . $value->perolehan . ')", value:"' . $value->perolehan . '"}';
    
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
        "renderAt": "{{$row->nomor_unsur}}",
        "width": "100%",
        "height": "350",
        "dataFormat": "json",
        dataSource: {
            "chart": {
                caption: "{{$row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan}}",
                subcaption: "{{strip_tags($row->isi_pertanyaan_unsur)}}",
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

@endforeach

@endsection