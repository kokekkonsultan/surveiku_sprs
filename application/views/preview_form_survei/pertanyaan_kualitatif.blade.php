@extends('include_backend/_template')

@php
$ci = get_instance();
@endphp

@section('style')
<!-- <link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> -->
@endsection

@section('content')

<style>
i {
    color: #3F4254 !important;
}
</style>

<div class="container mt-5 mb-5">
    <div class="text-center" data-aos="fade-up">
        <div id="progressbar" class="mb-5">
            <li class="active" id="account"><strong>Data Responden</strong></li>
            <li class="active" id="personal"><strong>Pertanyaan Survei</strong></li>
            @if($status_saran == 1)
            <li id="payment"><strong>Saran</strong></li>
            @endif
            <li id="completed"><strong>Completed</strong></li>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-8 offset-md-2" style="font-size: 16px; ">
            <div class="card shadow mb-4 mt-4" data-aos="fade-up" style="border-left: 0px solid #FFA800;">

            @include('survei/_include/_benner_survei')

            <div class="card-header text-center">
					<h3 class="mt-5" style="font-family: 'Exo 2', sans-serif;"><b>PERTANYAAN KUALITATIF</b></h3>
					@include('include_backend/partials_backend/_tanggal_survei')
                </div>

                <div class="card-body">



                    <form>

                        </br>

                        @php
                        $no = 1;
                        @endphp

                        @foreach ($kualitatif as $row)


                        <table class="table table-borderless" border="0">
                            <tr>
                                <td width="4%" valign="top">{{ $no++ }}.</td>
                                <td><?php echo $row->isi_pertanyaan ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <textarea type="text" rows="7" class="form-control" id="isi_jawaban_kualitatif"
                                        name="isi_jawaban_kualitatif[]" value=""
                                        placeholder="Masukkan jawaban pertanyaan kualitatif pada bidang ini.."></textarea>
                                </td>
                            </tr>
                        </table>

                        <br>

                        @endforeach



                </div>
                <div class="card-footer">

                    <table class="table table-borderless">
                        <tr>
                            <td class="text-left">
                                {!! anchor($url_back, '<i class="fa fa-arrow-left"></i>
                                Kembali',
                                ['class' => 'btn btn-secondary btn-lg font-weight-bold shadow']) !!}
                            </td>
                            <td class="text-right">
                                <a class="btn btn-warning btn-lg font-weight-bold shadow"
                                    href="<?php echo $url_next ?>">Selanjutnya <i class="fa fa-arrow-right text-white"></i></a>
                            </td>
                        </tr>
                    </table>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('javascript')
<script>
$('.form_survei').submit(function(e) {

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        cache: false,
        beforeSend: function() {
            $('.tombolCancel').attr('disabled', 'disabled');
            $('.tombolSave').attr('disabled', 'disabled');
            $('.tombolSave').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');

            KTApp.block('#kt_blockui_content', {
                overlayColor: '#FFA800',
                state: 'primary',
                message: 'Processing...'
            });

            setTimeout(function() {
                KTApp.unblock('#kt_blockui_content');
            }, 1000);

        },
        complete: function() {
            $('.tombolCancel').removeAttr('disabled');
            $('.tombolSave').removeAttr('disabled');
            $('.tombolSave').html('Selanjutnya <i class="fa fa-arrow-right"></i>');
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
                // toastr["success"]('Data berhasil disimpan');

                setTimeout(function() {
                    window.location.href = data.url_next;
                }, 500);
            }
        }
    })
    return false;
});
</script>
@endsection