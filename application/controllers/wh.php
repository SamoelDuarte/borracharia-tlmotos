<?php

class WH extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function woocommerceOrders()
    {
        // Lê o conteúdo da requisição (JSON enviado pelo WooCommerce)
        $data = file_get_contents('php://input');
        $decoded_data = json_decode($data, true);

        try {
            // Inicia a transação
            $this->db->trans_begin();

            // Extrai as informações do pedido
            $order_id = $decoded_data['id'];
            $status = $decoded_data['status'];
            $created_at = date('Y-m-d H:i:s', strtotime($decoded_data['date_created']));
            $updated_at = date('Y-m-d H:i:s', strtotime($decoded_data['date_modified']));
            $total_amount = $decoded_data['total'];
            $discount_total = $decoded_data['discount_total'];
            $shipping_cost = $decoded_data['shipping_total'];
            $customer_id = $decoded_data['customer_id'];
            $payment_method = $decoded_data['payment_method'];
            $customer_ip = $decoded_data['customer_ip_address'];
            $customer_user_agent = $decoded_data['customer_user_agent'];
            $order_key = $decoded_data['order_key'];
            $order_number = $decoded_data['number'];

            // Verifica se o pedido já existe no banco de dados
            $this->db->where('order_id', $order_id);
            $query = $this->db->get('orders');

            if ($query->num_rows() > 0) {
                // Atualiza o pedido
                $data = array(
                    'status' => $status,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                    'total_amount' => $total_amount,
                    'discount_total' => $discount_total,
                    'shipping_cost' => $shipping_cost,
                    'customer_id' => $customer_id,
                    'payment_method' => $payment_method,
                    'customer_ip' => $customer_ip,
                    'customer_user_agent' => $customer_user_agent,
                    'order_key' => $order_key,
                    'order_number' => $order_number
                );
                $this->db->where('order_id', $order_id);
                $this->db->update('orders', $data);
            } else {
                // Insere um novo pedido
                $data = array(
                    'order_id' => $order_id,
                    'status' => $status,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                    'total_amount' => $total_amount,
                    'discount_total' => $discount_total,
                    'shipping_cost' => $shipping_cost,
                    'customer_id' => $customer_id,
                    'payment_method' => $payment_method,
                    'customer_ip' => $customer_ip,
                    'customer_user_agent' => $customer_user_agent,
                    'order_key' => $order_key,
                    'order_number' => $order_number
                );
                $this->db->insert('orders', $data);
                $this->update_inventory_from_order($decoded_data);
            }

            // Se o status do pedido for cancelado, atualiza o estoque
            if ($status == 'cancelled') {
                $this->restore_inventory_from_order($decoded_data);
            } else {
                // Processa os itens do pedido
                foreach ($decoded_data['line_items'] as $item) {
                    $item_id = $item['id'];
                    $product_id = $item['product_id'];
                    $product_name = $item['name'];
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    $subtotal = $item['subtotal'];
                    $total = $item['total'];

                    // Verifica se o item já existe
                    $this->db->where('item_id', $item_id);
                    $this->db->where('order_id', $order_id);
                    $query = $this->db->get('order_items');

                    if ($query->num_rows() > 0) {
                        // Atualiza o item
                        $data = array(
                            'product_id' => $product_id,
                            'product_name' => $product_name,
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'total' => $total
                        );
                        $this->db->where('item_id', $item_id);
                        $this->db->where('order_id', $order_id);
                        $this->db->update('order_items', $data);
                    } else {
                        // Insere um novo item
                        $data = array(
                            'item_id' => $item_id,
                            'order_id' => $order_id,
                            'product_id' => $product_id,
                            'product_name' => $product_name,
                            'quantity' => $quantity,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'total' => $total
                        );
                        $this->db->insert('order_items', $data);
                    }
                }
            }

            // Confirma a transação
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Erro ao salvar pedido. Consulta: ' . $this->db->last_query());
                log_message('error', 'Erro ao salvar pedido. Erro DB: ' . $this->db->error());
                $this->db->trans_rollback();
                throw new Exception('Erro ao salvar o pedido e seus itens no banco de dados.');
            } else {
                // Confirma a transação
                $this->db->trans_commit();
            }
        } catch (Exception $e) {
            log_message('error', 'Erro no processamento do pedido: ' . $e->getMessage());
            echo json_encode(array("error" => "Erro ao processar o pedido: " . $e->getMessage()));
        }
    }

    function restore_inventory_from_order($order_data)
    {
        // Loop através de cada item no pedido
        foreach ($order_data['line_items'] as $item) {
            $item_id = $item['product_id'];
            $quantity_sold = $item['quantity'];

            // Recupera as informações atuais do estoque deste item
            $cur_item_info = $this->Item->get_info_wc($item_id);

            // Prepara os dados de transação de inventário
            $inv_data = array(
                'trans_date' => date('Y-m-d H:i:s'),
                'trans_items' => $cur_item_info->item_id,
                'trans_user' => '1',
                'trans_comment' => 'CANCELAMENTO PEDIDO N°' . $order_data['number'], // Pedido cancelado
                'trans_inventory' => $quantity_sold // Aumenta o estoque de volta
            );

            // Insere a transação de inventário
            $this->Inventory->insert($inv_data);

            // Atualiza a quantidade de estoque (aumentando o estoque)
            $new_quantity = $cur_item_info->quantity + $quantity_sold;

            // Prepara os dados do item para atualização de estoque
            $item_data = array(
                'quantity' => $new_quantity
            );

            // Salva a nova quantidade no estoque
            if ($this->Item->save($item_data, $cur_item_info->item_id)) {
                echo json_encode(array('success' => true, 'message' => 'Estoque restaurado para ' . $cur_item_info->name . ' (Pedido N°' . $order_data['number'] . ')', 'item_id' => $item_id));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Erro ao restaurar estoque para ' . $cur_item_info->name, 'item_id' => -1));
            }
        }
    }
    function update_inventory_from_order($order_data)
    {
        // Loop through each item in the order
        foreach ($order_data['line_items'] as $item) {
            // Get the product ID and the quantity sold
            $item_id = $item['product_id'];
            $quantity_sold = $item['quantity'];

            // Get the current stock information for this item
            $cur_item_info = $this->Item->get_info_wc($item_id);

            // print_r( $cur_item_info);
            // exit;

            // Prepare the inventory update data
            $inv_data = array(
                'trans_date' => date('Y-m-d H:i:s'),
                'trans_items' => $cur_item_info->item_id,
                'trans_user' => '1',
                'trans_comment' => 'VENDA SITE PEDIDO N°' . $order_data['number'], // Use the order number
                'trans_inventory' => -$quantity_sold // Decrease inventory by the quantity sold
            );

            // Insert the inventory transaction
            $this->Inventory->insert($inv_data);

            // Update the stock quantity (decrease the stock)
            $new_quantity = $cur_item_info->quantity - $quantity_sold;

            // Prepare the item data for updating the stock quantity
            $item_data = array(
                'quantity' => $new_quantity
            );

            // Save the new stock quantity for the item
            if ($this->Item->save($item_data, $cur_item_info->item_id)) {
                echo json_encode(array('success' => true, 'message' => 'Estoque atualizado para ' . $cur_item_info->name . ' (Pedido N°' . $order_data['number'] . ')', 'item_id' => $item_id));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar estoque para ' . $cur_item_info->name, 'item_id' => -1));
            }
        }
    }
}
