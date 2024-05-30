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

            <div class="card card-custom bgi-no-repeat gutter-b"
                style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)"
                data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            {{strtoupper($title)}}
                        </h3>
                    </div>
                </div>
            </div>

           

            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">

                <div id="chart"></div>

                    <div class="table-responsive mt-5">
                        <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%"
                            style="font-size: 12px;">
                            <thead class="bg-secondary">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th>Aspek</th>
                                    <th>Indeks</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>

                            <tbody>

                            @php
                            $no = 1;
                            $data_chart = [];
                            @endphp
                            @foreach($ci->db->query("SELECT *,
                                (SELECT nama_aspek FROM aspek_$table_identity WHERE id_aspek = aspek_$table_identity.id) AS nama_aspek,
                                AVG(rata_rata) AS skor

                                FROM (
                                SELECT unsur_pelayanan_$table_identity.id,
                                (SELECT id_aspek FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_aspek,
                                (SELECT id FROM dimensi_$table_identity WHERE id_dimensi = dimensi_$table_identity.id) AS id_dimensi,
                                nama_unsur_pelayanan,
                                AVG(skor_jawaban) AS rata_rata


                                FROM jawaban_pertanyaan_unsur_$table_identity
                                JOIN pertanyaan_unsur_pelayanan_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_pertanyaan_unsur = pertanyaan_unsur_pelayanan_$table_identity.id
                                JOIN unsur_pelayanan_$table_identity ON pertanyaan_unsur_pelayanan_$table_identity.id_unsur_pelayanan = unsur_pelayanan_$table_identity.id
                                JOIN survey_$table_identity ON jawaban_pertanyaan_unsur_$table_identity.id_responden = survey_$table_identity.id_responden
                                WHERE survey_$table_identity.is_submit = 1
                                GROUP BY IF(id_parent = 0, unsur_pelayanan_$table_identity.id, unsur_pelayanan_$table_identity.id_parent)
                                ) as_$table_identity

                                GROUP BY id_aspek")->result() as $row)
                           
                                <tr>
                                    <td>{{$no++}}</td>
                                    <td>{{$row->nama_aspek}}</td>
                                    <td>{{ROUND($row->skor,3)}}</td>
                                    <td>
                                    @php
                                     foreach ($ci->db->get("definisi_skala_$table_identity")->result() as $obj) {
                                        if (($row->skor * $skala_likert) <= $obj->range_bawah && ($row->skor * $skala_likert) >= $obj->range_atas) {
                                            echo $obj->kategori;
                                        }
                                    }
                                    if ($row->skor * $skala_likert <= 0) {
                                        echo 'NULL';
                                    }
                                    @endphp
                                    </td>
                                </tr>

                                @php
                                $data_chart[] = '{label:"' . $row->nama_aspek . '", value:"' . ROUND($row->skor,3) . '"}';
                                @endphp
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

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
    $('#table').DataTable();
});
</script>


<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "bar3d",
        renderAt: "chart",
        "width": "100%",
        "height": "90%",
        dataFormat: "json",
        dataSource: {
            chart: {
                caption: "Indeks Per Aspek Survei",
                // yaxisname: "Annual Income",
                showvalues: "1",
                "decimals": "3",
                theme: "umber",
                "bgColor": "#ffffff",
            },
            data: [<?= implode(", ", $data_chart) ?>]}
    });
    myChart.render();
});
</script>
@endsection