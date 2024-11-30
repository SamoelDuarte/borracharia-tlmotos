<?php
class Order extends CI_Model
{
    public function get_all_orders()
    {
        $query = $this->db->get('orders');
        $orders = $query->result_array();

        // Aplica as traduções de status e método de pagamento
        foreach ($orders as &$order) {
            $order['status'] = $this->translate_status($order['status']);
            $order['payment_method'] = $this->translate_payment_method($order['payment_method']);
            // Formata a data de criação
            $order['created_at'] = $this->format_created_at($order['created_at']);
        }

        return $orders;
    }
    // Função para formatar a data de criação em tempo relativo
    private function format_created_at($created_at)
    {
        $timestamp = strtotime($created_at);
        $now = time();
        $difference = $now - $timestamp;

        // Condições para retornar o tempo relativo
        if ($difference < 3600) {
            // Se for dentro de uma hora, mostra em minutos
            $minutes = floor($difference / 60);
            return ($minutes <= 1) ? 'há 1 minuto' : "há $minutes minutos";
        } elseif ($difference < 86400 && date('d', $now) == date('d', $timestamp)) {
            // Se for hoje, mostra "hoje às HH:MM"
            return 'hoje às ' . date('H:i', $timestamp);
        } elseif ($difference < 172800) {
            // Se for ontem (menos de 48 horas), mostra "ontem"
            return 'ontem';
        } else {
            // Se for mais de 2 dias, formata como dd/mm/aaaa
            return date('d/m/Y', $timestamp);
        }
    }

    // Função para traduzir status para português
    private function translate_status($status)
    {
        $status_map = [
            'completed' => 'Concluído',
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'on-hold' => 'Em espera',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'failed' => 'Falhou'
        ];

        return isset($status_map[$status]) ? $status_map[$status] : $status;
    }

    // Função para traduzir método de pagamento para português
    private function translate_payment_method($payment_method)
    {
        $payment_method_map = [
            'paypal' => 'PayPal',
            'bacs' => 'Transferência Bancária',
            'cheque' => 'Cheque',
            'cod' => 'Pagamento na Entrega',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'woo-mercado-pago-custom' => 'Mercado Pago'
        ];

        return isset($payment_method_map[$payment_method]) ? $payment_method_map[$payment_method] : $payment_method;
    }

    function get_filtered_orders($start_date = null, $end_date = null, $status = null)
    {
        $this->db->select('*');
        $this->db->from('orders');

        // Aplica os filtros de data, se fornecidos
        if ($start_date) {
            $this->db->where('created_at >=', $start_date);
        }

        if ($end_date) {
            $this->db->where('created_at <=', $end_date);
        }

        // Aplica o filtro de status, se fornecido
        if ($status) {
            // Converte o status traduzido para o original (se necessário)
            $status_map = array_flip([
                'completed' => 'Concluído',
                'pending' => 'Pendente',
                'processing' => 'Processando',
                'on-hold' => 'Em espera',
                'cancelled' => 'Cancelado',
                'refunded' => 'Reembolsado',
                'failed' => 'Falhou'
            ]);

            $status = isset($status_map[$status]) ? $status_map[$status] : $status;
            $this->db->where('status', $status);
        }

        $query = $this->db->get();
        $orders = $query->result_array();

        // Aplica traduções e formatações aos resultados
        foreach ($orders as &$order) {
            $order['status'] = $this->translate_status($order['status']);
            $order['payment_method'] = $this->translate_payment_method($order['payment_method']);
            $order['created_at'] = $this->format_created_at($order['created_at']);
        }
        // print_r($orders);
        // exit;

        return $orders;
    }
}
