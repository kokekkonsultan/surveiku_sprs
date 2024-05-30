@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')

@endsection

@section('content')

<div class="container">

    <div class="row justify-content-md-center">
        <div class="col col-lg-9 mt-5">
            {!! form_open("pengguna-klien/create-klien"); !!}

            <div class="card card-body mb-5">

                <div class="form-group row">
                    <label class="col-sm-3 col-form-label font-weight-bold">Dari Reseller ? <span class="text-danger">*</span></label>
                    <div class="col-sm-9">
                        <div class="radio-list mb-5">
                            <label class="radio"><input type="radio" name="res" id="2" value="2" class="tamplate"><span></span>&nbsp Tidak</label>
                            <label class="radio"><input type="radio" name="res" id="1" value="1" class="tamplate"><span></span>&nbsp Ya</label>
                        </div>
                        {!! form_dropdown($is_reseller); !!}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="text-primary font-weight-bolder">Data PIC Klien</h5>
                    <hr>
                    <br>

                    <div id="infoMessage text-danger">{!! $message; !!}</div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Nama Depan <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_input($first_name); !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Nama Belakang <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_input($last_name); !!}
                        </div>
                    </div>

                    @if ($identity_column !== 'email')
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Username <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_error('identity'); !!}
                            {!! form_input($identity); !!}
                        </div>
                    </div>
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Organisasi <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_input($company); !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Email <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_input($email); !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">HP <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_input($phone); !!}
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Password <span class="text-danger">*</span></label>
                        <div class="col-sm-9">

                            <div class="input-group">
                                {!! form_input($password); !!}
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                </div>
                            </div>

                            <a class="text-primary font-weight-bold mt-3 mb-5" data-toggle="modal" title="Generate Password" onclick="showuserdetail(1)" href="#exampleModal"><i class="fas fa-key text-primary"></i> Generate Password</a>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Ulangi Password <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_input($password_confirm); !!}
                        </div>
                    </div>
                </div>
            </div>



            <div class="card mt-5">
                <div class="card-body">
                    <h5 class="text-primary font-weight-bolder">Survei</h5>
                    <hr>
                    <br>


                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Klasifikasi Survei <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            {!! form_dropdown($id_klasifikasi_survei); !!}
                        </div>
                    </div>

                    <div class="form-group row mt-5">
                        <label class="col-sm-3 col-form-label font-weight-bold">Kelompok Skala <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <div class="radio-list mb-5">
                                <label class="radio">
                                    <input type="radio" name="id_kelompok_skala" value="1" class="id_kelompok_skala" required><span></span>&nbsp Nasional
                                </label>
                                <label class="radio">
                                    <input type="radio" name="id_kelompok_skala" value="2" class="id_kelompok_skala"><span></span>&nbsp Provinsi
                                </label>
                                <label class="radio">
                                    <input type="radio" name="id_kelompok_skala" value="3" class="id_kelompok_skala"><span></span>&nbsp Kota / Kabupaten
                                </label>
                                <label class="radio">
                                    <input type="radio" name="id_kelompok_skala" value="4" class="id_kelompok_skala"><span></span>&nbsp Kecamatan
                                </label>
                            </div>


                            <select class="form-control mt-3 select2" id="id_wilayah_provinsi" name="id_wilayah_provinsi" autofocus style="display:none">
                                <option value="">Pilih Provinsi</option>
                                @foreach($ci->db->get("wilayah_provinsi")->result() as $row)
                                <option value="{{$row->id}}">{{$row->nama_provinsi}}</option>
                                @endforeach
                            </select>

                            <select class="form-control mt-3" id="id_wilayah_kota_kab" name="id_wilayah_kota_kab" autofocus style="display:none">
                                 <option value="">Pilih Kota / Kabupaten</option>
                                @foreach($ci->db->get("wilayah_kota_kabupaten")->result() as $row)
                                <option value="{{$row->id}}">{{$row->nama_kota_kabupaten}}</option>
                                @endforeach
                            </select>

                            <select class="form-control mt-3" id="id_wilayah_kecamatan" name="id_wilayah_kecamatan" autofocus style="display:none">
                                <option value="">Pilih Kecamatan</option>
                                @foreach($ci->db->get("wilayah_kecamatan")->result() as $row)
                                <option value="{{$row->id}}">{{$row->kecamatan}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>









            <div class="card mt-5">
                <div class="card-body">
                    <h5 class="text-primary font-weight-bolder">Berlangganan</h5>
                    <hr>
                    <br>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Jadikan Sebagai Pengguna Trial <span class="text-danger">*</span></label>
                        <div class="col-sm-9">
                            <span class="switch switch-sm">
                                <label>
                                    <input value="1" type="checkbox" name="is_trial" id="toggle-event-subscrpbe" class="toggle_dash" checked />
                                    <span></span>
                                </label>
                            </span>
                        </div>
                    </div>

                    <section id="section-trial">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Paket Trial <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                {!! form_dropdown($id_paket_trial); !!}
                            </div>
                        </div>
                    </section>



                    <section id="section-subscrpbe" style="display: none;">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Paket Langganan <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                {!! form_dropdown($id_paket); !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Metode Pembayaran <span class="text-danger">*</span></label>
                            <div class="col-sm-9">
                                {!! form_dropdown($id_metode_pembayaran); !!}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label font-weight-bold">Tanggal Mulai Berlangganan <span class="text-danger">*</span></label>
                            <div class="col-sm-9">

                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                    </div>
                                    {!! form_input($tanggal_mulai); !!}
                                </div>

                            </div>
                        </div>

                    </section>
                </div>
            </div>

            <div class="text-right mt-5 mb-5">
                {!! anchor(base_url().'pengguna-klien', 'Cancel', ['class' => 'btn btn-light-primary font-weight-bold
                shadow-lg']); !!}
                {!! form_submit('submit', 'Create Klien', ['class' => 'btn btn-primary font-weight-bold shadow-lg']);
                !!}
            </div>

            {!! form_close(); !!}
        </div>
    </div>


</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close"></i>
                </button>
            </div>
            <div class="modal-body" id="bodyModalDetail">
                <div align="center" id="loading_registration">
                    <img src="{{ base_url() }}assets/img/ajax/ajax-loader-big.gif" alt="">
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')

<script type="text/javascript">
    $(function() {
        $(":radio.id_kelompok_skala").click(function() {
            if ($(this).val() == 2) {
                $("#id_wilayah_kota_kab").removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kecamatan").removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_provinsi").prop('required', true).select2({
                    placeholder: 'Pilih Provinsi'
                }).show();
            } else if ($(this).val() == 3) {
                $('#id_wilayah_provinsi').removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kecamatan").removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kota_kab").prop('required', true).select2({
                    placeholder: 'Pilih Kota / Kabupaten'
                }).show();
            } else if ($(this).val() == 4) {
                $('#id_wilayah_provinsi').removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kota_kab").removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kecamatan").prop('required', true).select2({
                    placeholder: 'Pilih Kecamatan'
                }).show();
            } else {
                $('#id_wilayah_provinsi').removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kota_kab").removeAttr('required').next(".select2-container").hide();
                $("#id_wilayah_kecamatan").removeAttr('required').next(".select2-container").hide();
            }
        });
    });
</script>

<!-- <script>
    var KTSelect2 = function() {
        var demos = function() {
            $('#id_wilayah_provinsi').select2({
                placeholder: 'Please Select'
            });

            // $('#id_wilayah_kota_kab').select2({
            //     placeholder: 'Please Select'
            // });

            // $('#id_wilayah_kecamatan').select2({
            //     placeholder: 'Please Select'
            // });
        }
        return {
            init: function() {
                demos();
            }
        };
    }();

    jQuery(document).ready(function() {
        KTSelect2.init();
    });
</script> -->


<script>
    $('#toggle-event-subscrpbe').change(function() {
        if ($('#toggle-event-subscrpbe').is(":checked")) {

            $("#section-trial").slideDown();
            $("#id_paket_trial").prop('required', true);

            $("#section-subscrpbe").slideUp();
            $('#id_paket').removeAttr('required');
            $('#id_metode_pembayaran').removeAttr('required');
            $('#tanggal_mulai').removeAttr('required');
        } else {

            $("#section-trial").slideUp();
            $('#id_paket_trial').removeAttr('required');

            $("#section-subscrpbe").slideDown();
            $("#id_paket").prop('required', true);
            $("#id_metode_pembayaran").prop('required', true);
            $("#tanggal_mulai").prop('required', true);
        }
    });

    $(function() {
        // $( "#datepicker" ).datepicker({
        //     dateFormat: 'yy-mm-dd',
        // });
    });
</script>
<script>
    var btn = document.getElementById("open_modal");

    btn.onclick = function() {
        $('#exampleModal').modal('show');
    }

    function showuserdetail(id) {
        $('#bodyModalDetail').html(
            "<div class='text-center'><img src='{{ base_url() }}assets/img/ajax/ajax-loader-big.gif'></div>");

        $.ajax({
            type: "post",
            url: "{{ base_url() }}auth/generate-password",
            data: "id=" + id,
            dataType: "text",
            success: function(response) {

                $('.modal-title').text('Generate Password');
                $('#bodyModalDetail').empty();
                $('#bodyModalDetail').append(response);
            }
        });
    }
</script>
<script>
    ! function($) {
        //eyeOpenClass: 'fa-eye',
        //eyeCloseClass: 'fa-eye-slash',
        'use strict';

        $(function() {
            $('[data-toggle="password"]').each(function() {
                var input = $(this);
                var eye_btn = $(this).parent().find('.input-group-text');
                eye_btn.css('cursor', 'pointer').addClass('input-password-hide');
                eye_btn.on('click', function() {
                    if (eye_btn.hasClass('input-password-hide')) {
                        eye_btn.removeClass('input-password-hide').addClass('input-password-show');
                        eye_btn.find('.fa').removeClass('fa-eye').addClass('fa-eye-slash')
                        input.attr('type', 'text');
                    } else {
                        eye_btn.removeClass('input-password-show').addClass('input-password-hide');
                        eye_btn.find('.fa').removeClass('fa-eye-slash').addClass('fa-eye')
                        input.attr('type', 'password');
                    }
                });
            });
        });

    }(window.jQuery);
</script>

<!-- <script>
var KTSelect2 = function() {
    var demos = function() {
        $('#is_reseller').select2({
            placeholder: 'Please Select'
        });
    }

    return {
        init: function() {
            demos();
        }
    };
}();

jQuery(document).ready(function() {
    KTSelect2.init();
});
</script> -->

<script type="text/javascript">
    $(function() {
        $(":radio.tamplate").click(function() {
            $("#is_reseller").hide()
            if ($(this).val() == "1") {
                $("#is_reseller").show().prop('required', true);
            } else {
                $("#is_reseller").removeAttr('required').hidden();
            }
        });
    });
</script>
@endsection