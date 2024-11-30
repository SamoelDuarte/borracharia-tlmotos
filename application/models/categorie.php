<?php
class Categorie extends CI_Model
{
    // Função para buscar uma categoria pelo nome
    public function get_by_name($category_name)
    {
        // Busca na tabela pos_categories_products pela coluna 'name'
        $this->db->where('category_name', $category_name);
        $query = $this->db->get('pos_categories_products');

        // Verifica se a categoria foi encontrada e retorna o resultado
        if ($query->num_rows() > 0) {
            return $query->row(); // Retorna o objeto da categoria
        } else {
            return false; // Se não encontrou, retorna false
        }
    }

    // Função para buscar uma categoria pelo nome
    public function get_info($category_id)
    {
        // Busca na tabela pos_categories_products pela coluna 'name'
        $this->db->where('category_id', $category_id);
        $query = $this->db->get('pos_categories_products');

        // Verifica se a categoria foi encontrada e retorna o resultado
        if ($query->num_rows() > 0) {
            return $query->row(); // Retorna o objeto da categoria
        } else {
            return false; // Se não encontrou, retorna false
        }
    }
    // Busca todas as categorias com paginação e filtros
    public function get_all($limit = null, $offset = null, $search = null)
    {
        if ($search) {
            $this->db->like('category_name', $search);
        }

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        $query = $this->db->get('categories_products');
        return $query->result_array();
    }

    // Conta o número total de categorias, com ou sem filtros
    public function count_all($search = null)
    {
        if ($search) {
            $this->db->like('category_name', $search);
        }

        return $this->db->count_all_results('categories_products');
    }



    // Função para excluir uma categoria
    public function delete($category_id)
    {
        $this->db->where('category_id', $category_id);
        return $this->db->delete('pos_categories_products');
    }

    public function get_without_wc_id()
    {
        $this->db->where('wc_id', null);
        $this->db->limit(5);
        $query = $this->db->get('categories_products');


        // Verifica se a categoria foi encontrada e retorna o resultado
        if ($query->num_rows() > 0) {
            return $query->result(); // Retorna o objeto da categoria
        } else {
            return false; // Se não encontrou, retorna false
        }
    }

    public function is_category_in_use($category_id)
    {
        // Carregue o modelo de Item
        $this->load->model('Item');

        // Verifica se há itens associados à categoria
        return $this->Item->count_by_category($category_id) > 0; // Retorna true se houver itens
    }


    public function get_wc_id($item_id)
    {
        // Se a categoria foi encontrada, busca o wc_id da categoria na tabela de categorias
        $this->db->select('wc_id');  // Seleciona o wc_id
        $this->db->from('pos_categories_products');  // Supondo que a tabela de categorias seja 'pos_categories_products'
        $this->db->where('category_id', $item_id);
        $category_query = $this->db->get();
        $category = $category_query->row();

        if ($category) {
            return $category->wc_id;  // Retorna o wc_id da categoria
        }


        // Se não encontrar o item ou a categoria, retorna null
        return null;
    }


    // Função para inserir uma nova categoria
    public function insert($category_data)
    {
        // Insere os dados na tabela pos_categories_products
        $this->db->insert('pos_categories_products', $category_data);

        // Retorna o ID da nova categoria inserida
        return $this->db->insert_id();
    }
    // Função para atualizar uma categoria
    public function update($category_id, $data)
    {
        $this->db->where('category_id', $category_id);
        return $this->db->update('pos_categories_products', $data);
    }
}
