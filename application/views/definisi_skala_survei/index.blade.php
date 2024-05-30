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

                        <!-- @if ($profiles->is_question == 1)
                        <a class="btn btn-primary btn-sm font-weight-bold"
                            href="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/definisi-skala/add' ?>"><i
                                class="fa fa-plus-square"></i>
                            Buat Range Nilai Interval
                        </a>
                        @endif -->

                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#UpdateRange">
                            <i class="fa fa-edit"></i> Ubah Range interval
                        </button>

                    </div>
                </div>
            </div>

            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">
                    <p>
                        Anda bisa mendefinisikan sendiri nilai interval untuk survei anda. Nilai Interval pada sistem
                        ini
                        menggunakan skala 100.
                        <br><br>
                    </p>
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%" style="font-size: 12px;">
                            <thead class="bg-secondary">
                                <tr>
                                    <th width="5%">Skala</th>
                                    <th>Batas Atas</th>
                                    <th>Batas Bawah</th>
                                    <th>Mutu</th>
                                    <th>Kategori</th>
                                    <th></th>
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



<!-- Modal Update Range -->
<div class="modal fade" id="UpdateRange" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="exampleModalLabel">Pilih Range Interval</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/definisi-skala/ganti-range' ?>" method="POST" class="form_default">

                    @php
                    $range_template = $ci->db->query("SELECT * FROM definisi_skala WHERE skala_likert =
                    $manage_survey->skala_likert GROUP BY kelompok_range");
                    $range_survei = $ci->db->query("SELECT * FROM definisi_skala_$profiles->table_identity GROUP BY
                    kelompok_range")->row();
                    @endphp
                    @foreach($range_template->result() as $row)
                    <div class="radio-inline">
                        <label class="radio radio">
                            <input type="radio" name="kelompok_range" id="range_{{$row->kelompok_range}}" value="{{$row->kelompok_range}}">
                            <span></span> Range {{$row->kelompok_range}}
                        </label>
                    </div>
                    <small class="form-text text-muted">Penentuan Nilai dan Predikat Nilai menggunakan <b>Range Interval
                            {{$row->kelompok_range}}</b></small>
                    <hr>
                    @endforeach

                    <br>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm tombolSimpanDefault">Simpan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>


<!-- ======================================= EDIT ========================================== -->
<div class="modal fade" id="modal_edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light-primary">
                <h5 class="modal-title" id="exampleModalLabel">Edit Range</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body" id="bodyModalEdit">
                <div align="center" id="loading_registration">
                    <img src="{{ base_url() }}assets/site/img/ajax-loader.gif" alt="">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>

@foreach($range_template->result() as $value)
<script>
    $("#range_<?= $value->kelompok_range ?>").prop("checked",
        <?= $value->kelompok_range == $range_survei->kelompok_range ? 'true' : 'false' ?>);
</script>
@endforeach


<script>
    $(document).ready(function() {
        table = $('#table').DataTable({
            "processing": true,
            "serverSide": true,
            "order": [],
            "language": {
                "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> ',
            },
            "ajax": {
                "url": "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/definisi-skala/ajax-list' ?>",
                "type": "POST",
                "data": function(data) {}
            },
            "columnDefs": [{
                "targets": [-1],
                "orderable": false,
            }, ],
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
                    table.ajax.reload();
                }
            }
        })
        return false;
    });
</script>


<script>
    function showedit(id) {
        $('#bodyModalEdit').html(
            "<div class='text-center'><img src='{{ base_url() }}assets/img/ajax/ajax-loader-big.gif'></div>");

        $.ajax({
            type: "post",
            url: "<?= base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/definisi-skala/modal-edit/' ?>" +
                id,
            // data: "id=" + id,
            dataType: "text",
            success: function(response) {

                // $('.modal-title').text('Edit Pertanyaan Unsur');
                $('#bodyModalEdit').empty();
                $('#bodyModalEdit').append(response);
            }
        });
    }
</script>
@endsection