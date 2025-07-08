# sql_query.py

# Consulta para contar o número total de usuários
def contar_usuarios():
    return "SELECT COUNT(*) FROM usuarios;"

# Você pode adicionar mais consultas SQL conforme necessário
# sql_query.py

# Consulta para obter as recompensas mais caras
def recompensas_mais_caras():
    return "SELECT nome_recompensa, valor FROM recompensas ORDER BY valor DESC LIMIT 5;"
