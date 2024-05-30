@extends('include_backend/_template')

@php
$ci = get_instance();
@endphp

@section('style')
<link rel="dns-prefetch" href="//fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
@endsection

@section('content')


<div class="container mt-5 mb-5">
    <div class="text-center" data-aos="fade-up">
        <div id="progressbar" class="mb-5">
            <li id="account"><strong>Data Responden</strong></li>
            <li id="personal"><strong>Pertanyaan Survei</strong></li>
            @if($status_saran == 1)
            <li id="payment"><strong>Saran</strong></li>
            @endif
            <li id="completed"><strong>Completed</strong></li>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow" data-aos="fade-up">

                @include('survei/_include/_benner_survei')

                <div class="card-body">
                    <div>
                        @php
                        $slug = $ci->uri->segment(2);

                        $manage_survey = $ci->db->query("SELECT *
                        FROM manage_survey
                        JOIN users ON manage_survey.id_user = users.id
                        WHERE slug = '$slug'")->row();
                        @endphp

                        {!! $manage_survey->deskripsi_opening_survey !!}
                    </div>
                    <br><br>
                    <a class="btn btn-warning btn-block font-weight-bold shadow"
                        href="{{base_url() . $ci->uri->segment(1) . '/' . $ci->uri->segment(2) . '/preview-form-survei/data-responden'}}">
                        IKUT SURVEI
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('javascript')

@endsection