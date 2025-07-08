# db.py
import mysql.connector

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",  # sua senha do XAMPP
    "database": "embalagens_db"
}

def executar_sql(query):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute(query)

    if query.strip().lower().startswith("select"):
        resultado = cursor.fetchall()
    else:
        conn.commit()
        resultado = { "linhas_afetadas": cursor.rowcount }

    cursor.close()
    conn.close()
    return resultado
