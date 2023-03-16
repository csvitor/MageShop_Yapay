[{
    "message_response": {
        "message": "error"
    },
    "error_response": {
        "validation_errors": [
            {
                "code": "3",
                "message": "não é válido",
                "field": "expdate_month",
                "message_complete": "Mês não é válido"
            }
        ]
    },
    "additional_data": {
        "transaction_id": 752227,
        "order_number": "752227",
        "status_id": 4,
        "status_name": "Aguardando Pagamento",
        "token_transaction": "57fc11e33bd334fd915a875ca6c57d5a"
    }
}


// error

{
    "message_response": {
        "message": "error"
    },
    "error_response": {
        "general_errors": [
            {
                "code": "003039",
                "message": "Vendedor inválido ou não encontrado"
            }
        ]
    },
    "additional_data": {
        "transaction_id": null,
        "order_number": null,
        "status_id": null,
        "status_name": null,
        "token_transaction": null
    }
}


//


{
    "message_response": {
        "message": "error"
    },
    "error_response": {
        "validation_errors": [
            {
                "code": "18",
                "message": "deve ser maior ou igual a 2023",
                "field": "expdate_year",
                "message_complete": "Ano deve ser maior ou igual a 2023"
            }
        ]
    },
    "additional_data": {
        "transaction_id": 752260,
        "order_number": "752260",
        "status_id": 4,
        "status_name": "Aguardando Pagamento",
        "token_transaction": "948a5083c3c69c52eda7758f2d951eaf"
    }
}

//

{
    "message_response": {
        "message": "error"
    },
    "error_response": {
        "validation_errors": [
            {
                "code": "3",
                "message": "não é válido",
                "field": "expdate_month",
                "message_complete": "Mês não é válido"
            }
        ]
    },
    "additional_data": {
        "transaction_id": 752261,
        "order_number": "752261",
        "status_id": 4,
        "status_name": "Aguardando Pagamento",
        "token_transaction": "1289392b7bb3a8a11e46bf8b0b60886f"
    }
}

// Reprovada
{
    "message_response": {
        "message": "success"
    },
    "data_response": {
        "transaction": {
            "order_number": "752267",
            "free": "mod_m1_mageshop",
            "transaction_id": 752267,
            "status_name": "Aguardando Pagamento",
            "status_id": 4,
            "token_transaction": "39d68a9af0c7ae4a0693243b7274013a",
            "payment": {
                "price_payment": "61.0",
                "price_original": "61.0",
                "payment_response": "Mensagem de venda fake",
                "payment_response_code": "12321",
                "url_payment": "",
                "qrcode_path": "",
                "qrcode_original_path": "",
                "tid": "1233",
                "brand_tid": "",
                "split": 2,
                "payment_method_id": 4,
                "payment_method_name": "Mastercard",
                "linha_digitavel": null,
                "card_token": "9a382bc4-3d2e-4e21-a476-6b75f5e403d7"
            },
            "customer": {
                "name": "Vitor Costa",
                "company_name": "",
                "trade_name": "",
                "cpf": "69577173640",
                "cnpj": ""
            }
        }
    }
}

// aprovado

{
    "message_response": {
        "message": "success"
    },
    "data_response": {
        "transaction": {
            "order_number": "752280",
            "free": "mod_m1_mageshop",
            "transaction_id": 752280,
            "status_name": "Aprovada",
            "status_id": 6,
            "token_transaction": "09cc674dd692b1dfd821af79daec4008",
            "payment": {
                "price_payment": "61.0",
                "price_original": "61.0",
                "payment_response": "Mensagem de venda fake",
                "payment_response_code": "12321",
                "url_payment": "",
                "qrcode_path": "",
                "qrcode_original_path": "",
                "tid": "1233",
                "brand_tid": "",
                "split": 1,
                "payment_method_id": 4,
                "payment_method_name": "Mastercard",
                "linha_digitavel": null,
                "card_token": "9a382bc4-3d2e-4e21-a476-6b75f5e403d7"
            },
            "customer": {
                "name": "Vitor Costa",
                "company_name": "",
                "trade_name": "",
                "cpf": "69577173640",
                "cnpj": ""
            }
        }
    }
}

{
    "message_response": {
        "message": "error"
    },
    "error_response": {
        "general_errors": [
            {
                "code": "003021",
                "message": "O vendedor não pode ser igual ao comprador"
            }
        ]
    },
    "additional_data": {
        "transaction_id": null,
        "order_number": null,
        "status_id": null,
        "status_name": null,
        "token_transaction": null
    }
}]

