	<?php
	class Item extends CI_Model
	{
		/*
		Determines if a given item_id is an item
		*/
		function exists($item_id)
		{
			$this->db->from('items');
			$this->db->where('item_id', $item_id);
			$query = $this->db->get();

			return ($query->num_rows() == 1);
		}

		function get_all($limit = 10000, $offset = 0)
		{
			$this->db->select('items.*, categories_products.category_name'); // Seleciona os dados de items e o nome da categoria
			$this->db->from('items');
			$this->db->join('categories_products', 'items.category_id = categories_products.category_id', 'left'); // Faz o JOIN com a tabela de categorias
			$this->db->where('items.deleted', 0); // Filtra por itens que não estão deletados
			$this->db->order_by('items.name', 'asc'); // Ordena pelos nomes dos itens
			$this->db->limit($limit); // Define o limite de resultados
			$this->db->offset($offset); // Define o deslocamento para paginação

			return $this->db->get(); // Retorna o resultado da query
		}

		function get_sinc_wc()
		{
			$this->db->select('i.*, c.category_name,c.category_id'); // Seleciona os dados de items e o nome da categoria
			$this->db->from('items as i');
			$this->db->join('categories_products as c', 'i.category_id = c.category_id', 'left'); // Faz o JOIN com a tabela de categorias
			$this->db->where('i.deleted', 0); // Filtra por itens que não estão deletados
			$this->db->where('i.id_wc', null); // Filtra por itens com id_wc não nulo
			$this->db->where('i.sinc_wc', 1); // Filtra por itens que não estão deletados
			$this->db->where('i.up_wc', 0); // Filtra por itens que foram atualizados no WooCommerce
			$this->db->limit(1);

			return $this->db->get()->result_array(); // Retorna o resultado da query
		}

		function get_up_sinc_wc()
		{
			$this->db->select('i.*, c.category_name, c.category_id'); // Seleciona os dados de items e o nome da categoria
			$this->db->from('items as i');
			$this->db->join('categories_products as c', 'i.category_id = c.category_id', 'left'); // Faz o JOIN com a tabela de categorias
			$this->db->where('i.deleted', 0); // Filtra por itens que não estão deletados
			$this->db->where('i.sinc_wc', 1); // Filtra por itens que precisam ser sincronizados com o WooCommerce
			$this->db->where('i.up_wc', 1); // Filtra por itens que foram atualizados no WooCommerce
			$this->db->limit(1);

			return $this->db->get()->result_array(); // Retorna o resultado da query
		}




		// Busca as imagens de um item
		public function get_item_images($item_id)
		{
			$this->db->from('item_images');
			$this->db->where('item_id', $item_id);
			$query = $this->db->get();
			return $query->result_array(); // Retorna um array com as URLs das imagens
		}

		function count_all()
		{
			$this->db->from('items');
			$this->db->where('deleted', 0);
			return $this->db->count_all_results();
		}

		public function count_by_category($category_id)
		{
			$this->db->where('category_id', $category_id);
			return $this->db->count_all_results('items'); // Substitua 'items' pelo nome da sua tabela de itens
		}


		function get_all_filtered($no_description, $low_inventory = 0, $is_serialized = 0)
		{
			$this->db->from('items');

			if ($low_inventory != 0) {
				$this->db->where('quantity <=', 'reorder_level', false);
			}

			if ($is_serialized != 0) {
				$this->db->where('is_serialized', 1);
			}

			if ($no_description != 0) {
				$this->db->where('description', '');
			}

			$this->db->where('deleted', 0);
			$this->db->order_by("name", "asc");
			return $this->db->get();
		}


		/*
		Gets information about a particular item
		*/
		function get_info($item_id)
		{
			$this->db->select('items.*, categories_products.category_name'); // Seleciona todos os campos da tabela items e category_name
			$this->db->from('items');
			// Altere 'category_id' e 'id' conforme necessário
			$this->db->join('categories_products', 'items.category_id = categories_products.category_id', 'left'); // Use o nome correto da coluna aqui
			$this->db->where('item_id', $item_id);

			$query = $this->db->get();

			if ($query->num_rows() == 1) {
				return $query->row(); // Retorna o objeto do item com a categoria
			} else {
				// Get empty base parent object, as $item_id is NOT an item
				$item_obj = new stdClass();

				// Get all the fields from items table
				$fields = $this->db->list_fields('items');

				foreach ($fields as $field) {
					$item_obj->$field = '';
				}

				$item_obj->category_name = ''; // Adiciona a propriedade category_name ao objeto

				return $item_obj;
			}
		}

		function get_info_wc($item_id)
		{
			$this->db->select('items.*, categories_products.category_name'); // Seleciona todos os campos da tabela items e category_name
			$this->db->from('items');
			// Altere 'category_id' e 'id' conforme necessário
			$this->db->join('categories_products', 'items.category_id = categories_products.category_id', 'left'); // Use o nome correto da coluna aqui
			$this->db->where('id_wc', $item_id);

			$query = $this->db->get();

			if ($query->num_rows() == 1) {
				return $query->row(); // Retorna o objeto do item com a categoria
			} else {
				// Get empty base parent object, as $item_id is NOT an item
				$item_obj = new stdClass();

				// Get all the fields from items table
				$fields = $this->db->list_fields('items');

				foreach ($fields as $field) {
					$item_obj->$field = '';
				}

				$item_obj->category_name = ''; // Adiciona a propriedade category_name ao objeto

				return $item_obj;
			}
		}




		/*
		Get an item id given an item number
		*/
		function get_item_id($item_number)
		{
			$this->db->from('items');
			$this->db->where('item_id', $item_number);

			$query = $this->db->get();

			if ($query->num_rows() == 1) {
				return $query->row()->item_id;
			}

			return false;
		}

		/*
		Gets information about multiple items
		*/
		function get_multiple_info($item_ids)
		{
			$this->db->from('items');
			$this->db->where_in('item_id', $item_ids);
			$this->db->order_by("item", "asc");
			return $this->db->get();
		}


		/*
		Inserts or updates a item
		*/
		function update_category_id($item_id, $categorie_id)
		{
			$this->db->where('item_id', $item_id);
			$this->db->update('items', ['category_id' => $categorie_id]);
		}
		function save(&$item_data, $item_id = false, $images = array(), $cover_image_index = null)
		{
			// Verifica se é um novo item ou um item existente
			if (!$item_id || !$this->exists($item_id)) {
				if ($this->db->insert('items', $item_data)) {
					$item_id = $this->db->insert_id();  // Obtém o novo item_id
				} else {
					return false;
				}
			} else {
				$item_data['up_wc'] = 1;
				$this->db->where('item_id', $item_id);
				$this->db->update('items', $item_data);
			}

			// Se houver imagens para serem salvas
			if (!empty($images)) {
				$this->save_item_images($item_id, $images, $cover_image_index);  // Chama a função para salvar as imagens
			}

			return true;
		}
		function save_item_images($item_id, $images, $cover_image_index = null)
		{
			$this->load->library('upload');

			// Itera sobre as imagens para salvá-las no banco de dados
			foreach ($images['tmp_name'] as $key => $image) {
				if (is_uploaded_file($image)) {
					$image_name = $images['name'][$key];
					$upload_path = 'uploads/items/' . $image_name;

					// Mova a imagem para o diretório correto
					move_uploaded_file($image, $upload_path);

					// Dados da imagem para inserir no banco
					$image_data = array(
						'item_id' => $item_id,
						'file_name' => $image_name,
						'file_path' => $upload_path,
						'is_cover' => ($key == $cover_image_index) ? 1 : 0,  // Define se é capa
					);

					// Insere a imagem no banco de dados
					$this->db->insert('item_images', $image_data);
				}
			}

			// Atualiza outras imagens para não serem capa (caso já exista capa)
			if ($cover_image_index !== null) {
				$this->db->where('item_id', $item_id);
				$this->db->where('id !=', $this->db->insert_id());  // Exclui a nova imagem de capa
				$this->db->update('item_images', array('is_cover' => 0));
			}
		}



		/*
		Updates multiple items at once
		*/
		function update_multiple($item_data, $item_ids)
		{
			$this->db->where_in('item_id', $item_ids);
			return $this->db->update('items', $item_data);
		}

		/*
		Deletes one item
		*/
		function delete($item_id)
		{
			$this->db->where('item_id', $item_id);
			return $this->db->update('items', array('deleted' => 1));
		}

		/*
		Deletes a list of items
		*/
		function delete_list($item_ids)
		{
			$this->db->where_in('item_id', $item_ids);
			return $this->db->update('items', array('deleted' => 1));
		}

		/*
		Get search suggestions to find items
		*/
		function get_search_suggestions($search, $limit = 25)
		{
			$suggestions = array();

			// Busca por nome
			$this->db->from('items');
			$this->db->like('name', $search);
			$this->db->where('deleted', 0);
			$this->db->order_by("name", "asc");
			$by_name = $this->db->get();
			foreach ($by_name->result() as $row) {
				$suggestions[] = $row->name;
			}

			// Busca por categoria
			// $this->db->select('category');
			// $this->db->from('items');
			// $this->db->where('deleted', 0);
			// $this->db->distinct();
			// $this->db->like('category', $search);
			// $this->db->order_by("category", "asc");
			// $by_category = $this->db->get();
			// foreach ($by_category->result() as $row) {
			// 	$suggestions[] = $row->categorie;
			// }

			// Busca por número do item
			$this->db->from('items');
			$this->db->like('item_number', $search);
			$this->db->where('deleted', 0);
			$this->db->order_by("item_number", "asc");
			$by_item_number = $this->db->get();
			foreach ($by_item_number->result() as $row) {
				$suggestions[] = $row->item_number;
			}

			// Limitar sugestões ao valor de $limit
			if (count($suggestions) > $limit) {
				$suggestions = array_slice($suggestions, 0, $limit);
			}

			return $suggestions;
		}


		function get_item_search_suggestions($search, $limit = 25)
		{
			$suggestions = array();

			$this->db->from('items');
			$this->db->where('deleted', 0);
			$this->db->like('name', $search);
			$this->db->order_by("name", "asc");
			$by_name = $this->db->get();
			foreach ($by_name->result() as $row) {
				$suggestions[] = $row->item_id . '|' . $row->name;
			}

			$this->db->from('items');
			$this->db->where('deleted', 0);
			$this->db->like('item_number', $search);
			$this->db->order_by("item_number", "asc");
			$by_item_number = $this->db->get();
			foreach ($by_item_number->result() as $row) {
				$suggestions[] = $row->item_id . '|' . $row->item_number;
			}

			if (count($suggestions) > $limit) {
				$suggestions = array_slice($suggestions, 0, $limit);
			}
			return $suggestions;
		}

		function get_category_suggestions($search)
		{
			$suggestions = array();
			$this->db->distinct();
			$this->db->select('category_name'); // Alterado para a coluna da tabela de categorias
			$this->db->from('categories_products'); // Usando a tabela de categorias
			$this->db->like('category_name', $search); // Aplicando o filtro de pesquisa na coluna correta
			$this->db->order_by("category_name", "asc"); // Ordenando os resultados pela coluna correta

			$by_category = $this->db->get(); // Executa a consulta

			foreach ($by_category->result() as $row) {
				$suggestions[] = $row->category_name; // Adiciona as categorias às sugestões
			}

			return $suggestions; // Retorna as sugestões
		}


		function get_distinct_categories()
		{
			$suggestions = array();

			// Seleciona categorias distintas da tabela categories_products
			$this->db->distinct();
			$this->db->select('category_name');
			$this->db->from('categories_products');
			$this->db->order_by("category_name", "asc");

			// Executa a consulta
			$by_category = $this->db->get();

			// Preenche o array com os nomes das categorias
			foreach ($by_category->result() as $row) {
				$suggestions[] = $row->category_name;
			}

			return $suggestions;
		}


		/*
		Preform a search on items
		*/
		function search($search)
		{

			// Seleciona os campos desejados, incluindo o nome da categoria
			$this->db->select('t.*, c.category_name');

			// Define a tabela principal (items)
			$this->db->from('items as t');

			// Faz o join com a tabela de categorias
			$this->db->join('categories_products as c', 't.category_id = c.category_id', 'inner');

			// Adiciona as condições de pesquisa no nome do item, número do item ou nome da categoria
			$this->db->where("(t.name LIKE '%" . $this->db->escape_like_str($search) . "%' 
							OR t.item_number LIKE '%" . $this->db->escape_like_str($search) . "%' 
							OR c.category_name LIKE '%" . $this->db->escape_like_str($search) . "%') 
							AND t.deleted = 0");

			// Ordena os resultados pelo nome do item
			$this->db->order_by("t.name", "asc");

			// Retorna os resultados da consulta
			return $this->db->get();
		}


		function get_categories()
		{
			// Seleciona a coluna 'category_name' da tabela de categorias
			$this->db->select('category_name');
			$this->db->from('categories_products'); // Tabela de categorias
			$this->db->distinct(); // Garante que sejam retornadas categorias distintas
			$this->db->order_by("category_name", "asc"); // Ordena as categorias por nome

			return $this->db->get(); // Executa a consulta e retorna o resultado
		}

		public function get_items_without_wc_id($limit = 5)
		{
			// Seleciona os itens onde o campo id_wc é NULL ou vazio
			$this->db->where('id_wc', NULL);  // Ou, se precisar especificar explicitamente:
			$this->db->where('sinc_wc', 1);
			$this->db->where('up_wc', 0);
			$this->db->limit($limit);

			// Retorna os resultados da tabela 'items'
			return $this->db->get('items'); // 'items' é o nome da tabela de produtos
		}

		// Método para atualizar o campo id_wc de um produto
		public function update_wc_id($item_id, $wc_id)
		{
			// Atualiza o campo id_wc do item com o id_wc fornecido
			$this->db->where('item_id', $item_id);
			$this->db->update('items', ['id_wc' => $wc_id]);
		}
	}
