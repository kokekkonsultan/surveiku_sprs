@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container">

	<div class="card mt-5">
		<div class="card-header">
			Kuota
		</div>
		<div class="card-body">
			<h5>Menampilkan paket yang saat ini dibeli</h5>
			<br>
			<h4>{{ $data_langganan->nama_paket }} - {{ $data_langganan->jumlah_responden }} Responden</h4>
			<div class="table-responsive">
				<table id="table" class="table table-bordered">
					<thead>
						<tr>
							<th>No</th>
							<th>Nama Survei</th>
							<th>Organisasi</th>
							<th>Total Responden</th>
						</tr>
					</thead>
					<tbody>
						@php
						$responden = 0;
						$total_responden = 0;
						$no = 1;
						@endphp
						@foreach($data_survey->result() as $value)
						@php
						$ci->db->select('COUNT(id) AS id');
						$ci->db->from('survey_' . $value->table_identity);
						// $ci->db->where("is_submit = 1");
						$responden = $ci->db->get()->row()->id;

						$total_responden += $responden;
						@endphp
						<tr>
							<td>{{ $no++ }}.</td>
							<td>{{ $value->survey_name }}</td>
							<td>{{ $value->organisasi }}</td>
							<td>{{ $responden }}</td>
						</tr>
						@endforeach
					</tbody>
					<tfoot>
						<tr>
							<td colspan="3"><b>Total</b></td>
							<td>{{ $total_responden }}</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		@php
		$sisa_responden = $data_langganan->jumlah_responden-$total_responden;
		@endphp

		<div class="card-footer">
			<h4>Sisa kuota : <!--1000 - 230 = --><span class="badge badge-primary">{{ $sisa_responden }}</span> Responden</h4>
		</div>
	</div>

</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    $(document).ready(function() {
        table = $('#table').DataTable();
    });
</script>
@endsection
