from flask import Flask, request, jsonify, render_template # render_template foi adicionado
from flask_jwt_extended import JWTManager
from flask_cors import CORS
import pymysql

jwt = JWTManager()

def create_app():
    app = Flask(__name__, template_folder='frontend') # Cria a instância de app aqui

    app.config['JWT_SECRET_KEY'] = 'sua-chave-jwt-super-secreta' 
    
    CORS(app)
    jwt.init_app(app) 

    @app.route("/")
    def index_page(): # Renomeei a função para evitar conflito com a global index se houvesse
        return render_template("cadastro.html")

    # Adicione uma rota para servir login.html também, se ainda não tiver
    @app.route("/login")
    def login_page():
        return render_template("login.html")

    from routes.auth import auth_bp 
    from routes.embalagens import embalagens_bp 
    from routes.descartes import descartes_bp   
    from routes.recompensas import recompensas_bp 
    from routes.pontos import pontos_bp 

    app.register_blueprint(auth_bp)
    app.register_blueprint(embalagens_bp)
    app.register_blueprint(descartes_bp)
    app.register_blueprint(recompensas_bp)
    app.register_blueprint(pontos_bp) 

    return app # Retorna a instância de app completamente configurada

def get_connection():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='',
        db='embalagens_db',
        cursorclass=pymysql.cursors.DictCursor
    )

if __name__ == '__main__':
    app = create_app()
    app.run(debug=True, port=5000)