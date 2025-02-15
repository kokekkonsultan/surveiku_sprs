<?php
defined('BASEPATH') or exit('No direct script access allowed');

class OlahData_model extends CI_Model
{

    var $table          = '';
    var $column_order   = array(null, null, null, null, null, null, null, null, null);
    var $column_search  = array('nama_lengkap');
    var $order          = array('id_responden' => 'asc');

    public function __construct()
    {
        parent::__construct();
    }

    private function _get_datatables_query($table_identity)
    {
        $this->db->select("*, rspdn_$table_identity.uuid AS uuid_responden");
        $this->db->from("(SELECT *, (SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity WHERE jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id) AS total_skor FROM responden_$table_identity) AS rspdn_$table_identity");
        $this->db->join("survey_$table_identity", "rspdn_$table_identity.id = survey_$table_identity.id_responden");
        $this->db->join("surveyor", "survey_$table_identity.id_surveyor = surveyor.id", "left");
        $this->db->join("users", "surveyor.id_user  = users.id", "left");
        $this->db->where('is_submit', 1);
        // $this->db->order_by("rspdn_$table_identity.total_skor", 'desc');

        if ($this->input->post('is_total_skor_asli')) {
            $this->db->order_by("rspdn_$table_identity.total_skor", $this->input->post('is_total_skor_asli'));
        }

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

    function get_datatables($table_identity)
    {
        $this->_get_datatables_query($table_identity);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered($table_identity)
    {
        $this->_get_datatables_query($table_identity);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($table_identity)
    {
        $this->db->from("(SELECT *, (SELECT SUM(skor_jawaban) FROM jawaban_pertanyaan_unsur_$table_identity WHERE jawaban_pertanyaan_unsur_$table_identity.id_responden = responden_$table_identity.id) AS total_skor FROM responden_$table_identity) AS rspdn_$table_identity");
        $this->db->join("survey_$table_identity", "rspdn_$table_identity.id = survey_$table_identity.id_responden");
        $this->db->join("surveyor", "survey_$table_identity.id_surveyor = surveyor.id", "left");
        $this->db->join("users", "surveyor.id_user  = users.id", "left");
        $this->db->where('is_submit', 1);

        return $this->db->count_all_results();
    }
}