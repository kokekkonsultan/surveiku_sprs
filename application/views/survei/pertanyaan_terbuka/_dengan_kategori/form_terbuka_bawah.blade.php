<!-- CEK ATRIBUTE -->
@if(in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey)) &&
$pertanyaan_terbuka_bawah->num_rows() > 0)

@foreach($ci->db->query("SELECT *
FROM (SELECT *,
(SELECT DISTINCT is_letak_pertanyaan FROM pertanyaan_terbuka_$table_identity WHERE id_kategori_pertanyaan_terbuka = kategori_pertanyaan_terbuka_$table_identity.id) AS is_letak_pertanyaan,

(SELECT COUNT(id) FROM pertanyaan_terbuka_$table_identity WHERE id_kategori_pertanyaan_terbuka = kategori_pertanyaan_terbuka_$table_identity.id) AS jumlah

FROM kategori_pertanyaan_terbuka_$table_identity) kpt_$table_identity
WHERE is_letak_pertanyaan = 2 && jumlah > 0")->result() as $kpt_bawah)

<h6 class="text-primary"><b>{{$kpt_bawah->nama_kategori}}</b></h6>




<!-- Looping Pertanyaan Terbuka Paling Bawah -->
@php
$b = $pertanyaan_terbuka_atas->num_rows() + $pertanyaan_terbuka->num_rows() + 1;
@endphp
@foreach ($pertanyaan_terbuka_bawah->result() as $row_terbuka_bawah)
@if($kpt_bawah->id == $row_terbuka_bawah->id_kategori_pertanyaan_terbuka)


@php
$is_required_tb = $row_terbuka_bawah->is_required != '' ? '' : '<b class="text-danger">*</b>';
$is_required_tb_i = $row_terbuka_bawah->is_required != '' ? '' : 'required';
$model_tb = $row_terbuka_bawah->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
@endphp

<div class="mt-10 mb-10">
    <input type="hidden" name="id_pertanyaan_terbuka[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}]" value="{{$row_terbuka_bawah->id_pertanyaan_terbuka}}">

    <table class="table table-borderless" width="100%" border="0">
        <tr>
            <td width="5%" valign="top">{!! $row_terbuka_bawah->nomor_pertanyaan_terbuka . '' .
                $is_required_tb !!}.
            </td>
            <td>{!! $row_terbuka_bawah->isi_pertanyaan_terbuka !!}</td>
        </tr>

        @if ($row_terbuka_bawah->id_jenis_pilihan_jawaban == 1)

        <tr>
            <td width="5%"></td>
            <td style="font-weight:bold;" width="95%">
                @foreach ($ci->db->get_where("isi_pertanyaan_ganda_$table_identity",
                ['id_perincian_pertanyaan_terbuka' =>
                $row_terbuka_bawah->id_perincian_pertanyaan_terbuka])->result() as
                $value_terbuka)

                <div class="{{$model_tb}}-inline mb-2">
                    <label class="{{$model_tb}} {{$model_tb}}-outline {{$model_tb}}-success {{$model_tb}}-lg" style="font-size: 16px;">
                        <input type="{{$model_tb}}" name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}][]" value="{{ $value_terbuka->pertanyaan_ganda }}" class="terbuka_{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}" {{ $is_required_tb_i }} <?= in_array($value_terbuka->pertanyaan_ganda, unserialize($row_terbuka_bawah->jawaban)) ? 'checked' : ''; ?>>
                        <span></span> {{ $value_terbuka->pertanyaan_ganda }}
                    </label>
                </div>
                @endforeach


                @if ($row_terbuka_bawah->dengan_isian_lainnya == 1 &&
                $row_terbuka_bawah->is_model_pilihan_ganda == 1)
                <input class="form-control" name="jawaban_lainnya[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}]" value="{{$row_terbuka_bawah->jawaban_lainnya}}" pattern="^[a-zA-Z0-9.,\s]*$|^\w$" placeholder="Masukkan jawaban lainnya ..." id="terbuka_lainnya_{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}" <?= in_array('Lainnya', unserialize($row_terbuka_bawah->jawaban)) ? 'required' : 'style="display:none"'; ?>>

                <small id="text_terbuka_{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}" class="text-danger" <?= in_array('Lainnya', unserialize($row_terbuka_bawah->jawaban)) ? '' : 'style="display:none"'; ?>>**Pengisian
                    form hanya dapat menggunakan tanda baca
                    (.) titik dan (,) koma</small>
                <br>
                @endif

            </td>
        </tr>
        @else

        <tr>
            <td width="5%"></td>
            <td style="font-weight:bold;" width="95%">

                <textarea class="form-control" type="text" name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_bawah->id_pertanyaan_terbuka }}][]" placeholder="Masukkan Jawaban Anda ...">{{ $row_terbuka_bawah->jawaban != '' ?  implode("", unserialize($row_terbuka_bawah->jawaban)) : ''; }}</textarea>
            </td>
        </tr>


        @endif
    </table>

</div>
@endif


@php
$b++;
@endphp
@endforeach


@endforeach

@endif