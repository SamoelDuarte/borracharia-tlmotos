<?php
class Employee extends Person
{
	/*
	Determines if a given person_id is an employee
	*/
	function exists($person_id)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.person_id', $person_id);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}

	/*
	Returns all the employees
	*/
	function get_all($limit = 10000, $offset = 0)
	{
		$this->db->from('employees');
		$this->db->where('deleted', 0);
		$this->db->join('people', 'employees.person_id=people.person_id');
		$this->db->order_by("last_name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('employees');
		$this->db->where('deleted', 0);
		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular employee
	*/
	function get_info($employee_id)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where('employees.person_id', $employee_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			//Get empty base parent object, as $employee_id is NOT an employee
			$person_obj = parent::get_info(-1);

			//Get all the fields from employee table
			$fields = $this->db->list_fields('employees');

			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field) {
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Gets information about multiple employees
	*/
	function get_multiple_info($employee_ids)
	{
		$this->db->from('employees');
		$this->db->join('people', 'people.person_id = employees.person_id');
		$this->db->where_in('employees.person_id', $employee_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();
	}

	/*
     * Inserts or updates an employee
     */
	public function save($person_data, $employee_id = false, $employee_data = null, $permission_data = null)
	{
		$success = false;

		// Inicia a transação
		$this->db->trans_start();
		
		// Salva os dados da pessoa usando o método da classe pai
		if (parent::save($person_data, $employee_id)) {
			// Verifica se o funcionário não existe (inserção) ou se já existe (atualização)
			if (!$employee_id || !$this->exists($employee_id)) {
				// Se não existe, insere um novo funcionário
				$employee_data['person_id'] = $person_data['person_id']; // Adiciona person_id aos dados do funcionário
				$success = $this->db->insert('employees', $employee_data);
				$employee_id = $this->db->insert_id(); // Captura o ID do novo registro
			} else {
				// Se já existe, atualiza o registro existente
				$this->db->where('person_id', $employee_id);
				$success = $this->db->update('employees', $employee_data);
			}

			// Se a inserção ou atualização foi bem-sucedida, lidamos com as permissões
			if ($success && !empty($permission_data)) {
				// Primeiro, limpa quaisquer permissões que o funcionário já possui
				$this->db->delete('permissions', array('person_id' => $employee_id));

				// Agora, insere as novas permissões
				foreach ($permission_data as $allowed_module) {
					// Insere as permissões e verifica o sucesso
					$insert_success = $this->db->insert('permissions', array(
						'module_id' => $allowed_module,
						'person_id' => $employee_id
					));

					// Se uma inserção falhar, não precisamos continuar
					if (!$insert_success) {
						$success = false; // A operação não foi bem-sucedida
						break; // Para sair do loop
					}
				}
			}
		}

		// Completa a transação
		$this->db->trans_complete();

		// Retorna o sucesso da operação
		return $success;
	}
	/*
	Deletes one employee
	*/
	function delete($employee_id)
	{
		$success = false;

		//Don't let employee delete their self
		if ($employee_id == $this->get_logged_in_employee_info()->person_id)
			return false;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		//Delete permissions
		if ($this->db->delete('permissions', array('person_id' => $employee_id))) {
			$this->db->where('person_id', $employee_id);
			$success = $this->db->update('employees', array('deleted' => 1));
		}
		$this->db->trans_complete();
		return $success;
	}

	/*
	Deletes a list of employees
	*/
	function delete_list($employee_ids)
	{
		$success = false;

		//Don't let employee delete their self
		if (in_array($this->get_logged_in_employee_info()->person_id, $employee_ids))
			return false;

		//Run these queries as a transaction, we want to make sure we do all or nothing
		$this->db->trans_start();

		$this->db->where_in('person_id', $employee_ids);
		//Delete permissions
		if ($this->db->delete('permissions')) {
			//delete from employee table
			$this->db->where_in('person_id', $employee_ids);
			$success = $this->db->update('employees', array('deleted' => 1));
		}
		$this->db->trans_complete();
		return $success;
	}

	/*
	Get search suggestions to find employees
	*/
	function get_search_suggestions($search, $limit = 5)
	{
		$suggestions = array();

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");
		$by_name = $this->db->get();
		foreach ($by_name->result() as $row) {
			$suggestions[] = $row->first_name . ' ' . $row->last_name;
		}

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("email", $search);
		$this->db->order_by("email", "asc");
		$by_email = $this->db->get();
		foreach ($by_email->result() as $row) {
			$suggestions[] = $row->email;
		}

		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("username", $search);
		$this->db->order_by("username", "asc");
		$by_username = $this->db->get();
		foreach ($by_username->result() as $row) {
			$suggestions[] = $row->username;
		}


		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("phone_number", $search);
		$this->db->order_by("phone_number", "asc");
		$by_phone = $this->db->get();
		foreach ($by_phone->result() as $row) {
			$suggestions[] = $row->phone_number;
		}


		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		return $suggestions;
	}

	/*
	Preform a search on employees
	*/
	function search($search)
	{
		$this->db->from('employees');
		$this->db->join('people', 'employees.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		username LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");

		return $this->db->get();
	}

	/*
	Attempts to login employee and set session. Returns boolean based on outcome.
	*/
	function login($username, $password)
	{

		$query = $this->db->get_where('employees', array('username' => $username, 'password' => md5($password), 'deleted' => 0), 1);
		// $query = $this->db->get_where('employees', array('username' => $username,'password'=>$password, 'deleted'=>0), 1);
		if ($query->num_rows() == 1) {
			$row = $query->row();
			$this->session->set_userdata('person_id', $row->person_id);
			return true;
		}
		return false;
	}

	/*
	Logs out a user by destorying all session data and redirect to login
	*/
	function logout()
	{
		$this->session->sess_destroy();
		redirect('login');
	}

	/*
	Determins if a employee is logged in
	*/
	function is_logged_in()
	{
		return $this->session->userdata('person_id') != false;
	}

	/*
	Gets information about the currently logged in employee.
	*/
	function get_logged_in_employee_info()
	{
		if ($this->is_logged_in()) {
			return $this->get_info($this->session->userdata('person_id'));
		}

		return false;
	}

	/*
	Determins whether the employee specified employee has access the specific module.
	*/
	function has_permission($module_id, $person_id)
	{
		//if no module_id is null, allow access
		if ($module_id == null) {
			return true;
		}

		$query = $this->db->get_where('permissions', array('person_id' => $person_id, 'module_id' => $module_id), 1);
		return $query->num_rows() == 1;


		return false;
	}
}
