@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

<style>
    a.text-secondary:hover, a.text-secondary:focus {
        color: #187DE4 !important;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">

<div class="card card-custom bgi-no-repeat gutter-b aos-init aos-animate"
        style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/rhone-2.svg)"
        data-aos="fade-down">
        <div class="card-body d-flex align-items-center">
            <div>
                <h3 class="text-white font-weight-bolder line-height-lg mb-5">
				{{ strtoupper($title) }}
                </h3>
            </div>
        </div>
    </div>


	<div class="card">
		<div class="card-body">
			@php
			$user_anak = $ci->db->get_where('users', ['id_parent_induk' => $ci->session->userdata('user_id')]);
			@endphp
			<label for="" class="font-weight-bold">Pilih Provinsi</label>
			<select name="akun_anak" id="akun_anak" class="form-control kt_select2_1">
				<option value="">Please Select</option>
				@foreach ($user_anak->result() as $value)
				<option value="{{ $value->id }}">{{ $value->first_name }} {{ $value->last_name }}</option>
				@endforeach
			</select>

			<div id="progress-table"></div>

		</div>
	</div>
</div>

@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
	$('.kt_select2_1').select2({
		placeholder: "Pilih Instansi"
	});

	$(document).ready(function() {

		loadData();

		$("#akun_anak").change(function() {
			loadData()
		});
	});

	function loadData() {
		var akun_anak = $("#akun_anak").val();
		// console.log(akun_anak);
		$.ajax({
			url: "{{ base_url() . 'rekap-unsur-induk/proses' }}",
			data: "akun_anak=" + akun_anak,
			success: function(html) {
				$("#progress-table").html(html);
			}
		});
	}
</script>
@endsection