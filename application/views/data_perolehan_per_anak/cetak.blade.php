@php
$ci = get_instance();
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak</title>
    <style>
    table {
        border-collapse: collapse;
        font-family: sans-serif;
        font-size: .8rem;
    }

    table,
    th,
    td {
        border: 1px solid black;
    }

    th,
    td {
        padding: 10px;
        font-size: 12px;
        font-family: 'Times New Roman', Times, serif;
    }
    </style>
</head>

<body>

    <div style="overflow-x:auto;">
        <table class="table table-bordered table-hover" cellspacing="0" width="100%">
            <thead>
                <tr style="background-color: yellow; text-align:center; ">
                    <td colspan="3" style="font-size: 20px; font-weight:bold;">
                        DATA PEROLEHAN SURVEI
                    </td>
                </tr>

                <tr>
                    <th>No</th>
                    <th>Organisasi</th>
                    <th>Perolehan</th>
                </tr>
                
            </thead>
            <tbody>
                @php
                $no = 1;
                @endphp
                @foreach($ci->db->get_where("users", ['id_parent_induk' => $ci->session->userdata('user_id')])->result() as $row)

                @php
                $array_lengkap = [];
                $lengkap= 0;
                foreach($ci->db->get_where("manage_survey", ['id_user' => $row->id])->result() as $get){
                    $array_lengkap[] = $ci->db->get_where("survey_$get->table_identity", ['is_submit' => 1])->num_rows();
                    $lengkap = array_sum($array_lengkap);
                }
                @endphp
                <tr>
                    <td align="center">{{$no++}}</td>
                    <td>{{$row->company}}</td>
                    <td align="center">{{$lengkap}}</td>
                </tr>
                @endforeach
                
            </tbody>
        </table>
    </div>
</body>

</html>