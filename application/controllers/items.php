<?php
require_once("secure_area.php");
require_once("interfaces/idata_controller.php");
class Items extends Secure_area implements iData_controller
{
	function __construct()
	{
		parent::__construct('items');
		// Carregue a biblioteca
		$this->load->library('WooCommerceLibrary');
		// Atribua a instância à propriedade
		$this->WooCommerceLibrary = new WooCommerceLibrary();
	}

	function index()
	{
		$config['base_url'] = site_url('/items/index');
		$config['total_rows'] = $this->Item->count_all();
		$config['per_page'] = '20';
		$config['uri_segment'] = 3;
		$this->pagination->initialize($config);

		$data['controller_name'] = strtolower(get_class());
		$data['form_width'] = $this->get_form_width();
		$data['manage_table'] = get_items_manage_table($this->Item->get_all($config['per_page'], $this->uri->segment($config['uri_segment'])), $this);
		$this->load->view('items/manage', $data);
	}

	function refresh()
	{
		$low_inventory = $this->input->post('low_inventory');
		$is_serialized = $this->input->post('is_serialized');
		$no_description = $this->input->post('no_description');

		$data['search_section_state'] = $this->input->post('search_section_state');
		$data['low_inventory'] = $this->input->post('low_inventory');
		$data['is_serialized'] = $this->input->post('is_serialized');
		$data['no_description'] = $this->input->post('no_description');
		$data['controller_name'] = strtolower(get_class());
		$data['form_width'] = $this->get_form_width();
		$data['manage_table'] = get_items_manage_table($this->Item->get_all_filtered($low_inventory, $is_serialized, $no_description), $this);
		$this->load->view('items/manage', $data);
	}

	function find_item_info()
	{
		$item_number = $this->input->post('scan_item_number');
		echo json_encode($this->Item->find_item_info($item_number));
	}

	function search()
	{
		$search = $this->input->post('search');
		$data_rows = get_items_manage_table_data_rows($this->Item->search($search), $this);
		echo $data_rows;
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest()
	{
		$suggestions = $this->Item->get_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n", $suggestions);
	}

	function item_search()
	{
		$suggestions = $this->Item->get_item_search_suggestions($this->input->post('q'), $this->input->post('limit'));
		echo implode("\n", $suggestions);
	}

	/*
	Gives search suggestions based on what is being searched for
	*/
	function suggest_category()
	{
		$suggestions = $this->Item->get_category_suggestions($this->input->post('q'));
		echo implode("\n", $suggestions);
	}

	function get_row()
	{
		$item_id = $this->input->post('row_id');
		$data_row = get_item_data_row($this->Item->get_info($item_id), $this);
		echo $data_row;
	}

	function view($item_id = -1)
	{
		$data['item_info'] = $this->Item->get_info($item_id);
		$data['item_tax_info'] = $this->Item_taxes->get_info($item_id);

		$suppliers = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_all()->result_array() as $row) {
			$suppliers[$row['person_id']] = $row['company_name'] . ' (' . $row['first_name'] . ' ' . $row['last_name'] . ')';
		}

		$data['suppliers'] = $suppliers;
		$data['selected_supplier'] = $this->Item->get_info($item_id)->supplier_id;
		$data['default_tax_1_rate'] = ($item_id == -1) ? $this->Appconfig->get('default_tax_1_rate') : '';
		$data['default_tax_2_rate'] = ($item_id == -1) ? $this->Appconfig->get('default_tax_2_rate') : '';

		// // Buscar as imagens relacionadas ao item
		// $data['item_images'] = $this->Item->get_item_images($item_id); // Adiciona as imagens à view

		$this->load->view("items/form", $data);
	}


	//Ramel Inventory Tracking
	function inventory($item_id = -1)
	{
		$data['item_info'] = $this->Item->get_info($item_id);
		$this->load->view("items/inventory", $data);
	}

	function count_details($item_id = -1)
	{
		$data['item_info'] = $this->Item->get_info($item_id);
		$this->load->view("items/count_details", $data);
	} //------------------------------------------- Ramel

	function generate_barcodes($item_ids)
	{
		$result = array();

		$item_ids = explode(':', $item_ids);
		foreach ($item_ids as $item_id) {
			$item_info = $this->Item->get_info($item_id);

			$result[] = array('name' => $item_info->name, 'id' => $item_id);
		}

		$data['items'] = $result;
		$this->load->view("barcode_sheet", $data);
	}

	function bulk_edit()
	{
		$data = array();
		$suppliers = array('' => $this->lang->line('items_none'));
		foreach ($this->Supplier->get_all()->result_array() as $row) {
			$suppliers[$row['person_id']] = $row['first_name'] . ' ' . $row['last_name'];
		}
		$data['suppliers'] = $suppliers;
		$data['allow_alt_desciption_choices'] = array(
			'' => $this->lang->line('items_do_nothing'),
			1 => $this->lang->line('items_change_all_to_allow_alt_desc'),
			0 => $this->lang->line('items_change_all_to_not_allow_allow_desc')
		);

		$data['serialization_choices'] = array(
			'' => $this->lang->line('items_do_nothing'),
			1 => $this->lang->line('items_change_all_to_serialized'),
			0 => $this->lang->line('items_change_all_to_unserialized')
		);
		$this->load->view("items/form_bulk", $data);
	}

	public function save($item_id = -1)
	{
		$this->load->model('categorie');
		$category = $this->categorie->get_by_name($this->input->post('category'));
		if ($category) {
			$id_category = $category->category_id;
		} else {
			$id_category = $this->categorie->insert(['category_name' => $this->input->post('category')]);
			// if($this->input->post('sinc_wc') == '1'){

			// }
			// $category = $this->categorie->get_by_name($this->input->post('category'));

			// try {
			// 	$data = [
			// 		'name' => $category->category_name,
			// 	];
			// 	$result = 	$this->WooCommerceLibrary->create_product_category($data); // Chama a função da biblioteca para criar a categoria
			// 	// sleep(6);
			// 	// Agora, vamos atualizar a tabela local com o ID
			// 	$update_data = [
			// 		'wc_id' => $result->id,  // Armazena o ID retornado do WooCommerce
			// 		'category_id' => $category->category_id,  // Outros dados que você pode querer atualizar
			// 	];
			// 	$this->categorie->update($category->category_id,$update_data);
			// } catch (Exception $e) {
			// 	log_message('error', 'Erro ao criar categoria no WooCommerce: ' . $e->getMessage());
			// }
		}
		$item_data = array(
			'name' => $this->input->post('name'),
			'valor_cnpj' => $this->input->post('valor_cnpj'),
			'description' => $this->input->post('description'),
			'category_id' => $id_category,
			'supplier_id' => $this->input->post('supplier_id') == '' ? null : $this->input->post('supplier_id'),
			'item_number' => $this->input->post('item_number') == '' ? null : $this->input->post('item_number'),
			'cost_price' => $this->input->post('cost_price'),
			// 'sale_price' => $this->input->post('sale_price'),
			'unit_price' => $this->input->post('unit_price'),
			// 'altura' => $this->input->post('altura'),
			// 'largura' => $this->input->post('largura'),
			// 'comprimento' => $this->input->post('comprimento'),
			// 'peso' => $this->input->post('peso'),
			'quantity' => $this->input->post('quantity'),
			'reorder_level' => $this->input->post('reorder_level'),
			'location' => $this->input->post('location'),
			'allow_alt_description' => $this->input->post('allow_alt_description'),
			'is_serialized' => $this->input->post('is_serialized'),
			// 'on_sale' => $this->input->post('on_sale') == '1' ? 1 : 0, // Checkbox para indicar promoção
			// 'sinc_wc' => $this->input->post('sinc_wc') == '1' ? 1 : 0,
			// 'featured' => $this->input->post('featured') == '1' ? 1 : 0  // Checkbox para indicar destaque
		);


		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);

		// Salvar o item
		if ($this->Item->save($item_data, $item_id)) {
			// Novo item
			if ($item_id == -1) {
				// Obtenha o item_id após inserir um novo item
				$item_id = $this->db->insert_id();
				echo json_encode(array('success' => true, 'message' => $this->lang->line('items_successful_adding') . ' ' . $item_data['name'], 'item_id' => $item_id));
			} else {
				// Atualizar item existente
				echo json_encode(array('success' => true, 'message' => $this->lang->line('items_successful_updating') . ' ' . $item_data['name'], 'item_id' => $item_id));
			}

			// Registro de inventário
			$cur_quantity = isset($cur_item_info->quantity) ? (float)$cur_item_info->quantity : 0;
			$new_quantity = (float)$this->input->post('quantity');

			$inv_data = array(
				'trans_date' => date('Y-m-d H:i:s'),
				'trans_items' => $item_id,
				'trans_user' => $employee_id,
				'trans_comment' => $this->lang->line('items_manually_editing_of_quantity'),
				'trans_inventory' => $new_quantity - $cur_quantity
			);
			$this->Inventory->insert($inv_data);

			// Gerenciar impostos do item
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			for ($k = 0; $k < count($tax_percents); $k++) {
				if (is_numeric($tax_percents[$k])) {
					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percents[$k]);
				}
			}
			$this->Item_taxes->save($items_taxes_data, $item_id);

			// Gerenciar upload de imagens
			// $this->handle_images_upload($item_id);

			// Integração com WooCommerce - Enviar ou atualizar o produto no WooCommerce
			// $this->sync_with_woocommerce($item_data, $item_id);
		} else {
			// Falha no salvamento
			echo json_encode(array('success' => false, 'message' => $this->lang->line('items_error_adding_updating') . ' ' . $item_data['name'], 'item_id' => -1));
		}
	}

	private function get_item_images($item_id)
	{
		// Seleciona as imagens do banco de dados
		$this->db->select('image_path, is_cover');
		$this->db->from('item_images');
		$this->db->where('item_id', $item_id);
		$query = $this->db->get();

		$images = [];
		$cover_image = null;

		// Organiza as imagens, separando a capa
		foreach ($query->result() as $row) {
			$image = [
				// 'src' => base_url($row->image_path) // Converte o caminho relativo para um URL absoluto
				'src' => 'https://images.pexels.com/photos/322207/pexels-photo-322207.jpeg?auto=compress&cs=tinysrgb&w=600' // Converte o caminho relativo para um URL absoluto
			];

			// Verifica se é a capa (is_cover = 1)
			if ($row->is_cover == 1) {
				$cover_image = $image;
			} else {
				$images[] = $image;
			}
		}

		// Coloca a imagem de capa como a primeira no array de imagens
		if ($cover_image) {
			array_unshift($images, $cover_image);
		}

		return $images;
	}

	private function handle_images_upload($item_id)
	{
		// Obtém fotos enviadas pelo formulário
		$uploaded_photos = $_FILES['photos'];
		$cover_index = $this->input->post('cover'); // Índice da nova imagem de capa
		$existing_cover_id = $this->input->post('cover_existing'); // ID da capa existente

		$upload_path = './uploads/items/';

		// Se o usuário selecionou uma capa existente, remova a capa anterior
		if ($existing_cover_id) {
			// Remove a capa atual (definir is_cover = 0 para todas as imagens deste item)
			$this->db->where('item_id', $item_id);
			$this->db->update('item_images', ['is_cover' => 0]);

			// Define a nova capa com base no ID da imagem existente
			$this->db->where('image_id', $existing_cover_id);
			$this->db->update('item_images', ['is_cover' => 1]);
		}

		// **Atualiza o is_cover se o usuário selecionou uma nova capa (sem upload de novas imagens)**
		if ($cover_index !== null && empty($uploaded_photos['name'][0])) {
			// Busca as imagens existentes para o item
			$item_images = $this->Item->get_item_images($item_id);

			// Remove a capa atual de todas as imagens
			$this->db->where('item_id', $item_id);
			$this->db->update('item_images', ['is_cover' => 0]);

			// Atualiza a imagem correspondente ao índice selecionado como nova capa
			if (isset($item_images[$cover_index])) {
				$this->db->where('image_id', $item_images[$cover_index]['image_id']);
				$this->db->update('item_images', ['is_cover' => 1]);
			}
		}

		// **Se novas imagens foram carregadas**
		if (!empty($uploaded_photos['name'][0])) {
			for ($i = 0; $i < count($uploaded_photos['name']); $i++) {
				$file_name = $uploaded_photos['name'][$i];
				$tmp_name = $uploaded_photos['tmp_name'][$i];

				// Gerar um nome único para a imagem
				$new_file_name = time() . '_' . $file_name;

				// Salva o arquivo no servidor
				if (move_uploaded_file($tmp_name, $upload_path . $new_file_name)) {
					// Insere o caminho da imagem e a indicação de capa no banco de dados
					$is_cover = ($i == $cover_index) ? 1 : 0;

					$this->db->insert('item_images', [
						'item_id' => $item_id,
						'image_path' => 'uploads/items/' . $new_file_name,
						'is_cover' => $is_cover
					]);

					// Se esta imagem foi marcada como capa, remova a capa de todas as outras
					if ($is_cover) {
						// Remove a capa anterior de outras imagens (aquelas com is_cover = 1)
						$inserted_image_id = $this->db->insert_id(); // ID da imagem inserida
						$this->db->where('item_id', $item_id);
						$this->db->where('image_id !=', $inserted_image_id); // Exclui a imagem recém-inserida
						$this->db->update('item_images', ['is_cover' => 0]);
					}
				}
			}
		}
	}




	// Função para obter a mensagem de erro correspondente ao código de erro de upload
	private function get_upload_error_message($error_code)
	{
		switch ($error_code) {
			case UPLOAD_ERR_INI_SIZE:
				return 'O arquivo excede o limite máximo definido no php.ini.';
			case UPLOAD_ERR_FORM_SIZE:
				return 'O arquivo excede o limite máximo definido no formulário.';
			case UPLOAD_ERR_PARTIAL:
				return 'O arquivo foi apenas parcialmente enviado.';
			case UPLOAD_ERR_NO_FILE:
				return 'Nenhum arquivo foi enviado.';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Faltando diretório temporário.';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Falha ao escrever o arquivo no disco.';
			case UPLOAD_ERR_EXTENSION:
				return 'Uma extensão do PHP interrompeu o upload do arquivo.';
			default:
				return 'Erro desconhecido durante o upload.';
		}
	}



	//Ramel Inventory Tracking
	function save_inventory($item_id = -1)
	{
		$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
		$cur_item_info = $this->Item->get_info($item_id);
		$inv_data = array(
			'trans_date' => date('Y-m-d H:i:s'),
			'trans_items' => $item_id,
			'trans_user' => $employee_id,
			'trans_comment' => $this->input->post('trans_comment'),
			'trans_inventory' => $this->input->post('newquantity')
		);
		$this->Inventory->insert($inv_data);

		//Update stock quantity
		$item_data = array(
			'quantity' => $cur_item_info->quantity + $this->input->post('newquantity')
		);

		// $sincWC = array(
		// 	'up_wc' => 1,
		// );
		
		$this->db->where('item_id', $item_id);
		// $this->db->update('items', $sincWC);


		if ($this->Item->save($item_data, $item_id)) {
			echo json_encode(array('success' => true, 'message' => $this->lang->line('items_successful_updating') . ' ' .
				$cur_item_info->name, 'item_id' => $item_id));
		} else //failure
		{
			echo json_encode(array('success' => false, 'message' => $this->lang->line('items_error_adding_updating') . ' ' .
				$cur_item_info->name, 'item_id' => -1));
		}
	} //---------------------------------------------------------------------Ramel

	function bulk_update()
	{
		$items_to_update = $this->input->post('item_ids');
		$item_data = array();

		foreach ($_POST as $key => $value) {
			//This field is nullable, so treat it differently
			if ($key == 'supplier_id') {
				$item_data["$key"] = $value == '' ? null : $value;
			} elseif ($value != '' and !(in_array($key, array('item_ids', 'tax_names', 'tax_percents')))) {
				$item_data["$key"] = $value;
			}
		}

		//Item data could be empty if tax information is being updated
		if (empty($item_data) || $this->Item->update_multiple($item_data, $items_to_update)) {
			$items_taxes_data = array();
			$tax_names = $this->input->post('tax_names');
			$tax_percents = $this->input->post('tax_percents');
			for ($k = 0; $k < count($tax_percents); $k++) {
				if (is_numeric($tax_percents[$k])) {
					$items_taxes_data[] = array('name' => $tax_names[$k], 'percent' => $tax_percents[$k]);
				}
			}
			$this->Item_taxes->save_multiple($items_taxes_data, $items_to_update);

			echo json_encode(array('success' => true, 'message' => $this->lang->line('items_successful_bulk_edit')));
		} else {
			echo json_encode(array('success' => false, 'message' => $this->lang->line('items_error_updating_multiple')));
		}
	}

	function delete()
	{
		$items_to_delete = $this->input->post('ids');

		// Carregar a biblioteca WooCommerce
		$this->load->library('WooCommerceLibrary');

		// Loop para excluir cada item individualmente do sistema e do WooCommerce
		foreach ($items_to_delete as $item_id) {
			// Buscar o ID do produto no WooCommerce
			$this->db->select('id_wc'); // Supondo que você armazene o ID do WooCommerce no campo 'id_wc'
			$this->db->from('items');
			$this->db->where('item_id', $item_id);
			$query = $this->db->get();
			$result = $query->row();

			if ($result && !empty($result->id_wc)) {
				try {
					// Excluir produto do WooCommerce
					$this->WooCommerceLibrary->delete_product($result->id_wc);

					// Logar sucesso
					log_message('info', 'Produto excluído do WooCommerce: ID ' . $result->id_wc);
				} catch (Exception $e) {
					// Logar o erro caso a exclusão falhe
					log_message('error', 'Erro ao excluir produto do WooCommerce: ' . $e->getMessage());
				}
			}

			// Excluir o item do sistema local
			if ($this->Item->delete($item_id)) {
				log_message('info', 'Item excluído localmente: ID ' . $item_id);
			} else {
				log_message('error', 'Erro ao excluir o item localmente: ID ' . $item_id);
			}
		}

		// Verificar se todos os itens foram excluídos
		if (count($items_to_delete) > 0) {
			echo json_encode(array(
				'success' => true,
				'message' => $this->lang->line('items_successful_deleted') . ' ' . count($items_to_delete) . ' ' . $this->lang->line('items_one_or_multiple')
			));
		} else {
			echo json_encode(array(
				'success' => false,
				'message' => $this->lang->line('items_cannot_be_deleted')
			));
		}
	}


	function excel()
	{
		$data = file_get_contents("import_items.csv");
		$name = 'import_items.csv';
		force_download($name, $data);
	}

	function excel_import()
	{
		$this->load->view("items/excel_import", null);
	}

	function do_excel_import()
	{
		$msg = 'do_excel_import';
		$failCodes = array();
		if ($_FILES['file_path']['error'] != UPLOAD_ERR_OK) {
			$msg = $this->lang->line('items_excel_import_failed');
			echo json_encode(array('success' => false, 'message' => $msg));
			return;
		} else {
			if (($handle = fopen($_FILES['file_path']['tmp_name'], "r")) !== FALSE) {
				//Skip first row
				fgetcsv($handle);

				$i = 1;
				while (($data = fgetcsv($handle)) !== FALSE) {
					$item_data = array(
						'name'			=>	$data[1],
						'description'	=>	$data[13],
						'location'		=>	$data[12],
						'category'		=>	$data[2],
						'cost_price'	=>	$data[4],
						'unit_price'	=>	$data[5],
						'quantity'		=>	$data[10],
						'reorder_level'	=>	$data[11],
						'supplier_id'	=>  $this->Supplier->exists($data[3]) ? $data[3] : null,
						'allow_alt_description' => $data[14] != '' ? '1' : '0',
						'is_serialized' => $data[15] != '' ? '1' : '0'
					);
					$item_number = $data[0];

					if ($item_number != "") {
						$item_data['item_number'] = $item_number;
					}

					if ($this->Item->save($item_data)) {
						$items_taxes_data = null;
						//tax 1
						if (is_numeric($data[7]) && $data[6] != '') {
							$items_taxes_data[] = array('name' => $data[6], 'percent' => $data[7]);
						}

						//tax 2
						if (is_numeric($data[9]) && $data[8] != '') {
							$items_taxes_data[] = array('name' => $data[8], 'percent' => $data[9]);
						}

						// save tax values
						if (count($items_taxes_data) > 0) {
							$this->Item_taxes->save($items_taxes_data, $item_data['item_id']);
						}

						$employee_id = $this->Employee->get_logged_in_employee_info()->person_id;
						$emp_info = $this->Employee->get_info($employee_id);
						$comment = 'Qty CSV Imported';
						$excel_data = array(
							'trans_items' => $item_data['item_id'],
							'trans_user' => $employee_id,
							'trans_comment' => $comment,
							'trans_inventory' => $data[10]
						);
						$this->db->insert('inventory', $excel_data);
						//------------------------------------------------Ramel
					} else //insert or update item failure
					{
						$failCodes[] = $i;
					}
				}

				$i++;
			} else {
				echo json_encode(array('success' => false, 'message' => 'Your upload file has no data or not in supported format.'));
				return;
			}
		}

		$success = true;
		if (count($failCodes) > 1) {
			$msg = "Most items imported. But some were not, here is list of their CODE (" . count($failCodes) . "): " . implode(", ", $failCodes);
			$success = false;
		} else {
			$msg = "Import items successful";
		}

		echo json_encode(array('success' => $success, 'message' => $msg));
	}

	/*
	get the width for the add/edit form
	*/
	function get_form_width()
	{
		return 360;
	}
}
