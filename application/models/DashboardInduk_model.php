<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DashboardInduk_model extends CI_Model
{

	var $table 				= '';
	var $column_order 		= array(null, null);
	var $column_search 		= array('manage_survey.survey_name');
	var $order 				= array('manage_survey.id' => 'desc');

	public function __construct()
	{
		parent::__construct();
	}

	private function _get_datatables_query($parent)
	{
		$this->db->select('*, manage_survey.slug AS slug_manage_survey');
		$this->db->from('manage_survey');
		// $this->db->where_in('id_user', $parent);
		$this->db->where("id_user IN ($parent)");
		$this->db->order_by('manage_survey.id', 'desc');


		$i = 0;

		foreach ($this->column_search as $item) {
			if ($_POST['search']['value']) {

				if ($i === 0) {
					$this->db->group_start();
					$this->db->like($item, $_POST['search']['value']);
				} else {
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if (count($this->column_search) - 1 == $i)
					$this->db->group_end();
			}
			$i++;
		}

		if (isset($_POST['order'])) {
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} else if (isset($this->order)) {
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables($parent)
	{
		$this->_get_datatables_query($parent);
		if ($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered($parent)
	{
		$this->_get_datatables_query($parent);
		$query = $this->db->get();
		return $query->num_rows();
	}

	public function count_all($parent)
	{
		$this->db->from('manage_survey');
		$this->db->where_in('id', $parent);
		$this->db->order_by('manage_survey.id', 'desc');
		return $this->db->count_all_results();
	}

	public function dropdown_sampling()
	{
		$this->db->where('is_digunakan', 1);
		$query = $this->db->get('sampling');

		if ($query->num_rows() > 0) {

			$dd[''] = 'Please Select';
			foreach ($query->result_array() as $row) {
				$dd[$row['id']] = $row['nama_sampling'];
			}

			return $dd;
		}
	}

	public function getAll($id_klasifikasi_survei)
	{

		$query = $this->db->query("SELECT *
		FROM jenis_pelayanan
		WHERE id_klasifikasi_survei = $id_klasifikasi_survei || id_klasifikasi_survei = 17");
		return $query->result();
	}
}


/* End of file ManageSurvey_model.php */
/* Location: ./application/models/ManageSurvey_model.php */