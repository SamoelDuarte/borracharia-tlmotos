<?php
require_once("secure_area.php");
require_once("interfaces/idata_controller.php");
class Categories extends Secure_area
{
	function __construct()
	{
		parent::__construct('categories');
		$this->load->model('Categorie');
		// Carregar a biblioteca WooCommerce
		$this->load->library('WooCommerceLibrary');
		// Atribua a instância à propriedade
		$this->WooCommerceLibrary = new WooCommerceLibrary();
	}

	public function index()
	{
		$this->load->model('Categorie');
		$this->load->view('categories/manage');
	}

	function get_categories_data()
	{
		$limit = $this->input->post('length');
		$offset = $this->input->post('start');
		$search = $this->input->post('search')['value'];

		$categories = $this->Categorie->get_all($limit, $offset, $search);
		$totalRecords = $this->Categorie->count_all();
		$totalFiltered = $this->Categorie->count_all($search);

		$data = [];
		foreach ($categories as $category) {
			
			$data[] = [
				'category_name' => $category['category_name'],
				'actions' => '
					<button class="btn btn-sm btn-warning edit-category" data-id="' . $category['category_id'] . '">Editar</button>
					<button class="btn btn-sm btn-danger delete-category" data-id="' . $category['category_id'] . '">Excluir</button>
				'
			];
		}

		echo json_encode([
			'draw' => intval($this->input->post('draw')),
			'recordsTotal' => $totalRecords,
			'recordsFiltered' => $totalFiltered,
			'data' => $data
		]);
	}

	public function send_to_wc()
	{
		$this->load->model('Categorie');
		$this->load->library('WooCommerceLibrary');

		// Obter o ID da categoria a partir da solicitação POST
		$category_id = $this->input->post('category_id');
	

		$category = $this->Categorie->get_info($category_id);
		if (!$category) {
			echo json_encode(array('success' => false, 'message' => 'Categoria não encontrada.'));
			return;
		}

		// Verifica se a categoria já possui um `wc_id`
		if (!empty($category->wc_id)) {
			echo json_encode(array('success' => false, 'message' => 'Categoria já sincronizada com o WooCommerce.'));
			return;
		}

		// Dados da categoria para envio ao WooCommerce
		$data = [
			'name' => $category->category_name
		];

		try {
			// Chama a função da biblioteca WooCommerce para criar a categoria
			$result = $this->WooCommerceLibrary->create_product_category($data);

			// Atualiza a tabela `categories` com o `wc_id` retornado
			$update_data = [
				'wc_id' => $result->id
			];
			$this->Categorie->update($category_id, $update_data);

			echo json_encode(array('success' => true, 'message' => 'Categoria enviada para o WooCommerce com sucesso.'));
		} catch (Exception $e) {
			log_message('error', 'Erro ao enviar categoria para WooCommerce: ' . $e->getMessage());
			echo json_encode(array('success' => false, 'message' => 'Erro ao sincronizar com o WooCommerce.'. $e->getMessage()));
		}
	}

	public function update()
	{
		$this->load->model('Categorie');

		$category_id = $this->input->post('category_id');
		$category_name = $this->input->post('category_name');

		// Atualiza a categoria no banco de dados
		$data = array(
			'category_name' => $category_name
		);

		if ($this->Categorie->update($category_id, $data)) {
			echo json_encode(array('success' => true, 'message' => 'Categoria atualizada com sucesso.'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar a categoria.'));
		}
	}

	public function create()
	{

		$this->load->model('Categorie');

		$category_name = $this->input->post('category_name');
		$insert_to_wc = $this->input->post('wc');

		if ($insert_to_wc == 1) {
			$data = [
				'name' => $category_name
			];
			$wc_id = 	$this->WooCommerceLibrary->create_product_category($data)->id; // Chama a função da biblioteca para criar a categoria
		} else {
			$wc_id = 	null; // Chama a função da biblioteca para criar a categoria
		}
		$data = array(
			'category_name' => $category_name,
			'wc_id' => $wc_id // ou a lógica que você preferir
		);

		if ($this->Categorie->insert($data)) {
			echo json_encode(array('success' => true, 'message' => 'Categoria criada com sucesso.'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Erro ao criar a categoria.'));
		}
	}


	public function delete()
	{
		$this->load->model('Categorie');

		$category_id = $this->input->post('category_id');

		// Verifica se a categoria está em uso
		if ($this->Categorie->is_category_in_use($category_id)) {
			echo json_encode(array('success' => false, 'message' => 'Esta categoria não pode ser excluída porque está sendo usada por itens.'));
			return; // Para a execução do método
		}

		// Deleta a categoria do banco de dados
		if ($this->Categorie->delete($category_id)) {
			echo json_encode(array('success' => true, 'message' => 'Categoria excluída com sucesso.'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'Erro ao excluir a categoria.'));
		}
	}
}
