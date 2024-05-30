<?php
$ci = get_instance();
?>

<?php $__env->startSection('style'); ?>
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
<?php $__env->stopSection(); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php $__env->startSection('content'); ?>
<div class="container mt-5 mb-5" style="font-family: nunito;">
    <div class="text-center" data-aos="fade-up">
        <div id="progressbar" class="mb-5">
            <li class="active" id="account"><strong>Data Responden</strong></li>
            <li class="active" id="personal"><strong>Pertanyaan Survei</strong></li>
            <?php if($status_saran == 1): ?>
            <li id="payment"><strong>Saran</strong></li>
            <?php endif; ?>
            <li id="completed"><strong>Completed</strong></li>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-8 offset-md-2" style="font-size: 16px;">
            <div class="card shadow mb-4 mt-4" data-aos="fade-up" style="font-family: 'Exo 2', sans-serif;">

                <?php echo $__env->make('survei/_include/_benner_survei', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <div class="card-header text-center">
                    <h3 class="mt-5" style="font-family: 'Exo 2', sans-serif;"><b>PERTANYAAN</b></h3>
                    <?php echo $__env->make('include_backend/partials_backend/_tanggal_survei', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>

                <form action="<?php echo e(base_url() . 'survei/' . $ci->uri->segment(2) . '/add_pertanyaan/' . $ci->uri->segment(4)); ?>" class="form_survei" method="POST">

                    <div class="card-body ml-5 mr-5">



                        

                        <?php echo $__env->make('survei/pertanyaan_terbuka/_tanpa_kategori/form_terbuka_atas', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>



                        <!--============================================================================================================================================================================================================================================================================================================================================================================== -->


                        <!-- LOOP DIMENSI -->
                        <?php $__currentLoopData = $ci->db->query("SELECT *
                        FROM (SELECT *, (SELECT COUNT(id) FROM unsur_pelayanan_$manage_survey->table_identity WHERE
                        dimensi_$manage_survey->table_identity.id =
                        unsur_pelayanan_$manage_survey->table_identity.id_dimensi) AS jumlah FROM
                        dimensi_$manage_survey->table_identity) dms_$manage_survey->table_identity
                        WHERE jumlah > 0
                        ORDER BY id ASC")->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dms): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>


                        <div class="text-center" style="background-color: #FCF7B6; padding: 1em; font-family: 'Exo 2', sans-serif; color:#294087;">
                            <h4 class="mt-2"><b><?php echo e(strtoupper($dms->dimensi)); ?></b></h4>
                            <span><?php echo e($dms->keterangan); ?></span>
                        </div>
                        

                        <!-- LOOP UNSUR -->
                        <?php
                        $i = 1;
                        ?>
                        <?php $__currentLoopData = $pertanyaan_unsur->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($dms->id == $row->id_dimensi): ?>
                        <?php
                        $is_required = $row->is_required == 1 ? 'required' : '';
                        $is_required_u = $row->is_required == 1 ? '<b class="text-danger">*</b>' : '';
                        $model_u = $row->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
                        ?>

                        <div class="mt-10 mb-10">
                            <input type="hidden" name="id_pertanyaan_unsur[<?php echo e($i); ?>]" value="<?php echo e($row->id_pertanyaan_unsur); ?>">
                            <table class="table table-borderless" width="100%" border="0">
                                <tr>
                                    <td width="5%" valign="top"><?php echo $row->nomor . '' . $is_required_u; ?>.</td>
                                    <td width="95%"><?php echo $row->isi_pertanyaan_unsur; ?></td>
                                </tr>

                                <tr>
                                    <td width="5%"></td>
                                    <td style="font-weight:bold;" width="95%">

                                        
                                        <?php $__currentLoopData = $ci->db->get_where("kategori_unsur_pelayanan_$table_identity",
                                        ['id_pertanyaan_unsur' => $row->id_pertanyaan_unsur])->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="<?php echo e($model_u); ?>-inline mb-2">
                                            <label class="<?php echo e($model_u); ?> <?php echo e($model_u); ?>-outline <?php echo e($model_u); ?>-success <?php echo e($model_u); ?>-lg" style="font-size: 16px;">

                                                <input type="<?php echo e($model_u); ?>" name="jawaban_pertanyaan_unsur[<?php echo e($i); ?>][]" value="<?php echo e($value->nomor_kategori_unsur_pelayanan); ?>" class="unsur_<?php echo e($value->id_pertanyaan_unsur); ?>" <?= in_array($value->nomor_kategori_unsur_pelayanan, unserialize($row->skor_jawaban)) ? 'checked' : ''; ?>
                                                <?php echo e($is_required); ?>><span></span>
                                                <?php echo e($value->nama_kategori_unsur_pelayanan); ?>

                                            </label>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                </tr>

                            </table>
                        </div>


                        

                        <?php echo $__env->make('survei/pertanyaan_terbuka/_tanpa_kategori/form_terbuka', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


                        <?php endif; ?>
                        <?php
                        $i++;
                        ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>







                        <!--============================================================================================================================================================================================================================================================================================================================================================================== -->




                        

                        <?php echo $__env->make('survei/pertanyaan_terbuka/_tanpa_kategori/form_terbuka_bawah', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    </div>


                    <div class="card-footer">
                        <table class="table table-borderless">
                            <tr>
                                <?php if($ci->uri->segment(5) == 'edit'): ?>
                                <td class="text-left">
                                    <a class="btn btn-secondary btn-lg font-weight-bold shadow" href="<?php echo e(base_url() . 'survei/' . $ci->uri->segment(2) . '/data-responden/' . $ci->uri->segment(4) . '/edit'); ?>"><i class="fa fa-arrow-left"></i> Kembali
                                    </a>
                                </td>
                                <?php endif; ?>

                                <td class="text-right">
                                    <button type="submit" class="btn btn-warning btn-lg font-weight-bold shadow-lg tombolSave">Selanjutnya
                                        <i class="fa fa-arrow-right"></i>
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>



<?php $__env->startSection('javascript'); ?>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>


<?php $__currentLoopData = $pertanyaan_unsur->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if($pr->is_model_pilihan_ganda == 2): ?>
<script type="text/javascript">
    $(document).ready(function() {
        var checkboxes = $('.unsur_<?php echo e($pr->id_pertanyaan_unsur); ?>');
        checkboxes.change(function() {
            // if ($('.unsur_<?php echo e($pr->id_pertanyaan_unsur); ?>:checked').length > 0) {
            //     checkboxes.removeAttr('required');
            // } else {
            //     checkboxes.attr('required', 'required');
            // }

            if($('.unsur_<?php echo e($pr->id_pertanyaan_unsur); ?>:checked').length > 0){
                checkboxes.removeAttr('required');

                <?php if($pr->limit_pilih > 0 || $pr->limit_pilih != '' || $pr->limit_pilih != null){ ?>
                if($('.unsur_<?php echo e($pr->id_pertanyaan_unsur); ?>:checked').length > <?= $pr->limit_pilih ?>){
                    this.checked = false;
                    alert("Anda hanya dapat memilih max <?php echo e($pr->limit_pilih); ?> pilihan jawaban.");
                }
                <?php } ?>
                
            } else {
                checkboxes.attr('required', 'required');
            }

            
        });
        if ($('.unsur_<?php echo e($pr->id_pertanyaan_unsur); ?>:checked').length > 0) {
            checkboxes.removeAttr('required');
        }
    });
</script>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



<?php $__currentLoopData = $ci->db->get("pertanyaan_terbuka_$manage_survey->table_identity")->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<script type="text/javascript">
    $(function() {
        $(":radio.terbuka_<?= $pt->id ?>").click(function() {
            if ($(this).val() == 'Lainnya') {
                $("#terbuka_lainnya_<?= $pt->id ?>").prop('required', true).show();
                $("#text_terbuka_<?= $pt->id ?>").show();
            } else {
                $("#terbuka_lainnya_<?= $pt->id ?>").removeAttr('required').hide();
                $("#text_terbuka_<?= $pt->id ?>").hide();
            }

        });

    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        var checkboxes = $('.terbuka_<?php echo e($pt->id); ?>');
        checkboxes.change(function() {
            if ($('.terbuka_<?php echo e($pt->id); ?>:checked').length > 0) {
                checkboxes.removeAttr('required');
            } else {
                checkboxes.attr('required', 'required');
            }
        });
        if ($('.terbuka_<?php echo e($pt->id); ?>:checked').length > 0) {
            checkboxes.removeAttr('required');
        }
    });
</script>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>




<script>
    $('.form_survei').submit(function(e) {

        var textboxes = document.getElementsByClassName("form-alasan");
        for (var i = 0; i < textboxes.length; i++) {
            var textbox = textboxes[i].value;
            var result = !!textbox.match(/[-:;!?"'()/{}<>@#$%^&*_+=|`~]/)
            if (result) {
                //alert("Pengisian alasan hanya dapat menggunakan tanda baca(.) titik dan (,) koma");
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pengisian alasan hanya dapat menggunakan tanda baca(.) titik dan (,) koma !',
                    confirmButtonColor: '#8950FC',
                    confirmButtonText: 'Baik, saya mengerti',
                })
                return false;
            }
        }

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSave').attr('disabled', 'disabled');
                $('.tombolSave').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

                KTApp.block('#kt_blockui_content', {
                    overlayColor: '#FFA800',
                    state: 'primary',
                    message: 'Processing...'
                });

                setTimeout(function() {
                    KTApp.unblock('#kt_blockui_content');
                }, 1000);

            },
            complete: function() {
                $('.tombolSave').removeAttr('disabled');
                $('.tombolSave').html('Selanjutnya <i class="fa fa-arrow-right"></i>');
            },

            error: function(e) {
                Swal.fire(
                    'Error !',
                    e,
                    'error'
                )
            },

            success: function(data) {
                if (data.validasi) {
                    $('.pesan').fadeIn();
                    $('.pesan').html(data.validasi);
                }
                if (data.sukses) {
                    // toastr["success"]('Data berhasil disimpan');

                    setTimeout(function() {
                        window.location.href = "<?= $url_next ?>";
                    }, 500);
                }
            }
        })
        return false;
    });


    $('.form-alasan').keyup(function() {
        var textboxes = document.getElementsByClassName("form-alasan");
        for (var i = 0; i < textboxes.length; i++) {
            var textbox = textboxes[i].value;
            var result = !!textbox.match(/[-:;!?"'()/{}<>@#$%^&*_+=|`~]/)
            if (result) {
                //alert("Pengisian alasan hanya dapat menggunakan tanda baca(.) titik dan (,) koma");
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Pengisian alasan hanya dapat menggunakan tanda baca(.) titik dan (,) koma !',
                    confirmButtonColor: '#8950FC',
                    confirmButtonText: 'Baik, saya mengerti',
                })
                textboxes[i].focus();
            }
        }
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('include_backend/_template', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/survei/pertanyaan_unsur/form_pertanyaan_in_dimensi.blade.php ENDPATH**/ ?>