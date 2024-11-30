<?php
class Customer extends Person
{
	/*
	Determines if a given person_id is a customer
	*/
	function exists($person_id)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('customers.person_id', $person_id);
		$query = $this->db->get();

		return ($query->num_rows() == 1);
	}

	/*
	Returns all the customers
	*/
	function get_all($limit = 10000, $offset = 0)
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->order_by("last_name", "asc");
		$this->db->limit($limit);
		$this->db->offset($offset);
		return $this->db->get();
	}

	function count_all()
	{
		$this->db->from('customers');
		$this->db->where('deleted', 0);
		return $this->db->count_all_results();
	}

	/*
	Gets information about a particular customer
	*/
	function get_info($customer_id)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where('customers.person_id', $customer_id);
		$query = $this->db->get();

		if ($query->num_rows() == 1) {
			$customer_info = $query->row();

			// Agora, vamos buscar as categorias associadas ao cliente
			$this->db->select('category_name');
			$this->db->from('customer_category_link');
			$this->db->where('person_id', $customer_id);
			$categories_query = $this->db->get();


			$categories_array = $categories_query->result_array();


			if (!empty($categories_array) && isset($categories_array[0]['category_name'])) {
				$category_names = array();
				foreach ($categories_array as $category) {
					$category_names[] = $category['category_name'];
				}
				$customer_info->categories = implode(',', $category_names);
			} else {
				// Faça algo se 'category_name' não estiver presente, como definir $customer_info->categories como vazio.
				$customer_info->categories = '';
			}


			return $customer_info;
		} else {
			//Get empty base parent object, as $customer_id is NOT an customer
			$person_obj = parent::get_info(-1);

			//Get all the fields from customer table
			$fields = $this->db->list_fields('customers');

			//append those fields to base parent object, we we have a complete empty object
			foreach ($fields as $field) {
				$person_obj->$field = '';
			}

			return $person_obj;
		}
	}

	/*
	Gets information about multiple customers
	*/
	function get_multiple_info($customer_ids)
	{
		$this->db->from('customers');
		$this->db->join('people', 'people.person_id = customers.person_id');
		$this->db->where_in('customers.person_id', $customer_ids);
		$this->db->order_by("last_name", "asc");
		return $this->db->get();
	}

/*
 * Inserts or updates a customer
 */
public function save($person_data, $customer_id = false, $categories = null) {
    $success = false;

    // Inicia a transação
    $this->db->trans_start();

    // Salva os dados da pessoa
    if (parent::save($person_data, $customer_id)) {
        // Verifica se é um novo cliente ou uma atualização
        if (!$customer_id || !$this->exists($customer_id)) {
            // Se for um novo cliente, prepara os dados para inserção
            $customer_data = array(
                // Preencha com os campos necessários do cliente
                'field_name_1' => isset($person_data['field_name_1']) ? $person_data['field_name_1'] : '',
                'field_name_2' => isset($person_data['field_name_2']) ? $person_data['field_name_2'] : '',
                // Adicione mais campos conforme necessário
            );
            
            // Insere o novo cliente
            $success = $this->db->insert('customers', $customer_data);
            
            // Se a inserção foi bem-sucedida e categorias foram fornecidas
            if ($success && !is_null($categories) && !empty($categories)) {
                $customer_data['person_id'] = $person_data['person_id']; // Set person_id

                // Insere as categorias associadas ao cliente
                foreach ($categories as $category) {
                    $category_data = array(
                        'person_id' => $customer_data['person_id'],
                        'category_name' => $category
                    );
                    $this->db->insert('customer_category_link', $category_data);
                }
            }
        } else {
            // Se for uma atualização, prepara os dados para atualização
            $customer_data = array(
                // Preencha com os campos que precisam ser atualizados
                'field_name_1' => isset($person_data['field_name_1']) ? $person_data['field_name_1'] : '',
                'field_name_2' => isset($person_data['field_name_2']) ? $person_data['field_name_2'] : '',
                // Adicione mais campos conforme necessário
            );

            // Atualiza o registro existente em 'customers'
            $this->db->where('person_id', $customer_id);
            $success_customers = $this->db->update('customers', $customer_data);

            // Agora, remove os dados existentes em 'customer_category_link' para a pessoa
            if ($success_customers) {
                // Remove as categorias anteriores
                $this->db->where('person_id', $customer_data['person_id']);
                $this->db->delete('customer_category_link');

                // Insere novas categorias se fornecidas
                if (!is_null($categories) && !empty($categories)) {
                    foreach ($categories as $category) {
                        $category_data = array(
                            'person_id' => $customer_data['person_id'],
                            'category_name' => $category
                        );
                        $this->db->insert('customer_category_link', $category_data);
                    }
                }
            }

            $success = $success_customers;
        }
    }

    // Completa a transação
    $this->db->trans_complete();

    // Retorna o sucesso da operação
    return $success;
}



	/*
	Deletes one customer
	*/
	function delete($customer_id)
	{
		$this->db->where('person_id', $customer_id);
		return $this->db->update('customers', array('deleted' => 1));
	}

	/*
	Deletes a list of customers
	*/
	function delete_list($customer_ids)
	{
		$this->db->where_in('person_id', $customer_ids);
		return $this->db->update('customers', array('deleted' => 1));
	}

	/*
	Get search suggestions to find customers
	*/
	function get_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");
		$by_name = $this->db->get();
		foreach ($by_name->result() as $row) {
			$suggestions[] = $row->first_name . ' ' . $row->last_name;
		}

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("email", $search);
		$this->db->order_by("email", "asc");
		$by_email = $this->db->get();
		foreach ($by_email->result() as $row) {
			$suggestions[] = $row->email;
		}

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("phone_number", $search);
		$this->db->order_by("phone_number", "asc");
		$by_phone = $this->db->get();
		foreach ($by_phone->result() as $row) {
			$suggestions[] = $row->phone_number;
		}

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("account_number", $search);
		$this->db->order_by("account_number", "asc");
		$by_account_number = $this->db->get();
		foreach ($by_account_number->result() as $row) {
			$suggestions[] = $row->account_number;
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		return $suggestions;
	}

	/*
	Get search suggestions to find customers
	*/
	function get_customer_search_suggestions($search, $limit = 25)
	{
		$suggestions = array();

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		document LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");
		$by_name = $this->db->get();
		foreach ($by_name->result() as $row) {
			$suggestions[] = $row->person_id . '|' . $row->first_name . ' ' . $row->last_name;
		}

		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where('deleted', 0);
		$this->db->like("account_number", $search);
		$this->db->order_by("account_number", "asc");
		$by_account_number = $this->db->get();
		foreach ($by_account_number->result() as $row) {
			$suggestions[] = $row->person_id . '|' . $row->account_number;
		}

		//only return $limit suggestions
		if (count($suggestions > $limit)) {
			$suggestions = array_slice($suggestions, 0, $limit);
		}
		return $suggestions;
	}
	/*
	Preform a search on customers
	*/
	function search($search)
	{
		$this->db->from('customers');
		$this->db->join('people', 'customers.person_id=people.person_id');
		$this->db->where("(first_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		last_name LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		email LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		phone_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		account_number LIKE '%" . $this->db->escape_like_str($search) . "%' or 
		CONCAT(`first_name`,' ',`last_name`) LIKE '%" . $this->db->escape_like_str($search) . "%') and deleted=0");
		$this->db->order_by("last_name", "asc");

		return $this->db->get();
	}
}
