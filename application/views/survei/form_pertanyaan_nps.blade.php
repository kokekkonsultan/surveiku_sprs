@extends('include_backend/_template')

@php
$ci = get_instance();
$is_edit = $ci->uri->segment(5) == 'edit' ? '/edit' : '';
@endphp

@section('style')
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
@endsection

@section('content')


<div class="container mt-5 mb-5" style="font-family: nunito;">
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
        <div class="col-md-8 offset-md-2" style="font-size: 16px; font-family:arial, helvetica, sans-serif;">
            <div class="card shadow mb-4 mt-4" id="kt_blockui_content" data-aos="fade-up">

            @include('survei/_include/_benner_survei')

            
                <div class="card-header text-center">
                    <h3 class="mt-5" style="font-family: 'Exo 2', sans-serif;"><b>PERTANYAAN NPS</b></h3>
					@include('include_backend/partials_backend/_tanggal_survei')
                </div>
                <div class="card-body">

                    <form action="{{base_url() . 'survei/' . $ci->uri->segment(2) . '/add-pertanyaan-nps/' . $ci->uri->segment(4)}}" class="form_survei" method="POST">


                        @php
                        $i = 1;
                        @endphp

                        @foreach ($pertanyaan_nps->result() as $row)

                        <input type="hidden" name="id[{{ $i }}]" value="{{ $row->id }}">
                        <table class="table table-borderless mt-5 mb-5" width="100%" border="0">
                            <tr>
                                <td width="5%" valign="top">{{$i}}.</td>
                                <td width="95%">{!! $row->isi_pertanyaan !!}</td>
                            </tr>

                            <tr>
                                <td width="5%"></td>
                                <td style="font-weight: bold;" width="95%">

                                <div class="radio-inline mb-2">
                                    @foreach ($ci->db->get_where("pilihan_jawaban_nps_$manage_survey->table_identity", ['id_pertanyaan_nps' => $row->id])->result() as $value)

                                    
                                        <label class="radio radio-rounded radio-primary" data-toggle="tooltip" data-placement="bottom" data-original-title="Skor = {{$value->bobot}}"
                                            style="font-size: 16px;">
                                            <input type="radio" name="jawaban_pertanyaan_nps[{{ $i }}]"
                                                value="{{ $value->bobot }}"
                                                <?= $value->bobot == $row->skor_jawaban ? 'checked' : '' ?>
                                                <?= $row->is_required == 1 ? 'required' : '' ?>><span></span>
                                                <div style="font-size:40px">
                                                    <img src="{{base_url() . 'assets/img/emoji/' . $value->nama_kategori}}" width="25">    
                                                </div>
                                        </label>
                                    

                                    @endforeach
                                    
                                </td>
                            </tr>
                        </table>
                        <br>

                        @php
                        $i++;
                        @endphp

                        @endforeach
                </div>


                <div class="card-footer">
                    <table class="table table-borderless">
                        <tr>
                            <td class="text-left">
                                @if(in_array(3, $atribut_pertanyaan))
                                <a class="btn btn-secondary btn-lg font-weight-bold shadow tombolCancel" href="{{base_url() . 'survei/' . $ci->uri->segment(2) . '/pertanyaan-kualitatif/' . $ci->uri->segment(4) . $is_edit}}"><i class="fa fa-arrow-left"></i> Kembali</a>

                                @elseif (in_array(1, $atribut_pertanyaan))
                                <a class="btn btn-secondary btn-lg font-weight-bold shadow tombolCancel" href="{{base_url() . 'survei/' . $ci->uri->segment(2) . '/pertanyaan-nps/' . $ci->uri->segment(4) . $is_edit}}"><i class="fa fa-arrow-left"></i> Kembali</a>

                                @else
                                <a class="btn btn-secondary btn-lg font-weight-bold shadow tombolCancel" href="{{base_url() . 'survei/' . $ci->uri->segment(2) . '/pertanyaan/' . $ci->uri->segment(4) . $is_edit}}"><i class="fa fa-arrow-left"></i> Kembali</a>
                                @endif
                            </td>
                            <td class="text-right">
                                <button type="submit"
                                    class="btn btn-warning btn-lg font-weight-bold shadow tombolSave">Selanjutnya
                                    <i class="fa fa-arrow-right"></i>
                                </button>
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
                    window.location.href = "<?php echo $url_next ?>";
                }, 500);
            }
        }
    })
    return false;
});
</script>
@endsection