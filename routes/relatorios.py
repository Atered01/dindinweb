# routes/relatorios.py
from flask import Blueprint, jsonify, request, Response, Flask
from flask_jwt_extended import jwt_required
from app import get_connection
import csv
import io
from fpdf import FPDF

relatorios_bp = Blueprint('relatorios', __name__, url_prefix='/relatorios')

@relatorios_bp.route('/descartes', methods=['GET'])
@jwt_required()
def relatorio_descartes():
    usuario = request.args.get('usuario', default=None)
    produto = request.args.get('produto', default=None)
    data_inicio = request.args.get('data_inicio', default=None)
    data_fim = request.args.get('data_fim', default=None)
    formato = request.args.get('formato', default='json')  # json, csv, pdf

    filtros = []
    valores = []

    if usuario:
        filtros.append("u.nome LIKE %s")
        valores.append(f"%{usuario}%")

    if produto:
        filtros.append("e.produto LIKE %s")
        valores.append(f"%{produto}%")

    if data_inicio:
        filtros.append("d.data >= %s")
        valores.append(data_inicio)

    if data_fim:
        filtros.append("d.data <= %s")
        valores.append(data_fim)

    where_clause = "WHERE " + " AND ".join(filtros) if filtros else ""

    query = f"""
        SELECT u.nome AS usuario, e.produto, d.data, d.localizacao
        FROM descartes d
        JOIN usuarios u ON d.id_usuario = u.id
        JOIN embalagens e ON d.id_embalagem = e.id
        {where_clause}
        ORDER BY d.data DESC
    """

    connection = get_connection()
    with connection:
        with connection.cursor() as cursor:
            cursor.execute(query, valores)
            dados = cursor.fetchall()

    if formato == 'csv':
        output = io.StringIO()
        writer = csv.DictWriter(output, fieldnames=['usuario', 'produto', 'data', 'localizacao'])
        writer.writeheader()
        writer.writerows(dados)
        response = Response(output.getvalue(), mimetype='text/csv')
        response.headers['Content-Disposition'] = 'attachment; filename=relatorio_descartes.csv'
        return response

    if formato == 'pdf':
        pdf = FPDF()
        pdf.add_page()
        pdf.set_font("Arial", size=12)
        pdf.cell(200, 10, txt="Relatório de Descartes", ln=True, align='C')
        pdf.ln(10)

        pdf.set_font("Arial", size=10)
        for d in dados:
            linha = f"Usuário: {d['usuario']} | Produto: {d['produto']} | Data: {d['data']} | Local: {d['localizacao']}"
            pdf.multi_cell(0, 10, txt=linha)

        output = io.BytesIO()
        pdf.output(output)
        response = Response(output.getvalue(), mimetype='application/pdf')
        response.headers['Content-Disposition'] = 'attachment; filename=relatorio_descartes.pdf'
        return response

    return jsonify({"message": "Report generated"})
