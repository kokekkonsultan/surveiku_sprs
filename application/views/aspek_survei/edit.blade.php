
<!-- ===================================================== MODAL EDIT ==================================================================== -->
@foreach($ci->db->get("aspek_$table_identity")->result() as $row)
<div class="modal fade" id="edit_{{$row->id}}" name="edit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="exampleModalLabel">Edit Aspek</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form
                    action="{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/aspek-survei/edit'}}"
                    class="form_default" method="POST">

                    <input name="id" value="{{$row->id}}" hidden>

                    <div class="form-group">
                        <label class="font-weight-bold">Aspek
                            <span class="text-danger">*</span></label>
                            <div class="input-group">
                            <div class="input-group-prepend"><span
                                    class="input-group-text font-weight-bold">{{$row->kode}}</span>
                            </div>
                            <input type="text" class="form-control" name="nama_aspek" value="{{$row->nama_aspek}}" required
                                autofocus>
                            </div>

                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="5">{{$row->keterangan}}</textarea>
                    </div>

                    <div class="text-right mt-5">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold tombolSimpan">Simpan</button>
                    </div>

                </form>


            </div>
        </div>
    </div>
</div>
@endforeach