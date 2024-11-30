<?php
class WooCommerce_model  extends CI_Model
{
    // Função para pegar itens pendentes de sincronização
    public function get_pending_sync_items()
    {
        // Selecionar todos os campos
        $this->db->select('*');
        $this->db->from('items');

        // Itens pendentes de sincronização: sinc_wc = 1, up_wc = 0 e id_wc é NULL
        $this->db->where('sinc_wc', 1);
        $this->db->where('up_wc', 0);
        $this->db->where('id_wc IS NULL'); // id_wc deve ser NULL

        // Obter os resultados
        $query = $this->db->get();

        // Retorna os itens encontrados
        return $query->result_array();
    }

    // Função para pegar itens pendentes de atualização
    public function get_pending_update_items()
    {
        // Selecionar todos os campos
        $this->db->select('*');
        $this->db->from('items');

        // Itens pendentes de atualização: sinc_wc = 1, up_wc = 1 e id_wc não é NULL
        $this->db->where('sinc_wc', 1);
        $this->db->where('up_wc', 1);
        $this->db->where('id_wc IS NOT NULL'); // id_wc não pode ser NULL

        // Obter os resultados
        $query = $this->db->get();

        // Retorna os itens encontrados
        return $query->result_array();
    }
    public function count_products_in_woocommerce()
{
    // Contar o número de produtos com id_wc não nulo (já sincronizados no WooCommerce)
    $this->db->select('COUNT(id_wc) as total_products');
    $this->db->from('items'); // Substitua "items" pelo nome da sua tabela de produtos

    // Filtrar os produtos que têm id_wc preenchido (não nulo)
    $this->db->where('id_wc IS NOT NULL');

    // Executar a consulta
    $query = $this->db->get();

    // Verificar se a consulta retornou algum resultado
    if ($query->num_rows() > 0) {
        // Retornar o total de produtos
        return $query->row()->total_products;
    } else {
        return 0; // Caso não tenha nenhum produto com id_wc
    }
}

}
