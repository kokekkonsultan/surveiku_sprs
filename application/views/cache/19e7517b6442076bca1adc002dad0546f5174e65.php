<?php
$ci = get_instance();
?>


<div class="card card-custom card-stretch gutter-b wave wave-animate-slow">

    <div class="card-header align-items-center border-0 mt-4">
        <h3 class="card-title align-items-start flex-column">
            <span class="font-weight-bolder text-dark">Aktivitas Terakhir</span>
            <span class="text-muted mt-3 font-weight-bold font-size-sm"><?php echo $total_log_survey ?> Responden Telah
                Mengisi Survei</span>
        </h3>
        <div class="card-toolbar">

        </div>
    </div>

    <div class="card-body pt-4">
        <div class="timeline timeline-6 mt-3">

            <?php $__currentLoopData = $log_survey; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

            <?php if ((substr($row->id, -1) == 0) || (substr($row->id, -1) == 5)) {
                $color = 'warning';
            } else if ((substr($row->id, -1) == 1) || (substr($row->id, -1) == 6)) {
                $color = 'success';
            } else if ((substr($row->id, -1) == 2) || (substr($row->id, -1) == 7)) {
                $color = 'danger';
            } else if ((substr($row->id, -1) == 3) || (substr($row->id, -1) == 8)) {
                $color = 'primary';
            } else if ((substr($row->id, -1) == 4) || (substr($row->id, -1) == 9)) {
                $color = 'info';
            } else {
                $color = 'primary';
            } ?>


            <div class="timeline-item align-items-start">
                <div class="timeline-label font-weight-bolder text-dark-75 font-size-lg">
                    <?php echo e(date("H:i", strtotime($row->log_time))); ?> <br> <?php echo e(date("d M", strtotime($row->log_time))); ?>

                </div>
                <div class="timeline-badge">
                    <i class="fa fa-genderless text-<?php echo $color ?> icon-xl"></i>
                </div>
                <div class="font-weight-mormal font-size timeline-content text-muted pl-3"><?php echo e($row->log_value); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/manage_survey/overview/list_activity.blade.php ENDPATH**/ ?>