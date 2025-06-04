from flask import Blueprint, request, jsonify
from flask_jwt_extended import jwt_required, get_jwt_identity
from app import get_connection

recompensas_bp = Blueprint('recompensas', __name__, url_prefix='/recompensas')

@recompensas_bp.route('/', methods=['GET'])
def listar_recompensas():
    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM recompensas")
            recompensas = cursor.fetchall()
    return jsonify(recompensas)

@recompensas_bp.route('/trocar/<int:id>', methods=['POST'])
@jwt_required()
def trocar_recompensa(id):
    user_id = get_jwt_identity()
    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM recompensas WHERE id = %s", (id,))
            recompensa = cursor.fetchone()
            cursor.execute("SELECT pontuacao FROM usuarios WHERE id = %s", (user_id,))
            usuario = cursor.fetchone()

            if not recompensa or not usuario:
                return jsonify({'msg': 'Dados inválidos'}), 404

            if usuario['pontuacao'] < recompensa['pontos_necessarios']:
                return jsonify({'msg': 'Pontos insuficientes'}), 403

            cursor.execute("UPDATE usuarios SET pontuacao = pontuacao - %s WHERE id = %s",
                           (recompensa['pontos_necessarios'], user_id))
        connection.commit()

    return jsonify({'msg': 'Recompensa trocada com sucesso'})