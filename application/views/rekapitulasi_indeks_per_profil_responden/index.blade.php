@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')

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
                            TABULASI DAN {{strtoupper($title)}}
                        </h3>

                    </div>
                </div>
            </div>

            <div class="card mt-5">
				<div class="card-body">

					<div>
						<div id="chart-container">FusionCharts XT will load here!</div>
					</div>
					<table width="466" border="1" class="table table-bordered">
						<tr>
						  <td width="233"><strong>Nama Survei Yang Dilakukan</strong></td>
						  <td width="233">{{ $manage_survey->survey_name }} </td>
						</tr>
						<tr>
						  <td><strong>Responden</strong></td>
						  <td>{{ $jumlah_kuisioner }}</td>
						</tr>
                        @php
                        $new_cart = '';
                        $indeks = 0;
                        $i = 0;
                        $b = 0;
                        @endphp
                        @foreach ($profil_responden as $key => $value)
                        
                        <tr>
						  <td><strong>{{ $value->nama_alias }}</strong></td>
						  <td></td>
						</tr>
                        @endforeach
						
                        
						<tr>
						  <td><strong>Indeks</strong></td>
						  <td></td>
						</tr>
					  </table>

				</div>
			</div>


        </div>
    </div>

</div>
</div>

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


<style type="text/css">
[pointer-events="bounding-box"] {
    display: none
}
</style>
    

<script type="text/javascript">
    //STEP 2 - Chart Data
    /*const chartData = [{
        "label": "Indeks Layanan A",
        "value": "3.1"
    }, {
        "label": "Indeks Layanan B",
        "value": "3.2"
    }];*/
    const chartData = [];
    
    //STEP 3 - Chart Configurations
    const chartConfig = {
    type: 'bar3d',//column3d
    renderAt: 'chart-container',
    width: '100%',
    height: '500',
    dataFormat: 'json',
    dataSource: {
        // Chart Configuration
        "chart": {
            "caption": "{{ $manage_survey->survey_name }}",
            "subCaption": "Grafik per profil responden yang disurvei",
            "xAxisName": "Profil Responden yang disurvei",
            "yAxisName": "Nilai Indeks",
            "numberSuffix": "",
			"showValues": "1",
          	"rotateValues": "0",
			"valueFont": "Arial",
			"valueFontColor": "#5d62b5",
			"valueFontSize": "12",
			"valueFontBold": "1",
            "theme": "fusion"//gammel
            },
        // Chart Data
        "data": chartData
        }
    };
    FusionCharts.ready(function(){
    var fusioncharts = new FusionCharts(chartConfig);
    fusioncharts.render();
    });

</script>
@endsection
