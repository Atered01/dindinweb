from app import db
from datetime import datetime

class Descarte(db.Model):
    __tablename__ = 'descartes'

    id = db.Column(db.Integer, primary_key=True)
    id_embalagem = db.Column(db.Integer, db.ForeignKey('embalagens.id'), nullable=False)
    id_usuario = db.Column(db.Integer, db.ForeignKey('usuarios.id'), nullable=False)
    data = db.Column(db.DateTime, default=datetime.utcnow)
    localizacao = db.Column(db.String(255), nullable=True)

    def to_dict(self):
        return {
            'id': self.id,
            'id_embalagem': self.id_embalagem,
            'id_usuario': self.id_usuario,
            'data': self.data.isoformat(),
            'localizacao': self.localizacao
        }
