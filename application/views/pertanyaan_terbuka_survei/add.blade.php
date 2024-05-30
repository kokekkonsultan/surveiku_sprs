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
            <div class="card" data-aos="fade-down">
                <div class="card-header bg-secondary">
                    <h5>{{ $title }}</h5>
                </div>

                <div class="card-body">
                    <?php echo form_open_multipart(base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-terbuka/add/' . $ci->uri->segment(5)); ?>
                    <span class="text-danger"><?php echo validation_errors(); ?></span>

                    

                    @if($ci->uri->segment(5) == 1)
                    <div class="form-group row">

                        <label class="col-sm-3 col-form-label font-weight-bold">Unsur Pelayanan Dari <span style="color:red;">*</span></label>
                        <div class="col-sm-9">
                            @php
                            echo form_dropdown($id_unsur_pelayanan);
                            @endphp
                        </div>
                    </div>

                    @else
                    <div class="form-group row mt-5">
                        <label class="col-sm-3 col-form-label font-weight-bold">Letak Pertanyaan <span style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            <select class="form-control" name="is_letak_pertanyaan_tambahan" id="is_letak_pertanyaan_tambahan" required>
                                <option value="">Please Select</option>
                                <option value="1">Paling Awal</option>
                                <option value="2">Paling Akhir</option>
                            </select>
                        </div>
                    </div>
                    @endif

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Nama Pertanyaan <span style="color:red;">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold">T<?php echo $jumlah_tambahan ?></span>
                                </div>
                                @php
                                echo form_input($nama_pertanyaan_terbuka);
                                @endphp
                            </div>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Isi Pertanyaan Tambahan <span style="color:red;">*</span></label>
                        <div class="col-sm-9">
                            @php
                            echo form_textarea($isi_pertanyaan_terbuka);
                            @endphp
                        </div>
                    </div>


                    <!-- <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Gambar</label>
                        <div class="col-sm-9">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="gambar_pertanyaan_terbuka" id="gambar_pertanyaan_terbuka">
                                <label class="custom-file-label" for="validatedCustomFile">Choose
                                    file...</label>
                            </div>
                        </div>
                    </div> -->


                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Status Pengisian Pertanyaan <span style="color:red;">*</span></label>
                        <div class="col-9 col-form-label">
                            <div class="radio-inline">
                                <label class="radio radio-md">
                                    <input type="radio" name="is_required" value="">
                                    <span></span>
                                    Wajib di Isi
                                </label>
                                <label class="radio radio-md">
                                    <input type="radio" name="is_required" value="1" required>
                                    <span></span>
                                    Tidak Wajib di Isi
                                </label>
                            </div>
                            <span class="form-text text-muted">Status pengisian pertanyaan ini digunakan untuk
                                mendefinisikan wajib atau tidaknya pertanyaan diisi.</span>
                        </div>
                    </div>



                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Pilihan Jawaban <span style="color:red;">*</span></label>
                        <div class="col-9 col-form-label">
                            <div class="radio-inline">
                                <label class="radio radio-md">
                                    <input type="radio" name="jenis_jawaban" value="2" class="pilihan" required>
                                    <span></span>
                                    Jawaban Singkat
                                </label>
                                <label class="radio radio-md">
                                    <input type="radio" name="jenis_jawaban" value="1" class="pilihan">
                                    <span></span>
                                    Dengan Pilihan Ganda
                                </label>
                            </div>
                            <span class="form-text text-muted">Pilih model pilihan jawaban Pertanyaan Terbuka.</span>
                        </div>
                    </div>





                    <div name="opsi_1" id="opsi_1" style="display:none">

                        <div class="form-group row">
                            <div class="col-3">
                            </div>
                            <div class="col-9">
                                <hr>
                            </div>
                        </div>



                        <div class="form-group fieldGroup">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label"></label>
                                <div class="col-sm-8">
                                    <input type="text" name="pilihan_jawaban[]" class="form-control" placeholder="Masukkan Pilihan Jawaban . . .">
                                </div>
                                <div class="input-group-addon col-sm-1">
                                    <a href="javascript:void(0)" class="btn btn-light-success addMore"><i class="fas fa-plus"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="form-group fieldGroupCopy" style="display:none">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label"></label>
                                <div class="col-sm-8">
                                    <input type="text" name="pilihan_jawaban[]" class="form-control" placeholder="Masukkan Pilihan Jawaban . . .">
                                </div>
                                <div class="input-group-addon col-sm-1">
                                    <a href="javascript:void(0)" class="btn btn-light-danger remove"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label fw-bold font-weight-bold">Dengan Pilihan Lainnya
                                <span style="color:red;">*</span></label>
                            <div class="col-9 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio-md">
                                        <input type="radio" name="opsi_pilihan_jawaban" id="opsi_pilihan_jawaban" value="1">
                                        <span></span>
                                        Ya
                                    </label>
                                    <label class="radio radio-md">
                                        <input type="radio" name="opsi_pilihan_jawaban" value="2">
                                        <span></span>
                                        Tidak
                                    </label>
                                </div>
                                <span class="form-text text-muted">Pilih YA jika pertanyaan tersebut menggunakan pilihan
                                    jawaban Lainnya.</span>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label fw-bold font-weight-bold">Model Pilihan Ganda
                                <span style="color:red;">*</span></label>
                            <div class="col-9 col-form-label">
                                <div class="radio-inline">
                                    <label class="radio radio-md">
                                        <input type="radio" name="is_model_pilihan_ganda" id="is_model_pilihan_ganda" value="1">
                                        <span></span>
                                        Hanya dapat memilih 1 Jawaban
                                    </label>
                                    <label class="radio radio-md">
                                        <input type="radio" name="is_model_pilihan_ganda" value="2">
                                        <span></span>
                                        Bisa memilih lebih dari 1 Jawaban
                                    </label>
                                </div>
                                <span class="form-text text-muted">Model Pilihan Jawaban ini akan diterapkan didalam form survei.</span>
                            </div>
                        </div>
                    </div>

                    <br>

                    <div class="text-right">

                        @php
                        echo
                        anchor(base_url().$ci->session->userdata('username').'/'.$ci->uri->segment(2).'/pertanyaan-terbuka',
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
@endsection



@section ('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>


<!-- <script type="text/javascript">
$(function() {
    $(":radio.custom").click(function() {
        $("#melekat_pada_unsur").hide();
        $("#tidak_melekat_pada_unsur").hide();
        $("#pertanyaan_lainnya").hide();
        if ($(this).val() == "1") {
            $("#is_letak_pertanyaan_tambahan").removeAttr('required');
            $("#id_unsur_pelayanan").prop('required', true);
            $("#melekat_pada_unsur").show();
            $("#pertanyaan_lainnya").show();
            $("#tidak_melekat_pada_unsur").hidden();
        } else {
            $("#id_unsur_pelayanan").removeAttr('required');
            $("#is_letak_pertanyaan_tambahan").prop('required', true);
            $("#tidak_melekat_pada_unsur").show();
            $("#pertanyaan_lainnya").show();
            $("#melekat_pada_unsur").hidden();
        }
    });
});
</script> -->



<script type="text/javascript">
    $(function() {
        $(":radio.pilihan").click(function() {
            $("#opsi_1").hide()
            if ($(this).val() == "1") {
                $("#opsi_pilihan_jawaban").prop('required', true);
                $("#is_model_pilihan_ganda").prop('required', true);
                $("#opsi_1").show();

            } else {
                $("#opsi_pilihan_jawaban").removeAttr('required');
                $("#is_model_pilihan_ganda").removeAttr('required');
                $("#opsi_1").hide();
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
        .create(document.querySelector('#isi_pertanyaan_terbuka'))
        .then(editor => {
            console.log(editor);
        })
        .catch(error => {
            console.error(error);
        });
</script>
@endsection