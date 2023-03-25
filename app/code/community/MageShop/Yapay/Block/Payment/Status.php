<?php 

class MageShop_Yapay_Block_Payment_Status extends Mage_Payment_Block_Info{

    public function _message($status_code)
    {
        switch ($status_code) {
            case 6:
                return "Olá %s,\n\r
                O seu pedido foi processado com sucesso! Confirmamos o pagamento e estamos trabalhando para preparar o seu pedido e enviá-lo o mais rápido possível.
                Em breve, você receberá um e-mail com as informações de rastreamento para acompanhar a entrega.";
            break;
            case 7:
            case 89:
                return "Olá %s,\n\r
                Lamentamos informar que o seu pedido foi cancelado devido ao não recebimento do pagamento.
                Caso você ainda tenha interesse em adquirir os produtos em questão, por favor, realize um novo pedido e efetue o pagamento corretamente.";
            break;
            case 24:
                return "Olá %s,\n\r
                O seu pedido foi recebido com sucesso e está em análise de pagamento. Assim que a transação for confirmada, enviaremos um e-mail para %s com as atualizações do seu pedido.";
            break;
            case 4:
            case 5:
            case 88:
            case 87:
                return "Olá %s,\n\r
                O seu pedido foi processado com sucesso! No entanto, precisamos confirmar o pagamento para prosseguir com a entrega. 
                Fique tranquilo, assim que recebermos a confirmação, enviaremos um e-mail para %s com as atualizações do seu pedido.";
            break;
            default:
                return "Olá %s,\n\r
                O seu pedido foi processado com sucesso! No entanto, precisamos confirmar o pagamento para prosseguir com a entrega. 
                Fique tranquilo, assim que recebermos a confirmação, enviaremos um e-mail para %s com as atualizações do seu pedido.";
        }
    }

    public function _status($status_code)
    {
        switch ($status_code) {
            case 6:
            return "yapay_status_peid_payment_cc";
            break;
            case 7:
            case 89:
            return "yapay_status_canceled_payment_cc";
            break;
            default:
            return "yapay_status_pending_payment_cc";
        }
    }
}