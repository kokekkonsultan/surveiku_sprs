@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css">
<link href="{{ base_url() }}assets/vendor/bootstrap/css/bootstrap-colorpicker.css" rel="stylesheet" type="text/css" />
<style>
    .outer-box {
        font-family: arial;
        font-size: 24px;
        width: 580px;
        height: 114px;
        padding: 2px;
    }

    .box-edge-logo {
        font-family: arial;
        font-size: 14px;
        width: 110px;
        height: 110px;
        padding: 8px;
        float: left;
        text-align: center;
    }

    .box-edge-text {
        font-family: arial;
        font-size: 14px;
        width: 466px;
        height: 110px;
        padding: 8px;
        float: left;
    }

    .box-title {
        font-size: 18px;
        font-weight: bold;
    }

    .box-desc {
        font-size: 12px;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/@jaames/iro@5"></script>
<style>
    /* body {
    color: #ffffff;
    background: #171F30;
    line-height: 150%;
} */

    .wrap {
        max-width: 720px;
        margin: 0 auto;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
    }

    .half {
        width: 50%;
        /* padding: 32px 0; */
    }

    .title-color {
        font-family: sans-serif;
        /* line-height: 24px; */
        display: block;
        padding: 8px 0;
        font-weight: bold;
    }

    .readout {
        /* margin-top: 32px; */
        line-height: 180%;
    }

    .colorSquare {
        height: 50px;
        width: 50px;
        /* background-color: red; */
        border-radius: 10%;
        margin-bottom: 10px;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

            <div class="card card-custom" data-aos="fade-down">
                <div class="card-header font-weight-bold">
                    <div class="card-title">
                        {{ $title }}
                    </div>
                    <div class="card-toolbar">
                        <a href="{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/preview-form-survei/opening'}}" class="btn btn-sm btn-primary font-weight-bold" target="_blank">
                            <i class="flaticon-interface-10"></i> Lihat Tampilan Form Survei
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Judul & Sub Judul</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="benner-tab" data-toggle="tab" href="#benner" role="tab" aria-controls="benner" aria-selected="false">Banner</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="benner-tab" data-toggle="tab" href="#background" role="tab" aria-controls="benner" aria-selected="false">Latar Belakang</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="description-tab" data-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="false">Kata Pembuka</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="saran-tab" data-toggle="tab" href="#saran" role="tab" aria-controls="saran" aria-selected="false">Form Saran</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="penutup-tab" data-toggle="tab" href="#penutup" role="tab" aria-controls="penutup" aria-selected="false">Kata Penutup</a>
                        </li>
                    </ul>

                    <br>


                    <div class=" tab-content" id="myTabContent">

                        <!------------------------------------- LOGO ------------------------------------->
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                            <div class="card shadow">

                                @php
                                $title_header = unserialize($manage_survey->title_header_survey);
                                $title_1 = $title_header[0];
                                $title_2 = $title_header[1];
                                @endphp

                                <form class="form_header" action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-header' ?>" method="POST">

                                    <div class="card-body">

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label font-weight-bold">Judul <span style="color: red;">*</span></label>
                                            <div class="col-sm-10">
                                                <textarea name="title[]" value="" class="form-control" required><?php echo $title_1 ?></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label font-weight-bold">Sub Judul <span style="color: red;">*</span></label>
                                            <div class="col-sm-10">
                                                <textarea name="title[]" value="" class="form-control" required><?php echo $title_2 ?></textarea>
                                            </div>
                                        </div>


                                        <div class="text-right mt-3">
                                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolSimpanHeader">Simpan</button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                            <br>

                            <nav class="navbar navbar-light bg-white shadow">
                                <div class="outer-box">
                                    <div class="box-edge-logo">
                                        @if ($data_user->foto_profile == NULL)
                                        <img src="<?php echo base_url(); ?>assets/klien/foto_profile/200px.jpg" width="100%" class="" alt="">
                                        @else
                                        <img src="<?php echo base_url(); ?>assets/klien/foto_profile/<?php echo $data_user->foto_profile ?>" width="100%" class="" alt="">
                                        @endif

                                    </div>
                                    <div class="box-edge-text">
                                        <div class="box-title">
                                            <?php echo $title_1 ?>
                                        </div>
                                        <div class="box-desc">
                                            <?php echo $title_2 ?>
                                        </div>
                                    </div>
                                </div>

                                {{-- <div class="font-weight-bold font-italic pt-5"><b class="text-danger">**</b> Logo akan tampil dibagian header dan cover laporan survei</div> --}}

                            </nav>
                            <div class="alert alert-secondary mt-5 mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Logo akan tampil dibagian header dan cover laporan survei
                            </div>
                        </div>

                        {{-- DESKRIPSI PEMBUKA --}}

                        <div class="tab-pane fade" id="description" role="tabpanel" aria-labelledby="description-tab">

                            <div class="alert alert-secondary mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Kata-kata ini akan ditampilkan pada halaman awal survei.
                            </div>

                            <div id="tablex">
                                @php
                                $checked = ($manage_survey->is_opening_survey == 'true') ? "checked" : "";
                                @endphp
                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_value" class="custom-control-input toggle_dash_1" value="{{ $manage_survey->id }}" id="customSwitch1" {{ $checked }} />
                                    <label class="custom-control-label" for="customSwitch1">Aktifkan halaman pembuka</label>
                                </div>
                            </div>

                            <div class="alert alert-secondary mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Jika Anda mengaktifkan halaman pembuka, Anda wajib mengisi bidang dibawah ini.
                            </div>

                            <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-display' ?>" class="form_pembuka">

                                <div class="form-group">
                                    <textarea name="deskripsi" id="editor" value="" class="form-control" required> <?php echo $manage_survey->deskripsi_opening_survey ?></textarea>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolSimpanPembuka">Update
                                        Deskripsi</button>
                                </div>
                            </form>

                        </div>

                        <!-- ATRIBUTE PERTANYAAN SURVEI -->
                        {{-- <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

                            <div class="alert alert-custom alert-notice alert-light-primary fade show mb-10"
                                role="alert">
                                <div class="alert-icon"><i class="flaticon-warning"></i></div>
                                <div class="alert-text">
                                    <span>Halaman ini digunakan untuk mengatur jenis pertanyaan yang dipakai di dalam
                                        survei.</span>
                                </div>
                                <div class="alert-close">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                    </button>
                                </div>
                            </div>

                            <form
                                action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/setting-pertanyaan' ?>"
                                class="form_atribut_pertanyaan">

                                <div class="form-group row">
                                    <label for="recipient-name" class="col-sm-4 col-form-label font-weight-bold">Jenis
                                        Pertanyaan Survei <span style="color:red;">*</span></label>

                                    <div class="col-sm-8">
                                        <input type="hidden" name="atribut_pertanyaan[]" value="0">
                                        <label class="font-weight-bold"><input type="checkbox" checked disabled>
                                            Pertanyaan Unsur</label><br>

                                        <label><input type="checkbox" name="atribut_pertanyaan[]" value="1"
                                                <?php echo (in_array(1, $atribut_pertanyaan_survey)) ? 'checked' : '' ?>>
                                            Pertanyaan Harapan</label><br>

                                        <label><input type="checkbox" name="atribut_pertanyaan[]" value="2"
                                                <?php echo (in_array(2, $atribut_pertanyaan_survey)) ? 'checked' : '' ?>>
                                            Pertanyaan Tambahan</label><br>

                                        <label><input type="checkbox" name="atribut_pertanyaan[]" value="3"
                                                <?php echo (in_array(3, $atribut_pertanyaan_survey)) ? 'checked' : '' ?>>
                                            Pertanyaan Kualitatif</label>
                                    </div>
                                </div>
                                <div class="font-weight-bold font-italic"><b class="text-danger">**</b> Mengubah
                                    Jenis Pertanyaan juga akan
                                    menghapus semua data perolehan survei yang sudah masuk !</div>


                                    @if($manage_survey->is_question == 1)
                                <div class="text-right mt-5">
                                    <button type="submit"
                                        onclick="return confirm('Apakah anda yakin ingin mengubah atribut pertanyaan survei ?')"
                                        class="btn btn-primary font-weight-bold btn-sm tombolSimpanJenisPertanyaan"
                                        <?php echo $manage_survey->is_survey_close == 1 ? 'disabled' : '' ?>>Update
                                        Jenis Pertanyaan</button>
                                </div>
                                @endif
                            </form>
                        </div> --}}


                        @php
                        $warna_benner = unserialize($manage_survey->warna_benner);
                        $warna_benner1 = (isset($warna_benner[0])) ? $warna_benner[0] : "#E4E6EF";
                        $warna_benner2 = (isset($warna_benner[1])) ? $warna_benner[1] : "#E4E6EF";
                        $warna_benner3 = (isset($warna_benner[2])) ? $warna_benner[2] : "#E4E6EF";
                        @endphp

                        <!-- HEADER SURVEI -->
                        <div class="tab-pane fade" id="benner" role="tabpanel" aria-labelledby="benner-tab">

                            <div class="alert alert-secondary mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Banner akan tampil dibagian header halaman survei
                            </div>

                            <!-- <div class="text-right mb-5">
                                <button class=" btn btn-light-primary btn-sm font-weight-bold" type="button" data-toggle="collapse"
                                    data-target="#collapseHeader" aria-expanded="false" aria-controls="collapseExample">
                                    <i class="fa fa-edit"></i> Edit Banner Form Survei
                                </button>
                            </div> -->

                            <div id="tableBanner">
                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_banner" class="custom-control-input toggle_banner_1" value="1" id="customBanner1" {{ ($manage_survey->is_benner == '1') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBanner1">Menggunakan banner gambar</label>
                                </div>

                                <!-- <div class="collapse" id="collapseHeader"> -->

                                <div class="card card-body shadow mb-5">
                                    <form id="uploadForm">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="upload" id="profil">
                                            <label class="custom-file-label" for="validatedCustomFile">Choose
                                                file...</label>
                                        </div>
                                        <br>
                                        <div class="alert alert-secondary mt-5" role="alert">
                                            <small class="text-danger">
                                                * Format file harus jpg/png.<br>
                                                * Ukuran max file adalah 10MB.<br>
                                                * Dimensi banner proporsional 1920px x 465px
                                            </small>
                                        </div>
                                        <!-- <br> -->

                                        <div class="text-right mt-3">
                                            <!-- <button class="btn btn-secondary btn-sm" type="button"
                                                data-toggle="collapse" data-target="#collapseHeader"
                                                aria-expanded="false" aria-controls="collapseExample">
                                                Close
                                            </button> -->
                                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolUploud">Upload</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- </div> -->

                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_banner" class="custom-control-input toggle_banner_1" value="2" id="customBanner2" {{ ($manage_survey->is_benner == '2') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBanner2">Menggunakan banner tulisan</label>
                                </div>

                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_banner" class="custom-control-input toggle_banner_1" value="3" id="customBanner3" {{ ($manage_survey->is_benner == '3') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBanner3">Menggunakan banner tulisan dengan warna solid</label>
                                    <div id="cp6" class="input-group" style="width: 145px; ">
                                        <input type="text" name="warna_benner[]" id="warna1" value="{{ $warna_benner1 }}" class="form-control input-lg" />
                                        <span class="input-group-append">
                                            <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_banner" class="custom-control-input toggle_banner_1" value="4" id="customBanner4" {{ ($manage_survey->is_benner == '4') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBanner4">Menggunakan banner tulisan dengan warna gradasi</label>
                                    <div class="row" style="padding-left:12px; ">
                                        <div id="cp4" class="input-group" style="width: 145px; float:left; margin-right:10px; ">
                                            <input type="text" name="warna_benner[]" id="warna2" value="{{ $warna_benner2 }}" class="form-control input-lg" />
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                        <div id="cp5" class="input-group" style="width: 145px; ">
                                            <input type="text" name="warna_benner[]" id="warna3" value="{{ $warna_benner3 }}" class="form-control input-lg" />
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_banner" class="custom-control-input toggle_banner_1" value="5" id="customBanner5" {{ ($manage_survey->is_benner == '5') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBanner5">Menggunakan banner tulisan dengan logo</label>
                                </div>
                            </div>

                            <div align="center"><a href="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/preview-form-survei/opening' ?>" class="btn btn-sm btn-primary font-weight-bold" target="_blank">
                                    <i class="flaticon-interface-10"></i> Lihat Tampilan
                                </a></div><br>


                            @if($manage_survey->is_benner == 2)
                            <div class="card-header text-dark" style="background-color: #E4E6EF;">
                                <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
                                    <h1 style="font-weight:800;">{{strtoupper($title_1)}}</h1>
                                    <h3 class="mt-3" style="font-weight:800;">{{strtoupper($title_2)}}</h3>
                                </div>
                            </div>

                            @elseif($manage_survey->is_benner == 3)
                            <div class="card-header text-dark" id="gantiwarna1" style="background-color: <?= $warna_benner1 ?>;">
                                <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
                                    <h1 style="font-weight:800;">{{strtoupper($title_1)}}</h1>
                                    <h3 class="mt-3" style="font-weight:800;">{{strtoupper($title_2)}}</h3>
                                </div>
                            </div>

                            @elseif($manage_survey->is_benner == 4)
                            <div class="card-header text-dark" id="gantiwarna2" style="background-image: linear-gradient(to bottom right, <?= $warna_benner2 . ', ' . $warna_benner3 ?>);">
                                <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
                                    <h1 style="font-weight:800;">{{strtoupper($title_1)}}</h1>
                                    <h3 class="mt-3" style="font-weight:800;">{{strtoupper($title_2)}}</h3>
                                </div>
                            </div>

                            @elseif($manage_survey->is_benner == 5)
                            <div class="card-header text-dark" style="background-color: <?= $warna_benner1 ?>;">
                                <table class="table table-borderless mt-5" width="100%">
                                    <tr>
                                        <td width="15%" style="vertical-align:middle;">
                                            <img src="{{base_url() . 'assets/klien/foto_profile/200px.jpg'}}" height="100" width="100" alt="">
                                        </td>
                                        <td style="font-family: Helvetica, Arial, sans-serif; vertical-align:middle;">
                                            <h1 style="font-weight:800;">{{strtoupper($title_1)}}</h1>
                                            <h3 class="mt-3" style="font-weight:800;">{{strtoupper($title_2)}}</h3>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            @else

                            @if($manage_survey->img_benner == '')
                            <img class="card-img-top" src="{{ base_url() }}assets/img/site/page/banner-survey.jpg" alt="new image" />
                            @else
                            <img class="card-img-top shadow" src="{{ base_url() }}assets/klien/benner_survei/{{$manage_survey->img_benner}}" alt="new image">
                            @endif

                            @endif

                        </div>


                        @php
                        $warna_latar_belakang = unserialize($manage_survey->warna_latar_belakang);
                        $warna_latar_belakang1 = (isset($warna_latar_belakang[0])) ? $warna_latar_belakang[0] : "#E4E6EF";
                        $warna_latar_belakang2 = (isset($warna_latar_belakang[1])) ? $warna_latar_belakang[1] : "#E4E6EF";
                        $warna_latar_belakang3 = (isset($warna_latar_belakang[2])) ? $warna_latar_belakang[2] : "#E4E6EF";
                        @endphp

                        <div class="tab-pane fade" id="background" role="tabpanel" aria-labelledby="background-tab">

                            <div class="alert alert-secondary mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Latar belakang ini akan ditampilkan pada halaman survei.
                            </div>

                            <div id="tableBackground">
                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_background" class="custom-control-input toggle_background_1" value="1" id="customBackground1" {{ ($manage_survey->is_latar_belakang == '1') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBackground1">Latar belakang default</label>
                                </div>

                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_background" class="custom-control-input toggle_background_1" value="2" id="customBackground2" {{ ($manage_survey->is_latar_belakang == '2') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBackground2">Menggunakan latar belakang warna solid</label>
                                    <div id="cp3" class="input-group" style="width: 145px; ">
                                        <input type="text" name="warna_latar_belakang[]" id="warna4" value="{{ $warna_latar_belakang1 }}" class="form-control input-lg" />
                                        <span class="input-group-append">
                                            <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="custom-control custom-switch mt-5 mb-5">
                                    <input type="checkbox" name="setting_background" class="custom-control-input toggle_background_1" value="3" id="customBackground3" {{ ($manage_survey->is_latar_belakang == '3') ? "checked" : ""; }} />
                                    <label class="custom-control-label" for="customBackground3">Menggunakan latar belakang warna gradasi</label>
                                    <div class="row" style="padding-left:12px; ">
                                        <div id="cp1" class="input-group" style="width: 145px; float:left; margin-right:10px; ">
                                            <input type="text" name="warna_latar_belakang[]" id="warna5" value="{{ $warna_latar_belakang2 }}" class="form-control input-lg" />
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                        <div id="cp2" class="input-group" style="width: 145px; ">
                                            <input type="text" name="warna_latar_belakang[]" id="warna6" value="{{ $warna_latar_belakang3 }}" class="form-control input-lg" />
                                            <span class="input-group-append">
                                                <span class="input-group-text colorpicker-input-addon"><i></i></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>



                        </div>


                        <!-- ==================================== SARAN ========================================== -->
                        <div class="tab-pane fade" id="saran" role="tabpanel" aria-labelledby="saran-tab">


                            <div class="alert alert-secondary mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Jika diaktifkan, maka akan ditampilkan form saran survei.
                            </div>

                            <form action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-saran' ?>" class="form_saran">

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label font-weight-bold">Aktifkan Form Saran <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="is_saran" value="<?php echo set_value('is_saran'); ?>">
                                            <option value="1" <?php if ($manage_survey->is_saran == "1") {
                                                                    echo "selected";
                                                                } ?>>Ya</option>
                                            <option value="2" <?php if ($manage_survey->is_saran == "2") {
                                                                    echo "selected";
                                                                } ?>>Tidak</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label font-weight-bold">Judul Form
                                        Saran <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" name="judul_form_saran" value="" rows="3"><?php echo $manage_survey->judul_form_saran ?></textarea>
                                    </div>
                                </div>

                                @if($manage_survey->is_question == 1)
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolSimpanSaran">Update
                                        Form
                                        Saran</button>
                                </div>
                                @endif
                            </form>

                        </div>

                        <div class="tab-pane fade" id="penutup" role="tabpanel" aria-labelledby="penutup-tab">

                            <div class="alert alert-secondary mb-5" role="alert">
                                <i class="flaticon-exclamation-1"></i> Kata-kata ini akan ditampilkan pada halaman akhir survei.
                            </div>

                            <form action="{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei/update-kata-penutup" class="form_penutup">

                                <div class="form-group">
                                    <textarea name="deskripsi_selesai_survei" id="editor-penutup" value="" class="form-control" required> <?php echo $manage_survey->deskripsi_selesai_survei ?></textarea>
                                </div>

                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolSimpanPenutup">Update Kata Penutup</button>
                                </div>
                            </form>

                        </div>


                    </div>
                </div>
            </div>



            <!-- ==================================== DESKRIPSI PEMBUKA ========================================== -->
            {{-- <div class="card card-custom card-sticky mt-5" data-aos="fade-down">
                <div class="card-header">
                    <div class="card-title">
                        Deskripsi Pembuka Survei
                    </div>
                </div>
                <div class="card-body">
                    <form
                        action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-display' ?>"
                        class="form_pembuka">

                        <div class="form-group">
                            <textarea name="deskripsi" id="editor" value="" class="form-control"
                                required> <?php echo $manage_survey->deskripsi_opening_survey ?></textarea>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary font-weight-bold tombolSimpanPembuka">Update
                                Deskripsi</button>
                        </div>
                    </form>
                </div>
            </div> --}}

        </div>
    </div>
</div>


<!-- 
<div class="modal fade" id="warna-survei" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content border border-white" style="background-color: #1c2840; color:#ffffff;">
            <div class="modal-body">

                <form
                    action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-kode-warna' ?>"
                    class="form_header">

                    <div class="wrap">
                        <div class="half">
                            <div class="colorPicker"></div>
                        </div>
                        <div class="half readout">
                            <h6 class="title-color">Warna Yang di Pilih :</h6>
                            <div class="colorSquare" id="colorSquare"></div>
                            <div class="" id="values"></div>

                            <div class="input-group input-group-sm mb-3 mt-5">
                                <input class="form-control" id="hexInput" name="kode_warna"
                                    placeholder="Silahkan pilih warna..." aria-label="Small"
                                    aria-describedby="inputGroup-sizing-sm">
                                <div class="input-group-append">
                                    <span class="input-group-text" id="inputGroup-sizing-sm"><i
                                            class="fa fa-paint-brush"></i></span>
                                </div>
                            </div>



                        </div>
                    </div>

                    <div class="text-right mt-5">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit"
                            class="btn btn-light-primary btn-sm font-weight-bold tombolSimpanHeader">Update
                            Warna</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div> -->

@endsection

@section('javascript')
<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
<script src="{{ base_url() }}assets/vendor/bootstrap/js/bootstrap-colorpicker.js"></script>

<script>
    $(function() {
        //$('#cp1, #cp2, #cp3').colorpicker({"color": "#CCC"});
        $('#cp3').colorpicker({
            "color": "{{ $warna_latar_belakang1 }}"
        });
        $('#cp1').colorpicker({
            "color": "{{ $warna_latar_belakang2 }}"
        });
        $('#cp2').colorpicker({
            "color": "{{ $warna_latar_belakang3 }}"
        });

        $('#cp6').colorpicker({
            "color": "{{ $warna_benner1 }}"
        });
        $('#cp4').colorpicker({
            "color": "{{ $warna_benner2 }}"
        });
        $('#cp5').colorpicker({
            "color": "{{ $warna_benner3 }}"
        });
    });
</script>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .then(editor => {
            console.log(editor);
        })
        .catch(error => {
            console.error(error);
        });
    ClassicEditor.create(document.querySelector('#editor-penutup'));
</script>

<script>
    $('.form_pembuka').submit(function(e) {

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSimpanPembuka').attr('disabled', 'disabled');
                $('.tombolSimpanPembuka').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

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
                $('.tombolSimpanPembuka').removeAttr('disabled');
                $('.tombolSimpanPembuka').html('Update Deskripsi');
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
                }
            }
        })
        return false;
    });

    $('.form_penutup').submit(function(e) {

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSimpanPenutup').attr('disabled', 'disabled');
                $('.tombolSimpanPenutup').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

                // KTApp.block('#content_1', {
                // 	overlayColor: '#000000',
                // 	state: 'primary',
                // 	message: 'Processing...'
                // });

                // setTimeout(function() {
                // 	KTApp.unblock('#content_1');
                // }, 1000);

            },
            complete: function() {
                $('.tombolSimpanPenutup').removeAttr('disabled');
                $('.tombolSimpanPenutup').html('Update Kata Penutup');
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
                    toastr["success"](data.sukses);
                }
            }
        })
        return false;
    });

    $('.form_header').submit(function(e) {

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSimpanHeader').attr('disabled', 'disabled');
                $('.tombolSimpanHeader').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

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
                $('.tombolSimpanHeader').removeAttr('disabled');
                $('.tombolSimpanHeader').html('Update');
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
                    }, 1500);
                }
            }
        })
        return false;
    });

    $('.form_saran').submit(function(e) {
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSimpanSaran').attr('disabled', 'disabled');
                $('.tombolSimpanSaran').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

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
                $('.tombolSimpanSaran').removeAttr('disabled');
                $('.tombolSimpanSaran').html('Update Form Saran');
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
                }
            }
        })
        return false;
    });
</script>

<script>
    $(document).ready(function() {
        $('#tablex').on('change', '.toggle_dash_1', function() {
            // alert("TT");
            var mode = $(this).prop('checked');
            var nilai_id = $(this).val();

            $.ajax({

                type: 'POST',
                dataType: 'JSON',
                url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei/update-is-kata-pembuka",
                data: {
                    'mode': mode,
                    'nilai_id': nilai_id
                },
                success: function(data) {
                    var data = eval(data);
                    message = data.message;
                    success = data.success;

                    toastr["success"](message);

                    setTimeout(function() {
                        window.location.href = ("{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei");
                    }, 2000);

                }
            });
        });


        //--------------------------------BANNER DAN LATAR BELAKANG--------------------------------//
        $('#tableBanner').on('change', '.toggle_banner_1', function() {
            // alert("TT");
            var mode = $(this).prop('checked');
            var nilai_id = $(this).val();
            //var warna_benner = [];
            warna_benner = $('input:enabled[name="warna_benner[]"]').map(function() {
                return $(this).val();
            }).get();

            $.ajax({

                type: 'POST',
                dataType: 'JSON',
                url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei/update-is-banner",
                data: {
                    'mode': mode,
                    'nilai_id': nilai_id,
                    'warna_benner': warna_benner,
                },
                success: function(data) {
                    var data = eval(data);
                    message = data.message;
                    success = data.success;

                    toastr["success"](message);

                    setTimeout(function() {
                        window.location.href = ("{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei");
                    }, 2000);

                }
            });
        });


        function ganti_warna_banner(mode, nilai_id, warna_benner) {
            $.ajax({

                type: 'POST',
                dataType: 'JSON',
                url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei/update-is-banner",
                data: {
                    'mode': mode,
                    'nilai_id': nilai_id,
                    'warna_benner': warna_benner,
                },
                success: function(data) {
                    var data = eval(data);
                    message = data.message;
                    success = data.success;

                    //toastr["success"](message);

                }
            });
        }

        function ganti_warna_backgorund(mode, nilai_id, warna_latar_belakang) {
            $.ajax({

                type: 'POST',
                dataType: 'JSON',
                url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei/update-is-background",
                data: {
                    'mode': mode,
                    'nilai_id': nilai_id,
                    'warna_latar_belakang': warna_latar_belakang,
                },
                success: function(data) {
                    var data = eval(data);
                    message = data.message;
                    success = data.success;

                    //toastr["success"](message);

                }
            });
        }

        document.getElementById("warna1").onchange = function() {
            var mode = $(this).prop('checked');
            var nilai_id = '{{ $manage_survey->is_benner }}';
            warna_benner = $('input:enabled[name="warna_benner[]"]').map(function() {
                return $(this).val();
            }).get();

            ganti_warna_banner(mode, nilai_id, warna_benner);
            document.getElementById("gantiwarna1").style.backgroundColor = warna_benner.toString().split(',').shift();
        }

        document.getElementById("warna2").onchange = function() {
            var mode = $(this).prop('checked');
            var nilai_id = '{{ $manage_survey->is_benner }}';
            warna_benner = $('input:enabled[name="warna_benner[]"]').map(function() {
                return $(this).val();
            }).get();

            ganti_warna_banner(mode, nilai_id, warna_benner);
            document.getElementById("gantiwarna2").style.backgroundImage = 'linear-gradient(to bottom right, ' + warna_benner.toString().split(',')[1] + ', ' + warna_benner.toString().split(',')[2] + ')';
        }

        document.getElementById("warna3").onchange = function() {
            var mode = $(this).prop('checked');
            var nilai_id = '{{ $manage_survey->is_benner }}';
            warna_benner = $('input:enabled[name="warna_benner[]"]').map(function() {
                return $(this).val();
            }).get();

            ganti_warna_banner(mode, nilai_id, warna_benner);
            document.getElementById("gantiwarna2").style.backgroundImage = 'linear-gradient(to bottom right, ' + warna_benner.toString().split(',')[1] + ', ' + warna_benner.toString().split(',')[2] + ')';
        }

        document.getElementById("warna4").onchange = function() {
            var mode = $(this).prop('checked');
            var nilai_id = '{{ $manage_survey->is_latar_belakang }}';
            warna_latar_belakang = $('input:enabled[name="warna_latar_belakang[]"]').map(function() {
                return $(this).val();
            }).get();

            ganti_warna_backgorund(mode, nilai_id, warna_latar_belakang);
        }

        document.getElementById("warna5").onchange = function() {
            var mode = $(this).prop('checked');
            var nilai_id = '{{ $manage_survey->is_latar_belakang }}';
            warna_latar_belakang = $('input:enabled[name="warna_latar_belakang[]"]').map(function() {
                return $(this).val();
            }).get();

            ganti_warna_backgorund(mode, nilai_id, warna_latar_belakang);
        }

        document.getElementById("warna6").onchange = function() {
            var mode = $(this).prop('checked');
            var nilai_id = '{{ $manage_survey->is_latar_belakang }}';
            warna_latar_belakang = $('input:enabled[name="warna_latar_belakang[]"]').map(function() {
                return $(this).val();
            }).get();

            ganti_warna_backgorund(mode, nilai_id, warna_latar_belakang);
        }


        $('#tableBackground').on('change', '.toggle_background_1', function() {
            // alert("TT");
            var mode = $(this).prop('checked');
            var nilai_id = $(this).val();
            //var warna_latar_belakang = [];
            warna_latar_belakang = $('input:enabled[name="warna_latar_belakang[]"]').map(function() {
                return $(this).val();
            }).get();

            $.ajax({

                type: 'POST',
                dataType: 'JSON',
                url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei/update-is-background",
                data: {
                    'mode': mode,
                    'nilai_id': nilai_id,
                    'warna_latar_belakang': warna_latar_belakang,
                },
                success: function(data) {
                    var data = eval(data);
                    message = data.message;
                    success = data.success;

                    toastr["success"](message);

                    setTimeout(function() {
                        window.location.href = ("{{ base_url() }}{{ $ci->session->userdata('username') }}/{{ $ci->uri->segment(2) }}/form-survei");
                    }, 2000);

                }
            });
        });
        //--------------------------------END BANNER DAN LATAR BELAKANG--------------------------------//

    });
</script>

<script type="text/javascript">
    $('#uploadForm').submit(function(e) {
        e.preventDefault();

        var reader = new FileReader();
        reader.readAsDataURL(document.getElementById('profil').files[0]);

        var formdata = new FormData();
        formdata.append('file', document.getElementById('profil').files[0]);
        $.ajax({
            method: 'POST',
            contentType: false,
            cache: false,
            processData: false,
            data: formdata,
            dataType: 'json',
            url: "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/do-uploud' ?>",
            beforeSend: function() {
                $('.tombolUploud').attr('disabled', 'disabled');
                $('.tombolUploud').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

            },
            complete: function() {
                $('.tombolUploud').removeAttr('disabled');
                $('.tombolUploud').html('Upload');
            },
            error: function(e) {
                Swal.fire(
                    'Error !',
                    e,
                    'error'
                )
            },

            success: function(data) {
                if (data.error) {
                    toastr["danger"]('Data gagal disimpan');
                } else {
                    $('#uploadForm')[0].reset();
                    toastr["success"]('Data berhasil disimpan');
                    window.setTimeout(function() {
                        location.reload()
                    }, 1000);
                }

            }
        });
        return false;
    });
</script>

<script>
    $('.form_atribut_pertanyaan').submit(function(e) {
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSimpanJenisPertanyaan').attr('disabled', 'disabled');
                $('.tombolSimpanJenisPertanyaan').html(
                    '<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

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
                $('.tombolSimpanJenisPertanyaan').removeAttr('disabled');
                $('.tombolSimpanJenisPertanyaan').html('Update Jenis Pertanyaan');
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
                    }, 1000);
                }
            }
        })
        return false;
    });
</script>


<script>
    var kode_warna = "<?php echo $kode_warna ?>";
    var values = document.getElementById("values");
    var hexInput = document.getElementById("hexInput");
    let colorSquare = document.getElementById("colorSquare");

    var colorPicker = new iro.ColorPicker(".colorPicker", {
        width: 180,
        color: kode_warna,
        borderWidth: 2,
        borderColor: "#fff"
    });

    colorPicker.on(["color:init", "color:change"], function(color) {
        values.innerHTML = [
            "<b>HEX : </b>" + color.hexString,
            "<b>RGB : </b>" + color.rgbString,
            "<b>HSL : </b>" + color.hslString
        ].join("<br>");

        hexInput.value = color.hexString;
        colorSquare.style.backgroundColor = color.hexString;
    });
    hexInput.addEventListener("change", function() {
        colorPicker.color.hexString = this.value;
    });
</script>
@endsection