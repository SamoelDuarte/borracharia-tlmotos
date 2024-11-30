<?php
class Appconfig extends CI_Model
{

	function exists($key)
	{
		$this->db->from('app_config');
		$this->db->where('app_config.key', $key);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}
	function get_wc_credentials()
	{
		// Defina as chaves que você deseja buscar
		$keys = ['url_wc', 'consumer_key', 'secret_key'];

		// Realize a consulta ao banco de dados para obter essas chaves
		$this->db->select('key, value');
		$this->db->from('app_config');
		$this->db->where_in('key', $keys);
		$query = $this->db->get();

		// Inicialize um array para armazenar os resultados
		$result = [];

		// Preencha o array com as chaves e valores
		foreach ($query->result() as $row) {
			$result[$row->key] = $row->value;
		}

		return $result;
	}

	function get_all()
	{
		$this->db->from('app_config');
		$this->db->order_by("key", "asc");
		return $this->db->get();
	}

	function get($key)
	{
		$query = $this->db->get_where('app_config', array('key' => $key), 1);

		if ($query->num_rows() == 1) {
			return $query->row()->value;
		}

		return "";
	}

	function save($key, $value)
	{
		$config_data = array(
			'key' => $key,
			'value' => $value
		);

		if (!$this->exists($key)) {
			return $this->db->insert('app_config', $config_data);
		}

		$this->db->where('key', $key);
		return $this->db->update('app_config', $config_data);
	}

	function batch_save($data)
	{
		$success = true;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();
		foreach ($data as $key => $value) {
			if (!$this->save($key, $value)) {
				$success = false;
				break;
			}
		}

		$this->db->trans_complete();
		return $success;
	}

	function delete($key)
	{
		return $this->db->delete('app_config', array('key' => $key));
	}

	function delete_all()
	{
		return $this->db->empty_table('app_config');
	}
}
