<?php
$ci = get_instance();
?>

<?php $__env->startSection('style'); ?>
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="container mt-5 mb-5" style="font-family: nunito;">
    <div class="text-center" data-aos="fade-up">
        <div id="progressbar" class="mb-5">
            <li id="account"><strong>Data Responden</strong></li>
            <li id="personal"><strong>Pertanyaan Survei</strong></li>
            <?php if($status_saran == 1): ?>
            <li id="payment"><strong>Saran</strong></li>
            <?php endif; ?>
            <li id="completed"><strong>Completed</strong></li>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow" data-aos="fade-up">
                
                <?php echo $__env->make('survei/_include/_benner_survei', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <div class="card-body">
                    <div>
                        <?php
                        $slug = $ci->uri->segment(2);

                        $data_user = $ci->db->query("SELECT *
                        FROM manage_survey
                        JOIN users ON manage_survey.id_user = users.id
                        WHERE slug = '$slug'")->row();
                        ?>

                        <?php echo $data_user->deskripsi_opening_survey; ?>

                    </div>
                    <br><br>
                    <?php if($ci->uri->segment(3) == NULL): ?>
                    <?php echo anchor(base_url() . 'survei/' . $ci->uri->segment(2) . '/data-responden', 'IKUT SURVEI',
                    ['class' => 'btn btn-warning btn-block font-weight-bold shadow']); ?>

                    <?php else: ?>
                    <?php echo anchor(base_url() . 'survei/' . $ci->uri->segment(2) . '/data-responden/' .
                    $ci->uri->segment(3), 'IKUT SURVEI', ['class' => 'btn btn-warning btn-block']); ?>

                    <?php endif; ?>
                </div>
            </div>
			
        </div>
    </div>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('include_backend/_template', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/survei/form_opening.blade.php ENDPATH**/ ?>