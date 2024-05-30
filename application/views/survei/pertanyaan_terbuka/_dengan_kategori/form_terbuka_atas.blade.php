<!-- CEK ATRIBUTE -->
@if(in_array(2, unserialize($manage_survey->atribut_pertanyaan_survey)) &&
$pertanyaan_terbuka_atas->num_rows() > 0)


@foreach($ci->db->query("SELECT *
FROM (SELECT *,
(SELECT DISTINCT is_letak_pertanyaan FROM pertanyaan_terbuka_$table_identity WHERE id_kategori_pertanyaan_terbuka = kategori_pertanyaan_terbuka_$table_identity.id) AS is_letak_pertanyaan,

(SELECT COUNT(id) FROM pertanyaan_terbuka_$table_identity WHERE id_kategori_pertanyaan_terbuka = kategori_pertanyaan_terbuka_$table_identity.id) AS jumlah

FROM kategori_pertanyaan_terbuka_$table_identity) kpt_$table_identity
WHERE is_letak_pertanyaan = 1 && jumlah > 0")->result() as $kpt_atas)

<h6 class="text-primary"><b>{{$kpt_atas->nama_kategori}}</b></h6>

<!-- Looping Pertanyaan Terbuka Paling Atas -->
@php
$a = 1;
@endphp
@foreach ($pertanyaan_terbuka_atas->result() as $row_terbuka_atas)
@if($kpt_atas->id == $row_terbuka_atas->id_kategori_pertanyaan_terbuka)


@php
$is_required_ta = $row_terbuka_atas->is_required != '' ? '' : '<b class="text-danger">*</b>';
$is_required_ta_i = $row_terbuka_atas->is_required != '' ? '' : 'required';
$model_ta = $row_terbuka_atas->is_model_pilihan_ganda == 2 ? 'checkbox' : 'radio';
@endphp
<div class="mt-10 mb-10">
    <input type="hidden" name="id_pertanyaan_terbuka[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}]" value="{{$row_terbuka_atas->id_pertanyaan_terbuka}}">

    <table class="table table-borderless" width="100%" border="0">
        <tr>
            <td width="5%" valign="top">{!! $row_terbuka_atas->nomor_pertanyaan_terbuka . '' .
                $is_required_ta !!}.
            </td>
            <td>{!! $row_terbuka_atas->isi_pertanyaan_terbuka !!}</td>
        </tr>

        @if ($row_terbuka_atas->id_jenis_pilihan_jawaban == 1)
        <tr>
            <td width="5%"></td>
            <td style="font-weight:bold;" width="95%">
                @foreach ($ci->db->get_where("isi_pertanyaan_ganda_$table_identity",
                ['id_perincian_pertanyaan_terbuka' =>
                $row_terbuka_atas->id_perincian_pertanyaan_terbuka])->result() as
                $value_terbuka)

                <div class="{{$model_ta}}-inline mb-2">
                    <label class="{{$model_ta}} {{$model_ta}}-outline {{$model_ta}}-success {{$model_ta}}-lg" style="font-size: 16px;">
                        <input type="{{$model_ta}}" name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}][]" value="{{ $value_terbuka->pertanyaan_ganda }}" class="terbuka_{{ $row_terbuka_atas->id_pertanyaan_terbuka }}" <?= $value_terbuka->pertanyaan_ganda == $row_terbuka_atas->jawaban ? 'checked' : '' ?> {{ $is_required_ta_i }} <?= in_array($value_terbuka->pertanyaan_ganda, unserialize($row_terbuka_atas->jawaban)) ? 'checked' : ''; ?>>
                        <span></span> {{ $value_terbuka->pertanyaan_ganda }}
                    </label>
                </div>
                @endforeach


                @if ($row_terbuka_atas->dengan_isian_lainnya == 1 &&
                $row_terbuka_atas->is_model_pilihan_ganda == 1)
                <input class="form-control" name="jawaban_lainnya[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}]" value="{{$row_terbuka_atas->jawaban_lainnya}}" pattern="^[a-zA-Z0-9.,\s]*$|^\w$" placeholder="Masukkan jawaban lainnya ..." id="terbuka_lainnya_{{ $row_terbuka_atas->id_pertanyaan_terbuka }}" <?= in_array('Lainnya', unserialize($row_terbuka_atas->jawaban)) ? 'required' : 'style="display:none"'; ?>>

                <small id="text_terbuka_{{ $row_terbuka_atas->id_pertanyaan_terbuka }}" class="text-danger" <?= in_array('Lainnya', unserialize($row_terbuka_atas->jawaban)) ? '' : 'style="display:none"'; ?>>**Pengisian
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

                <textarea class="form-control" type="text" name="jawaban_pertanyaan_terbuka[{{ $row_terbuka_atas->id_pertanyaan_terbuka }}][]" placeholder="Masukkan Jawaban Anda ..." <?= $row_terbuka_atas->stts_required ?>>{{ $row_terbuka_atas->jawaban != '' ?  implode("", unserialize($row_terbuka_atas->jawaban)) : ''; }}</textarea>
            </td>
        </tr>


        @endif
    </table>



</div>
@endif

@php
$a++;
@endphp
@endforeach


@endforeach

@endif