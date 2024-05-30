@php
$title_header = unserialize($manage_survey->title_header_survey);
$title_1 = $title_header[0];
$title_2 = $title_header[1];


$color = unserialize($manage_survey->warna_benner);
$color1 = $color[0];
$color2 = $color[1];
$color3 = $color[2];
@endphp

@if($manage_survey->is_benner == 2)
<div class="card-header text-dark" style="background-color: #E4E6EF;">
    <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
        <h1 style="font-weight:800;">{!! strtoupper($title_1) !!}</h1>
        <h3 class="mt-3" style="font-weight:800;">{!! strtoupper($title_2) !!}</h3>
    </div>
</div>

@elseif($manage_survey->is_benner == 3)
<div class="card-header text-dark" style="background-color: <?= $color1 ?>;">
    <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
        <h1 style="font-weight:800;">{!! strtoupper($title_1) !!}</h1>
        <h3 class="mt-3" style="font-weight:800;">{!! strtoupper($title_2) !!}</h3>
    </div>
</div>

@elseif($manage_survey->is_benner == 4)
<div class="card-header text-dark"  style="background-image: linear-gradient(to bottom right, <?= $color2 . ', ' . $color3 ?>);">
    <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
        <h1 style="font-weight:800;">{!! strtoupper($title_1) !!}</h1>
        <h3 class="mt-3" style="font-weight:800;">{!! strtoupper($title_2) !!}</h3>
    </div>
</div>

@elseif($manage_survey->is_benner == 5)
<div class="card-header text-dark" style="background-color: <?= $color1 ?>;">
    <table class="table table-borderless mt-5" width="100%">
        <tr>
            <td width="15%" style="vertical-align:middle;">
                <img src="{{ base_url() . 'assets/klien/foto_profile/200px.jpg' }}" height="100" width="100" alt="">
            </td>
            <td style="font-family: Helvetica, Arial, sans-serif; vertical-align:middle;">
                <h1 style="font-weight:800;">{!! strtoupper($title_1) !!}</h1>
                <h3 class="mt-3" style="font-weight:800;">{!! strtoupper($title_2) !!}</h3>
            </td>
        </tr>
    </table>
</div>

@else

@if($manage_survey->img_benner == '')
<img class="card-img-top" src="{{ base_url() . 'assets/img/site/page/banner-survey.jpg' }}" alt="new image" />
@else
<img class="card-img-top shadow" src="{{ base_url() . 'assets/klien/benner_survei/' . $manage_survey->img_benner}}" alt="new image">
@endif

@endif





<!-- 1 -->
<!-- <img class="card-img-top" src="{{ base_url() }}assets/img/site/page/banner-survey.jpg" alt="new image" /> -->

<!-- 2 -->
<!-- <div class="card-header" style="background-color: #E4E6EF;">
    <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
        <h1 style="font-weight:800;">{{strtoupper('Survei Kepuasan Pelanggan')}}</h1>
        <h3 class="mt-3" style="font-weight:800;">{{strtoupper('Direktorat Jendral Pajak')}}</h3>
    </div>
</div> -->

<!-- 3 -->
<!-- <div class="card-header" style="background-color: yellow;">
    <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
        <h1 style="font-weight:800;">{{strtoupper('Survei Kepuasan Pelanggan')}}</h1>
        <h3 class="mt-3" style="font-weight:800;">{{strtoupper('Direktorat Jendral Pajak')}}</h3>
    </div>
</div> -->

<!-- 4 -->
<!-- <div class="card-header"  style="background-image: linear-gradient(to bottom right, red, yellow);">
    <div class="text-center font-weight-bold mt-5 mb-5" style="font-family: Helvetica, Arial, sans-serif;">
        <h1 style="font-weight:800;">{{strtoupper('Survei Kepuasan Pelanggan')}}</h1>
        <h3 class="mt-3" style="font-weight:800;">{{strtoupper('Direktorat Jendral Pajak')}}</h3>
    </div>
</div> -->

<!-- 5 -->
<!-- <div class="card-header" style="background-color: #E4E6EF;">
    <table class="table table-borderless mt-5" width="100%">
        <tr>
            <td width="15%">
                <img src="{{base_url() . 'assets/klien/foto_profile/200px.jpg'}}" height="100" width="100" alt="">
            </td>
            <td style="font-family: Helvetica, Arial, sans-serif; vertical-align:middle;">
            <h1 style="font-weight:800;">{{strtoupper('Survei Kepuasan Pelanggan')}}</h1>
            <h3 class="mt-3" style="font-weight:800;">{{strtoupper('Direktorat Jendral Pajak')}}</h3>
            </td>
        </tr>
    </table>
</div> -->

