<?php
$ci = get_instance();

$user = $ci->ion_auth->user()->row();

//===========================================================================================================================


foreach ($ci->db->query("SELECT * FROM manage_survey WHERE id_user = $user->id")->result() as $row) {   
    $total_res = $ci->db->get_where("survey_$row->table_identity", ['is_submit' => 1])->num_rows();
    $array_chart[] = '{ label: "' . $row->survey_name . '", value: "' . $total_res . '" }';

    $array_tabel[] = '<tr>
                        <td>' . $row->survey_name . '</td>
                        <td>' . $total_res . '</td>
                        </tr>';
}
$chart_perolehan = implode(", ", $array_chart);
$table_perolehan = implode("", $array_tabel);
?>




<div class="row">
    <div class="col-md-5">
        <div class="card card-body">
            <div id="chart"></div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card card-body">
            <table class="table table-bordered example">
            <thead>
            <tr>
                <th>Survei</th>
                <th>Perolehan</th>
            </tr>
            </thead>
            <tbody>
            <?php echo $table_perolehan; ?>

            </tbody>
        </table>
        <br>
        <br>
        <br>
        <br>
        <br>
        </div>
    </div>
</div>




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

<script>
    FusionCharts.ready(function() {
        var myChart = new FusionCharts({
            type: "pie3d",
            renderAt: "chart",
            width: "100%",
            "height": "277",
            dataFormat: "json",
            dataSource: {
                chart: {
                    caption: "Grafik Perolehan <?php echo $klien ?>",
                    subcaption: "Badan Siber dan Sandi Negara Republik Indonesia",
                    "enableSmartLabels": "1",
                    "startingAngle": "0",
                    "showPercentValues": "1",
                    "decimals": "2",
                    "useDataPlotColorForLabels": "1",
                    "bgColor": "#ffffff",
                    theme: "umber"
                },
                data: [<?= $chart_perolehan ?>]
            }
        });
        myChart.render();
    });
</script>








<!-- <div class="card card-custom card-stretch card-stretch-half gutter-b overflow-hidden">
    <div class="card-body">
        <div class="text-center">
            <div id="chart"></div>
        </div>
    </div>
</div>



<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "column3d",
        renderAt: "chart",
        width: "100%",
        "height": "309",
        dataFormat: "json",
        dataSource: {
            chart: {
                caption: "Grafik Perolehan <?php echo $klien ?>",
                subcaption: "Sampai dengan Tahun <?php echo $tahun_awal ?>",
                decimals: "2",
                theme: "umber",
                "bgColor": "#ffffff"
            },
            data: [<?php echo $new_chart ?>]
        }
    });
    myChart.render();
});
</script> --><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/dashboard/chart_survei.blade.php ENDPATH**/ ?>