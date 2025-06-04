from flask import Blueprint, request, jsonify
from flask_jwt_extended import jwt_required
from app import get_connection
from datetime import datetime

embalagens_bp = Blueprint('embalagens', __name__, url_prefix='/embalagens')

@embalagens_bp.route('/', methods=['POST'])
@jwt_required()
def cadastrar_embalagem():
    data = request.get_json()
    codigo_rfid = data['codigo_rfid']
    produto = data['produto']
    data_registro = datetime.utcnow().strftime('%Y-%m-%d %H:%M:%S')

    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("INSERT INTO embalagens (codigo_rfid, produto, data_registro) VALUES (%s, %s, %s)",
                           (codigo_rfid, produto, data_registro))
        connection.commit()

    return jsonify({'msg': 'Embalagem cadastrada com sucesso'})

@embalagens_bp.route('/', methods=['GET'])
@jwt_required()
def listar_embalagens():
    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM embalagens")
            embalagens = cursor.fetchall()
    return jsonify(embalagens)

