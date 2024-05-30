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

                <a class="btn btn-primary btn-sm" href="{{base_url() . 'rekap-unsur-induk'}}"><i class="fa fa-arrow-left"></i> Kembali</a>
            </div>
        </div>
    </div>


            @foreach ($ci->db->get_where("profil_responden_$table_identity", ['jenis_isian' => 1])->result() as $row)
            <div class="card card-custom card-sticky mb-5">
                <div class="card-body">

                    <div class="d-flex justify-content-center" id="{{$row->nama_alias}}"></div>
                    <br>

                    <table class="table table-bordered table-striped example" style="width:100%">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Kelompok</th>
                                <th>Perolehan</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                           

                            @php
                            $no = 1;
                            @endphp

                            @foreach ($ci->db->query("SELECT *,
                            (SELECT COUNT(*) FROM survey_$table_identity WHERE is_submit = 1) AS jumlah,
                            (SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id = responden_$table_identity.$row->nama_alias && is_submit = 1) AS perolehan
                            FROM kategori_profil_responden_$table_identity")->result() as $value)
                            @if ($value->id_profil_responden == $row->id)

                            <tr>
                                <td>{{$no++}}</td>
                                <td>{{$value->nama_kategori_profil_responden}}</td>
                                <td>{{$value->perolehan}}</th>
                                <td>{{ROUND(($value->perolehan/$value->jumlah) * 100,2)}} %</td>
                                
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


@foreach ($ci->db->get_where("profil_responden_$table_identity", ['jenis_isian' => 1])->result() as $get)

@php
$kategori_profil_responden = $ci->db->query("SELECT *, (SELECT COUNT(*) FROM responden_$table_identity JOIN
survey_$table_identity ON responden_$table_identity.id = survey_$table_identity.id_responden WHERE
kategori_profil_responden_$table_identity.id = responden_$table_identity.$get->nama_alias && is_submit = 1) AS
perolehan,
ROUND((((SELECT COUNT(*) FROM responden_$table_identity JOIN survey_$table_identity ON responden_$table_identity.id =
survey_$table_identity.id_responden WHERE kategori_profil_responden_$table_identity.id =
responden_$table_identity.$get->nama_alias && is_submit = 1) / (SELECT COUNT(*) FROM survey_$table_identity WHERE
is_submit = 1)) * 100), 2) AS persentase

FROM kategori_profil_responden_$table_identity")
@endphp

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
                "bgColor": "#ffffff"
            },
            "data": [

                <?php foreach ($kategori_profil_responden->result() as $kpr) {
                    if ($kpr->id_profil_responden == $get->id) { ?> 
                        {
                            "label": <?php echo str_word_count($kpr->nama_kategori_profil_responden) > 3 ? '"' . substr($kpr->nama_kategori_profil_responden, 0, 7) . ' [...] (' . $kpr->perolehan . ')"' : '"' . $kpr->nama_kategori_profil_responden . ' (' . $kpr->perolehan . ')"'  ?>,
                            "value": "<?php echo $kpr->perolehan ?>"
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