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

            <div class="card" data-aos="fade-down">
                <div class="card-header bg-secondary">
                    <h5>{{ $title }}</h5>
                </div>
                <div class="card-body">
                    <span class="text-danger"><?php echo validation_errors(); ?></span>
                    <br>
                    <?php echo form_open(base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-unsur/add'); ?>


                    @if($manage_survey->is_dimensi == 1)
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Dimensi <span
                                style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            @php
                            echo form_dropdown($id_dimensi);
                            @endphp

                            <a href="#" class="font-weight-bold text-primary" data-toggle="modal"
                                data-target="#add">Tambah Dimensi Baru</a>
                        </div>
                    </div>
                    @else
                    <input value="" name="id_dimensi" hidden>
                    @endif



                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Unsur Pelayanan <span
                                style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <div class="input-group-prepend"><span
                                        class="input-group-text font-weight-bold">U<?php echo $jumlah_unsur ?></span>
                                </div>

                                <!-- <input name="nama_unsur_pelayanan" value="Unsur <?php echo $jumlah_unsur ?>"> -->
                                @php
                                echo form_input($nama_unsur_pelayanan);
                                @endphp
                                <!-- <small>
                                    Menurut Permenpan dan RB, unsur SKP terbagi 9 unsur antara lain: 1) Persyaratan 2)
                                    Sistem, Mekanisme, dan Prosedur 3) Waktu Penyelesaian 4) Biaya/Tarif 5) Produk
                                    Spesifikasi Jenis Pelayanan 6) Kompetensi Pelaksana 7) Perilaku Pelaksana 8)
                                    Penanganan Pengaduan, Saran dan Masukan 9) Sarana dan prasarana
                                </small> -->
                            </div>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Sub Unsur Pelayanan <span
                                style="color: red;">*</span></label>
                        <div class="col-9 col-form-label">
                            <div class="radio-inline">
                                <label class="radio radio">
                                    <input type="radio" name="is_sub_unsur_pelayanan" id="default" value="2"
                                        class="custom" required>
                                    <span></span> Tanpa Sub Unsur
                                </label>
                            </div>
                            <span class="form-text text-muted">Jenis Pertanyaan yang tidak memiliki turunan sub.</span>
                            <hr>
                            <div class="radio-inline">
                                <label class="radio radio">
                                    <input type="radio" name="is_sub_unsur_pelayanan" id="custom" value="1"
                                        class="custom">
                                    <span></span> Dengan Sub Unsur
                                </label>
                            </div>
                            <span class="form-text text-muted">Jenis Pertanyaan yang memiliki turunan sub unsur.</span>
                        </div>
                    </div>

                    <!-- //DENGAN SUB UNSUR -->
                    <div id="dengan_sub_unsur" style="display: none;">
                        <div class="alert alert-custom alert-notice alert-light-info fade show mb-10" role="alert">
                            <div class="alert-icon"><i class="flaticon-warning"></i></div>
                            <div class="alert-text"> <span>Silahkan <b>simpan</b> terlebih dahulu lalu anda akan
                                    diarahkan untuk mengisi form <b>Pertanyaan Sub Unsur Pelayanan</b> atau anda dapat
                                    melanjutkan
                                    malalui menu <b>Tambah Pertanyaan Sub Unsur Pelayanan</b></span></div>
                            <div class="alert-close">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- TANPA SUB UNSUR -->
                    <div id="tanpa_sub_unsur" style="display: none;">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Pertanyaan Unsur <span
                                    style="color: red;">*</span></label>
                            <div class="col-sm-9">
                                @php
                                echo form_textarea($isi_pertanyaan_unsur);
                                @endphp
                            </div>
                            </label>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label
                            font-weight-bold">Wajib Diisi <span style="color: red;">*</span></label>
                            <div class="col-sm-9">
                                <select class="form-control" id="is_required" name="is_required" required>
                                    <option value=''>Please Select</option>
                                    <option value='1'>Aktif</option>
                                    <option value='2'>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label fw-bold font-weight-bold">Model Pilihan Ganda
                                <span style="color:red;">*</span></label>
                            <div class="col-9 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio-md">
                                        <input class="is_model_pilihan_ganda" type="radio" name="is_model_pilihan_ganda"
                                            id="is_model_pilihan_ganda" value="1">
                                        <span></span>
                                        Hanya dapat memilih 1 Jawaban
                                    </label>
                                    <label class="radio radio-md">
                                        <input class="is_model_pilihan_ganda" type="radio" name="is_model_pilihan_ganda"
                                            value="2">
                                        <span></span>
                                        Bisa memilih lebih dari 1 Jawaban
                                    </label>
                                </div>
                                <span class="form-text text-muted">Model Pilihan Jawaban ini akan diterapkan didalam
                                    form survei.</span>



                                <div class="form-group row mt-5" id="form-limit" style="display: none;">
                                    <label class="col-sm-5 col-form-label font-weight-bold">Limit Maximal Pilih Jawaban
                                        <span style="color: red;">*</span></label>
                                    <div class="col-sm-7">
                                        <input type="number" class="form-control" id="limit_pilih" name="limit_pilih"
                                            placeholder="Masukkan jumlah maximal pilihan yang dapat dipilih responden...">
                                    </div>
                                </div>
                            </div>
                        </div>






                        <h6 class="text-primary mt-5">Pilihan Jawaban</h6>
                        <hr class="mb-5">


                        <div class="form-group fieldGroup">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label font-weight-bold">Pilihan Jawaban <span
                                        style="color: red;">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="pilihan_jawaban[]" class="form-control"
                                        placeholder="Masukkan Pilihan Jawaban . . .">
                                </div>
                                <div class="input-group-addon col-sm-1">
                                    <a href="javascript:void(0)" class="btn btn-light-success addMore"><i
                                            class="fas fa-plus"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="form-group fieldGroupCopy" style="display:none">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label font-weight-bold">Pilihan Jawaban <span
                                        style="color: red;">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="pilihan_jawaban[]" class="form-control"
                                        placeholder="Masukkan Pilihan Jawaban . . .">
                                </div>
                                <div class="input-group-addon col-sm-1">
                                    <a href="javascript:void(0)" class="btn btn-light-danger remove"><i
                                            class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        </div>






                    </div>



                    <div class="text-right">
                        @php
                        echo
                        anchor(base_url().$ci->session->userdata('username').'/'.$ci->uri->segment(2).'/pertanyaan-unsur',
                        'Batal', ['class' => 'btn btn-light-primary font-weight-bold'])
                        @endphp
                        <?php echo form_submit('submit', 'Simpan', ['class' => 'btn btn-primary font-weight-bold']); ?>
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>

        </div>
    </div>

</div>



<!-- ============================================ MODAL ADD DIMENSI ========================================================== -->
<div class="modal fade" id="add" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Dimensi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form
                    action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/dimensi-survei/add' ?>"
                    class="form_default" method="POST">
                    

                    <div class="form-group">
                        <label class="font-weight-bold">Dimensi
                            <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span
                                    class="input-group-text font-weight-bold">D{{$ci->db->get("dimensi_$profiles->table_identity")->num_rows() + 1}}</span>
                            </div>
                            <input type="text" class="form-control" name="dimensi" required autofocus>
                        </div>
                        <input type="hidden" name="kode" value="D{{$ci->db->get("dimensi_$profiles->table_identity")->num_rows() + 1}}">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" value=""></textarea>
                    </div>

                    <div class="text-right mt-5">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit"
                            class="btn btn-primary btn-sm font-weight-bold tombolSimpan">Simpan</button>
                    </div>

                </form>


            </div>
        </div>
    </div>
</div>

@endsection

@section ('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
$(function() {
    $(":radio.is_model_pilihan_ganda").click(function() {
        $("#form-limit").hide();
        if ($(this).val() == 1) {

            $("#limit_pilih").removeAttr('required');
            $("#form-limit").hide();
        } else {
            $("#limit_pilih").prop('required', true);
            $("#form-limit").show();
        }
    });
});


$(function() {
    $(":radio.custom").click(function() {
        $("#dengan_sub_unsur").hide();
        $("#tanpa_sub_unsur").hide();
        if ($(this).val() == "1") {
            $("#is_required").removeAttr('required');
            $("#is_alasan").removeAttr('required');
            $("#dengan_sub_unsur").show();
            $("#tanpa_sub_unsur").hidden();
        } else {
            $("#is_required").prop('required', true);
            $("#is_alasan").prop('required', true);
            $("#tanpa_sub_unsur").show();
            $("#dengan_sub_unsur").hidden();
        }
    });
});
</script>


<script>
$(document).ready(function() {
    // membatasi jumlah inputan
    var maxGroup = 20;

    //melakukan proses multiple input 
    $(".addMore").click(function() {
        if ($('body').find('.fieldGroup').length < maxGroup) {
            var fieldHTML = '<div class="form-group fieldGroup">' + $(".fieldGroupCopy").html() +
                '</div>';
            $('body').find('.fieldGroup:last').after(fieldHTML);
        } else {
            alert('Maximum ' + maxGroup + ' groups are allowed.');
        }
    });

    //remove fields group
    $("body").on("click", ".remove", function() {
        $(this).parents(".fieldGroup").remove();
    });
});
</script>


<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script>
ClassicEditor
    .create(document.querySelector('#isi_pertanyaan_unsur'))
    .then(editor => {
        console.log(editor);
    })
    .catch(error => {
        console.error(error);
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
            $('.tombolSimpan').attr('disabled', 'disabled');
            $('.tombolSimpan').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

        },
        complete: function() {
            $('.tombolSimpan').removeAttr('disabled');
            $('.tombolSimpan').html('Simpan');
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
</script>
@endsection