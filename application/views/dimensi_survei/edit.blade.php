<!-- ===================================================== MODAL EDIT ==================================================================== -->
@foreach($ci->db->get("dimensi_$table_identity")->result() as $row)
<div class="modal fade" id="edit_{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="exampleModalLabel">Edit Dimensi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form
                action="{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/dimensi-survei/edit'}}"
                class="form_default" method="POST">
                <div class="modal-body">


                    <input name="id" value="{{$row->id}}" hidden>
                    

                    <div class="form-group">
                        <label class="font-weight-bold">Dimensi
                            <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span
                                    class="input-group-text font-weight-bold">{{$row->kode}}</span>
                            </div>
                            <input type="text" class="form-control" name="dimensi" value="{{$row->dimensi}}" required
                                autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="5">{{$row->keterangan}}</textarea>
                    </div>


                </div>
                <div class="modal-footer">
                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                        <button type="submit"
                            class="btn btn-primary btn-sm font-weight-bold tombolSimpan">Simpan</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endforeach