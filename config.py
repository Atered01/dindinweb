class Config:
    SQLALCHEMY_DATABASE_URI = 'mysql+pymysql://usuario:senha@localhost/embalagens_db'
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    SECRET_KEY = 'sua-chave-secreta'
    JWT_SECRET_KEY = 'jwt-chave-super-secreta'
