<?php
class Sale_lib
{
	var $CI;

	function __construct()
	{
		$this->CI = &get_instance();
	}

	function get_cart()
	{
		if (!$this->CI->session->userdata('cart'))
			$this->set_cart(array());

		return $this->CI->session->userdata('cart');
	}

	function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('cart', $cart_data);
	}

	//Alain Multiple Payments
	function get_payments()
	{
		if (!$this->CI->session->userdata('payments'))
			$this->set_payments(array());

		return $this->CI->session->userdata('payments');
	}

	//Alain Multiple Payments
	function set_payments($payments_data)
	{
		$this->CI->session->set_userdata('payments', $payments_data);
	}

	function get_comment()
	{
		return $this->CI->session->userdata('comment');
	}

	function set_comment($comment)
	{
		$this->CI->session->set_userdata('comment', $comment);
	}

	function clear_comment()
	{
		$this->CI->session->unset_userdata('comment');
	}

	function get_email_receipt()
	{
		return $this->CI->session->userdata('email_receipt');
	}

	function set_email_receipt($email_receipt)
	{
		$this->CI->session->set_userdata('email_receipt', $email_receipt);
	}

	function clear_email_receipt()
	{
		$this->CI->session->unset_userdata('email_receipt');
	}

	function add_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		$payment = array(
			$payment_id =>
			array(
				'payment_type' => $payment_id,
				'payment_amount' => $payment_amount
			)
		);

		//payment_method already exists, add to payment_amount
		if (isset($payments[$payment_id])) {
			$payments[$payment_id]['payment_amount'] += $payment_amount;
		} else {
			//add to existing array
			$payments += $payment;
		}

		$this->set_payments($payments);
		return true;
	}

	//Alain Multiple Payments
	function edit_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		if (isset($payments[$payment_id])) {
			$payments[$payment_id]['payment_type'] = $payment_id;
			$payments[$payment_id]['payment_amount'] = $payment_amount;
			$this->set_payments($payment_id);
		}

		return false;
	}

	//Alain Multiple Payments
	function delete_payment($payment_id)
	{
		$payments = $this->get_payments();
		unset($payments[urldecode($payment_id)]);
		$this->set_payments($payments);
	}

	//Alain Multiple Payments
	function empty_payments()
	{
		$this->CI->session->unset_userdata('payments');
	}

	//Alain Multiple Payments
	function get_payments_total()
	{
		$subtotal = 0;
		foreach ($this->get_payments() as $payments) {
			$subtotal += $payments['payment_amount'];
		}
		return to_currency_no_money($subtotal);
	}

	//Alain Multiple Payments
	function get_amount_due()
	{
		$amount_due = 0;
		$payment_total = $this->get_payments_total();
		$sales_total = $this->get_total();
		$amount_due = to_currency_no_money($sales_total - $payment_total);
		return $amount_due;
	}

	function get_customer()
	{
		if (!$this->CI->session->userdata('customer'))
			$this->set_customer(-1);

		return $this->CI->session->userdata('customer');
	}

	function set_customer($customer_id)
	{
		$this->CI->session->set_userdata('customer', $customer_id);
	}

	function get_mode()
	{
		if (!$this->CI->session->userdata('sale_mode'))
			$this->set_mode('sale');

		return $this->CI->session->userdata('sale_mode');
	}

	function set_mode($mode)
	{
		$this->CI->session->set_userdata('sale_mode', $mode);
	}

	function add_item($item_id, $quantity = 1, $discount = 0, $price = null, $description = null, $serialnumber = null)
	{
		//make sure item exists


		// print_r($item_id);
		// exit;
		// if ($this->CI->Item->existsEATivo($item_id)) {
		// var_dump($this->CI->Item->get_item_id($item_id));
		// exit;
		//try to get item id given an item_number
		$item_id = $this->CI->Item->get_item_id($item_id);

		if (!$item_id)
			return false;
		// }else{
		// 	return false;
		// }


		//Alain Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_cart();

		//We need to loop through all items in the cart.
		//If the item is already there, get it's key($updatekey).
		//We also need to get the next key that we are going to use in case we need to add the
		//item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

		$maxkey = 0;                       //Highest key so far
		$itemalreadyinsale = FALSE;        //We did not find the item yet.
		$insertkey = 0;                    //Key to use for new entry.
		$updatekey = 0;                    //Key to use to update(quantity)

		foreach ($items as $item) {
			//We primed the loop so maxkey is 0 the first time.
			//Also, we have stored the key in the element itself so we can compare.

			if ($maxkey <= $item['line']) {
				$maxkey = $item['line'];
			}

			if ($item['item_id'] == $item_id) {
				$itemalreadyinsale = TRUE;
				$updatekey = $item['line'];
			}
		}

		$insertkey = $maxkey + 1;

		//array/cart records are identified by $insertkey and item_id is just another field
		$category = $this->CI->Item->get_info($item_id)->category;
		$customer = $this->get_customer();

		$valorItem = $price != null ? $price : $this->CI->Item->get_info($item_id)->unit_price;
		if ($customer != -1) {

			$this->CI->db->from('pos_customer_category_link');
			$this->CI->db->where('person_id', $customer);
			$this->CI->db->where('category_name', $category);
			$verifydesconto = $this->CI->db->get();

			$customerDetail = $this->CI->Customer->get_info($customer);
			if ($verifydesconto->num_rows() == 0) {


				$discount = $customerDetail->desconto;
			}

			if ($this->validarCNPJ($customerDetail->document) && $this->CI->Item->get_info($item_id)->valor_cnpj != "") {
				$valorItem = $this->CI->Item->get_info($item_id)->valor_cnpj;
			}
		}
		$item = array(($insertkey) =>
			array(
				'item_id' => $item_id,
				'line' => $insertkey,
				'name' => $this->CI->Item->get_info($item_id)->name,
				'item_number' => $this->CI->Item->get_info($item_id)->item_number,
				'description' => $description != null ? $description : $this->CI->Item->get_info($item_id)->description,
				'serialnumber' => $serialnumber != null ? $serialnumber : '',
				'allow_alt_description' => $this->CI->Item->get_info($item_id)->allow_alt_description,
				'is_serialized' => $this->CI->Item->get_info($item_id)->is_serialized,
				'quantity' => $quantity,
				'discount' => $discount,
				'price' => $valorItem,
				'valor_cnpj' =>  $this->CI->Item->get_info($item_id)->valor_cnpj
			)
		);

		//Item already exists and is not serialized, add to quantity
		if ($itemalreadyinsale && ($this->CI->Item->get_info($item_id)->is_serialized == 0)) {
			$items[$updatekey]['quantity'] += $quantity;
		} else {
			//add to existing array
			$items += $item;
		}

		$this->set_cart($items);
		return true;
	}

	public function validarCNPJ($documento)
	{
		// Remove todos os caracteres exceto os números
		$documento = preg_replace('/[^0-9]/', '', $documento);

		// Verifica se o CNPJ possui 14 dígitos
		if (strlen($documento) != 14) {
			return false;
		}

		// Verifica se todos os dígitos são iguais, o que não é permitido em um CNPJ válido
		if (preg_match('/(\d)\1{13}/', $documento)) {
			return false;
		}

		// Calcula o primeiro dígito verificador
		$soma = 0;
		for ($i = 0; $i < 4; $i++) {
			$soma += $documento[$i] * (5 - $i);
		}
		for ($i = 4; $i < 12; $i++) {
			$soma += $documento[$i] * (13 - $i);
		}
		$digitoVerificador1 = 11 - ($soma % 11);
		if ($digitoVerificador1 >= 10) {
			$digitoVerificador1 = 0;
		}

		// Calcula o segundo dígito verificador
		$soma = 0;
		for ($i = 0; $i < 5; $i++) {
			$soma += $documento[$i] * (6 - $i);
		}
		for ($i = 5; $i < 13; $i++) {
			$soma += $documento[$i] * (14 - $i);
		}
		$digitoVerificador2 = 11 - ($soma % 11);
		if ($digitoVerificador2 >= 10) {
			$digitoVerificador2 = 0;
		}

		// Verifica se os dígitos verificadores calculados são iguais aos dígitos informados
		if ($documento[12] == $digitoVerificador1 && $documento[13] == $digitoVerificador2) {
			return true;
		} else {
			return false;
		}
	}

	function out_of_stock($item_id)
	{
		//make sure item exists
		if (!$this->CI->Item->exists($item_id)) {
			//try to get item id given an item_number
			$item_id = $this->CI->Item->get_item_id($item_id);

			if (!$item_id)
				return false;
		}

		$item = $this->CI->Item->get_info($item_id);
		$quanity_added = $this->get_quantity_already_added($item_id);

		if ($item->quantity - $quanity_added < 0) {
			return true;
		}

		return false;
	}

	function check_replacement_level($item_id)
	{
		//make sure item exists
		if (!$this->CI->Item->exists($item_id)) {
			//try to get item id given an item_number
			$item_id = $this->CI->Item->get_item_id($item_id);

			if (!$item_id)
				return false;
		}

		$item = $this->CI->Item->get_info($item_id);
		$quanity_added = $this->get_quantity_already_added($item_id);

		if ($item->quantity - $quanity_added <= $item->reorder_level) {
			return true;
		}

		return false;
	}


	function get_quantity_already_added($item_id)
	{
		$items = $this->get_cart();
		$quanity_already_added = 0;
		foreach ($items as $item) {
			if ($item['item_id'] == $item_id) {
				$quanity_already_added += $item['quantity'];
			}
		}

		return $quanity_already_added;
	}

	function get_item_id($line_to_get)
	{
		$items = $this->get_cart();

		foreach ($items as $line => $item) {
			if ($line == $line_to_get) {
				return $item['item_id'];
			}
		}

		return -1;
	}

	function edit_item($line, $description, $serialnumber, $quantity, $discount, $price)
	{
		$items = $this->get_cart();
		if (isset($items[$line])) {
			$items[$line]['description'] = $description;
			$items[$line]['serialnumber'] = $serialnumber;
			$items[$line]['quantity'] = $quantity;
			$items[$line]['discount'] = $discount;
			$items[$line]['price'] = $price;
			$this->set_cart($items);
		}

		return false;
	}

	function is_valid_receipt($receipt_sale_id)
	{
		//POS #
		$pieces = explode(' ', $receipt_sale_id);

		if (count($pieces) == 2) {
			return $this->CI->Sale->exists($pieces[1]);
		}

		return false;
	}

	function is_valid_item_kit($item_kit_id)
	{
		//KIT #
		$pieces = explode(' ', $item_kit_id);

		if (count($pieces) == 2) {
			return $this->CI->Item_kit->exists($pieces[1]);
		}

		return false;
	}

	function return_entire_sale($receipt_sale_id)
	{
		//POS #
		$pieces = explode(' ', $receipt_sale_id);
		$sale_id = $pieces[1];

		$this->empty_cart();
		$this->remove_customer();

		foreach ($this->CI->Sale->get_sale_items($sale_id)->result() as $row) {
			$this->add_item($row->item_id, -$row->quantity_purchased, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber);
		}
		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}

	function add_item_kit($external_item_kit_id)
	{
		//KIT #
		$pieces = explode(' ', $external_item_kit_id);
		$item_kit_id = $pieces[1];

		foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item) {
			$this->add_item($item_kit_item['item_id'], $item_kit_item['quantity']);
		}
	}

	function copy_entire_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach ($this->CI->Sale->get_sale_items($sale_id)->result() as $row) {
			$this->add_item($row->item_id, $row->quantity_purchased, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber);
		}
		foreach ($this->CI->Sale->get_sale_payments($sale_id)->result() as $row) {
			$this->add_payment($row->payment_type, $row->payment_amount);
		}
		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}

	function copy_entire_suspended_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach ($this->CI->Sale_suspended->get_sale_items($sale_id)->result() as $row) {
			$this->add_item($row->item_id, $row->quantity_purchased, $row->discount_percent, $row->item_unit_price, $row->description, $row->serialnumber);
		}
		foreach ($this->CI->Sale_suspended->get_sale_payments($sale_id)->result() as $row) {
			$this->add_payment($row->payment_type, $row->payment_amount);
		}
		$this->set_customer($this->CI->Sale_suspended->get_customer($sale_id)->person_id);
		$this->set_comment($this->CI->Sale_suspended->get_comment($sale_id));
	}

	function delete_item($line)
	{
		$items = $this->get_cart();
		unset($items[$line]);
		$this->set_cart($items);
	}

	function empty_cart()
	{
		$this->CI->session->unset_userdata('cart');
	}

	function remove_customer()
	{
		$this->CI->session->unset_userdata('customer');
	}

	function clear_mode()
	{
		$this->CI->session->unset_userdata('sale_mode');
	}

	function clear_all()
	{
		$this->clear_mode();
		$this->empty_cart();
		$this->clear_comment();
		$this->clear_email_receipt();
		$this->empty_payments();
		$this->remove_customer();
	}

	function get_taxes()
	{
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);

		//Do not charge sales tax if we have a customer that is not taxable
		if (!$customer->taxable and $customer_id != -1) {
			return array();
		}

		$taxes = array();
		foreach ($this->get_cart() as $line => $item) {
			$tax_info = $this->CI->Item_taxes->get_info($item['item_id']);

			foreach ($tax_info as $tax) {
				$name = $tax['percent'] . '% ' . $tax['name'];
				$tax_amount = ($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100) * (($tax['percent']) / 100);


				if (!isset($taxes[$name])) {
					$taxes[$name] = 0;
				}
				$taxes[$name] += $tax_amount;
			}
		}

		return $taxes;
	}

	function get_subtotal()
	{
		$subtotal = 0;
		foreach ($this->get_cart() as $item) {
			$subtotal += ($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100);
		}
		return to_currency_no_money($subtotal);
	}

	function get_total()
	{
		$total = 0;
		foreach ($this->get_cart() as $item) {
			$total += ($item['price'] * $item['quantity'] - $item['price'] * $item['quantity'] * $item['discount'] / 100);
		}

		foreach ($this->get_taxes() as $tax) {
			$total += $tax;
		}

		return to_currency_no_money($total);
	}
}
