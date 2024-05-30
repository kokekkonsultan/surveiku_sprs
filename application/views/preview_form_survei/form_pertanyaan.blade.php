@extends('include_backend/_template')

@php
$ci = get_instance();
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
        <div class="col-md-8 offset-md-2" style="font-size: 16px;">
            <div class="card shadow mb-4 mt-4" data-aos="fade-up" style="font-family: 'Exo 2', sans-serif;">

            @include('survei/_include/_benner_survei')

				<div class="card-header text-center">
					<h3 class="mt-5" style="font-family: 'Exo 2', sans-serif;"><b>PERTANYAAN UNSUR</b></h3>
					@include('include_backend/partials_backend/_tanggal_survei')
                </div>

                <form>

                    <div class="card-body ml-5 mr-5">

                        <!-- Looping Pertanyaan Terbuka Paling Atas -->
                        @foreach ($pertanyaan_terbuka_atas->result() as $row_terbuka_atas)
                        @php
                        $model_ta = $row_terbuka_atas->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
                        @endphp

                        <div class="mt-10 mb-10">

                            <table class="table table-borderless" width="100%" border="0">
                                <tr>
                                    <td width="5%" valign="top">{!! $row_terbuka_atas->nomor_pertanyaan_terbuka !!}.
                                    </td>
                                    <td>{!! $row_terbuka_atas->isi_pertanyaan_terbuka !!}</td>
                                </tr>

                                <tr>
                                    <td width="5%"></td>
                                    <td style="font-weight:bold;" width="95%">

                                        @foreach ($jawaban_pertanyaan_terbuka->result() as $value_terbuka_atas)
                                        @if ($value_terbuka_atas->id_perincian_pertanyaan_terbuka ==
                                        $row_terbuka_atas->id_perincian_pertanyaan_terbuka)

                                        <div class="{{$model_ta}}-inline mb-2">
                                            <label class="{{$model_ta}} {{$model_ta}}-outline {{$model_ta}}-success {{$model_ta}}-lg"
                                                style="font-size: 16px;">

                                                <input type="{{$model_ta}}"
                                                    name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}]"
                                                    value="{{ $value_terbuka_atas->pertanyaan_ganda; }}"
                                                    class="terbuka_{{ $value_terbuka_atas->id_pertanyaan_terbuka }}">
                                                <span></span> {{ $value_terbuka_atas->pertanyaan_ganda; }}
                                            </label>
                                        </div>
                                        @endif
                                        @endforeach



                                        @if ($row_terbuka_atas->dengan_isian_lainnya == 1)
                                        
                                        <input class="form-control" name="jawaban_lainnya[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}]" 
                                            value=""
                                            pattern="^[a-zA-Z0-9.,\s]*$|^\w$"
                                            placeholder="Masukkan jawaban lainnya ..."
                                            id="terbuka_lainnya_{{ $row_terbuka_atas->id_pertanyaan_terbuka }}" style="display:none">
                                            
                                            <small id="text_terbuka_{{ $row_terbuka_atas->id_pertanyaan_terbuka }}" class="text-danger" style="display:none">**Pengisian form hanya dapat menggunakan tanda baca
                                            (.) titik dan (,) koma</small>
                                            <br>
                                        @endif


                                        @if ($row_terbuka_atas->id_jenis_pilihan_jawaban == 2)
                                        <textarea class="form-control" type="text"
                                            name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}]"
                                            placeholder="Masukkan Jawaban Anda ..."></textarea>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        @endforeach






                        <!-- Looping Pertanyaan Unsur -->
                        @php
                        $i = 1;
                        @endphp
                        @foreach ($pertanyaan_unsur->result() as $row)
                        @php
                        $model_u = $row->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
                        @endphp
                        <div class="mt-10 mb-10">
                            <table class="table table-borderless" width="100%" border="0">
                                <tr>
                                    <td width="5%" valign="top">{!! $row->nomor !!}.</td>
                                    <td width="95%">{!! $row->isi_pertanyaan_unsur !!}</td>
                                </tr>

                                <tr>
                                    <td width="5%"></td>
                                    <td style="font-weight:bold;" width="95%">


                                        {{-- Looping Pilihan Jawaban --}}
                                        @foreach ($jawaban_pertanyaan_unsur->result() as $value)
                                        @if ($value->id_pertanyaan_unsur == $row->id_pertanyaan_unsur)
                                        <div class="{{$model_u}}-inline mb-2">
                                            <label class="{{$model_u}} {{$model_u}}-outline {{$model_u}}-success {{$model_u}}-lg"
                                                style="font-size: 16px;">

                                                <input type="{{$model_u}}" name="jawaban_pertanyaan_unsur[{{ $i }}]"
                                                    value="{{$value->nomor_kategori_unsur_pelayanan}}"
                                                    class="unsur_{{$value->id_pertanyaan_unsur}}"><span></span> {{$value->nama_kategori_unsur_pelayanan}}
                                            </label>
                                        </div>
                                        @endif
                                        @endforeach
                                    </td>
                                </tr>

                        
                                
                                
                            </table>
                        </div>


                        <div id="display_terbuka_{{ $row->id_pertanyaan_unsur }}">
                            <!-- Looping Pertanyaan Terbuka -->

                            @foreach ($pertanyaan_terbuka->result() as $row_terbuka)
                            @if ($row_terbuka->id_unsur_pelayanan == $row->id_unsur_pelayanan)
                            @php
                            $model_t = $row_terbuka->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
                            @endphp
                            <div class=" mt-10 mb-10">

                                <table class="table table-borderless" width="100%" border="0">
                                    <tr>
                                        <td width="5%" valign="top">{!! $row_terbuka->nomor_pertanyaan_terbuka !!}.</td>
                                        <td width="95%">{!! $row_terbuka->isi_pertanyaan_terbuka !!}</td>
                                    </tr>

                                    <tr>
                                        <td width="5%"></td>
                                        <td style="font-weight:bold;" width="95%">
                                            @foreach ($jawaban_pertanyaan_terbuka->result() as $value_terbuka)
                                            @if ($value_terbuka->id_perincian_pertanyaan_terbuka ==
                                            $row_terbuka->id_perincian_pertanyaan_terbuka)

                                            <div class="{{$model_t}}-inline mb-2">
                                                <label class="{{$model_t}} {{$model_t}}-outline {{$model_t}}-success {{$model_t}}-lg"
                                                    style="font-size: 16px;">
                                                    <input type="{{$model_t}}"
                                                        name="jawaban_pertanyaan_terbuka[{{ $row_terbuka->id_pertanyaan_terbuka }}]"
                                                        value="{{ $value_terbuka->pertanyaan_ganda; }}"
                                                        class="terbuka_{{ $row_terbuka->id_pertanyaan_terbuka }}">
                                                    <span></span> {{ $value_terbuka->pertanyaan_ganda }}
                                                </label>
                                            </div>
                                            @endif
                                            @endforeach


                                            @if ($row_terbuka->dengan_isian_lainnya == 1)
                                            
                                            <input class="form-control" name="jawaban_lainnya[{{ $row_terbuka->id_pertanyaan_terbuka }}]" 
                                            value=""
                                            pattern="^[a-zA-Z0-9.,\s]*$|^\w$"
                                            placeholder="Masukkan jawaban lainnya ..." id="terbuka_lainnya_{{ $row_terbuka->id_pertanyaan_terbuka }}" style="display:none">

                                            <small id="text_terbuka_{{ $row_terbuka->id_pertanyaan_terbuka }}" class="text-danger" style="display:none">**Pengisian form hanya dapat menggunakan tanda baca
                                            (.) titik dan (,) koma</small>
                                            <br>
                                            @endif



                                            @if ($row_terbuka->id_jenis_pilihan_jawaban == 2)
                                            <textarea class="form-control" type="text"
                                                name="jawaban_pertanyaan_terbuka[{{ $row_terbuka->id_pertanyaan_terbuka }}]"
                                                placeholder="Masukkan Jawaban Anda ..."></textarea>

                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            @endif
                            @endforeach
                        </div>

                        @php
                        $i++;
                        @endphp
                        @endforeach




                        <!-- Looping Pertanyaan Terbuka Paling Bawah -->
                        @foreach ($pertanyaan_terbuka_bawah->result() as $row_terbuka_bawah)
                        @php
                            $model_tb = $row_terbuka_bawah->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
                            @endphp
                        <div class="mt-10 mb-10">

                            <table class="table table-borderless" width="100%" border="0">
                                <tr>
                                    <td width="5%" valign="top">{!! $row_terbuka_bawah->nomor_pertanyaan_terbuka !!}.
                                    </td>
                                    <td width="95%">{!! $row_terbuka_bawah->isi_pertanyaan_terbuka !!}</td>
                                </tr>

                                <tr>
                                    <td width="5%"></td>
                                    <td style="font-weight:bold;" width="95%">
                                        @foreach ($jawaban_pertanyaan_terbuka->result() as $value_terbuka_bawah)
                                        @if ($value_terbuka_bawah->id_perincian_pertanyaan_terbuka ==
                                        $row_terbuka_bawah->id_perincian_pertanyaan_terbuka)

                                        <div class="{{$model_tb}}-inline mb-2">
                                            <label class="{{$model_tb}} {{$model_tb}}-outline {{$model_tb}}-success {{$model_tb}}-lg"
                                                style="font-size: 16px;">

                                                <input type="{{$model_tb}}"
                                                    name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}]"
                                                    value="{{ $value_terbuka_bawah->pertanyaan_ganda; }}"
                                                    class="terbuka_{{ $value_terbuka_bawah->id_pertanyaan_terbuka }}">
                                                <span></span> {{ $value_terbuka_bawah->pertanyaan_ganda; }}
                                            </label>
                                        </div>
                                        @endif
                                        @endforeach

                                        @if ($row_terbuka_bawah->dengan_isian_lainnya == 1)
                                           <input class="form-control" name="jawaban_lainnya[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}]" 
                                            value=""
                                            pattern="^[a-zA-Z0-9.,\s]*$|^\w$"
                                            placeholder="Masukkan jawaban lainnya ..."
                                            id="terbuka_lainnya_{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}" style="display:none">
                                            
                                            <small id="text_terbuka_{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}" class="text-danger" style="display:none">**Pengisian form hanya dapat menggunakan tanda baca
                                            (.) titik dan (,) koma</small>
                                            <br>
                                        @endif


                                        @if ($row_terbuka_bawah->id_jenis_pilihan_jawaban == 2)
                                        <textarea class="form-control" type="text"
                                            name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}]"
                                            placeholder="Masukkan Jawaban Anda ..."></textarea>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        @endforeach
                    </div>


                    <div class="card-footer">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-left">
                                    {!! anchor(base_url() . $ci->session->userdata('username') . '/' .
                                    $ci->uri->segment(2)
                                    . '/preview-form-survei/data-responden', '<i class="fa fa-arrow-left"></i>
                                    Kembali',
                                    ['class' => 'btn btn-secondary btn-lg font-weight-bold shadow']) !!}
                                </td>
                                <td class="text-right">
                                    <a class="btn btn-warning btn-lg font-weight-bold shadow"
                                        href="<?php echo $url_next ?>">Selanjutnya
                                        <i class="fa fa-arrow-right"></i></a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection



@section('javascript')

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>


@foreach ($ci->db->get("pertanyaan_terbuka_$manage_survey->table_identity")->result() as $pt)
<script type="text/javascript">
$(function() {
    $(":radio.terbuka_<?= $pt->id ?>").click(function() {
         if($(this).val() == 'Lainnya'){
            $("#terbuka_lainnya_<?= $pt->id ?>").prop('required', true).show();
            $("#text_terbuka_<?= $pt->id ?>").show();
        } else {
            $("#terbuka_lainnya_<?= $pt->id ?>").removeAttr('required').hide();
            $("#text_terbuka_<?= $pt->id ?>").hide();
        }

    });

});
</script>
@endforeach


@endsection