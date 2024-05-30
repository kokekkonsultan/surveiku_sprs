@extends('include_backend/template_backend')
@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet"
    type="text/css" />

<style type="text/css">
[pointer-events="bounding-box"] {
    display: none
}
</style>
<style type="text/css">
.dataTables_length,
.dataTables_filter,
.dataTables_info,
.dataTables_paginate {
    display: none
}

[pointer-events="bounding-box"] {
    display: none
}
</style>

@endsection

@section('content')
<div class=" container-fluid">
    <div class="card card-custom bgi-no-repeat gutter-b aos-init aos-animate"
        style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/rhone-2.svg)"
        data-aos="fade-down">
        <div class="card-body d-flex align-items-center">
            <div>
                <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                    {{strtoupper($title)}}
                </h3>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card card-body">
            <div id="chart-aspek"></div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-body">
            <div id="chart-dimensi"></div>
            </div>
        </div>

    </div>


    <div class="card shadow aos-init aos-animate" data-aos="fade-up">
        <div class="card-body">

            {!! $data_html !!}

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
    $('.table').DataTable();
});
</script>


<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "bar3d",
        renderAt: "chart-aspek",
        "width": "100%",
        "height": "300",
        dataFormat: "json",
        dataSource: {
            chart: {
                caption: "Grafik Indeks Per Aspek",
                // yaxisname: "Annual Income",
                showvalues: "1",
                "decimals": "2",
                theme: "umber",
                "bgColor": "#ffffff",
            },
            data: [<?= $data_nilai_aspek ?>]
        }
    });
    myChart.render();
});


FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "column3d",
        renderAt: "chart-dimensi",
        "width": "100%",
        "height": "300",
        dataFormat: "json",
        dataSource: {
            chart: {
                caption: "Grafik Indeks Per Dimensi",
                // yaxisname: "Annual Income",
                showvalues: "1",
                "decimals": "2",
                theme: "umber",
                "bgColor": "#ffffff",
            },
            data: [<?= $data_nilai_dimensi ?>
            ]
        }
    });
    myChart.render();
});
</script>
@endsection