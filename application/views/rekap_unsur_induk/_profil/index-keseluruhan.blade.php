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


    @foreach($ci->db->query("SELECT * FROM profil_responden_cst383 WHERE jenis_isian = 1")->result() as $row)

    <div class="card card-custom card-sticky mb-5" data-aos="fade-down">
        <div class="card-body">

            <div class="d-flex justify-content-center" id="{{$row->nama_alias}}"></div>
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
                    @foreach ($kategori_profil->result() as $value)
                    @if ($value->id_profil_responden == $row->id)

                    <tr>
                        <td align="center">{{$no++}}</td>
                        <td>{{$value->nama_kategori_profil_responden}}</td>
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




@foreach($ci->db->query("SELECT * FROM profil_responden_cst383 WHERE jenis_isian = 1")->result() as $get)

<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        "type": "bar3d",
        "renderAt": "{{$get->nama_alias}}",
        "width": "100%",
        "height": "350",
        "dataFormat": "json",
        dataSource: {
            "chart": {
                caption: "{{$get->nama_profil_responden}}",
                subcaption: "Chart Profil Responden Survei",
                "enableSmartLabels": "1",
                "startingAngle": "0",
                "showPercentValues": "1",
                "decimals": "2",
                "useDataPlotColorForLabels": "1",
                "theme": "umber",
                "bgColor": "#ffffff",

            
            },
            "data": [

                <?php foreach ($kategori_profil->result() as $kpr) {
                        if ($kpr->id_profil_responden == $get->id) { ?>
                        
                        {   
                            label:"<?= $kpr->nama_kategori_profil_responden . ', ' . ROUND(($kpr->perolehan / $kpr->total_survei) * 100, 2) ?>%",
                            "value": "<?= $kpr->perolehan ?>"
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