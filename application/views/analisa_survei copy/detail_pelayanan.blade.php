@extends('include_backend/template_backend')

@php
$ci = get_instance();
@endphp

@section('style')
<link href="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.css" rel="stylesheet"
    type="text/css" />
@endsection

@section('content')

<div class="container-fluid">
    @include("include_backend/partials_no_aside/_inc_menu_repository")

    <div class="row mt-5">
        <div class="col-md-3">
            @include('manage_survey/menu_data_survey')
        </div>
        <div class="col-md-9">

        <a href="{{ base_url() }}{{ $ci->session->userdata('username'); }}/{{ $ci->uri->segment(2) }}/analisa-survei"
        title="Kembali" class="btn btn-secondary font-weight-bold">Kembali</a>

    <br><br>

            <div class="card card-body" data-aos="fade-down">
                <h4 class="text-center text-primary"><b>Nilai SKP {{ $layanan->nama_layanan }} : {{ROUND($ikm, 3)}}
                        <?php foreach ($definisi_skala->result() as $obj) {
                            if ($ikm <= $obj->range_bawah && $ikm >= $obj->range_atas) {
                                echo '(' . $obj->kategori . ')';
                            }
                        }
                        if ($ikm <= 0) {
                            echo  'NULL';
                        }
                        ?>
                    </b>
                </h4>
                <hr>
                <br>

                <h4 class="text-primary"><b>Unsur {{ $layanan->nama_layanan }}</b></h4>
                <div class="table-responsive">
                    <table class="table table-bordered display">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Unsur</th>
                                <th>Indeks</th>
                                <th>Kategori</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no = 1;
                        foreach ($nilai_per_unsur->result() as $value) { ?>
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $value->nomor_unsur }}. {{ $value->nama_unsur_pelayanan }}</td>
                                <td>{{ ROUND($value->nilai_per_unsur,2) }}</td>
                                <td>
                                    <?php
                                    $nilai_konversi = $value->nilai_per_unsur * $skala_likert;
                                    foreach ($definisi_skala->result() as $obj) {
                                        if ($nilai_konversi <= $obj->range_bawah && $nilai_konversi >= $obj->range_atas) {
                                            echo $obj->kategori;
                                        }
                                    }
                                    if ($nilai_konversi <= 0) {
                                        echo  'NULL';
                                    }

                                    // if ($nilai_konversi >= 25 && $nilai_konversi <= 64.99) {
                                    //     echo "Tidak Baik";
                                    // } elseif ($nilai_konversi >= 65 && $nilai_konversi <= 76.60) {
                                    //     echo "Kurang Baik";
                                    // } elseif ($nilai_konversi >= 76.61 && $nilai_konversi <= 88.30) {
                                    //     echo "Baik";
                                    // } elseif ($nilai_konversi >= 88.31 && $nilai_konversi <= 100) {
                                    //     echo "Sangat Baik";
                                    // } else {
                                    //     echo "NULL";
                                    // };
                                    ?>
                                </td>
                                <td>
                                    <a href="{{ base_url() }}{{ $ci->session->userdata('username'); }}/{{ $ci->uri->segment(2) }}/analisa-survei/detail-unsur/{{ $value->id_sub }}/{{ $ci->uri->segment(5) }}"
                                        class="btn btn-light-primary btn-sm font-weight-bold"><i class="fa fa-book"></i>
                                        Lakukan Analisa</a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>


                @if($nilai_per_sub_unsur->num_rows() > 0)
                <br>
                <h4 class="text-primary"><b>Sub Unsur</b></h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th>No</th>
                            <th>Unsur</th>
                            <th>Indeks</th>
                            <th>Kategori</th>
                            <th></th>
                        </tr>

                        <?php
                        $no = 1;
                        foreach ($nilai_per_sub_unsur->result() as $value) { ?>
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ $value->nomor_unsur }}. {{ $value->nama_unsur_pelayanan }}</td>
                            <td>{{ ROUND($value->rata_skor,2) }}</td>
                            <td>
                                <?php
                                $nilai_konversi_sub = $value->rata_skor * $skala_likert;
                                foreach ($definisi_skala->result() as $obj) {
                                    if ($nilai_konversi_sub <= $obj->range_bawah && $nilai_konversi_sub >= $obj->range_atas) {
                                        echo $obj->kategori;
                                    }
                                }
                                if ($ikm <= 0) {
                                    echo  'NULL';
                                }

                                // if ($nilai_konversi_sub >= 25 && $nilai_konversi_sub <= 64.99) {
                                //     echo "Tidak Baik";
                                // } elseif ($nilai_konversi_sub >= 65 && $nilai_konversi_sub <= 76.60) {
                                //     echo "Kurang Baik";
                                // } elseif ($nilai_konversi_sub >= 76.61 && $nilai_konversi_sub <= 88.30) {
                                //     echo "Baik";
                                // } elseif ($nilai_konversi_sub >= 88.31 && $nilai_konversi_sub <= 100) {
                                //     echo "Sangat Baik";
                                // } else {
                                //     echo "NULL";
                                // };
                                ?>
                            </td>
                            <td>
                                <a href="{{ base_url() }}{{ $ci->session->userdata('username'); }}/{{ $ci->uri->segment(2) }}/analisa-survei/detail-unsur/{{ $value->id_unsur_pelayanan }}"
                                    class="btn btn-light-primary btn-sm font-weight-bold"><i class="fa fa-book"></i>
                                    Lakukan Analisa</a>
                            </td>
                        </tr>
                        <?php 
                    } ?>
                    </table>
                </div>
                @endif

                

            </div>



            <!-- <div class="card card-body mt-5" id="content_1" data-aos="fade-down">
                <form
                    action="<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/update-executive-summary' ?>"
                    class="form_executive_summary" method="POST">
                    <div class="form-group">
                        <label class="form-label font-weight-bold">Executive Summary <span
                                style="color: red;">*</span></label>

                        @php
                        echo form_textarea($executive_summary);
                        @endphp
                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-light-primary font-weight-bold tombolSimpan"
                            onclick="tinyMCE.triggerSave();">Update
                            Executive Summary</button>
                    </div>
                </form>
            </div> -->

            <?php
            $ci->db->select("*, analisa_$table_identity.id AS id_analisa");
            $ci->db->from("analisa_$table_identity");
            $ci->db->join("unsur_pelayanan_$table_identity", "unsur_pelayanan_$table_identity.id = analisa_$table_identity.id_unsur_pelayanan");
            $ci->db->where("id_layanan_survei",  $ci->uri->segment(5));
            $ci->db->order_by("id_unsur_pelayanan asc");
            $data_analisa = $ci->db->get();

            if($data_analisa->num_rows() > 0 ) {
            ?>

            <div class="card card-custom card-sticky mt-5" data-aos="fade-down">
                <div class="card-header">
                    <div class="card-title">
                        {{$title}} {{ $layanan->nama_layanan }}
                    </div>
                    <!-- <div class="card-toolbar">
                        @php
                        echo
                        anchor(base_url().$ci->session->userdata('username').'/'.$ci->uri->segment(2).'/analisa-survei/add',
                        '<i class="fas fa-plus"></i> Tambah Analisa Survei', ['class' => 'btn btn-primary btn-sm
                        font-weight-bold shadow-lg']);
                        @endphp
                    </div> -->
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <?php
                        $texthtml = '<table width="100%">';
                        foreach ($data_analisa->result() as $data) {
                            $texthtml .= '<tr>
                                <td colspan="3" style="padding-bottom:10px; "><h6 class="text-primary"><b>'.$data->nomor_unsur.'. '.$data->nama_unsur_pelayanan.'</b></h6> 
                                <a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="'. base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/analisa-survei/edit/' . $data->id_analisa.'" title="Edit ' . $data->nama_unsur_pelayanan . '"><i class="fa fa-edit"></i> Edit</a>
                                <a class="btn btn-light-primary btn-sm font-weight-bold shadow" href="javascript:void(0)" title="Hapus ' . $data->nama_unsur_pelayanan . '" onclick="delete_analisa_survei(' . "'" . $data->id_analisa . "'" . ')"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <tr>
                                <td width="27%" valign="top"><b>Faktor-faktor yang mempengaruhi</b></td>
                                <td width="3%" valign="top">:</td>
                                <td width="70%" valign="top">'.$data->faktor_penyebab.'</td>
                            </tr>
                            <tr>
                                <td valign="top"><b>Rencana tindak lanjut</b></td>
                                <td valign="top">:</td>
                                <td valign="top">'.$data->rencana_perbaikan.'</td>
                            </tr>
                            <tr>
                                <td valign="top"><b>Waktu</b></td>
                                <td valign="top">:</td>
                                <td valign="top">'.$data->waktu.'</td>
                            </tr>
                            <tr>
                                <td valign="top"><b>Penanggung jawab</b></td>
                                <td valign="top">:</td>
                                <td valign="top">'.$data->penanggung_jawab.'</td>
                            </tr>
                            <tr>
                                <td colspan="3">&nbsp;</td>
                            </tr>';
                        }
                        $texthtml .= '</table>';
                        echo $texthtml;
                        ?>
                        {{--
                        <table id="table" class="table table-bordered table-hover" cellspacing="0" width="100%"
                            style="font-size: 12px;">
                            <thead class="bg-secondary">
                                <tr>
                                    <th width="5%">No.</th>
                                    <th>Unsur</th>
                                    <th>Faktor-faktor Yang Mempengaruhi</th>
                                    <th>Rencana Tindak Lanjut</th>
                                    <th>Waktu</th>
                                    <th>Penanggung Jawab</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        --}}
                    </div>
                    <br>
                </div>
            </div>
            <?php } ?>


        </div>
    </div>

</div>
@endsection

@section('javascript')
<script src="{{ TEMPLATE_BACKEND_PATH }}plugins/custom/datatables/datatables.bundle.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.11/tinymce.min.js"></script>

<script>
var KTTinymce = function() {
    var demos = function() {
        tinymce.init({
            selector: '#executive_summary'
        });
    }
    return {
        init: function() {
            demos();
        }
    };
}();

// Initialization
jQuery(document).ready(function() {
    KTTinymce.init();
});
</script>

<!-- <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>

<script>
ClassicEditor
    .create(document.querySelector('#executive_summary'))
    .then(editor => {
        console.log(editor);
    })
    .catch(error => {
        console.error(error);
    });
</script> -->

<script>
$('.form_executive_summary').submit(function(e) {

    $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        cache: false,
        beforeSend: function() {
            $('.tombolSimpan').attr('disabled', 'disabled');
            $('.tombolSimpan').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');
        },
        complete: function() {
            $('.tombolSimpan').removeAttr('disabled');
            $('.tombolSimpan').html('Update Executive Summary');
        },
        error: function(e) {
            Swal.fire(
                'Error !',
                e,
                'error'
            )
        },
        success: function(data) {
            if (data.validasi) {
                $('.pesan').fadeIn();
                $('.pesan').html(data.validasi);
            }
            if (data.sukses) {
                toastr["success"]('Data berhasil disimpan');
            }
        }
    })
    return false;
});
</script>


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
            "url": "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/analisa-survei/ajax-list' . '/' . $ci->uri->segment(5) ?>",
            "type": "POST",
            "data": function(data) {}
        },

        "columnDefs": [{
            "targets": [-1],
            "orderable": false,
        }, ],

    });
});

$(document).ready(function () {
    $('table.display').DataTable();
});

$('#btn-filter').click(function() {
    table.ajax.reload();
});
$('#btn-reset').click(function() {
    $('#form-filter')[0].reset();
    table.ajax.reload();
});

function delete_analisa_survei(id) {
    if (confirm('Are you sure delete this data?')) {
        $.ajax({
            url: "<?php echo base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/analisa-survei/delete/' ?>" +
                id,
            type: "POST",
            dataType: "JSON",
            success: function(data) {
                if (data.status) {

                    table.ajax.reload();

                    Swal.fire(
                        'Informasi',
                        'Berhasil menghapus data',
                        'success'
                    );
                } else {
                    Swal.fire(
                        'Informasi',
                        'Hak akses terbatasi. Bukan akun administrator.',
                        'warning'
                    );
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error deleting data');
            }
        });

    }
}
</script>
@endsection