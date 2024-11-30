<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Automattic\WooCommerce\Client;

require_once __DIR__ . '/../../vendor/autoload.php';

class WooCommerceLibrary
{
    protected $client;
    protected $config;

    public function __construct()
    {
        // Obtenha a instância do CodeIgniter
        $CI = &get_instance();

        // Carregue o modelo Appconfig
        $CI->load->model('appconfig');

        // Chame a função para obter as credenciais
        $wc_credentials = $CI->appconfig->get_wc_credentials();

        // Acesse os valores diretamente
        $url_wc = $wc_credentials['url_wc'] ?? null;
        $consumer_key = $wc_credentials['consumer_key'] ?? null;
        $secret_key = $wc_credentials['secret_key'] ?? null;

        // Inicialize as configurações (opcional, se necessário)
        $this->config = $CI->config;

        // Inicialize o cliente WooCommerce
        $this->client = new Client(
            $url_wc,
            $consumer_key,
            $secret_key,
            [
                'version' => 'wc/v3',
                'timeout' => 90
            ]
        );
    }


    // Métodos restantes continuam os mesmos
    public function get_products()
    {
        return $this->client->get('products');
    }

    public function get_products_categories()
    {
        return $this->client->get('products/categories', ['per_page' => 99]);
    }

    public function create_product($data)
    {
        return $this->client->post('products', $data, ['timeout' => 30]);
    }

    public function update_product($product_id, $data)
    {
        return $this->client->put('products/' . $product_id, $data, ['timeout' => 30]);
    }

    public function delete_product($product_id)
    {
        try {
            return $this->client->delete('products/' . $product_id);
        } catch (Exception $e) {
            log_message('error', 'Erro ao excluir o produto no WooCommerce: ' . $e->getMessage());
            return false;
        }
    }
}
