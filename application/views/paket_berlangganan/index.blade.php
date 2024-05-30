@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container">

	<div class="card">
		<div class="card-header">
			Paket saat ini yang anda gunakan
		</div>
		<div class="card-body">

			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Nama Paket</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{{ $last_payment->nama_paket }}">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Deskripsi</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{!! strip_tags($last_payment->deskripsi_paket) !!}">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Lama Berlangganan</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{{ $last_payment->panjang_hari }} Hari">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Harga Paket</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{{ number_format($last_payment->harga_paket, 2, ',', '.') }}">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Tanggal Pembelian</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{{ date('d-m-Y', strtotime($last_payment->tanggal_mulai)) }}">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Jumlah Akun Anak</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{{ $last_payment->jumlah_user }}">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Jumlah Responden</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{{ $last_payment->jumlah_responden }}">
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Status Paket</label>
				<div class="col-sm-10">
					<h5><span class="badge badge-success">{!! $status_paket !!}</span></h5>
				</div>
			  </div>
			<div class="form-group row">
				<label for="" class="col-sm-2 col-form-label">Tanggal Jatuh Tempo</label>
				<div class="col-sm-10">
				  <input type="text" readonly class="form-control-plaintext" id="" value="{!! $status_jatuh_tempo !!}">
				</div>
			  </div>
			
		</div>
	</div>
	
	<div class="card mt-5">
		<div class="card-header">
			Riwayat berlangganan
		</div>
		<div class="card-body">
			
			<div class="table-responsive">
				@php
				echo $table;
				@endphp
			</div>
			<!-- <table class="table table-bordered table-hover">
				<tr>
					<th>No</th>
					<th>Nama Paket</th>
					<th>Panjang Hari</th>
					<th>Harga Paket (Rp.)</th>
					<th>Tanggal Aktif</th>
					<th>Tanggal Kedaluarsa</th>
				</tr>
				<tr>
					<td>1.</td>
					<td>Paket A</td>
					<td>365</td>
					<td>5.000.000,00</td>
					<td>19-06-2023</td>
					<td>18-06-2024</td>
				</tr>
			</table> -->
		</div>
	</div>

</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
$(document).ready(function() {
    table = $('#table').DataTable({

        "columnDefs": [{
            "targets": [-1],
            "orderable": false,
        }, ],

    });
});

$('#btn-filter').click(function() {
    table.ajax.reload();
});
$('#btn-reset').click(function() {
    $('#form-filter')[0].reset();
    table.ajax.reload();
});

/*function reload_table() {
    table.ajax.reload(null, false);
}*/
</script>
@endsection
