<div class="" data-aos="fade-down">
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: white;">
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
            <div class="navbar-nav">
                <a class="nav-item nav-link
      <?php if ($ci->uri->segment(4) == 'survey') {
            echo 'active text-primary';
        } ?>
      " href="<?php echo e(base_url()); ?><?php echo e($ci->session->userdata('username')); ?>/<?php echo e($ci->uri->segment(2)); ?>/settings/survey"><i
                        class="fa fa-clock"></i> Pengaturan Waktu Survei</span></a>

                <!-- <a class="nav-item nav-link 
      <?php if ($ci->uri->segment(4) == 'display') {
            echo 'active text-primary';
        } ?>" href="<?php echo e(base_url()); ?><?php echo e($ci->session->userdata('username')); ?>/<?php echo e($ci->uri->segment(2)); ?>/settings/display"><i class="fa fa-tasks"></i> Pengaturan Form Survei</a> -->

                <a class="nav-item nav-link 
      <?php if ($ci->uri->segment(4) == '') {
            echo 'active text-primary';
        } ?>" href="<?php echo e(base_url()); ?><?php echo e($ci->session->userdata('username')); ?>/<?php echo e($ci->uri->segment(2)); ?>/settings"><i
                        class="fas fa-cog"></i> Pengaturan Umum</a>
            </div>
        </div>
    </nav>
</div><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/setting_survei/menu_settings.blade.php ENDPATH**/ ?>