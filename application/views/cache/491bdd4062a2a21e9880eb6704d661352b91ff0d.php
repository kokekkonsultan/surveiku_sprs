<!-- CEK ATRIBUTE -->
<?php if(in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey)) &&
$pertanyaan_terbuka->num_rows() > 0): ?>
<div id="display_terbuka_<?php echo e($row->id_pertanyaan_unsur); ?>">
    <!-- Looping Pertanyaan Terbuka -->
    <?php
    $n = $pertanyaan_terbuka_atas->num_rows() + 1;
    ?>
    <?php $__currentLoopData = $pertanyaan_terbuka->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row_terbuka): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($row_terbuka->id_unsur_pelayanan == $row->id_unsur_pelayanan): ?>
    <?php
    $is_required_t = $row_terbuka->is_required != '' ? '' : '<b class="text-danger">*</b>';
    $is_required_t_i = $row_terbuka->is_required != '' ? '' : 'required';
    $model_t = $row_terbuka->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
    ?>

    <div class=" mt-10 mb-10">
        <input type="hidden" name="id_pertanyaan_terbuka[<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>]" value="<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>">
        <table class="table table-borderless" width="100%" border="0">
            <tr>
                <td width="5%" valign="top"><?php echo $row_terbuka->nomor_pertanyaan_terbuka . '' .
                    $is_required_t; ?>.</td>
                <td width="95%"><?php echo $row_terbuka->isi_pertanyaan_terbuka; ?></td>
            </tr>


            <?php if($row_terbuka->id_jenis_pilihan_jawaban == 1): ?>

            <tr>
                <td width="5%"></td>
                <td style="font-weight:bold;" width="95%">
                    <?php $__currentLoopData = $ci->db->get_where("isi_pertanyaan_ganda_$table_identity",
                    ['id_perincian_pertanyaan_terbuka' =>
                    $row_terbuka->id_perincian_pertanyaan_terbuka])->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value_terbuka): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <div class="<?php echo e($model_t); ?>-inline mb-2">
                        <label class="<?php echo e($model_t); ?> <?php echo e($model_t); ?>-outline <?php echo e($model_t); ?>-success <?php echo e($model_t); ?>-lg" style="font-size: 16px;">
                            <input type="<?php echo e($model_t); ?>" name="jawaban_pertanyaan_terbuka[<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>][]" value="<?php echo e($value_terbuka->pertanyaan_ganda); ?>" class="terbuka_<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>" <?php echo e($is_required_t_i); ?> <?= in_array($value_terbuka->pertanyaan_ganda, unserialize($row_terbuka->jawaban)) ? 'checked' : ''; ?>>
                            <span></span> <?php echo e($value_terbuka->pertanyaan_ganda); ?>

                        </label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                    <?php if($row_terbuka->dengan_isian_lainnya == 1 &&
                    $row_terbuka->is_model_pilihan_ganda == 1): ?>

                    <input class="form-control" name="jawaban_lainnya[<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>]" value="<?php echo e($row_terbuka->jawaban_lainnya); ?>" pattern="^[a-zA-Z0-9.,\s]*$|^\w$" placeholder="Masukkan jawaban lainnya ..." id="terbuka_lainnya_<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>" <?= in_array('Lainnya', unserialize($row_terbuka->jawaban)) ? 'required' : 'style="display:none"'; ?>>

                    <small id="text_terbuka_<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>" class="text-danger" <?= in_array('Lainnya', unserialize($row_terbuka->jawaban)) ? '' : 'style="display:none"'; ?>>**Pengisian
                        form hanya dapat menggunakan tanda baca
                        (.) titik dan (,) koma</small>
                    <br>
                    <?php endif; ?>
                </td>
            </tr>
            <?php else: ?>

            <tr>
                <td width="5%"></td>
                <td style="font-weight:bold;" width="95%">

                    <textarea class="form-control" type="text" name="jawaban_pertanyaan_terbuka[<?php echo e($row_terbuka->id_pertanyaan_terbuka); ?>][]" placeholder="Masukkan Jawaban Anda ..." <?= $row_terbuka->stts_required ?>><?php echo e($row_terbuka->jawaban != '' ?  implode("", unserialize($row_terbuka->jawaban)) : ''); ?></textarea>
                </td>
            </tr>


            <?php endif; ?>


        </table>



    </div>
    <?php endif; ?>
    <?php
    $n++;
    ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/survei/pertanyaan_terbuka/_tanpa_kategori/form_terbuka.blade.php ENDPATH**/ ?>