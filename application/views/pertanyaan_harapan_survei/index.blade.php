@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet"
    type="text/css" />
@endsection

@section('content')

<div class="container-fluid">
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            <div class="card card-custom bgi-no-repeat gutter-b"
                style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)"
                data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            {{strtoupper($title)}}
                        </h3>

                        <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-lg"
                            data-toggle="modal" data-target="#exampleModal">
                            <i class="fas fa-edit"></i> Edit Pilihan Jawaban
                        </button>
                    </div>
                </div>
            </div>

            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">

                    <p>
                        Pertanyaan Harapan SKP ini bertujuan untuk membandingkan antara harapan dan kebutuhannya dalam
                        survei. Isi dari pertanyaan harapan ini sama dengan pertanyaan unsur namun pilihan jawaban anda
                        bebas memilihnya. Secara umum pilihan jawaban yaitu (Tidak Penting | Kurang Penting | Penting |
                        Sangat Penting)
                    </p>

                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%"
                            style="font-size: 12px;">
                            <thead class="bg-secondary">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th>Unsur Pelayanan</th>
                                    <th>Isi Pertanyaan</th>
                                    <th>Pilihan Jawaban</th>
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




<!-- MODAL -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Edit Pilihan Jawaban</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form class="form_edit"
                    action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-harapan/edit' ?>"
                    method="POST">

                    @php
                    echo validation_errors();
                    @endphp

                    <datalist id="data_jawaban">
                        <?php
                        foreach ($pilihan_jawaban->result() as $d) {
                            echo "<option value='$d->id'>$d->pilihan_1</option>";
                        }
                        ?>
                    </datalist>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Pilihan Jawaban
                            1</label>
                        <div class="col-sm-8">
                            <input class="form-control" list="data_jawaban" type="text" name="pilihan_1" id="id"
                                placeholder="Masukkan Pilihan Jawaban ..." onchange="return autofill();" autofocus
                                autocomplete='off' required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Pilihan Jawaban
                            2</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="pilihan_2" id="pilihan_2" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Pilihan Jawaban
                            3</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="pilihan_3" id="pilihan_3" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Pilihan Jawaban
                            4</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="pilihan_4" id="pilihan_4" required>
                        </div>
                    </div>

                    @if($manage_survey->skala_likert == 5)
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label font-weight-bold">Pilihan Jawaban
                            5</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="pilihan_5" id="pilihan_5" required>
                        </div>
                    </div>
                    @endif

                    <br>

                    <div class="text-right">
                        <button type="button" class="btn btn-light-primary font-weight-bold btn-sm shadow"
                            data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary font-weight-bold btn-sm shadow tombolSimpan">Ubah
                            Pilihan
                            Jawaban</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
$(document).ready(function() {
    table = $('#table').DataTable({

        "processing": true,
        "serverSide": true,
        "lengthMenu": [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, "Semua data"]
        ],
        "pageLength": 5,
        "order": [],
        "language": {
            "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> ',
        },
        "ajax": {
            "url": "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-harapan/ajax-list' ?>",
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
</script>

<script>
function autofill() {
    var id = document.getElementById('id').value;
    $.ajax({
        url: "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-harapan/cari' ?>",
        data: '&id=' + id,
        success: function(data) {
            var hasil = JSON.parse(data);

            $.each(hasil, function(key, val) {

                document.getElementById('id').value = val.pilihan_1;
                document.getElementById('pilihan_2').value = val.pilihan_2;
                document.getElementById('pilihan_3').value = val.pilihan_3;
                document.getElementById('pilihan_4').value = val.pilihan_4;
                document.getElementById('pilihan_5').value = val.pilihan_5;
            });
        }
    });
}
</script>


<script>
$('.form_edit').submit(function(e) {

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        cache: false,
        beforeSend: function() {
            $('.tombolSimpan').attr('disabled', 'disabled');
            $('.tombolSimpan').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

        },
        complete: function() {
            $('.tombolSimpan').removeAttr('disabled');
            $('.tombolSimpan').html('Ubah Pilihan Jawaban');
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
                table.ajax.reload();
            }
        }
    })
    return false;

});
</script>
@endsection