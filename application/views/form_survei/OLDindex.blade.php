@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet"
    type="text/css">

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

            <div class="card card-custom bgi-no-repeat gutter-b"
                style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)"
                data-aos="fade-down">
                <div class="card-body d-flex align-items-center">
                    <div>
                        <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                            {{strtoupper($title)}}
                        </h3>

                        <a class="btn btn-primary font-weight-bold btn-sm" target="_blank"
                            href="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/preview-form-survei/opening' ?>"><i
                                class="fas fa-solid fa-eye"></i>Lihat Tampilan Form Survei</a>

                        <!-- <button type="button" class="btn btn-light font-weight-bold btn-sm" data-toggle="modal"
                            data-target="#warna-survei">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-paint-bucket" viewBox="0 0 16 16">
                                <path
                                    d="M6.192 2.78c-.458-.677-.927-1.248-1.35-1.643a2.972 2.972 0 0 0-.71-.515c-.217-.104-.56-.205-.882-.02-.367.213-.427.63-.43.896-.003.304.064.664.173 1.044.196.687.556 1.528 1.035 2.402L.752 8.22c-.277.277-.269.656-.218.918.055.283.187.593.36.903.348.627.92 1.361 1.626 2.068.707.707 1.441 1.278 2.068 1.626.31.173.62.305.903.36.262.05.64.059.918-.218l5.615-5.615c.118.257.092.512.05.939-.03.292-.068.665-.073 1.176v.123h.003a1 1 0 0 0 1.993 0H14v-.057a1.01 1.01 0 0 0-.004-.117c-.055-1.25-.7-2.738-1.86-3.494a4.322 4.322 0 0 0-.211-.434c-.349-.626-.92-1.36-1.627-2.067-.707-.707-1.441-1.279-2.068-1.627-.31-.172-.62-.304-.903-.36-.262-.05-.64-.058-.918.219l-.217.216zM4.16 1.867c.381.356.844.922 1.311 1.632l-.704.705c-.382-.727-.66-1.402-.813-1.938a3.283 3.283 0 0 1-.131-.673c.091.061.204.15.337.274zm.394 3.965c.54.852 1.107 1.567 1.607 2.033a.5.5 0 1 0 .682-.732c-.453-.422-1.017-1.136-1.564-2.027l1.088-1.088c.054.12.115.243.183.365.349.627.92 1.361 1.627 2.068.706.707 1.44 1.278 2.068 1.626.122.068.244.13.365.183l-4.861 4.862a.571.571 0 0 1-.068-.01c-.137-.027-.342-.104-.608-.252-.524-.292-1.186-.8-1.846-1.46-.66-.66-1.168-1.32-1.46-1.846-.147-.265-.225-.47-.251-.607a.573.573 0 0 1-.01-.068l3.048-3.047zm2.87-1.935a2.44 2.44 0 0 1-.241-.561c.135.033.324.11.562.241.524.292 1.186.8 1.846 1.46.45.45.83.901 1.118 1.31a3.497 3.497 0 0 0-1.066.091 11.27 11.27 0 0 1-.76-.694c-.66-.66-1.167-1.322-1.458-1.847z" />
                            </svg> Ubah Warna Form Survei</button> -->
                    </div>
                </div>
            </div>

            <div class="card card-custom card-sticky" data-aos="fade-down">
                <div class="card-body">

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                                aria-controls="home" aria-selected="true">DESKRIPSI FORM SURVEI</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="benner-tab" data-toggle="tab" href="#benner" role="tab"
                                aria-controls="benner" aria-selected="false">BANNER FORM SURVEI</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                                aria-controls="profile" aria-selected="false">JENIS PERTANYAAN SURVEI</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" id="saran-tab" data-toggle="tab" href="#saran" role="tab"
                                aria-controls="saran" aria-selected="false">FORM SARAN</a>
                        </li>
                    </ul>

                    <br>


                    <div class=" tab-content" id="myTabContent">

                        <!------------------------------------- LOGO ------------------------------------->
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <div class="text-right mb-5">
                                <button class=" btn btn-light-primary btn-sm" type="button" data-toggle="collapse"
                                    data-target="#collapseExample" aria-expanded="false"
                                    aria-controls="collapseExample">
                                    <i class="fa fa-edit"></i> Edit Deskripsi Survei
                                </button>
                            </div>

                            <div class="collapse" id="collapseExample">
                                <div class="card shadow">

                                    <div class="card-body">
                                        @php
                                        $title_header = unserialize($manage_survey->title_header_survey);
                                        $title_1 = $title_header[0];
                                        $title_2 = $title_header[1];
                                        @endphp

                                        <form
                                            action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-header' ?>"
                                            class="form_header" method="POST">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label font-weight-bold">Judul <span
                                                        style="color: red;">*</span></label>
                                                <div class="col-sm-10">
                                                    <textarea name="title[]" value="" class="form-control"
                                                        required><?php echo $title_1 ?></textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label font-weight-bold">Sub Judul <span
                                                        style="color: red;">*</span></label>
                                                <div class="col-sm-10">
                                                    <textarea name="title[]" value="" class="form-control"
                                                        required><?php echo $title_2 ?></textarea>
                                                </div>
                                            </div>


                                            <div class="text-right">
                                                <button class="btn btn-secondary btn-sm" type="button"
                                                    data-toggle="collapse" data-target="#collapseExample"
                                                    aria-expanded="false" aria-controls="collapseExample">
                                                    Close
                                                </button>
                                                <button type="submit"
                                                    class="btn btn-light-primary btn-sm font-weight-bold tombolSimpanHeader">Update</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <br>
                            </div>

                            <nav class="navbar navbar-light bg-white shadow">
                                <div class="outer-box">
                                    <div class="box-edge-logo">
                                        @if ($data_user->foto_profile == NULL)
                                        <img src="<?php echo base_url(); ?>assets/klien/foto_profile/200px.jpg"
                                            width="100%" class="" alt="">
                                        @else
                                        <img src="<?php echo URL_AUTH; ?>assets/klien/foto_profile/<?php echo $data_user->foto_profile ?>"
                                            width="100%" class="" alt="">
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
                            </nav>

                            <div class="font-weight-bold font-italic pt-5"><b class="text-danger">**</b> Logo akan tampil dibagian header dan cover laporan survei</div>
                        </div>


                        <!-- ATRIBUTE PERTANYAAN SURVEI -->
                        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

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
                        </div>




                        <!-- HEADER SURVEI -->
                        <div class="tab-pane fade" id="benner" role="tabpanel" aria-labelledby="benner-tab">

                            <div class="text-right mb-5">
                                <button class=" btn btn-light-primary btn-sm" type="button" data-toggle="collapse"
                                    data-target="#collapseHeader" aria-expanded="false" aria-controls="collapseExample">
                                    <i class="fa fa-edit"></i> Edit Banner Form Survei
                                </button>
                            </div>

                            <div class="collapse" id="collapseHeader">

                                <div class="card card-body shadow mb-5">
                                    <form id="uploadForm">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="upload" id="profil">
                                            <label class="custom-file-label" for="validatedCustomFile">Choose
                                                file...</label>
                                        </div>
                                        <br>
                                        <small class="text-danger">* Format file harus jpg/png.<br>* Ukuran max
                                            file adalah 10MB.</small>

                                        <div class="text-right mt-3">
                                            <button class="btn btn-secondary btn-sm" type="button"
                                                data-toggle="collapse" data-target="#collapseHeader"
                                                aria-expanded="false" aria-controls="collapseExample">
                                                Close
                                            </button>
                                            <button type="submit"
                                                class="btn btn-primary btn-sm font-weight-bold tombolUploud">Upload</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @if($manage_survey->img_benner == '')
                            <img class="card-img-top" src="{{ base_url() }}assets/img/site/page/banner-survey.jpg"
                                alt="new image" />
                            @else
                            <img class="card-img-top shadow"
                                src="{{ base_url() }}assets/klien/benner_survei/{{$manage_survey->img_benner}}"
                                alt="new image">
                            @endif

                            <div class="font-weight-bold font-italic pt-5"><b class="text-danger">**</b> Banner akan tampil dibagian header halaman survei</div>
                        </div>






                        <!-- ==================================== SARAN ========================================== -->
                        <div class="tab-pane fade" id="saran" role="tabpanel" aria-labelledby="saran-tab">

                            <div class="alert alert-custom alert-notice alert-light-primary fade show mb-7"
                                role="alert">
                                <div class="alert-icon"><i class="flaticon-warning"></i></div>
                                <div class="alert-text">Jika diaktifkan, maka akan ditampilkan form saran
                                    survei.</div>
                                <div class="alert-close">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true"><i class="ki ki-close"></i></span>
                                    </button>
                                </div>
                            </div>

                            <form
                                action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/form-survei/update-saran' ?>"
                                class="form_saran">

                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label font-weight-bold">Aktifkan Form
                                        Saran <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="is_saran"
                                            value="<?php echo set_value('is_saran'); ?>">
                                            <option value="1" <?php if ($manage_survey->is_saran == "1") {
                                                                    echo "selected";
                                                                } ?>>Ya</option>
                                            <option value="2" <?php if ($manage_survey->is_saran == "2") {
                                                                    echo "selected";
                                                                } ?>>Tidak</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-form-label font-weight-bold">Judul Form
                                        Saran <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="judul_form_saran" value=""
                                        rows="3"><?php echo $manage_survey->judul_form_saran ?></textarea>
                                </div>

                                @if($manage_survey->is_question == 1)
                                <div class="text-right">
                                    <button type="submit"
                                        class="btn btn-primary btn-sm font-weight-bold tombolSimpanSaran">Update
                                        Form
                                        Saran</button>
                                </div>
                                @endif
                            </form>

                        </div>
                    </div>
                </div>
            </div>




            <!-- ==================================== DESKRIPSI PEMBUKA ========================================== -->
            <div class="card card-custom card-sticky mt-5" data-aos="fade-down">
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
            </div>

        </div>
    </div>
</div>



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
</div>

@endsection

@section('javascript')
<script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>

<script>
ClassicEditor
    .create(document.querySelector('#editor'))
    .then(editor => {
        console.log(editor);
    })
    .catch(error => {
        console.error(error);
    });
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