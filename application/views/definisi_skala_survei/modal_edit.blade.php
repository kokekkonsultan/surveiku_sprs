@php
$ci = get_instance();
@endphp


<form action="{{base_url() . $ci->session->userdata('username') . '/' . $ci->uri->segment(2) . '/definisi-skala/edit'}}" method="POST" class="form_default">

    <input name="id" value="{{$definisi_skala->id}}" hidden>

    <div class="form-group row">
        <div class="col-md-6">
            <label class="font-weight-bold">Batas Atas <span style="color: red;">*</span></label>
            <input class="form-control" name="batas_atas" value="{{$definisi_skala->range_atas}}" disabled>
        </div>
        <div class="col-md-6">
            <label class="font-weight-bold">Batas Bawah <span style="color: red;">*</span></label>
            <input class="form-control" name="batas_bawah" value="{{$definisi_skala->range_bawah}}" disabled>
        </div>
    </div>
    <hr>
    <hr>
    <br>

    <div class=" form-group row">
        <label class="col-sm-3 col-form-label font-weight-bold">Mutu<span style="color: red;">*</span></label>
        <div class="col-sm-9">
            <input class="form-control" name="mutu" value="{{$definisi_skala->mutu}}">
        </div>
    </div>
    <div class=" form-group row">
        <label class="col-sm-3 col-form-label font-weight-bold">Kategori<span style="color: red;">*</span></label>
        <div class="col-sm-9">
            <input class="form-control" name="kategori" value="{{$definisi_skala->kategori}}">
        </div>
    </div>

    <div class=" text-right">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-sm tombolSimpanDefault">Simpan</button>
    </div>
</form>



<script>
    $('.form_default').submit(function(e) {
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            beforeSend: function() {
                $('.tombolSimpanDefault').attr('disabled', 'disabled');
                $('.tombolSimpanDefault').html('<i class="fa fa-spin fa-spinner"></i> Sedang diproses');
                KTApp.block('#content_1', {
                    overlayColor: '#000000',
                    state: 'primary',
                    message: 'Processing...'
                });
                setTimeout(function() {
                    KTApp.unblock('#content_1');
                }, 1000);
            },
            complete: function() {
                $('.tombolSimpanDefault').removeAttr('disabled');
                $('.tombolSimpanDefault').html('Simpan');
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
                    table.ajax.reload();
                }
            }
        })
        return false;
    });
</script>