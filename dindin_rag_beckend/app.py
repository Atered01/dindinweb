# app.py
from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
import json
import re

# (Assumindo que você tem um arquivo db.py como no exemplo anterior)
# Se não tiver, precisará de uma função para conectar e executar SQL.
# Exemplo de db.py:
# import mysql.connector
# def executar_sql(query, params=None):
#     # ... (código de conexão e execução)
#     return resultados

from db import executar_sql 

app = Flask(__name__)
CORS(app) # Permite que seu frontend JS chame esta API

# --- O CÉREBRO DO RAG ---
# Aqui, fornecemos o esquema do seu banco de dados para a IA ter contexto.
# Isso é a parte de "Retrieval" (Busca/Recuperação de contexto).
DATABASE_SCHEMA = """
-- Tabela para usuários finais do sistema.
CREATE TABLE `usuarios` (
  `id_personalizado` varchar(20) NOT NULL PRIMARY KEY,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(2) DEFAULT NULL,
  `data_cadastro` datetime,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
);

-- Tabela para empresas parceiras.
CREATE TABLE `empresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome_empresa` varchar(100) NOT NULL,
  `email_contato` varchar(100) NOT NULL UNIQUE
);

-- Tabela para os produtos que podem ser reciclados.
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `empresa_id` int(11) NOT NULL,
  `nome_produto` varchar(100) NOT NULL,
  `pontos_ddv` int(11) NOT NULL,
  FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
);

-- Tabela para as recompensas que os usuários podem resgatar.
CREATE TABLE `recompensas` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome` varchar(255) NOT NULL,
  `custo_em_ddv` decimal(10,2) NOT NULL
);

-- Tabela que registra os resgates de recompensas.
CREATE TABLE `recompensas_resgatadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `usuario_id_personalizado` varchar(20) NOT NULL,
  `recompensa_id` int(11) NOT NULL,
  `data_resgate` datetime,
  FOREIGN KEY (`usuario_id_personalizado`) REFERENCES `usuarios` (`id_personalizado`),
  FOREIGN KEY (`recompensa_id`) REFERENCES `recompensas` (`id`)
);

-- Tabela de estatísticas dos usuários.
CREATE TABLE `estatisticas_usuario` (
  `usuario_id_personalizado` varchar(20) NOT NULL PRIMARY KEY,
  `itens_reciclados` int(11) DEFAULT 0,
  `saldo_ddv` decimal(10,2) DEFAULT 0.00,
  FOREIGN KEY (`usuario_id_personalizado`) REFERENCES `usuarios` (`id_personalizado`)
);
"""

# --- Funções Seguras para Acessar o Banco ---
# Cada função corresponde a uma "capacidade" do nosso assistente.

def get_total_users():
    """Retorna a contagem total de usuários."""
    sql = "SELECT COUNT(*) as total FROM usuarios;"
    result = executar_sql(sql)
    return result

def get_partner_companies():
    """Retorna o nome de todas as empresas parceiras."""
    sql = "SELECT nome_empresa FROM empresas ORDER BY nome_empresa;"
    result = executar_sql(sql)
    return result

def get_most_expensive_rewards():
    """Retorna as 3 recompensas com maior custo em pontos."""
    sql = "SELECT nome, custo_em_ddv FROM recompensas ORDER BY custo_em_ddv DESC LIMIT 3;"
    result = executar_sql(sql)
    return result
    
def get_most_redeemed_rewards():
    """Retorna as recompensas mais resgatadas pelos usuários."""
    sql = """
        SELECT r.nome, COUNT(rr.id) as total_resgates
        FROM recompensas_resgatadas rr
        JOIN recompensas r ON rr.recompensa_id = r.id
        GROUP BY r.nome
        ORDER BY total_resgates DESC
        LIMIT 3;
    """
    result = executar_sql(sql)
    return result

# Mapeia a intenção (string) para a função Python
FUNCTION_MAP = {
    "get_total_users": get_total_users,
    "get_partner_companies": get_partner_companies,
    "get_most_expensive_rewards": get_most_expensive_rewards,
    "get_most_redeemed_rewards": get_most_redeemed_rewards
}

# --- PROMPT PARA A IA ---
# Este é o prompt que instrui a IA a agir como um roteador de intenções.
PROMPT_TEMPLATE = f"""
Você é um assistente de IA especialista em analisar perguntas de usuários e direcioná-las para a função correta.
Com base no esquema do banco de dados abaixo e na pergunta do usuário, sua única tarefa é retornar um objeto JSON com a chave "intent" que corresponde à função correta a ser chamada.

### Esquema do Banco de Dados:
{DATABASE_SCHEMA}

### Funções Disponíveis:
- `get_total_users`: Use para perguntas sobre a quantidade total de usuários.
- `get_partner_companies`: Use para perguntas que pedem a lista de empresas parceiras.
- `get_most_expensive_rewards`: Use para perguntas sobre as recompensas mais caras ou com maior custo de pontos.
- `get_most_redeemed_rewards`: Use para perguntas sobre as recompensas mais populares ou mais resgatadas.
- `unknown`: Use se a pergunta não corresponder a nenhuma das funções acima.

### Pergunta do Usuário:
"{{user_question}}"

### Resposta (APENAS JSON):
"""

def chamar_ollama(prompt, model="mistral:instruct"):
    """Função para chamar a API do Ollama."""
    url = "http://localhost:11434/api/generate"
    try:
        response = requests.post(url, json={
            "model": model,
            "prompt": prompt,
            "stream": False,
            "format": "json" # Pedimos explicitamente JSON para o Ollama
        }, timeout=180)
        response.raise_for_status()
        
        # A resposta do Ollama com format:json é uma string JSON, então precisamos parsear
        return json.loads(response.json().get("response", "{}"))

    except requests.exceptions.RequestException as e:
        print(f"Erro ao chamar Ollama: {e}")
        return None
    except json.JSONDecodeError as e:
        print(f"Erro ao decodificar JSON da resposta do Ollama: {e}")
        return None

@app.route("/rag", methods=["POST"])
def rag_endpoint():
    data = request.get_json()
    user_prompt = data.get("prompt", "")

    if not user_prompt:
        return jsonify({"erro": "Prompt ausente."}), 400

    try:
        # 1. Análise de Intenção
        final_prompt = PROMPT_TEMPLATE.format(user_question=user_prompt)
        ia_response = chamar_ollama(final_prompt)
        
        if not ia_response or "intent" not in ia_response:
             return jsonify({"resposta": "Desculpe, não consegui entender sua solicitação. Poderia reformular a pergunta?"})

        intent = ia_response.get("intent")
        
        # 2. Execução Segura
        if intent in FUNCTION_MAP:
            # Chama a função Python correspondente à intenção
            function_to_call = FUNCTION_MAP[intent]
            db_data = function_to_call()
            
            # Retorna os dados diretamente para o frontend
            return jsonify({
                "resposta": f"Encontrei estas informações sobre '{user_prompt}':",
                "dados": db_data,
                "debug_info": {"intent_detected": intent}
            })
        else:
            # Se a intenção não for reconhecida, retorna uma resposta padrão.
            return jsonify({
                "resposta": "Não tenho a capacidade de responder a essa pergunta. Tente uma das sugestões ou reformule a sua questão.",
                "debug_info": {"intent_detected": intent}
            })

    except Exception as e:
        print(f"Erro no endpoint /rag: {e}")
        return jsonify({"erro": f"Ocorreu um erro interno: {e}"}), 500

if __name__ == "__main__":
    # Use 0.0.0.0 para tornar acessível a partir do seu PHP (se não estiver na mesma máquina)
    app.run(host='0.0.0.0', port=5000, debug=True)