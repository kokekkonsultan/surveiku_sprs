@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

<style>
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

            <div class="card card-custom bgi-no-repeat gutter-b"
                style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)"
                data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            TABULASI DAN {{strtoupper($title)}}
                        </h3>

                        <span class="btn btn-light btn-sm font-weight-bold">
                            <i class="fa fa-bookmark"></i> <strong><?php echo $jumlah_kuisioner; ?></strong> Kuesioner
                            Lengkap
                        </span>
                    </div>
                </div>
            </div>

            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%"
                            style="font-size: 12px;">
                            <thead class="bg-secondary">
                                <tr>
                                    <th width="5%">No.</th>
                                    <!-- <th>Status</th>
                                    <th>Surveyor</th> -->
                                    <th>Responden</th>
                                    @php
                                    $n=1;
                                    @endphp
                                    @foreach ($nps->result() as $row)
                                    <th>P{{ $n++; }}</th>
                                    @endforeach

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php
            $kategori = '';
            foreach ($benchmark_nps->result() as $obj) {
                //if ($nilai_nps <= $obj->skor_benchmark && $nilai_nps >= $obj->skor_benchmark) {
                if ($nilai_nps >= $obj->skor_benchmark) {
                    $kategori = '('.$obj->kategori_benchmark.')';
                }
            }
            if ($nilai_nps <= 0) {
                $kategori = '';
            }
            ?>

            <div class="card card-body mt-5" data-aos="fade-down">
                <h3>Tabulasi</h3>
                <div class="card-deck mt-3">
                    <a class="card card-body text-dark bg-success">
                        <div class="text-center font-weight-bold">Promoters
                            <hr>
                            <h3 class="text-white"><b>{{ $promoters; }}%</b></h3>
                        </div>
                    </a>
                    <a class="card card-body text-dark bg-warning">
                        <div class="text-center font-weight-bold">Detractors
                            <hr>
                            <h3 class="text-white"><b>{{ $detractors; }}%</b></h3>
                        </div>
                    </a>
                    <a data-toggle="modal" class="card card-body text-dark bg-danger">
                        <div class="text-center font-weight-bold">Passives
                            <hr>
                            <h3 class="text-white"><b>{{ $passives; }}%</b></h3>
                        </div>
                    </a>
                    <a data-toggle="modal" class="card card-body text-dark bg-secondary">
                        <div class="text-center font-weight-bold">Nilai NPS
                            <hr>
                            <h3 class="text-dark"><b>{{ $nilai_nps; }}% {{ $kategori }}</b></h3>
                        </div>
                    </a>
                </div>
            </div>


                

                <div class="row mt-5">
                    <div class="col-md-7">
                    <div class="text-center">
                            <div id="chart"></div>
                        </div>
                    </div>

                    <div class="col-md-5">
                    <div class="text-center">
                            <div id="stackedchart"></div>
                        </div>
</div>
                </div>




        </div>
    </div>

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


<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "bar2d",
        renderAt: "chart",
        width: "100%",
        "height": "500",
        dataFormat: "json",
        dataSource: {
            chart: {
                //caption: "",
                //subcaption: "",
                // yaxisname: "",
                decimals: "2",
                theme: "gammel",
                "bgColor": "#ffffff"
            },
            data: [<?php echo $new_chart ?>]
        }
    });
    myChart.render();
});

FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "mscolumn2d",
        renderAt: "stackedcharts",
        width: "100%",
        "height": "500",
        dataFormat: "json",
        dataSource: {
            chart: {
                //caption: "",
                //subcaption: "",
                // yaxisname: "",
                decimals: "2",
                theme: "gammel",
                "bgColor": "#ffffff"
            },
            data: [{ label: "Promoters", value: "{{ $promoters; }}", color: "#159892" }, { label: "Detractors", value: "{{ $detractors; }}", color: "#cc8600" }, { label: "Passives", value: "{{ $passives; }}", color: "#f41d34" }]
        }
    });
    myChart.render();
});

const dataSource = {
  chart: {
    decimals: "2",
    theme: "fusion",
    plotSpacePercent: "40",
    legendPosition: "top",
    //legendIconScale: "2",
    //"legendIconBgColor": "#ff0000",
    //"legendBgColor": "#CCCCCC",
    showLegend: "1"
  },
  categories: [
    {
      category: [
        {
          //label: "NPS"
        }
      ]
    }
  ],
  dataset: [
    {
      seriesname: "Promoters",
      //legendIconBgColor: "#ff0000",
      data: [
        {
          value: "{{ $promoters; }}",
          color: "#159892",
          valueFontColor: "#FFFFFF",
          displayValue: "{{ $promoters; }}%",
          showValue: "1",
          //legendBgColor: "#CCCCCC",
        }
      ]
    },
    {
      seriesname: "Detractors",
      data: [
        {
          value: "{{ $detractors; }}",
          color: "#cc8600",
          valueFontColor: "#FFFFFF",
          displayValue: "{{ $detractors; }}%",
          showValue: "1"
        }
      ]
    },
    {
      seriesname: "Passives",
      data: [
        {
          value: "{{ $passives; }}",
          color: "#f41d34",
          valueFontColor: "#FFFFFF",
          displayValue: "{{ $passives; }}%",
          showValue: "1"
        }
      ]
    }
  ]
};

FusionCharts.ready(function() {
  var myChart = new FusionCharts({
    type: "stackedcolumn3d",
    renderAt: "stackedchart",
    width: "100%",
    height: "100%",
    dataFormat: "json",
    dataSource
  }).render();
});
</script>

<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
$(document).ready(function() {
    table = $('#table').DataTable({

        "processing": true,
        "serverSide": true,
        // paging: true,
        //     dom: 'Blfrtip',
        //     "buttons": [
        //         {
        //             extend: 'collection',
        //             text: 'Export',
        //             buttons: [
        //                 'excel'
        //             ]
        //         }
        //     ],

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
            "url": "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/olah-data-nps/ajax-list' ?>",
            "type": "POST",
            "data": function(data) {}
        },

        "columnDefs": [{
            "targets": [-1],
            "orderable": false,
        }, ],

    });
});

$('#btn-filter').click(function() {
    table.ajax.reload();
});
$('#btn-reset').click(function() {
    $('#form-filter')[0].reset();
    table.ajax.reload();
});
</script>
@endsection