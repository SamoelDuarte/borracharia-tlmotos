<?php
require_once("secure_area.php");
class Orders extends Secure_area
{
	function __construct()
	{
		parent::__construct('orders');
	
	}

	function index()
{
    $this->load->model('order');
    $start_date = $this->input->get('start_date');
    $end_date = $this->input->get('end_date');
    $status = $this->input->get('status');

    $data['orders'] = $this->order->get_filtered_orders($start_date, $end_date, $status);
    $this->load->view('orders/manage', $data);
}

}
