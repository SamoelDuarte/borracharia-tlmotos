<?php
require_once("report.php");
class Detailed_sales extends Report
{
	function __construct()
	{
		parent::__construct();
	}

	public function getDataColumns()
	{
		return array(
			'summary' => array($this->lang->line('reports_sale_id'), $this->lang->line('reports_date'), $this->lang->line('reports_items_purchased'), $this->lang->line('reports_sold_by'), $this->lang->line('reports_sold_to'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'), $this->lang->line('reports_payment_type'), $this->lang->line('reports_comments')),
			'details' => array($this->lang->line('reports_name'), $this->lang->line('reports_category'), $this->lang->line('reports_serial_number'), $this->lang->line('reports_description'), $this->lang->line('reports_quantity_purchased'), $this->lang->line('reports_subtotal'), $this->lang->line('reports_total'), $this->lang->line('reports_tax'), $this->lang->line('reports_profit'), $this->lang->line('reports_discount'))
		);
	}

	public function getData(array $inputs)
	{
		$data = array();
		$data['summary'] = array();
		$data['details'] = array();

		if ($inputs['sale_type'] == 'deletions') {
			$sales_table = $this->db->dbprefix('sales');
			$this->db->select('CAST(REPLACE(trans_comment, "POS ", "") AS UNSIGNED) as sale_id, DATE(MIN(trans_date)) as sale_date, SUM(ABS(trans_inventory)) as items_purchased, CONCAT(employee.first_name, " ", employee.last_name) as employee_name, "-" as customer_name, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as subtotal, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as total, 0 as tax, SUM(ABS(trans_inventory) * (IFNULL(items.unit_price,0)-IFNULL(items.cost_price,0))) as profit, "-" as payment_type, "Venda apagada pelo botao Excluir" as comment', false);
			$this->db->from('inventory');
			$this->db->join('people as employee', 'trans_user = employee.person_id', 'left');
			$this->db->join($this->db->dbprefix('items') . ' as items', 'trans_items = items.item_id', 'left');
			$this->db->where('DATE(trans_date) BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
			$this->db->where('trans_comment REGEXP "^POS [0-9]+$"', null, false);
			$this->db->where('trans_inventory < 0');
			$this->db->where('NOT EXISTS (SELECT 1 FROM ' . $sales_table . ' s WHERE s.sale_id = CAST(REPLACE(trans_comment, "POS ", "") AS UNSIGNED))', null, false);
			$this->db->group_by('trans_comment');
			$this->db->order_by('sale_date');

			$data['summary'] = $this->db->get()->result_array();

			foreach ($data['summary'] as $key => $value) {
				$this->db->select('items.name, categories_products.category_name as category, "-" as serialnumber, trans_comment as description, SUM(ABS(trans_inventory)) as quantity_purchased, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as subtotal, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as total, 0 as tax, SUM(ABS(trans_inventory) * (IFNULL(items.unit_price,0)-IFNULL(items.cost_price,0))) as profit, 0 as discount_percent', false);
				$this->db->from('inventory');
				$this->db->join($this->db->dbprefix('items') . ' as items', 'trans_items = items.item_id');
				$this->db->join($this->db->dbprefix('categories_products') . ' as categories_products', 'items.category_id = categories_products.category_id', 'left');
				$this->db->where('trans_comment', 'POS ' . $value['sale_id']);
				$this->db->group_by('trans_items');
				$data['details'][$key] = $this->db->get()->result_array();
			}

			return $data;
		}

		$this->db->select('sales_items_temp.sale_id, sales_items_temp.sale_date, SUM(sales_items_temp.quantity_purchased) as items_purchased, CONCAT(employee.first_name," ",employee.last_name) as employee_name, CONCAT(customer.first_name," ",customer.last_name) as customer_name, SUM(sales_items_temp.subtotal) as subtotal, SUM(sales_items_temp.total) as total, SUM(sales_items_temp.tax) as tax, SUM(sales_items_temp.profit) as profit, sales_items_temp.payment_type, sales_items_temp.comment', false);
		$this->db->from($this->db->dbprefix('sales_items_temp') . ' as sales_items_temp');
		$this->db->join('people as employee', 'sales_items_temp.employee_id = employee.person_id');
		$this->db->join('people as customer', 'sales_items_temp.customer_id = customer.person_id', 'left');
		$this->db->where('sales_items_temp.sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');

		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('sales_items_temp.quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('sales_items_temp.quantity_purchased < 0');
		}

		$this->db->group_by('sales_items_temp.sale_id');
		$this->db->order_by('sales_items_temp.sale_date');

		$data['summary'] = $this->db->get()->result_array();

		foreach ($data['summary'] as $key => $value) {
			$this->db->select('items.name, categories_products.category_name as category, sales_items_temp.quantity_purchased, sales_items_temp.serialnumber, sales_items_temp.description, sales_items_temp.subtotal, sales_items_temp.total, sales_items_temp.tax, sales_items_temp.profit, sales_items_temp.discount_percent');
			$this->db->from($this->db->dbprefix('sales_items_temp') . ' as sales_items_temp');
			$this->db->join('items', 'sales_items_temp.item_id = items.item_id');
			$this->db->join('categories_products', 'items.category_id = categories_products.category_id', 'left');
			$this->db->where('sales_items_temp.sale_id', $value['sale_id']);
			$data['details'][$key] = $this->db->get()->result_array();
		}

		return $data;
	}


	public function getSummaryData(array $inputs)
	{
		if ($inputs['sale_type'] == 'deletions') {
			$sales_table = $this->db->dbprefix('sales');
			$this->db->select('SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as subtotal, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as total, 0 as tax, SUM(ABS(trans_inventory) * (IFNULL(items.unit_price,0)-IFNULL(items.cost_price,0))) as profit', false);
			$this->db->from('inventory');
			$this->db->join($this->db->dbprefix('items') . ' as items', 'trans_items = items.item_id', 'left');
			$this->db->where('DATE(trans_date) BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
			$this->db->where('trans_comment REGEXP "^POS [0-9]+$"', null, false);
			$this->db->where('trans_inventory < 0');
			$this->db->where('NOT EXISTS (SELECT 1 FROM ' . $sales_table . ' s WHERE s.sale_id = CAST(REPLACE(trans_comment, "POS ", "") AS UNSIGNED))', null, false);

			$row = $this->db->get()->row_array();
			if (!$row) {
				return array('subtotal' => 0, 'total' => 0, 'tax' => 0, 'profit' => 0);
			}

			return array(
				'subtotal' => $row['subtotal'] ? $row['subtotal'] : 0,
				'total' => $row['total'] ? $row['total'] : 0,
				'tax' => 0,
				'profit' => $row['profit'] ? $row['profit'] : 0,
			);
		}

		$this->db->select('sum(subtotal) as subtotal, sum(total) as total, sum(tax) as tax, sum(profit) as profit');
		$this->db->from('sales_items_temp');
		$this->db->where('sale_date BETWEEN "' . $inputs['start_date'] . '" and "' . $inputs['end_date'] . '"');
		if ($inputs['sale_type'] == 'sales') {
			$this->db->where('quantity_purchased > 0');
		} elseif ($inputs['sale_type'] == 'returns') {
			$this->db->where('quantity_purchased < 0');
		}

		return $this->db->get()->row_array();
	}

	public function getDeletedSaleReceiptData($sale_id)
	{
		$this->db->select('DATE(MIN(trans_date)) as sale_date, CONCAT(employee.first_name, " ", employee.last_name) as employee_name, SUM(ABS(trans_inventory)) as items_purchased, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as subtotal, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as total, 0 as tax, SUM(ABS(trans_inventory) * (IFNULL(items.unit_price,0)-IFNULL(items.cost_price,0))) as profit', false);
		$this->db->from('inventory');
		$this->db->join('people as employee', 'trans_user = employee.person_id', 'left');
		$this->db->join($this->db->dbprefix('items') . ' as items', 'trans_items = items.item_id', 'left');
		$this->db->where('trans_comment', 'POS ' . $sale_id);
		$this->db->where('trans_inventory < 0');
		$summary = $this->db->get()->row_array();

		$this->db->select('items.name, categories_products.category_name as category, SUM(ABS(trans_inventory)) as quantity_purchased, IFNULL(items.unit_price,0) as unit_price, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as subtotal, SUM(ABS(trans_inventory) * IFNULL(items.unit_price,0)) as total, 0 as tax, SUM(ABS(trans_inventory) * (IFNULL(items.unit_price,0)-IFNULL(items.cost_price,0))) as profit', false);
		$this->db->from('inventory');
		$this->db->join($this->db->dbprefix('items') . ' as items', 'trans_items = items.item_id', 'left');
		$this->db->join($this->db->dbprefix('categories_products') . ' as categories_products', 'items.category_id = categories_products.category_id', 'left');
		$this->db->where('trans_comment', 'POS ' . $sale_id);
		$this->db->where('trans_inventory < 0');
		$this->db->group_by('trans_items');
		$details = $this->db->get()->result_array();

		return array('summary' => $summary, 'details' => $details);
	}
}
