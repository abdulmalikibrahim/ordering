<?php

class model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	public function insert($table,$data)
	{
		$this->db->insert($table,$data);
		return true;
	}
	public function insert_batch($table,$data)
	{
		$this->db->insert_batch($table,$data);
		return true;
	}
	public function update($table,$where,$data)
	{
		$this->db->where($where);
		$this->db->update($table,$data);
		return true;
	}
	public function delete($table,$where)
	{
		$this->db->where($where);
		$this->db->delete($table);
		return true;
	}
	public function query_exec($query,$status)
	{
		$query = $this->db->query($query);
		return $query->$status();
	}
	public function gd($table,$select,$where,$status)
	{
		$this->db->where($where);
		$this->db->select($select);
		$this->db->from($table);
		return $this->db->get()->$status();
	}
	public function join_data($table, $table_join, $on_join, $select, $where, $status)
	{
		$this->db->select($select);
		$this->db->where($where);
		$this->db->from($table);
		$this->db->join($table_join, $on_join);
		if($status == 'result'){
			return $this->db->get()->result();
		}else if($status == 'result_array'){
			return $this->db->get()->result_array();
		}else{
			return $this->db->get()->row();
		}
	}
}