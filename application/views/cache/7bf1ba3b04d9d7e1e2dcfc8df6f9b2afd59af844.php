

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

            <div class="card card-custom bgi-no-repeat gutter-b" style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)" data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            <?php echo e(strtoupper($title)); ?>

                        </h3>

                        <?php if($profiles->is_question == 1): ?>
                        <a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add"><i class="fa fa-plus"></i> Tambah Wilayah
                        </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>




            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover example" style="width:100%">
                            <thead class="bg-secondary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Wilayah</th>

                                    <?php if($profiles->is_question == 1): ?>
                                    <th></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                ?>
                                <?php $__currentLoopData = $layanan->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td width="5%"><?php echo e($no++); ?></td>
                                    <td><?php echo e($value->nama_wilayah); ?></td>

                                    <?php if($profiles->is_question == 1): ?>
                                    <td>
                                        <a class="btn btn-light-primary btn-sm" data-toggle="modal" data-target="#edit_<?php echo e($value->id); ?>"><i class="fa fa-edit"></i> Edit
                                        </a>

                                        <a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus <?php echo e($value->nama_wilayah); ?>" onclick="delete_data(<?php echo e($value->id); ?>)"><i class="fa fa-trash"></i> Delete</a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>



<!-- MODAL ADD -->
<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <span class="modal-title" id="exampleModalLabel">Tambah Layanan</s>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/wilayah-survei/add' ?>" method="POST" class="form_default">


                    <div class="form-group">
                        <label class="col-form-label font-weight-bold">Nama Wilayah<span style="color: red;">*</span></label>
                        <input class="form-control" name="nama_wilayah" value="" required>
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm tombolSimpanDefault">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal EDIT -->
<?php $__currentLoopData = $layanan->result(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="modal fade" id="edit_<?php echo e($row->id); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <span class="modal-title" id="exampleModalLabel">Ubah Layanan</s>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/wilayah-survei/edit' ?>" method="POST" class="form_default">

                    <input name="id" value="<?php echo e($row->id); ?>" hidden>


                    <div class="form-group">
                        <label class="col-form-label font-weight-bold">Nama Wilayah<span style="color: red;">*</span></label>
                        <input class="form-control" name="nama_wilayah" value="<?php echo e($row->nama_wilayah); ?>" required>
                    </div>


                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm tombolSimpanDefault">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('javascript'); ?>
<script src="<?php echo e(TEMPLATE_BACKEND_PATH); ?>plugins/custom/datatables/datatables.bundle.js"></script>
<script src="<?php echo e(base_url()); ?>assets/themes/metronic/assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    $(document).ready(function() {
        $('.example').DataTable({
            "lengthMenu": [
                [5, 10, 50, -1],
                [5, 10, 50, "Semua data"]
            ],
            "pageLength": 5,
        });
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
                $('.tombolSimpanDefault').attr('disabled', 'disabled');
                $('.tombolSimpanDefault').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');
                KTApp.block('#content_1', {
                    overlayColor: '#000000',
                    state: 'primary',
                    message: 'Processing...'
                });
                setTimeout(function() {
                    KTApp.unblock('#content_1');
                }, 1000);
            },
            complete: function() {
                $('.tombolSimpanDefault').removeAttr('disabled');
                $('.tombolSimpanDefault').html('Simpan');
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
                    toastr["success"]('Data berhasil disimpan');
                    window.setTimeout(function() {
                        location.reload()
                    }, 2000);
                }
            }
        })
        return false;
    });




    function delete_data(id) {
        if (confirm('Are you sure delete this data?')) {
            $.ajax({
                url: "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/wilayah-survei/delete/' ?>" +
                    id,
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    if (data.status) {

                        Swal.fire(
                            'Informasi',
                            'Berhasil menghapus data',
                            'success'
                        );

                        window.setTimeout(function() {
                            location.reload()
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
<?php echo $__env->make('include_backend/template_backend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\IT\Documents\Htdocs MAMP\surveiku_sprs\application\views/wilayah_survei/index.blade.php ENDPATH**/ ?>