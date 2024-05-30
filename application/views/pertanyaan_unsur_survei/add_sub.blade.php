@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')

@endsection

@section('content')

<div class="container-fluid">
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            @if($ci->uri->segment(5) != '')
            <div class="alert alert-custom alert-notice alert-light-dark fade show" role="alert">
                <div class="alert-icon"><i class="flaticon-warning"></i></div>
                <div class="alert-text">Lanjutkan Mengisi Pertanyaan Sub Unsur Pelayanan</div>
                <div class="alert-close">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true"><i class="ki ki-close"></i></span>
                    </button>
                </div>
            </div>
            @endif

            <div class="card" data-aos="fade-down">
                <div class="card-header bg-secondary">
                    <h5>{{ $title }}</h5>
                </div>
                <div class="card-body">
                    <span class="text-danger"><?php echo validation_errors(); ?></span>
                    <br>
                    <?php echo form_open(base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/pertanyaan-unsur/add-sub'); ?>


                    @if($ci->uri->segment(5) == '')
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label
                        font-weight-bold">Sub Unsur Dari <span style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            @php
                            echo form_dropdown($id_parent);
                            @endphp
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label
                        font-weight-bold">Nama Unsur Pelayanan <span style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            @php
                            echo form_input($nama_unsur_pelayanan);
                            @endphp
                            <!-- <small>
                                Menurut Permenpan dan RB, unsur SKP terbagi 9 unsur antara lain: 1) Persyaratan 2)
                                Sistem, Mekanisme, dan Prosedur 3) Waktu Penyelesaian 4) Biaya/Tarif 5) Produk
                                Spesifikasi Jenis Pelayanan 6) Kompetensi Pelaksana 7) Perilaku Pelaksana 8)
                                Penanganan
                                Pengaduan, Saran dan Masukan 9) Sarana dan prasarana
                            </small> -->
                        </div>
                    </div>
                    @else

                    @php
                    $unsur_pelayanan = $ci->db->get_where('unsur_pelayanan_' . $manage_survey->table_identity,
                    array('id' => $ci->uri->segment(5)))->row();

                    $jumlah_parent = $ci->db->get_where('unsur_pelayanan_' . $manage_survey->table_identity,
                    array('id_parent' =>
                    $ci->uri->segment(5)))->num_rows();
                    @endphp

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label
                        font-weight-bold">Sub Unsur Dari <span style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            <input value="{{$ci->uri->segment(5)}}" name="id_parent" hidden>
                            <input class="form-control" value="{{$unsur_pelayanan->nomor_unsur . '. ' . $unsur_pelayanan->nama_unsur_pelayanan}}" disabled>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Unsur Pelayanan <span style="color: red;">*</span></label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text font-weight-bold"><?php echo $unsur_pelayanan->nomor_unsur . '.' . ($jumlah_parent + 1) ?></span>
                                </div>
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
                    @endif




                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label font-weight-bold">Pertanyaan Unsur <span style="color: red;">*</span></label>
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
                            <label class="col-sm-3 col-form-label font-weight-bold">Pilihan Jawaban <span style="color: red;">*</span></label>
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
                            <label class="col-sm-3 col-form-label font-weight-bold">Pilihan Jawaban <span style="color: red;">*</span></label>
                            <div class="col-sm-8">
                                <input type="text" name="pilihan_jawaban[]" class="form-control" placeholder="Masukkan Pilihan Jawaban . . .">
                            </div>
                            <div class="input-group-addon col-sm-1">
                                <a href="javascript:void(0)" class="btn btn-light-danger remove"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </div>





                    <br>
                    <div class="text-right">
                        @php
                        echo
                        anchor(base_url().$ci->session->userdata('username').'/'.$ci->uri->segment(2).'/pertanyaan-unsur',
                        'Batal', ['class' => 'btn btn-light-primary font-weight-bold'])
                        @endphp

                        <button type="submit" value="1" name="is_submit" class="btn btn-primary font-weight-bold">Simpan</button>

                        @if($ci->uri->segment(5) != '')
                        <button type="submit" value="2" name="is_submit" class="btn btn-success font-weight-bold" onclick="preventBack()">Simpan & Lanjutkan Mengisi Sub Unsur <i class="fa fa-arrow-right"></i></button>
                        @endif
                    </div>

                    <?php echo form_close(); ?>
                </div>
            </div>

        </div>
    </div>

</div>

@endsection

@section ('javascript')
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
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
                $("#jenis_pilihan_jawaban").removeAttr('required');
                $("#dengan_sub_unsur").show();
                $("#tanpa_sub_unsur").hidden();
            } else {
                $("#jenis_pilihan_jawaban").prop('required', true);
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

<script type="text/javascript">
    function preventBack() {
        window.history.forward();
    }
    setTimeout("preventBack()", 0);
    window.onunload = function() {
        null
    };
</script>
@endsection