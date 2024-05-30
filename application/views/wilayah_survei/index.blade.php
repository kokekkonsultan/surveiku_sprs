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

                        @if ($profiles->is_question == 1)
                        <a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#add"><i class="fa fa-plus"></i> Tambah Wilayah
                        </a>
                        @endif

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

                                    @if ($profiles->is_question == 1)
                                    <th></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $no = 1;
                                @endphp
                                @foreach($layanan->result() as $value)
                                <tr>
                                    <td width="5%">{{$no++}}</td>
                                    <td>{{$value->nama_wilayah}}</td>

                                    @if ($profiles->is_question == 1)
                                    <td>
                                        <a class="btn btn-light-primary btn-sm" data-toggle="modal" data-target="#edit_{{$value->id}}"><i class="fa fa-edit"></i> Edit
                                        </a>

                                        <a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus {{$value->nama_wilayah}}" onclick="delete_data({{$value->id}})"><i class="fa fa-trash"></i> Delete</a>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
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
@foreach($layanan->result() as $row)
<div class="modal fade" id="edit_{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <span class="modal-title" id="exampleModalLabel">Ubah Layanan</s>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/wilayah-survei/edit' ?>" method="POST" class="form_default">

                    <input name="id" value="{{$row->id}}" hidden>


                    <div class="form-group">
                        <label class="col-form-label font-weight-bold">Nama Wilayah<span style="color: red;">*</span></label>
                        <input class="form-control" name="nama_wilayah" value="{{$row->nama_wilayah}}" required>
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
@endforeach

@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="{{ base_url() }}assets/themes/metronic/assets/plugins/custom/datatables/datatables.bundle.js"></script>
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
@endsection