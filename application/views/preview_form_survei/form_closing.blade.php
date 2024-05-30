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
            <li class="active" id="account"><strong>Data Responden</strong></li>
            <li class="active" id="personal"><strong>Pertanyaan Survei</strong></li>
            @if($status_saran == 1)
            <li class="active" id="payment"><strong>Saran</strong></li>
            @endif
            <li class="active" id="completed"><strong>Completed</strong></li>
        </div>
    </div>
    <br>
    <br>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow" data-aos="fade-up">
                
                @include('survei/_include/_benner_survei')
            
                <div class="card-body">

                    <div class="text-center">

                        <i class="fa fa-check-circle" style="font-size: 72px; color: #32CD32;"></i>

                        <br>
                        <br>
                        <br>


                        <div class="font-weight-bold" style="font-size: 15px;">
                            <!-- Terima kasih atas kesediaannya dan partisipasinya untuk mengisi kuesioner
                            <?php echo $manage_survey->survey_name ?>.
                            <br>
                            Saran dan penilaian Saudara memberikan konstribusi yang sangat berarti bagi
                            peningkatan instansi kami. -->
                        @php
                        $slug = $ci->uri->segment(2);

                        $manage_survey = $ci->db->query("SELECT *
                        FROM manage_survey
                        JOIN users ON manage_survey.id_user = users.id
                        WHERE slug = '$slug'")->row();
                        @endphp

                        {!! $manage_survey->deskripsi_selesai_survei !!}
                        </div>

                        <br>
                        <br>


                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    let timerInterval
    Swal.fire({
        icon: 'success',
        title: 'Sukses',
        text: 'Data survei anda berhasil disimpan.',
        confirmButtonColor: '#32CD32',
        confirmButtonText: 'Baik, saya mengerti',
        timer: 6000,
        footer: '<span style="color:d3d3d3;">Data yang anda inputkan kami simpan dengan aman dan tidak kami bagikan kepada yang tidak memiliki kepentingan.</span>',

    }).then((result) => {
        if (
            result.dismiss === Swal.DismissReason.timer
        ) {
            console.log('I was closed by the timer')
        }
    });
});
</script>
@endsection