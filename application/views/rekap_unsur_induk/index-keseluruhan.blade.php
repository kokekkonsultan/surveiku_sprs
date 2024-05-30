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
    <div class="card card-custom bgi-no-repeat gutter-b" style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)" data-aos="fade-down">
        <div class="card-body d-flex align-items-center">
            <div>
                <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                    {{strtoupper($title)}}
                </h3>
            </div>
        </div>
    </div>


    @foreach($ci->db->query("SELECT *, pertanyaan_unsur_pelayanan_cst383.id AS id_pertanyaan_unsur FROM pertanyaan_unsur_pelayanan_cst383
    JOIN unsur_pelayanan_cst383 ON pertanyaan_unsur_pelayanan_cst383.id_unsur_pelayanan = unsur_pelayanan_cst383.id")->result() as $row)

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
                    @foreach ($kategori_unsur->result() as $value)
                    @if ($value->id_pertanyaan_unsur == $row->id_pertanyaan_unsur)

                    <tr>
                        <td align="center">{{$no++}}</td>
                        <td>{{$value->nama_kategori_unsur_pelayanan}}</td>
                        <td align="center">{{$value->perolehan}}</th>
                        <td align="center">{{ROUND(($value->perolehan / $value->total_survei) * 100, 2)}} %</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>



    @endforeach

    

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
            "pageLength": 5,
        });
    });
</script>




@foreach($ci->db->query("SELECT *, pertanyaan_unsur_pelayanan_cst383.id AS id_pertanyaan_unsur FROM pertanyaan_unsur_pelayanan_cst383
    JOIN unsur_pelayanan_cst383 ON pertanyaan_unsur_pelayanan_cst383.id_unsur_pelayanan = unsur_pelayanan_cst383.id")->result() as $row)

<script>
    FusionCharts.ready(function() {
        var myChart = new FusionCharts({
            "type": "bar3d",
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
                "data": [
                    <?php foreach ($kategori_unsur->result() as $value) {
                        if ($value->id_pertanyaan_unsur == $row->id_pertanyaan_unsur) { ?>
                    {
                        label:"<?= str_word_count($value->nama_kategori_unsur_pelayanan) > 3 ? substr($value->nama_kategori_unsur_pelayanan, 0, 15) . ' [...]' : $value->nama_kategori_unsur_pelayanan ?>, <?= ROUND(($value->perolehan / $value->total_survei) * 100, 2) ?>%",
                        value:"<?= $value->perolehan ?>"
                    },
                    <?php } } ?>
                    ]
            }

        });
        myChart.render();
    });
</script>

@endforeach

@endsection