from flask import Blueprint, request, jsonify
from flask_jwt_extended import jwt_required, get_jwt_identity
import pymysql

from app import get_connection
pontos_bp = Blueprint('pontos', __name__, url_prefix='/pontos')

@pontos_bp.route('', methods=['GET'])
@jwt_required()
def get_user_score():
    """
    Rota para buscar a pontuação do usuário logado.
    O ID do usuário é obtido a partir do token JWT.
    """
    current_user_email = get_jwt_identity()
    
    conn = None
    cursor = None
    try:
        conn = get_connection()
        cursor = conn.cursor()
        
        sql = "SELECT id, pontuacao FROM usuarios WHERE email = %s"
        cursor.execute(sql, (current_user_email,))
        user_data = cursor.fetchone()
        
        if user_data:
            return jsonify({
                "id_usuario": user_data['id'],
                "pontuacao": user_data['pontuacao']
            }), 200
        else:
            return jsonify({"msg": "Usuário não encontrado."}), 404
            
    except pymysql.MySQLError as e:
        print(f"Erro no banco de dados ao buscar pontuação: {e}")
        return jsonify({"msg": "Erro ao buscar pontuação."}), 500
    except Exception as e:
        print(f"Erro inesperado ao buscar pontuação: {e}")
        return jsonify({"msg": "Ocorreu um erro inesperado."}), 500
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

@pontos_bp.route('/adicionar', methods=['POST'])
@jwt_required()
def add_user_score():
    """
    Rota para adicionar ou subtrair pontos da conta do usuário logado.
    A quantidade de pontos é recebida no corpo da requisição JSON.
    """
    current_user_email = get_jwt_identity()
    data = request.get_json()

    if not data or 'pontos' not in data or not isinstance(data['pontos'], (int, float)):
        return jsonify({"msg": "Corpo da requisição inválido. 'pontos' é obrigatório e deve ser um número."}), 400

    pontos_a_modificar = int(data['pontos'])
    
    conn = None
    cursor = None
    try:
        conn = get_connection()
        cursor = conn.cursor()
        
    
        sql_get_user_id = "SELECT id FROM usuarios WHERE email = %s"
        cursor.execute(sql_get_user_id, (current_user_email,))
        user = cursor.fetchone()

        if not user:
            return jsonify({"msg": "Usuário não encontrado."}), 404
        
        user_id = user['id']

        sql_update = "UPDATE usuarios SET pontuacao = pontuacao + %s WHERE id = %s"
        cursor.execute(sql_update, (pontos_a_modificar, user_id))
        
    
        if cursor.rowcount == 0:
            conn.rollback()
            return jsonify({"msg": "Nenhum usuário encontrado para atualizar a pontuação."}), 404

        sql_get_new_score = "SELECT pontuacao FROM usuarios WHERE id = %s"
        cursor.execute(sql_get_new_score, (user_id,))
        updated_user = cursor.fetchone()
        
        conn.commit()
        
        return jsonify({
            "msg": "Pontuação atualizada com sucesso!",
            "nova_pontuacao": updated_user['pontuacao']
        }), 200
            
    except pymysql.MySQLError as e:
        if conn:
            conn.rollback() 
        print(f"Erro no banco de dados ao atualizar pontuação: {e}")
        return jsonify({"msg": "Erro ao atualizar pontuação."}), 500
    except Exception as e:
        if conn:
            conn.rollback()
        print(f"Erro inesperado ao atualizar pontuação: {e}")
        return jsonify({"msg": "Ocorreu um erro inesperado."}), 500
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()