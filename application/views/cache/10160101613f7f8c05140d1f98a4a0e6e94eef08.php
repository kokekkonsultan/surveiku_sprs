<?php
$ci = get_instance();
?>

<?php $__env->startSection('style'); ?>
<link href="<?php echo e(TEMPLATE_BACKEND_PATH); ?>plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />


<style type="text/css">
    [pointer-events="bounding-box"] {
        display: none
    }

</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">
    <?php echo $__env->make("include_backend/partials_no_aside/_inc_menu_repository", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="row mt-5">
        <div class="col-md-3">
            <?php echo $__env->make('manage_survey/menu_data_survey', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        <div class="col-md-9">


            <?php $__currentLoopData = $pertanyaan_unsur->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card card-custom card-sticky mb-5" data-aos="fade-down">
                <div class="card-body">

                    <div class="d-flex justify-content-center" id="<?php echo e($row->nomor_unsur); ?>"></div>
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
                        <?php
                        $no = 1;
                        ?>
                        <?php $__currentLoopData = $jawaban_ganda->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($value->id_pertanyaan_unsur == $row->id_pertanyaan_unsur): ?>
                        
                        <tr>
                            <td align="center"><?php echo $no++ ?></td>
                            <td><?php echo $value->nama_kategori_unsur_pelayanan ?></td>
                            <td align="center"><?php echo $value->perolehan ?></th>
                            <td align="center"><?php echo ROUND(($value->perolehan / $value->total_survei) * 100, 2) ?> %</td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>





                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
<script src="<?php echo e(TEMPLATE_BACKEND_PATH); ?>plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.accessibility.js">
</script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.candy.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.carbon.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fint.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.gammel.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.ocean.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.umber.js"></script>
<script src="<?php echo e(base_url()); ?>assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.zune.js"></script>

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



<?php $__currentLoopData = $pertanyaan_unsur->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
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
?>



<script>
FusionCharts.ready(function() {
    var myChart = new FusionCharts({
        "type": "pie3d",
        "renderAt": "<?php echo e($row->nomor_unsur); ?>",
        "width": "100%",
        "height": "350",
        "dataFormat": "json",
        dataSource: {
            "chart": {
                caption: "<?php echo e($row->nomor_unsur . '. ' . $row->nama_unsur_pelayanan); ?>",
                subcaption: "<?php echo e(strip_tags($row->isi_pertanyaan_unsur)); ?>",
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

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('include_backend/template_backend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/visualisasi/index.blade.php ENDPATH**/ ?>