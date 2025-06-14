// Em Dindinweb/js/cadastro.js

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Seleciona todos os campos do formulário que vamos manipular
    const cepInput = document.getElementById('cep');
    const logradouroInput = document.getElementById('logradouro');
    const numeroInput = document.getElementById('numero');
    const bairroInput = document.getElementById('bairro');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');
    const cpfInput = document.getElementById('CPF'); // Novo seletor
    const telefoneInput = document.getElementById('telefone'); // Novo seletor
    
    // Se não encontrar os campos nesta página, o script não continua.
    if (!cepInput || !cpfInput || !telefoneInput) {
        return; 
    }

    // --- LÓGICA DE MÁSCARAS DE FORMATAÇÃO ---

    // Máscara para CEP: XXXXX-XXX
    cepInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, ''); // Remove não-números
        value = value.slice(0, 8); // Limita a 8 dígitos
        value = value.replace(/^(\d{5})(\d)/, '$1-$2'); // Adiciona o hífen
        e.target.value = value;
    });

    // NOVO: Máscara para CPF: XXX.XXX.XXX-XX
    cpfInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, ''); // Remove não-números
        value = value.slice(0, 11); // Limita a 11 dígitos
        value = value.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o primeiro ponto
        value = value.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona o segundo ponto
        value = value.replace(/(\d{3})(\d{2})$/, '$1-$2'); // Adiciona o hífen
        e.target.value = value;
    });

    // NOVO: Máscara para Telefone: (XX) XXXXX-XXXX
    telefoneInput.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, ''); // Remove não-números
        value = value.slice(0, 11); // Limita a 11 dígitos
        value = value.replace(/^(\d{2})/, '($1) '); // Adiciona os parênteses e espaço
        value = value.replace(/(\d{5})(\d{4})$/, '$1-$2'); // Adiciona o hífen para celular com 9 dígitos
        e.target.value = value;
    });


    // --- LÓGICA DE BUSCA DE ENDEREÇO (VIA CEP) ---

    const buscarEndereco = async (cep) => {
        logradouroInput.value = '...';
        bairroInput.value = '...';
        cidadeInput.value = '...';
        estadoInput.value = '...';
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (!data.erro) {
                logradouroInput.value = data.logradouro;
                bairroInput.value = data.bairro;
                cidadeInput.value = data.localidade;
                estadoInput.value = data.uf;
                numeroInput.focus();
            } else {
                alert("CEP não encontrado.");
                logradouroInput.value = "";
                bairroInput.value = "";
                cidadeInput.value = "";
                estadoInput.value = "";
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    };
    
    // Aciona a busca do CEP quando o usuário sai do campo
    cepInput.addEventListener('blur', (e) => {
        const cep = e.target.value.replace(/\D/g, '');
        if (cep.length === 8) {
            buscarEndereco(cep);
        }
    });
});