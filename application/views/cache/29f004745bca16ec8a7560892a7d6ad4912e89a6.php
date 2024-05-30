<?php
$ci = get_instance();
?>

<?php $__env->startSection('style'); ?>
<link href="<?php echo e(TEMPLATE_BACKEND_PATH); ?>plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

<div class="container-fluid">
    <?php echo $__env->make("include_backend/partials_no_aside/_inc_menu_repository", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row mt-5">
        <div class="col-md-3">
            <?php echo $__env->make('manage_survey/menu_data_survey', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        <div class="col-md-9">

            <div class="card" data-aos="fade-down">
                <div class="card-header bg-secondary">
                    <h5><?php echo e($title); ?></h5>
                </div>
                <div class="card-body">


                    <?php echo form_open_multipart(base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/edit/' . $ci->uri->segment(5)); ?>

                    <span class="text-danger"><?php echo validation_errors(); ?></span>

                    </br>

                    

                    <div class="form-group row">

                        <?php
                        echo form_label('Pertanyaan Tambahan <span style="color:red;">*</span>', '', ['class' =>
                        'col-sm-3 col-form-label
                        font-weight-bold']);
                        ?>

                        <div class="col-sm-9">

                            <div class="input-group">

                                <div class="input-group-prepend">
                                    <span class="input-group-text"><?php echo e($current->nomor_pertanyaan_terbuka); ?></span>
                                </div>
                                <?php
                                echo form_input($nama_pertanyaan_terbuka);
                                ?>
                            </div>

                        </div>
                    </div>


                    <div class="form-group row">

                        <?php
                        echo form_label('Isi Pertanyaan Tambahan <span style="color:red;">*</span>', '', ['class' =>
                        'col-sm-3 col-form-label
                        font-weight-bold']);
                        ?>
                        <div class="col-sm-9">
                            <?php
                            echo form_textarea($isi_pertanyaan_terbuka);
                            ?>
                        </div>
                    </div>


                    <!-- <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Gambar</label>
                        <div class="col-sm-9">
                            <?php if ($current->gambar_pertanyaan_terbuka) : ?>
                                <img src="<?php echo e(base_url()); ?>assets/img/site/pertanyaan/<?php echo e($current->gambar_pertanyaan_terbuka); ?>" alt="" style="max-width: 100%;">
                                <input type="hidden" name="old_gambar_pertanyaan_terbuka" value="<?php echo e($current->gambar_pertanyaan_terbuka); ?>">
                                <div style="padding-top:10px; padding-bottom:10px; "><a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus Gambar" onclick="delete_gambar_pertanyaan_terbuka('<?php echo e($current->id); ?>')"><i class="fa fa-trash"></i> Hapus Gambar</a></div>
                            <?php else : ?>
                                <input type="hidden" name="old_gambar_pertanyaan_terbuka" value="">
                            <?php endif; ?>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="gambar_pertanyaan_terbuka" id="gambar_pertanyaan_terbuka">
                                <label class="custom-file-label" for="validatedCustomFile">Choose
                                    file...</label>
                            </div>
                        </div>
                    </div> -->


                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Status Pengisian Pertanyaan <span style="color:red;">*</span></label>
                        <div class="col-9 col-form-label">
                            <div class="radio-inline">
                                <label class="radio radio-md">
                                    <input type="radio" name="is_required" value="" <?= $current->is_required == '' ? 'checked' : '' ?>>
                                    <span></span>
                                    Wajib di Isi
                                </label>
                                <label class="radio radio-md">
                                    <input type="radio" name="is_required" value="1" <?= $current->is_required == 1 ? 'checked' : '' ?> required>
                                    <span></span>
                                    Tidak Wajib di Isi
                                </label>
                            </div>
                            <span class="form-text text-muted">Status pengisian pertanyaan ini digunakan untuk
                                mendefinisikan wajib atau tidaknya pertanyaan diisi.</span>
                        </div>
                    </div>


                    <input type="text" name="id_jenis_jawaban" value="<?php echo e($current->id_jenis_pilihan_jawaban); ?>" hidden>

                    <div class="form-group row" <?= $current->id_jenis_pilihan_jawaban == 2 ? 'hidden' : '' ?>>
                        <label class="col-sm-3 col-form-label fw-bold font-weight-bold">Model Pilihan Ganda
                            <span style="color:red;">*</span></label>
                        <div class="col-9 col-form-label">
                            <div class="radio-inline">
                                <label class="radio radio-md">
                                    <input type="radio" name="is_model_pilihan_ganda" id="is_model_pilihan_ganda" value="1" <?= $current->is_model_pilihan_ganda == 1 ? 'checked' : '' ?> required>
                                    <span></span>
                                    Hanya dapat memilih 1 Jawaban
                                </label>
                                <label class="radio radio-md">
                                    <input type="radio" name="is_model_pilihan_ganda" value="2" <?= $current->is_model_pilihan_ganda == 2 ? 'checked' : '' ?>>
                                    <span></span>
                                    Bisa memilih lebih dari 1 Jawaban
                                </label>
                            </div>
                            <span class="form-text text-muted">Model Pilihan Jawaban ini akan diterapkan didalam form
                                survei.</span>
                        </div>
                    </div>



                    <?php
                    $no = 1;
                    ?>
                    <?php $__currentLoopData = $pilihan_jawaban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <input type="hidden" class="form-control" id="id_kategori" name="id_kategori[]" value="<?php echo e($row->id_isi_pertanyaan_ganda); ?>">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Pilihan Jawaban <?php echo e($no++); ?>

                            <span style="color:red;">*</span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="pertanyaan_ganda" name="pertanyaan_ganda[]" value="<?php echo e($row->pertanyaan_ganda); ?>" <?= $row->pertanyaan_ganda == 'Lainnya' && $row->dengan_isian_lainnya == 1 ? 'disabled' : 'required'; ?>>
                            <?php if($row->pertanyaan_ganda == 'Lainnya' && $row->dengan_isian_lainnya == 1): ?>
                            <input name="pertanyaan_ganda[]" value="Lainnya" hidden>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                    <br>
                    <div class="text-right">

                        <?php
                        echo
                        anchor(base_url().$ci->session->userdata('username').'/'.$ci->uri->segment(2).'/pertanyaan-terbuka',
                        'Cancel', ['class' => 'btn btn-light-primary font-weight-bold'])
                        ?>
                        <?php echo form_submit('submit', 'Update', ['class' => 'btn btn-primary font-weight-bold']); ?>
                    </div>


                    <?php echo form_close(); ?>

                </div>

            </div>
        </div>
    </div>

</div>





<?php $__env->stopSection(); ?>



<?php $__env->startSection('javascript'); ?>
<script src="<?php echo e(TEMPLATE_BACKEND_PATH); ?>plugins/custom/datatables/datatables.bundle.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>


<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#isi_pertanyaan_terbuka'))
        .then(editor => {
            console.log(editor);
        })
        .catch(error => {
            console.error(error);
        });
</script>

<script>
    function delete_gambar_pertanyaan_terbuka(id_pertanyaan_terbuka) {
        if (confirm('Are you sure delete this data?')) {
            $.ajax({
                url: "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/delete-gambar/' ?>" +
                    id_pertanyaan_terbuka,
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    if (data.status) {

                        Swal.fire(
                            'Informasi',
                            'Berhasil menghapus gambar',
                            'success'
                        );
                        setTimeout(function() {
                            window.location.href = ('<?php echo e(base_url() . $ci->session->userdata('
                                username ')); ?>/<?php echo e($ci->uri->segment(2)); ?>/pertanyaan-terbuka/edit/<?php echo e($ci->uri->segment(5)); ?>'
                            );
                        }, 2000);
                    } else {
                        Swal.fire(
                            'Informasi',
                            'Hak akses terbatasi. Bukan akun administrator.',
                            'warning'
                        );
                    }


                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error deleting data');
                }
            });

        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('include_backend/template_backend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/pertanyaan_terbuka_survei/edit.blade.php ENDPATH**/ ?>