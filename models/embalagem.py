from app import db

class Embalagem(db.Model):
    __tablename__ = 'embalagens'

    id = db.Column(db.Integer, primary_key=True)
    codigo_rfid = db.Column(db.String(100), unique=True, nullable=False)
    produto = db.Column(db.String(100), nullable=False)
    data_registro = db.Column(db.DateTime, nullable=False)

    descartes = db.relationship('Descarte', backref='embalagem', lazy=True)

    def to_dict(self):
        return {
            'id': self.id,
            'codigo_rfid': self.codigo_rfid,
            'produto': self.produto,
            'data_registro': self.data_registro.isoformat()
        }

