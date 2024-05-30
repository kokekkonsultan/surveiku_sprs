@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="container-fluid">
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            <div class="card card-custom bgi-no-repeat gutter-b" style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)" data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            {{strtoupper($title)}}
                        </h3>

                        @if ($is_question == 1)
                        <button type="button" class="btn btn-primary font-weight-bold shadow-lg btn-sm" data-toggle="modal" data-target="#exampleModal">
                            <i class="fas fa-plus"></i> Tambah Pertanyaan Tambahan
                        </button>
                        @endif

                    </div>
                </div>
            </div>

            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%" style="font-size: 12px;">
                            <thead class="bg-secondary">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th>Isi Pertanyaan</th>
                                    <th>Pilihan Jawaban</th>
                                    <th>Letak Pertanyaan</th>
                                    <th></th>
                                    <?php if ($is_question == 1) {
                                        echo '<th></th>';
                                    } ?>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                Pilih Jenis Pertanyaan Tambahan
            </div>
            <div class="modal-body">
                <div class="card-deck">
                    <a href="{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/add/1'}}" class="card card-body btn btn-outline-primary shadow">
                        <div class="text-center font-weight-bold">
                            <i class="fas fa-plus"></i><br>Melekat Pada Unsur Pelayanan
                        </div>
                    </a>

                    <a href="{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/add/2'}}" class="card card-body btn btn-outline-primary shadow">
                        <div class="text-center font-weight-bold">
                            <i class="fas fa-plus"></i><br>Tidak Melekat Pada Unsur Pelayanan
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>



@php
$no = 1;
@endphp
@foreach($pertanyaan_tambahan->result() as $value)
<!-- Modal -->
<div class="modal fade" id="exampleModal{{ $no++ }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit nomor tambahan dan nama tambahan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>

            <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/edit-terbuka' ?>" method="POST" class="form_default">
                <div class="modal-body">

                    <input name="id" value="{{$value->id_terbuka}}" hidden>
                    <div class="form-group">
                        <label>Nomor tambahan dan nama tambahan saat ini</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">{{ $value->nomor_pertanyaan_terbuka }}</span></div>
                            <input type="text" class="form-control" placeholder="" value="{{ $value->nama_pertanyaan_terbuka }}" disabled />
                        </div>
                    </div>

                    <div class="">
                        <label>Inputkan nomor tambahan dan nama tambahan yang akan anda ubah pada bidang dibawah ini</label>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <input type="text" name="nomor_pertanyaan_terbuka" class="form-control" value="{{ $value->nomor_pertanyaan_terbuka }}" required />
                        </div>
                        <div class="col-md-10">
                            <input type="text" name="nama_pertanyaan_terbuka" class="form-control" value="{{ $value->nama_pertanyaan_terbuka }}" required />
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary tombolSimpanDefault">Simpan</button>
                </div>
            </form>

        </div>
    </div>
</div>
@endforeach




@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
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
                    // window.setTimeout(function() {
                    //     location.reload()
                    // }, 2000);
                    table.ajax.reload();
                }
            }
        })
        return false;
    });

    $(document).ready(function() {

        table = $('#table').DataTable({

            "processing": true,
            "serverSide": true,
            "order": [],
            "language": {
                "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> ',
            },
            "ajax": {
                "url": "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/ajax-list' ?>",
                "type": "POST",
                "data": function(data) {}
            },

            "columnDefs": [{
                "targets": [-1],
                "orderable": false,
            }, ],

        });
    });

    $('#btn-filter').click(function() {
        table.ajax.reload();
    });
    $('#btn-reset').click(function() {
        $('#form-filter')[0].reset();
        table.ajax.reload();
    });

    function delete_pertanyaan_terbuka(id_pertanyaan_terbuka) {
        if (confirm('Are you sure delete this data?')) {
            $.ajax({
                url: "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka-survey/delete/' ?>" +
                    id_pertanyaan_terbuka,
                type: "POST",
                dataType: "JSON",
                success: function(data) {
                    if (data.status) {

                        table.ajax.reload();

                        Swal.fire(
                            'Informasi',
                            'Berhasil menghapus data',
                            'success'
                        );
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
@endsection