@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')

<style>
    .input[data-readonly] {
        pointer-events: none;
    }
</style>

@endsection

@section('content')
<div class="container">


    <div class="row justify-content-md-center">
        <div class="col-md-12">

            <div class="card card-custom card-sticky" id="kt_blockui_content">

                <div class="card-header">
                    <div class="mt-5">
                        <div class="card-title text-primary">
                            Buat Survei Baru
                        </div>
                        <small>Sebuah survei setidaknya harus diisi dengan nama survei, tahun survei, kapan survei
                            dilaksanakan dan kapan survei berakhir.</small>
                        <br>
                        <br>
                    </div>
                </div>
                <div class="card-body">

                    <form action="{{ $form_action }}" method="post" class="form_submit mt-5">

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Nama Survei <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                {!! form_input($survey_name); !!}
                            </div>
                        </div>

                        <!-- <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Organisasi Yang di Survei
                                <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                {!! form_input($organisasi); !!}
                            </div>
                        </div> -->

                        @php
                        $survey_yg_dibuat = $ci->db->get_where('manage_survey', array('id_user' =>
                        $data_user->id));
                        @endphp

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Template Survei <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="radio-list">
                                    <label class="radio"><input type="radio" name="template" id="2" value="2" class="template" required><span></span>&nbsp Tanpa Template</label>
                                    <label class="radio"><input type="radio" name="template" id="1" value="1" class="template"><span></span>&nbsp Dengan Template</label>

                                    @if($survey_yg_dibuat->num_rows() > 0)
                                    <label class="radio"><input type="radio" name="template" id="3" value="3" class="template"><span></span>&nbsp Ambil Pertanyaan dari Survei
                                        Sebelumnya</label>
                                    @endif
                                </div>

                                <br>
                                <select class="form-control" id="id_jenis_pelayanan" name="id_jenis_pelayanan" autofocus style="display:none">
                                    <option value="">Pilih Template</option>

                                    @foreach ($jenis_pelayanan as $row)
                                    <option value="<?php echo $row->id ?>">
                                        {{$row->nama_jenis_pelayanan_responden}}
                                    </option>
                                    @endforeach

                                </select>

                                @if($survey_yg_dibuat->num_rows() > 0)
                                <select class="form-control" id="id_manage_survey" name="id_manage_survey" autofocus style="display:none">
                                    <option value="">Pilih Survei Sebelumnya..</option>
                                    @foreach($survey_yg_dibuat->result() as $row)
                                    <option value="{{$row->id}}">{{$row->survey_name}}
                                    </option>
                                    @endforeach
                                </select>
                                @endif

                            </div>

                        </div>

                        <div class=" form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Tanggal Survei <span class="text-danger">*</span></label>
                            <div class="col-sm-10">

                                <div class='input-group' id='kt_daterangepicker_2'>

                                    <input class="form-control readonly" id="tanggal_survei" name="tanggal_survei" type="text" style="width: 300px;" placeholder="Pilih rentang tanggal survei" required autocomplete="off">

                                    <div class="input-group-append">
                                        <span class="input-group-text"><i class="la la-calendar-check-o"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Deskripsi
                                <small>(Optional)</small></label>
                            <div class="col-sm-10">
                                {!! form_textarea($description); !!}
                            </div>
                        </div>

                        <input type="hidden" name="custom" id="default" value="Default">
                        <!-- <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Link Kuesioner <span class="text-danger">*</span></label>
                            <div class="col-10 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="custom" id="default" value="Default" class="custom" required>
                                        <span></span>
                                        Default
                                    </label>
                                </div>
                                <span class="form-text text-muted">Sistem akan membuat link survei untuk
                                    anda.</span>
                                <hr>
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="custom" id="custom" value="Custom" class="custom">
                                        <span></span>
                                        Custom
                                    </label>
                                </div>
                                <span class="form-text text-muted">Anda yang akan menentukan link survei.</span>

                                <div class="mt-5">
                                    <input class="form-control" type="text" name="link" id="link" placeholder="Masukkan Link Survei Anda ..." style="display:none" />
                                </div>
                            </div>
                        </div> -->


                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Metode Sampling <span class="text-danger">*</span></label>
                            <div class="col-10 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="is_sampling" id="0" value="0" class="sampling" required>
                                        <span></span>
                                        Tanpa Sampling
                                    </label>
                                </div>
                                <span class="form-text text-muted">Tidak ada batas responden minimal dalam pengisian
                                    survei.</span>
                                <hr>
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="is_sampling" id="2" value="2" class="sampling">
                                        <span></span>
                                        Dengan Sampling
                                    </label>
                                </div>
                                <span class="form-text text-muted">Anda dapat menentukan batas responden di dalam
                                    survei</span>

                                <input class="form-control mt-5 mb-3" name="total_sampling" placeholder="Masukkan jumlah Responden yang ingin di survei.." id="total_sampling" style="display: none;">

                                {{--<hr>
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="is_sampling" id="1" value="1" class="sampling">
                                        <span></span>
                                        Dengan Sampling Statistik
                                    </label>
                                </div>
                                <span class="form-text text-muted">Anda bisa menggunakan perhitungan sampling statistik
                                    untuk menentukan batas responden</span>

                                <br>

                                @php
                                echo form_dropdown($id_sampling);
                                @endphp--}}
                            </div>
                        </div>




                        {{--<div id="sampling-hitung">
                            <div class="form-group row" class="krejcie" id="krejcie" hidden>
                                <div class="col-sm-2"></div>
                                <div class="col-sm-5">
                                    <label class="col-form-label font-weight-bold">Jumlah Populasi <span class="text-danger">*</span></label>
                                    <input type="text" id="populasi_krejcie" name="populasi_krejcie" class="form-control" placeholder="10000">
                                </div>
                                <div class="col-sm-5">
                                    <label class="col-form-label font-weight-bold">Jumlah Minimal Sampling
                                        <span class="text-danger">*</span></label>
                                    <input type="text" id="total_krejcie" name="total_krejcie" class="form-control" placeholder="370" style="background-color: #F3F6F9;" readonly>
                                </div>
                            </div>

                            <div class="form-group row" class="slovin" id="slovin" hidden>
                                <div class="col-sm-2"></div>
                                <div class="col-sm-5">
                                    <label class="col-form-label font-weight-bold">Jumlah Populasi <span class="text-danger">*</span></label>
                                    <input type="text" id="populasi_slovin" name="populasi_slovin" class="form-control" placeholder="10000">
                                </div>
                                <div class="col-sm-5">
                                    <label class="col-form-label font-weight-bold">Jumlah Minimal Sampling
                                        <span class="text-danger">*</span></label>
                                    <input type="text" id="total_slovin" name="total_slovin" class="form-control" placeholder="385" style="background-color: #F3F6F9;" readonly>
                                </div>
                            </div>
                        </div>

                        <br>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Menggunakan
                                Dimensi ?<span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="radio-list">
                                    <label class="radio"><input type="radio" name="is_dimensi" value="1" required><span></span>&nbsp Ya</label>
                                    <label class="radio"><input type="radio" name="is_dimensi" value="2"><span></span>&nbsp Tidak</label>
                                </div>

								<div class="mt-3">Dimensi dalam hal ini adalah pengelompokan unsur survei. Jika dalam survei anda menggunakan <b>dimensi</b>, maka pilih "<b>Ya</b>"</div>
                            </div>
                        </div>--}}


                        <!-- <br>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label font-weight-bold">Jenis Pertanyaan Survei
                                <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="checkbox-list">
                                    <input type="hidden" name="atribut_pertanyaan[]" value="0">
                                    <label class="checkbox checkbox-disabled">
                                        <input type="checkbox" disabled="disabled" checked="checked" name="Checkboxes1" /><span></span>
                                        Pertanyaan Unsur
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="atribut_pertanyaan[]" value="1">
                                        <span></span> Pertanyaan Harapan
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="atribut_pertanyaan[]" value="2">
                                        <span></span> Pertanyaan Tambahan
                                    </label>
                                    <label class="checkbox">
                                        <input type="checkbox" name="atribut_pertanyaan[]" value="3">
                                        <span></span> Pertanyaan Kualitatif
                                    </label>
                                </div>
                            </div>
                        </div> -->


                        <!-- <input value="4" name="skala_likert" hidden> -->
                        <!-- <div class="form-group row mb-5" id="pilih_skala" style="display: none;">
                            <label class="col-sm-2 col-form-label font-weight-bold">Skala Likert <span class="text-danger">*</span></label>
                            <div class="col-10 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="skala_likert" value="4" id="skala_likert">
                                        <span></span> Skala Likert 4
                                    </label>
                                </div>
                                <span class="form-text text-muted">Pertanyaan Survei akan menggunakan 4 Pilihan
                                    Jawaban <b>(Tidak Baik, Kurang Baik, Baik, Sangat Baik)</b>.</span>
                                <hr>
                                <div class="radio-inline">
                                    <label class="radio radio">
                                        <input type="radio" name="skala_likert" value="5">
                                        <span></span> Skala Likert 5
                                    </label>
                                </div>
                                <span class="form-text text-muted">Pertanyaan Survei akan menggunakan 5 Pilihan
                                    Jawaban <b>(Sangat Tidak Setuju, Tidak Setuju, Netral, Setuju, Sangat
                                        Setuju)</b>.</span>
                            </div>
                        </div> -->

                        <!-- <br>
                        <br>
                        <div style="background-color: white; padding: 20px; border: 1px DodgerBlue dotted;">
                            <span class="text-primary"><b>Keterangan :</b></span>
                            <ul class="mt-3">
                                <li><strong>Pertanyaan unsur</strong> dibuat sesuai unsur pada survei
                                    yang anda buat</li>
                                <li><strong>Pertanyaan harapan</strong> dibuat untuk membandingkan
                                    antara harapan dan kebutuhannya dalam survei. Isi dari
                                    pertanyaan harapan ini sama dengan pertanyaan unsur namun pilihan jawaban
                                    berupa (Penting, Kurang Penting, Penting, Sangat Penting).</li>
                                <li><strong>Pertanyaan tambahan</strong> adalah pertanyaan dibuat
                                    secara bebas namun mengacu kepada unsur pertanyaan yang anda
                                    pilih.<br>
                                    Isian dari pertanyaan tambahan dapat berupa pilihan atau
                                    inputan. Hasil akhir dari pertanyaan tambahan dapat direkap berupa
                                    persentase
                                    dan rekapan inputan pada hasil akhir.</li>
                                <li><strong>Pertanyaan kualitatif</strong> adalah pertanyaan yang
                                    dibuat
                                    secara bebas.<br>
                                    Isian dari pertanyaan tambahan berupa inputan. Hasil akhir dari pertanyaan
                                    kualitatif berupa rekap inputan.
                                </li>
                            </ul>
                        </div> -->
                        <br> <br>

                        <div class="text-right">
                            <button type="button" onclick="link_back()" class="btn btn-light-primary font-weight-bold shadow-lg tombolCancel">Kembali</button>
                            <button type="submit" class="btn btn-primary font-weight-bold shadow-lg tombolSubmit">Simpan</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('javascript')


<script>
    $(".readonly").on('keydown paste focus mousedown', function(e) {
        if (e.keyCode != 9) // ignore tab
            e.preventDefault();
    });
</script>

<!-- <script type="text/javascript">
    $(function() {
        $("#id_sampling").change(function() {
            console.log($("#id_sampling option:selected").val());
            // $("#krejcie").hide();
            if ($("#id_sampling option:selected").val() == 1) {
                $('#krejcie').prop('hidden', false);
                $('#slovin').prop('hidden', true);
            } else if ($("#id_sampling option:selected").val() == 3) {
                $('#krejcie').prop('hidden', true);
                $('#slovin').prop('hidden', false);
            } else {
                $('#krejcie').prop('hidden', true);
                $('#slovin').prop('hidden', true);
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#populasi_krejcie").keyup(function() {
            var populasi_krejcie = $("#populasi_krejcie").val();
            var total_krejcie = (3.841 * parseInt(populasi_krejcie) * 0.5 * 0.5) / ((0.05 * 0.05) * (
                parseInt(populasi_krejcie) - 1) + (3.841 * 0.5 * 0.5));
            $("#total_krejcie").val(Math.ceil(total_krejcie));
        });

        $("#populasi_slovin").keyup(function() {
            var populasi_slovin = $("#populasi_slovin").val();
            var total_slovin = parseInt(populasi_slovin) / (1 + parseInt(populasi_slovin) * (0.05 * 0.05));
            $("#total_slovin").val(Math.ceil(total_slovin));
        });
    });
</script> -->

@php
$link_back = base_url().$ci->session->userdata('username').'/manage-survey';
@endphp
<script>
    function link_back() {

        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Anda akan meninggalkan halaman ini ?",
            type: 'warning',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oke',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.value) {

                window.location.href = "{{ $link_back }}";

            }
        })
    }
</script>
<script type="text/javascript">
    $(function() {
        $(":radio.custom").click(function() {
            $("#link").hide()
            if ($(this).val() == "Custom") {
                $("#link").prop('required', true).show();
            } else {
                $("#link").removeAttr('required').hidden();
            }
        });
    });
</script>

<script type="text/javascript">
    $(function() {
        $(":radio.template").click(function() {
            // $("#id_jenis_pelayanan").hide();
            // $("#id_manage_survey").hide();
            if ($(this).val() == 1) {
                $("#id_jenis_pelayanan").prop('required', true).show();
                $("#id_manage_survey").removeAttr('required').hide();
                $("#pilih_skala").hide();
                $("#skala_likert").removeAttr('required');
            } else if ($(this).val() == 3) {
                $("#id_manage_survey").prop('required', true).show();
                $("#id_jenis_pelayanan").removeAttr('required').hide();
                $("#pilih_skala").hide();
                $("#skala_likert").removeAttr('required');
            } else {
                $("#pilih_skala").show();
                $("#skala_likert").prop('required', true);
                $("#id_jenis_pelayanan").removeAttr('required').hide();
                $("#id_manage_survey").removeAttr('required').hide();
            }
        });
    });
</script>

<script type="text/javascript">
    $(function() {
        $(":radio.sampling").click(function() {
            if ($(this).val() == 1) {
                $("#id_sampling").prop('required', true).show();
                $("#sampling-hitung").show();
                $("#total_sampling").removeAttr('required').hide();

            } else if ($(this).val() == 2) {
                $("#sampling-hitung").hide();
                $("#id_sampling").removeAttr('required').hide();
                $("#total_sampling").prop('required', true).show();
            } else {
                $("#id_sampling").removeAttr('required').hide();
                $("#sampling-hitung").hide();
                $("#total_sampling").removeAttr('required').hide();
            }
        });
    });
</script>

<script>
    $(document).ready(function(e) {
        $('.form_submit').submit(function(e) {

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                cache: false,
                beforeSend: function() {
                    $('.tombolCancel').attr('disabled', 'disabled');
                    $('.tombolSubmit').attr('disabled', 'disabled');
                    $('.tombolSubmit').html(
                        '<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

                    KTApp.block('#kt_blockui_content', {
                        overlayColor: '#000000',
                        state: 'primary',
                        message: 'Processing...'
                    });

                    setTimeout(function() {
                        KTApp.unblock('#kt_blockui_content');
                    }, 3000);

                    Swal.fire({
                        title: 'Memproses data',
                        html: 'Mohon tunggu sebentar. Sistem sedang menyiapkan request anda.',
                        allowOutsideClick: false,
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });

                },
                complete: function() {
                    $('.tombolCancel').removeAttr('disabled');
                    $('.tombolSubmit').removeAttr('disabled');
                    $('.tombolSubmit').html('Simpan');
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
                        window.location.href = "{{ $link_back }}";

                    }
                }
            })
            return false;
        });
    });
</script>



<script>
    var KTBootstrapDaterangepicker = function() {
        // Private functions
        var demos = function() {
            // input group and left alignment setup
            $('#kt_daterangepicker_2').daterangepicker({
                buttonClasses: ' btn',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary'
            }, function(start, end, label) {
                $('#kt_daterangepicker_2 .form-control').val(start.format('YYYY-MM-DD') + ' / ' + end
                    .format('YYYY-MM-DD'));
            });
        }

        return {
            // public functions
            init: function() {
                demos();
            }
        };
    }();
    jQuery(document).ready(function() {
        KTBootstrapDaterangepicker.init();
    });
</script>
@endsection