<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Survei_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function dropdown_layanan_survei($table_identity)
    {
        $query = $this->db->get_where("layanan_survei_$table_identity", array('is_active' => 1));

        if ($query->num_rows() > 0) {

            $dd[''] = 'Please Select';
            foreach ($query->result_array() as $row) {
                $dd[$row['id']] = $row['nama_layanan'];
            }

            return $dd;
        }
    }


    public function dropdown_wilayah($table_identity)
    {
        $query = $this->db->get("wilayah_survei_$table_identity");

        if ($query->num_rows() > 0) {

            $dd[''] = 'Please Select';
            foreach ($query->result_array() as $row) {
                $dd[$row['id']] = $row['nama_wilayah'];
            }

            return $dd;
        }
    }
}

/* End of file JenisPelayanan_model.php */
/* Location: ./application/models/JenisPelayanan_model.php */