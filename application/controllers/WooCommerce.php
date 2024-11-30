<?php
require_once("secure_area.php");
class WooCommerce extends Secure_area
{
    function __construct()
    {
        parent::__construct('woocommerce');
        $this->load->model('WooCommerce_model'); // Carregar o modelo

    }

    public function index()
{
    // Carregar o modelo
    $this->load->model('WooCommerce_model');

    // Pegar itens pendentes de sincronização
    $data['pending_sync_items'] = $this->WooCommerce_model->get_pending_sync_items();

    // Pegar itens pendentes de atualização
    $data['pending_update_items'] = $this->WooCommerce_model->get_pending_update_items();
     // Obter a quantidade de produtos sincronizados
     $total_products = $this->WooCommerce_model->count_products_in_woocommerce();

     // Passar o valor para a view
     $data['total_products'] = $total_products; 

    // Carregar a view com os dados
    $this->load->view('woocommerce/index', $data);
}
}
