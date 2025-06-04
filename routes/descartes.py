from flask import Blueprint, request, jsonify
from flask_jwt_extended import jwt_required, get_jwt_identity
from app import get_connection
from datetime import datetime

descartes_bp = Blueprint('descartes', __name__, url_prefix='/descartes')

@descartes_bp.route('/', methods=['POST'])
@jwt_required()
def registrar_descarte():
    data = request.get_json()
    id_embalagem = data['id_embalagem']
    localizacao = data.get('localizacao', None)
    id_usuario = get_jwt_identity()
    data_descarte = datetime.utcnow().strftime('%Y-%m-%d %H:%M:%S')

    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("INSERT INTO descartes (id_embalagem, id_usuario, data, localizacao) VALUES (%s, %s, %s, %s)",
                           (id_embalagem, id_usuario, data_descarte, localizacao))
            cursor.execute("UPDATE usuarios SET pontuacao = pontuacao + 10 WHERE id = %s", (id_usuario,))
        connection.commit()

    return jsonify({'msg': 'Descarte registrado com sucesso'})

@descartes_bp.route('/', methods=['GET'])
@jwt_required()
def listar_descartes():
    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM descartes")
            descartes = cursor.fetchall()
    return jsonify(descartes)
