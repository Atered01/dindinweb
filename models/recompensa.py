from app import db

class Recompensa(db.Model):
    __tablename__ = 'recompensas'

    id = db.Column(db.Integer, primary_key=True)
    nome = db.Column(db.String(100), nullable=False)
    pontos_necessarios = db.Column(db.Integer, nullable=False)

    def to_dict(self):
        return {
            'id': self.id,
            'nome': self.nome,
            'pontos_necessarios': self.pontos_necessarios
        }
