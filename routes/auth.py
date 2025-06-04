from flask import Blueprint, request ,jsonify
from flask_jwt_extended import create_access_token, jwt_required, get_jwt_identity
from werkzeug.security import generate_password_hash, check_password_hash
from app import get_connection

auth_bp = Blueprint('auth', __name__, url_prefix='/auth')


@auth_bp.route('/register', methods=['POST'])
def register():
    data = request.get_json()
    nome = data['nome']
    email = data['email']
    senha = generate_password_hash(data['senha'])

    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM usuarios WHERE email = %s", (email,))
            if cursor.fetchone():
                return jsonify({'msg': 'Usuário já existe'}), 409
            cursor.execute("INSERT INTO usuarios (nome, email, senha) VALUES (%s, %s, %s)", (nome, email, senha))
        connection.commit()

    return jsonify({'msg': 'Usuário registrado com sucesso'})

@auth_bp.route('/login', methods=['POST'])
def login():
    data = request.get_json()
    email = data['email']
    senha = data['senha']

    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT * FROM usuarios WHERE email = %s", (email,))
            usuario = cursor.fetchone()

    if not usuario or not check_password_hash(usuario['senha'], senha):
        return jsonify({'msg': 'Credenciais inválidas'}), 401

    access_token = create_access_token(identity=usuario['id'])
    return jsonify({'access_token': access_token})

@auth_bp.route('/perfil', methods=['GET'])
@jwt_required()
def perfil():
    user_id = get_jwt_identity()
    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute("SELECT id, nome, email, pontuacao FROM usuarios WHERE id = %s", (user_id,))
            usuario = cursor.fetchone()
    print("Dados do perfil (backend):", usuario)  # Adicionado para debug
    return jsonify(usuario)