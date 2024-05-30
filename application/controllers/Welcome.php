<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{



		foreach ($this->db->get("manage_survey")->result() as $key => $row) {


			$no = 1;
			foreach ($this->db->query("SELECT *
			FROM (
			SELECT id_pertanyaan_terbuka, pertanyaan_ganda, 
			(SELECT COUNT(*) FROM survey_$row->table_identity JOIN lap_jawaban_pertanyaan_terbuka_$row->table_identity ON survey_$row->table_identity.id_responden = lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_responden WHERE survey_$row->table_identity.is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$row->table_identity.jawaban = isi_pertanyaan_ganda_$row->table_identity.pertanyaan_ganda) AS perolehan,
			
			(SELECT COUNT(*) FROM survey_$row->table_identity JOIN responden_$row->table_identity ON survey_$row->table_identity.id_responden = responden_$row->table_identity.id JOIN lap_jawaban_pertanyaan_terbuka_$row->table_identity ON responden_$row->table_identity.id = lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_responden WHERE is_submit = 1 && lap_jawaban_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$row->table_identity.id_pertanyaan_terbuka && lap_jawaban_pertanyaan_terbuka_$row->table_identity.jawaban != '' ) AS jumlah_survei
			
			FROM isi_pertanyaan_ganda_$row->table_identity
			JOIN perincian_pertanyaan_terbuka_$row->table_identity ON isi_pertanyaan_ganda_$row->table_identity.id_perincian_pertanyaan_terbuka = perincian_pertanyaan_terbuka_$row->table_identity.id
			WHERE id_pertanyaan_terbuka = 3 && perincian_pertanyaan_terbuka_$row->table_identity.id_jenis_pilihan_jawaban = 1
			) ppt_$row->table_identity
			ORDER BY perolehan ASC")->result() as $val) {

				$data_array[$key][] = '<tr>
									<td>' . $no++ . '</td>
									<td>' . $val->pertanyaan_ganda . '</td>
									<td>' . $val->perolehan . '</td>
									<td>' . ROUND(($val->perolehan / $val->jumlah_survei) * 100, 2). '%</td>
								</tr>';
			}



			$html[] = '<table>
						<tr>
							<td colspan="5">' . $row->organisasi . '</td>
						</tr>
						<tr>
							<td>No</td>
							<td>Kategori</td>
							<td>Jumlah</td>
							<td>Persentase</td>
						</tr>
						' . implode("", $data_array[$key]) . '
					</table>
					<br>';
		}

		echo implode("", $html);
	}
}
