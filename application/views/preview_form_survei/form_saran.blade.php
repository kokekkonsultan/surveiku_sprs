@extends('include_backend/_template')

@php
$ci = get_instance();
@endphp

@section('style')
<!-- <link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet"> -->
@endsection

@section('content')


<div class="container mt-5 mb-5">
    <div class="text-center" data-aos="fade-up">
        <div id="progressbar" class="mb-5">
            <li class="active" id="account"><strong>Data Responden</strong></li>
            <li class="active" id="personal"><strong>Pertanyaan Survei</strong></li>
            <li class="active" id="payment"><strong>Saran</strong></li>
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
                    <h4><b>SARAN</b> - @include('include_backend/partials_backend/_tanggal_survei')</h4>
                </div>
                <div class="card-body">

                    <div>
                        <label
                            style="font-size: 14px; text-transform: capitalize;"><?php echo $manage_survey->judul_form_saran ?></label>
                        <br />
                        {!! form_textarea($saran) !!}
                        <small class="text-danger">* Inputan form saran tidak dapat menggunakan tanda baca!</small>
                    </div>

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
                                    href="<?php echo base_url() . $ci->uri->segment(1) . '/' . $ci->uri->segment(2) . '/preview-form-survei/selesai' ?>">Selanjutnya <i class="fa fa-arrow-right"></i></a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>


    @endsection

    @section('javascript')

    @endsection