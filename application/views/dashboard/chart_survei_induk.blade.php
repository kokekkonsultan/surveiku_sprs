@php
$ci = get_instance();

$user = $ci->ion_auth->user()->row();
$nilai_index_induk = $ci->db->get_where("indeks_admin_induk", array('id_user' => $ci->session->userdata('user_id')));


//===========================================================================================================================

$sum_survey = 0;
foreach($ci->db->query("SELECT * FROM manage_survey")->result() as $row){
    $survey[] = $ci->db->get_where("survey_$row->table_identity", ['is_submit' => 1])->num_rows();
    $sum_survey = array_sum($survey);
}


foreach ($ci->db->query("SELECT *, (SELECT company FROM users WHERE id = manage_survey.id_user) AS company FROM manage_survey")->result() as $row) {   
    $total_res = $ci->db->get_where("survey_$row->table_identity", ['is_submit' => 1])->num_rows();
    $array_chart[] = '{ label: "' . $row->company . '", value: "' . $total_res . '" }';

    $array_tabel[] = '<tr>
                        <td>' . $row->company . '</td>
                        <td>' . $total_res . '</td>
                        <td>' . ROUND(($total_res / $sum_survey) * 100, 2) . '%</td>
                        </tr>';
}
$chart_perolehan = implode(", ", $array_chart);
$table_perolehan = implode("", $array_tabel);
@endphp



<div class="bg-white mt-4 mb-5" style="padding:10px; border-radius: 5px;">
  <div class="text-center">
    <h5 class="text-primary font-weight-bolder">TOTAL PEROLEHAN : <b class="text-dark">{{$sum_survey}}
        Responden</b></h5>
  </div>
</div>


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
            <th>Wilayah</th>
            <th>Perolehan</th>
            <th>Persentase</th>
          </tr>
        </thead>
        <tbody>
          {!! $table_perolehan !!}
        </tbody>
      </table>
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
            "height": "364",
            dataFormat: "json",
            dataSource: {
                chart: {
                    caption: "Perolehan Per Provinsi",
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

        <div class="text-right">
            <a class="btn btn-light-info btn-sm font-weight-bold" href="{{base_url() . 'history-nilai-per-periode'}}">
                Kelola Nilai Indeks
            </a>

            <button type="button" class="btn btn-light-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#exampleModal">
                Generate Indeks
            </button>
        </div>
        <div class="text-center">
            <div id="chart-index"></div>
        </div>
    </div>
</div>

<div class="card card-custom card-stretch card-stretch-half gutter-b overflow-hidden">

    <div class="card-body">
        <div class="text-center">
            <div id="chart"></div>
        </div>
    </div>
</div>



<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border border-primary">
            <div class="modal-body">

                <form class="form_default" action="<?php echo base_url() . 'olah-data-keseluruhan/proses-index' ?>" method="POST">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Masukkan Nama Label Indeks <span class="text-danger">*</span></label>
                        <input class="form-control" name="label" placeholder="Survei Kepuasan Pelanggan Tahun 2023" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label font-weight-bold">Pilih survei yang dijadikan indeks <span class="text-danger">*</span>
                            <hr>
                        </label>
                        {!! $checkbox !!}
                    </div>

                    <br>



                    <div class="text-right mt-5">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolDefault">Simpan</button>
                    </div>

                </form>

            </div>

        </div>
    </div>

<script>
        FusionCharts.ready(function() {
            var myChart = new FusionCharts({
                type: "column3d",
                renderAt: "chart-index",
                width: "100%",
                "height": "309",
                dataFormat: "json",
                dataSource: {
                    chart: {
                        caption: "Indeks Kepuasan Pelanggan",
                        subcaption: "<?php echo $user->company ?>",
                        // yaxisname: "Deforested Area{br}(in Hectares)",
                        decimals: "1",
                        showvalues: "1",
                        theme: "fusion",
                        "bgColor": "#ffffff"
                    },
                    data: [

                        <?php foreach ($nilai_index_induk->result() as $row) { ?> {
                                    "label": "<?= $row->label ?>",
                                    value: <?= $row->nilai_indeks ?>
                                },
                        <?php } ?>
                    ]
                }
            });
            myChart.render();
        });
    </script>

<script>
        $('.form_default').submit(function(e) {
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                cache: false,
                beforeSend: function() {
                    $('.tombolDefault').attr('disabled', 'disabled');
                    $('.tombolDefault').html(
                        '<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

                    Swal.fire({
                        title: 'Memproses data',
                        html: 'Mohon tunggu sebentar. Sistem sedang melakukan request anda.',
                        allowOutsideClick: false,
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });

                },
                complete: function() {
                    $('.tombolDefault').removeAttr('disabled');
                    $('.tombolDefault').html('Simpan');
                },
                error: function(e) {
                    Swal.fire(
                        'Informasi',
                        'Gagal memproses data!',
                        'error'
                    );
                    // window.setTimeout(function() {
                    //     location.reload()
                    // }, 2000);
                },

                success: function(data) {
                    // if (data.gagal) {
                    //     Swal.fire(
                    //         'Informasi',
                    //         'Gagal memproses data!',
                    //         'error'
                    //     );
                    // }
                    if (data.sukses) {
                        $('#checkAll').prop('checked', false);
                        Swal.fire(
                            'Informasi',
                            'Berhasil mendapatkan nilai indeks',
                            'success'
                        );

                        window.setTimeout(function() {
                            location.reload()
                        }, 2000);
                    }
                }
            });
            return false;
        });
    </script>

<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        type: "<?php if ($total_survey > 10) {
                    echo 'bar3d';
                } else {
                    echo 'column3d';
                } ?>",
        renderAt: "chart",
        width: "100%",
        "height": "<?php if ($total_survey > 10) {
                        echo '700';
                    } else {
                        echo '350';
                    } ?>",
        dataFormat: "json",
        dataSource: {
            chart: {
                caption: "Hasil Survei Kepuasan Pelanggan Per Akun Anak",
                subcaption: "Sampai dengan Tahun <?php echo $tahun_awal ?>",
                // yaxisname: "Deforested Area{br}(in Hectares)",
                decimals: "1",
                showvalues: "1",
                theme: "gammel",
                "bgColor": "#ffffff"
            },
            data: [<?php echo $new_chart ?>]
        }
    });
    myChart.render();
});
</script> -->