@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

<style type="text/css">
[pointer-events="bounding-box"] {
    display: none
}
</style>
@endsection

@section('content')

<div class="container-fluid">

    @php
    $user_id = $ci->session->userdata('user_id');
    $user_now = $ci->ion_auth->user($user_id)->row();
    @endphp


            <div class="card card-custom card-stretch-half gutter-b overflow-hidden">
                <div class="card-body d-flex p-0">
                    <div class="flex-grow-1 p-12 card-rounded bgi-no-repeat d-flex flex-column justify-content-center align-items-start"
                        style="background-color: #1B283F; background-position: center; background-size: 100% auto; background-image:url(/assets/img/banner/bg_indo.jpg);">
                        <h1 class="font-weight-bolder text-white mb-2">Halo, {{ $user_now->first_name }}
                            {{ $user_now->last_name }}
                        </h1>
                        <div class="font-size-h6 text-white mb-8">Selamat memulai aktifitas anda kembali</div>
                        <br>
                        <br>
                    </div>
                </div>
            </div>



 
    @if ($ci->ion_auth->in_group('admin'))

    @endif



    @if ($ci->ion_auth->in_group('client'))

    <!-- <span class="mb-5">
    <div id="response-list-activity">
                <div align="center"><br><br>
                    <img src="{{ base_url() }}assets/img/ajax/preloader.gif" alt="" width="80px">
                </div>
            </div>
    </span> -->

    <span class="mb-5">
        <div id="chart-survei">
                <div align="center">
                    <img src="{{ base_url() }}assets/img/ajax/preloader.gif" alt="" width="80px">
                </div>
            </div>
    </span>

    <!-- <span class="mb-5">
        <div id="tabel-survei">
            <div align="center">
                <img src="{{ base_url() }}assets/img/ajax/preloader.gif" alt="" width="80px">
            </div>
        </div>
    </span> -->

    <!-- <span class="m-5 mb-5">
        <div id="jumlah-survei">
            <div align="center">
                <img src="{{ base_url() }}assets/img/ajax/preloader.gif" alt="" width="80px">
            </div>
        </div>
    </span> -->
    @endif





    @if ($ci->ion_auth->in_group('client_induk'))

    <span class="mb-5">
        <div id="chart-survei-induk">
            <div align="center">
                <img src="{{ base_url() }}assets/img/ajax/preloader.gif" alt="" width="80px">
            </div>
        </div>
    </span>

    <!-- <span class="m-5 mb-5">
        <div id="tabel-survei-induk">
            <div align="center">
                <img src="{{ base_url() }}assets/img/ajax/preloader.gif" alt="" width="80px">
            </div>
        </div>
    </span> -->
    @endif






</div>

@endsection

@section('javascript')

<script src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.accessibility.js">
</script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.candy.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.carbon.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fint.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.fusion.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.gammel.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.ocean.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.umber.js"></script>
<script src="{{ base_url() }}assets/vendor/fusioncharts-suite-xt/js/themes/fusioncharts.theme.zune.js"></script>

<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="{{ base_url() }}assets/themes/metronic/assets/js/pages/features/charts/apexcharts.js"></script>

<script>
jQuery(function($) {
    $('#selMenu').on('change', function() {
        var url = "{{ $ci->session->userdata('username') }}/" + $(this).val();
        if (url) {
            window.location = url;
        }
        return false;
    });
});


$(document).ready(function() {
    $("#selMenu").select2({
        placeholder: 'Cari yang anda perlukan disini',
        theme: "bootstrap4",
        // minimumInputLength: 5,
        multiple: false,
        // separator: '|',
        allowClear: true,
        ajax: {
            url: "{{ base_url() }}get-menu",
            type: "post",
            dataType: 'json',
            delay: 250,
            cache: true,
            data: function(params) {
                return {
                    searchTerm: params.term, // search term
                    //type: 'public'
                    page: params.page || 1
                };
            },
            processResults: function(data, params) {
                console.log(data);
                var page = params.page || 1;
                return {
                    results: data,
                    "pagination": {
                        more: (page * 10) <= data[0].total_count
                    }
                };
            },
        },
    });
});


</script>


@if ($ci->ion_auth->in_group('admin'))
@endif


@if ($ci->ion_auth->in_group('client'))
<script>
    
$(function() {
    $.ajax({
        type: "GET",
        url: "{{ base_url() }}dashboard/jumlah-survei",
        dataType: "html",
        success: function(response) {
            $("#jumlah-survei").html(response);
        },
    });
});

$(function() {
    $.ajax({
        type: "GET",
        url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/dashboard/chart-survei",
        dataType: "html",
        success: function(response) {
            $("#chart-survei").html(response);
        },
    });

});

$(function() {

    $.ajax({
        type: "GET",
        url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/dashboard/tabel-survei",
        dataType: "html",
        success: function(response) {
            $("#tabel-survei").html(response);
        }
    });

});


$(function() {

    $.ajax({
        type: "GET",
        url: "{{ base_url() }}{{ $ci->session->userdata('username') }}/overview/list-activity",
        dataType: "html",
        success: function(response) {
            $("#response-list-activity").html(response);
        }

    });

});
</script>
@endif

@if ($ci->ion_auth->in_group('client_induk'))
<script>
    $(function() {
    $.ajax({
        type: "GET",
        url: "{{ base_url() }}dashboard/chart-survei-induk",
        dataType: "html",
        success: function(response) {
            $("#chart-survei-induk").html(response);
        },
        // error: function(data) {
        //     alert("Error Request Found");
        // }
    });

});

$(function() {

    $.ajax({
        type: "GET",
        url: "{{ base_url() }}dashboard/tabel-survei-induk",
        dataType: "html",
        success: function(response) {
            $("#tabel-survei-induk").html(response);
        },
        // error: function(data) {
        //     alert("Error Request Found");
        // }
    });

});
</script>
@endif



@endsection