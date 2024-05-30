@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">

    <div class="card card-custom bgi-no-repeat gutter-b" style="height: 150px; background-color: #1c2840; background-position: calc(100% + 0.5rem) 100%; background-size: 100% auto; background-image: url(/assets/img/banner/taieri.svg)" data-aos="fade-down">
        <div class="card-body d-flex align-items-center">
            <div>
                <h3 class="text-white font-weight-bolder line-height-lg mb-5">
                    {{strtoupper($title)}}
                    <br>
                    {{$users->company}}
                </h3>
            </div>
        </div>
    </div>


    <div class="card card-custom card-sticky" data-aos="fade-down">

        <div class="card-body">

            <div class="table-responsive">
                <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%" style="font-size: 12px;">
                    <thead class="bg-secondary">
                        <tr>
                            <th width="5%">No.</th>
                            <th>Bagian</th>
                            <th>Status</th>
                            <th>Form</th>
                            <th>Jenis Pelayanan</th>
                            <th>Nama Responden</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>



@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    $(document).ready(function() {
        table = $('#table').DataTable({

            "processing": true,
            "serverSide": true,
            "order": [],
            "language": {
                "processing": '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span> ',
            },
            "ajax": {
                "url": "<?php echo base_url() . 'data-perolehan-per-anak/' . $ci->uri->segment(2) . '/ajax-list' ?>",
                "type": "POST",
                "data": function(data) {
                }
            },

            "columnDefs": [{
                "targets": [-1],
                "orderable": false,
            }, ],

        });
    });

</script>
@endsection