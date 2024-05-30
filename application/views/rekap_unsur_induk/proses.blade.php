@php
$ci = get_instance();
@endphp

<br>
<br>


@if($manage_survey->num_rows() > 0)
<table class="table table-hover example">
    <thead class="">
        <tr>
            <th width="5%">No</th>
            <th>Nama Survei</th>
            <th>Perolehan Responden</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach ($manage_survey->result() as $key => $value)

        @php
        $lengkap[$key] = 0;
        $tidaklengkap[$key] = 0;
        foreach($ci->db->query("SELECT * FROM survey_$value->table_identity")->result() as $row){
        if ($row->is_submit == 1) {
        $lengkap[$key] = $lengkap[$key] + 1;
        } else {
        $tidaklengkap[$key] = $tidaklengkap[$key] + 1;
        }
        }
        @endphp

        <tr>
            <td>{{$no++}}</td>
            <td>{{$value->survey_name}}</td>
            <td><span class="badge badge-success">{{$lengkap[$key]}}</span></td>
            <td>
                <a class="btn btn-info btn-sm font-weight-bold" href="{{base_url() . 'rekap-profil-induk/' . $value->table_identity}}" target="_blank"><i class="fa fa-info-circle"></i> Grafik Profil Responden</a>

                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn
                        btn-primary btn-sm font-weight-bold dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-info-circle"></i> Grafik Unsur
                    </button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">

                        <a class="dropdown-item text-secondary font-weight-bold"
                            href="{{base_url() . 'rekap-unsur-induk/detail/' . $value->id}}"
                            target="_blank">Semua Wilayah Survei</a>

                        <a class="dropdown-item text-secondary font-weight-bold" data-toggle="modal"
                            data-target="#proses{{$value->id}}">Per Wilayah Survei</a>
                    </div>
                </div>



                <div class="modal fade" id="proses{{$value->id}}" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <!-- <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div> -->
                            <div class="modal-body">

                                <form action="{{base_url() . 'rekap-unsur-induk/detail/' . $value->id}}" method="GET">

                                    <div class="form group">
                                        <label class="form-label font-weight-bold">Pilih Wilayah <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control" name="id_wilayah">
                                            <option value="">Please Select</option>

                                            @foreach($ci->db->get("wilayah_survei_$value->table_identity")->result() as
                                            $row)
                                            <option value="{{$row->id}}">{{$row->nama_wilayah}}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="text-right mt-5">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary btn-sm" target="_blank">Save changes</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else

<div class="text-center">
    <i>Belum ada survei yang dibuat</i>
</div>

@endif



<script>
$(document).ready(function() {
    $('.example').DataTable();
});
</script>