@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class=" container-fluid">

    <div class="card card-custom bgi-no-repeat gutter-b aos-init aos-animate" style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/rhone-2.svg)" data-aos="fade-down">
        <div class="card-body d-flex align-items-center">
            <div>
                <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                LAPORAN SURVEI
                </h3>
            </div>
        </div>
    </div>



    <div class="card shadow aos-init aos-animate" data-aos="fade-up">
        <div class="card-body">

			{{-- <pre>
				<?php 
				print_r($_SESSION);
				?>
			</pre> --}}

			@if ($_SESSION['username'] == "bssn-induk")
			
			@php
				// $arr_data = [];
				// $arr_data = [
				// 	[
				// 		'id' => 1,
				// 		'prov_name' => 'BSSN Aceh'
				// 		'docx_name' => "1.Laporan_BSSN_Aceh.docx",
				// 		'pdf_name' => "1.Laporan_BSSN_Aceh.pdf"
				// 	]
					
				// 	];
			@endphp
			<table class="table table-striped table-hover mt-5" cellspacing="0" width="100%">
				<thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kelompok</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
					<tr>
						<td>1</td>
						<td>BSSN Aceh</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/1.Laporan_BSSN_Aceh.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>2</td>
						<td>BSSN Sumatera Utara</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/2.Laporan_BSSN_Sumatera_Utara.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>3</td>
						<td>BSSN Sumatera Barat</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/3.Laporan_BSSN_Sumatera_Barat.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>4</td>
						<td>BSSN Riau</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/4.Laporan_BSSN_Riau.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>5</td>
						<td>BSSN Jambi</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/5.Laporan_BSSN_Jambi.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>6</td>
						<td>BSSN Sumatera Selatan</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/6.Laporan_BSSN_Sumatera_Selatan.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>7</td>
						<td>BSSN Bengkulu</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/7.Laporan_BSSN_Bengkulu.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>8</td>
						<td>BSSN Lampung</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/8.Laporan_BSSN_Lampung.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>9</td>
						<td>BSSN Kepulauan Bangka Belitung</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/9.Laporan_BSSN_Kepulauan_Bangka_Belitung.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>10</td>
						<td>BSSN Kepulauan Riau</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/10.Laporan_BSSN_Kepulauan_Riau.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>11</td>
						<td>BSSN Dki Jakarta</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/11.Laporan_BSSN_Dki_Jakarta.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>12</td>
						<td>BSSN Jawa Barat</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/12.Laporan_BSSN_Jawa_Barat.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>13</td>
						<td>BSSN Jawa Tengah</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/13.Laporan_BSSN_Jawa_Tengah.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>14</td>
						<td>BSSN Di Yogyakarta</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/14.Laporan_BSSN_Di_Yogyakarta.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>15</td>
						<td>BSSN Jawa Timur</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}laporan-induk" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/15.Laporan_BSSN_Jawa_Timur.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>16</td>
						<td>BSSN Banten</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/16.Laporan_BSSN_Banten.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>17</td>
						<td>BSSN Bali</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/17.Laporan_BSSN_Bali.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>18</td>
						<td>BSSN Nusa Tenggara Barat</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/18.Laporan_BSSN_Nusa_Tenggara_Barat.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>19</td>
						<td>BSSN Nusa Tenggara Timur</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/19.Laporan_BSSN_Nusa_Tenggara_Timur.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>20</td>
						<td>BSSN Kalimantan Barat</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/20.Laporan_BSSN_Kalimantan_Barat.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>21</td>
						<td>BSSN Kalimantan Tengah</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/21.Laporan_BSSN_Kalimantan_Tengah.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>22</td>
						<td>BSSN Kalimantan Selatan</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/22.Laporan_BSSN_Kalimantan_Selatan.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>23</td>
						<td>BSSN Kalimantan Timur</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/23.Laporan_BSSN_Kalimantan_Timur.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>24</td>
						<td>BSSN Kalimantan Utara</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/24.Laporan_BSSN_Kalimantan_Utara.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>25</td>
						<td>BSSN Sulawesi Utara</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/25.Laporan_BSSN_Sulawesi_Utara.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>26</td>
						<td>BSSN Sulawesi Tengah</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/26.Laporan_BSSN_Sulawesi_Tengah.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>27</td>
						<td>BSSN Sulawesi Selatan</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/27.Laporan_BSSN_Sulawesi_Selatan.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>28</td>
						<td>BSSN Sulawesi Tenggara</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/28.Laporan_BSSN_Sulawesi_Tenggara.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>29</td>
						<td>BSSN Gorontalo</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/29.Laporan_BSSN_Gorontalo.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>30</td>
						<td>BSSN Sulawesi Barat</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/30.Laporan_BSSN_Sulawesi_Barat.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>31</td>
						<td>BSSN Maluku</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/31.Laporan_BSSN_Maluku.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>32</td>
						<td>BSSN Maluku Utara</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/32.Laporan_BSSN_Maluku_Utara.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>33</td>
						<td>BSSN Papua Barat</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/33.Laporan_BSSN_Papua_Barat.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
					<tr>
						<td>34</td>
						<td>BSSN Papua</td>
						<td>
							{{-- <a class="btn btn-danger" href="{{ base_url() }}assets/files/laporan/bssn/" target="_blank"><i class="fa fa-file-pdf"></i></a> --}}
							<a class="btn btn-primary" href="{{ base_url() }}assets/files/laporan/bssn/34.Laporan_BSSN_Papua.docx" target="_blank"><i class="fa fa-file-word"></i></a>
						</td>
					</tr>
                </tbody>
			</table>

			@else
				
            <table id="table" class="table table-striped table-hover mt-5" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Kelompok</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
			
			@endif
			
        </div>
    </div>
</div>


@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    var table;
    $(document).ready(function() {
        table = $("#table").DataTable({
            "processing": true,
            "serverSide": true,
            "lengthMenu": [
                [5, 10, -1],
                [5, 10, "Semua data"]
            ],
            "pageLength": 5,
            "ordering": true,
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "<?php echo base_url() . 'laporan-induk/ajax-list' ?>",
                "type": "POST",
                "dataType": "json",
                "dataSrc": function(jsonData) {
                    return jsonData.data;
                },
                "data": function(data) {},

            },
            "columnDefs": [{
                "targets": [0],
                "orderable": false,
            }, ],

        });
    });
</script>
@endsection
