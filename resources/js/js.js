/**
 * Select2
 */

$(document).ready(function () {
  // Inicialização do Select2 com AJAX
  $("#pontosAcesso").select2({
    ajax: {
      url: "/ajax/pontoAcesso", 
      dataType: "json",         
      delay: 250,                
      data: function (params) {  
        return {
          search: params.term,   
          page: params.page || 1 
        };
      },
      processResults: function (data, params) { 
        params.page = params.page || 1;

        // Ordenar os resultados em ordem alfabética
        if (data && data.results) {
          data.results.sort(function (a, b) {
            return a.text.localeCompare(b.text);
          });
        }

        return {
          results: data.results,
          pagination: {
            more: data.pagination && data.pagination.more
          }
        };
      }
    },
    placeholder: "Selecione um Ponto de Acesso", 
    multiple: true,        
    closeOnSelect: false, 
    theme: "bootstrap-5"   
  });
  $('#pontosAcesso').next('.select2-container').find('.select2-selection').addClass('shadow');

  $("#pontosAcessoEdit").select2({
    ajax: {
      url: "/ajax/pontoAcesso", 
      dataType: "json",         
      delay: 250,                
      data: function (params) {  
        return {
          search: params.term,   
          page: params.page || 1 
        };
      },
      processResults: function (data, params) { 
        params.page = params.page || 1;

        // Ordenar os resultados em ordem alfabética
        if (data && data.results) {
          data.results.sort(function (a, b) {
            return a.text.localeCompare(b.text);
          });
        }

        return {
          results: data.results,
          pagination: {
            more: data.pagination && data.pagination.more
          }
        };
      }
    },
    placeholder: "Selecione um Ponto de Acesso", 
    multiple: true,        
    closeOnSelect: false,  
    theme: "bootstrap-5"   
  });
  // Requisição AJAX para buscar os dados do ponto de acesso pré-selecionado
  var select = $('#pontosAcessoEdit');
  const urlParams = new URLSearchParams(window.location.search);
  let id = urlParams.get('id');
  $.ajax({
    type: 'GET',
    url: '/ajax/pontoAcessoEdit',
    data: {id : id}, 
    dataType: 'json'
  }).then(function (data) {
    data.forEach(function (item) {
      option = new Option(item.text, item.id, true, true);

  
      select.append(option); 
    });
  
    // Atualiza o Select2
    select.trigger('change');
  

    select.trigger({
      type: 'select2:select',
      params: {
        data: data[0]
      }
    });
  });
    $('#pontosAcessoEdit').next('.select2-container').find('.select2-selection').addClass('shadow');
  /**
   * Motivos
   */
    // Inicialização do Select2
    $("#motivos").select2({
      placeholder: "Selecione um Motivo", 
      multiple: false,                  
      closeOnSelect: true,               
      theme: "bootstrap-5",          
      data: [
        { id: "Manutenção - Melhoria de Rede", text: "Manutenção - Melhoria de Rede" },
        { id: "Manutenção Preventiva", text: "Manutenção Preventiva" },
        { id: "Manutenção Emergencial", text: "Manutenção Emergencial" },
        { id: "Acidente Automobilístico", text: "Acidente Automobilístico" },
        { id: "Aquecimento do POP", text: "Aquecimento do POP" },
        { id: "Atenuação de Fibra", text: "Atenuação de Fibra" },
        { id: "Ativo de Rede", text: "Ativo de Rede" },
        { id: "Banco de Baterias", text: "Banco de Baterias" },
        { id: "Carga Alta", text: "Carga Alta" },
        { id: "Cordão Conector", text: "Cordão Conector" },
        { id: "Emenda Malfeita", text: "Emenda Malfeita" },
        { id: "Equipamento Queimado", text: "Equipamento Queimado" },
        { id: "Equipamento Travado", text: "Equipamento Travado" },
        { id: "Erro de Configuração", text: "Erro de Configuração" },
        { id: "Falha no Cordão", text: "Falha no Cordão" },
        { id: "Falha no DIO", text: "Falha no DIO" },
        { id: "Falha no Equipamento Rádio", text: "Falha no Equipamento Rádio" },
        { id: "Falha no GBIC", text: "Falha no GBIC" },
        { id: "Falha na Antena", text: "Falha na Antena" },
        { id: "Falha na Fonte do Equipamento", text: "Falha na Fonte do Equipamento" },
        { id: "Falha no Nobreak", text: "Falha no Nobreak" },
        { id: "Falha de Software", text: "Falha de Software" },
        { id: "Falha no Splitter de Saída", text: "Falha no Splitter de Saída" },
        { id: "Falta de Energia", text: "Falta de Energia" },
        { id: "Ferragem Malfixada", text: "Ferragem Malfixada" },
        { id: "Fibra Mal Acomodada", text: "Fibra Mal Acomodada" },
        { id: "Frequência de Ruído Elevado", text: "Frequência de Ruído Elevado" },
        { id: "Incêndio", text: "Incêndio" },
        { id: "Incêndio em Poste", text: "Incêndio em Poste" },
        { id: "Incêndio em Vegetação", text: "Incêndio em Vegetação" },
        { id: "Insetos na Caixa", text: "Insetos na Caixa" },
        { id: "Linha de Cerol", text: "Linha de Cerol" },
        { id: "Manutenção Não Informada", text: "Manutenção Não Informada" },
        { id: "Máquina Agrícola", text: "Máquina Agrícola" },
        { id: "Máquina de Terraplanagem", text: "Máquina de Terraplanagem" },
        { id: "Nobreak", text: "Nobreak" },
        { id: "Obra de Terceiros", text: "Obra de Terceiros" },
        { id: "Poda Vegetação", text: "Poda Vegetação" },
        { id: "Problema na Caixa de Emenda", text: "Problema na Caixa de Emenda" },
        { id: "Problema no Quadro de Disjuntores", text: "Problema no Quadro de Disjuntores" },
        { id: "Processamento Elevado", text: "Processamento Elevado" },
        { id: "Queda de Árvore", text: "Queda de Árvore" },
        { id: "Queda de Poste", text: "Queda de Poste" },
        { id: "Refração na OLT", text: "Refração na OLT" },
        { id: "Roedores", text: "Roedores" },
        { id: "Causa Não Informada", text: "Causa Não Informada" },
        { id: "Rompimento Parcial", text: "Rompimento Parcial" },
        { id: "Troca de Poste", text: "Troca de Poste" },
        { id: "Vandalismo ou Roubo", text: "Vandalismo ou Roubo" },
        { id: "Responsabilidade de Terceiros", text: "Responsabilidade de Terceiros" }
      ]
    });
    $('#motivos').next('.select2-container').find('.select2-selection').addClass('shadow');


    $("#email").select2({
      placeholder: "Selecione um Motivo", 
      multiple: false,                  
      closeOnSelect: true,               
      theme: "bootstrap-5",           
      data: [
        { id: "emergencial", text: "Manutenção Emergencial" },
        { id: "cancelada", text: "Manutenção Cancelada" },
        { id: "preventiva", text: "Manutenção Preventiva" },
        { id: "incidente", text: "Incidente" },
        { id: "atualizacao", text: "Atualização de Evento" }
      ]
    });
    $('#email').next('.select2-container').find('.select2-selection').addClass('shadow');
});

/**
* DataTable
 */
$(document).ready(function () {
  $('#table').DataTable({
      paging: true,        // Ativa a paginação
      searching: true,     // Ativa a pesquisa
      ordering: true,      // Permite ordenação nas colunas
      info: true,          // Exibe informações sobre os registros
      autoWidth: false,    // Impede que as colunas tenham largura automática
      responsive: true,    // Torna a tabela responsiva
      language: {
          url: "/resources/json/datatable-pt-br.json"  // Tradução para português
      },
      columnDefs: [
          { width: "8px", targets: 0 }  // Ajusta a largura da primeira coluna
      ],
      createdRow: function (row, data, dataIndex) {
        // A primeira célula (coluna de status)
        const status = data.status;  // Supondo que o 'status' seja um valor como 'em-andamento', 'concluido', etc.
        
        // Aqui você pode ajustar a classe dependendo do status
        $(row).find('td').eq(0).addClass('default');  // Adiciona a classe à primeira coluna (status)
      }
  });
});


$(document).ready(function () {
  const urlParams = new URLSearchParams(window.location.search);
  let status = urlParams.get('evento-status');

  if(status != 'Clientes Afetados'){
  $('#tableEvento').DataTable({
      processing: true,  // Exibe indicador de carregamento
      serverSide: true,  // Ativa o lazy loading (server-side processing)
      ajax: {
          url: '/ajax/eventos', // URL para obter os dados
          type: 'GET',           // Método HTTP para a requisição (GET ou POST)
          data: function (d) {
              // Aqui você pode enviar parâmetros adicionais, se necessário
              return {
                  start: d.start,       // Índice do primeiro item da página
                  length: d.length,     // Quantidade de itens por página
                  search: d.search.value, // Termo de busca
                  draw: d.draw,
                  'status': status     // Valor para identificar a requisição no lado do servidor
              };
          }
      },
      columns: [
          { data: null, defaultContent: '' },  // Primeira coluna (coluna vazia)
          { data: 'protocolo' },              // Protocolo
          { data: 'tipo' },                   // Tipo
          { data: 'horario-inicial' },        // Horário Inicial
          { data: 'pontos-acesso' },          // Ponto de Acesso
          { data: 'regional' },               // Regional
          { data: 'observacao' },             // Observação
          { data: 'email' },                   // E-mail
      ],
      language: {
          url: '/resources/json/datatable-pt-br.json'  // Arquivo de tradução do DataTables para o português
      },
      paging: true,          // Habilita paginação
      searching: true,      // Habilita busca
      info: true,           // Habilita informação de quantos registros estão sendo exibidos
      autoWidth: false,     // Impede que o DataTables defina automaticamente a largura das colunas
      order: [[1, 'asc']],
      columnDefs: [
        { width: "8px", targets: 0 }
      ],
      createdRow: function (row, data, dataIndex) {
        // A primeira célula (coluna de status)
        const status = data.status;  // Supondo que o 'status' seja um valor como 'em-andamento', 'concluido', etc.
        
        // Aqui você pode ajustar a classe dependendo do status
        $(row).find('td').eq(0).addClass(status);  // Adiciona a classe à primeira coluna (status)
        $(row).find('td').eq(4).attr({
          "data-bs-toggle": "tooltip",
          "data-bs-placement": "bottom",
          "title": data['pontos-acesso']
      });
      
      $(row).find('td').eq(6).attr({
          "data-bs-toggle": "tooltip",
          "data-bs-placement": "bottom",
          "title": data['observacao']
      });
      
        // Torna a linha clicável e redireciona para a URL de edição
        $(row).click(function () {
          window.location.href = `/evento/edit?id=${data.id}`; 
        });

        $(row).find('[data-bs-toggle="tooltip"]').tooltip();
      }
  });
} else {
  $(document).ready(function () {
    $("#tableEvento").DataTable({
      searching: true,
      paging: true, 
      info: true, //
      autoWidth: false, 
      language: {
        url: "/resources/json/datatable-pt-br.json"
      },
      columnDefs: [
        { width: "8px", targets: 0 }
      ]
    });
  });
}
});

$(document).ready(function () {
  $('#topCaixas').DataTable({
    ajax: {
        url: '/ajax/graficos/top-caixas', 
        type: 'GET',
        dataSrc: ''           
    },
    columns: [
        { data: null, defaultContent: '' },  
        { data: 'nome' },               
        { data: 'total' },          
        { data: 'horas' }                 
    ],
    language: {
        url: '/resources/json/datatable-pt-br.json' 
    },
    paging: false,          
    searching: false,     
    info: false,        
    autoWidth: false,    
    columnDefs: [
      { width: "8px", targets: 0 }
    ],
    createdRow: function (row, data) {
      $(row).find('td').eq(0).addClass('default');  // Adiciona a classe à primeira coluna (status)
      $(row).find('td').addClass('text-center');
    }
  });
});
$(document).ready(function () {
  $('#topMotivos').DataTable({
    ajax: {
        url: '/ajax/graficos/top-motivos', 
        type: 'GET',
        dataSrc: ''           
    },
    columns: [
        { data: null, defaultContent: '' },  
        { data: 'motivo' },               
        { data: 'total' },                       
    ],
    language: {
        url: '/resources/json/datatable-pt-br.json' 
    },
    paging: false,          
    searching: false,     
    info: false,        
    autoWidth: false,    
    columnDefs: [
      { width: "8px", targets: 0 }
    ],
    createdRow: function (row, data) {
      $(row).find('td').eq(0).addClass('default');
      $(row).find('td').addClass('text-center'); // Adiciona a classe à primeira coluna (status)
    }
  });
});





  $(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    let id = urlParams.get('id');
  
    $("#comentarios").DataTable({
      searching: false,
      paging: true,
      pageLength: 5,
      info: false,
      autoWidth: false,
      dom: '<"top"p>rt<"bottom"><"clear">',
      language: {
        emptyTable: "Nenhum comentário encontrado.",
        url: "/resources/json/datatable-pt-br.json"
      },
      ajax: {
        type: 'GET',
        url: '/ajax/comentarios',
        data: { id: id },
        dataSrc: ''
      },
      columns: [
        { data: 'id' },
        { data: 'num' },
        { data: 'autor' },
        { data: 'data' }
      ],
      columnDefs: [
        { width: "8px", targets: 0 }
      ],
      createdRow: function (row, data) {
        $(row).find('td').addClass('text-center');
        $(row).find('td').eq(0).html('');
        $(row).find('td').eq(0).addClass('default');
  
        $(row).on('click', function () {
          $.ajax({
            type: 'GET',
            url: '/ajax/comentario-detalhado',
            data: { id: data.id },
            success: function (response) {
              response = JSON.parse(response);
              if (response && response.comentario) {
                $('#alert').html(response.comentario);
              } else {
                $('#alert').html('Comentário não encontrado.');
              }
            },
            error: function (err) {
              console.error('Erro ao carregar o comentário:', err);
              $('#alert').html('Ocorreu um erro ao carregar o comentário.');
            }
          });
        });
      }
    });

    $("#alteracoes").DataTable({
      searching: false,
      pageLength: 10,
      info: false,
      autoWidth: false,
      language: {
        emptyTable: "Nenhuma Alteração.",
        url: "/resources/json/datatable-pt-br.json"
      },
      ajax: {
        type: 'GET',
        url: '/ajax/alteracoes',
        data: { id: id },
        dataSrc: ''
      },
      columns: [
        { data: 'id' },
        { data: 'alteracao' },
        { data: 'autor' },
        { data: 'data' }
      ],
      columnDefs: [
        { width: "8px", targets: 0 }
      ],
      createdRow: function (row, data) {
        $(row).find('td').addClass('text-center');
        $(row).find('td').eq(0).html('');
        $(row).find('td').eq(0).addClass('default');
      }
    });
  });
  
  $(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    let id = urlParams.get('id');
    $("#btnSalvar").click(function () {
      var comentario = $("#comentario").val().trim();

      if (comentario === "") {
        $("#mensagem").html('<div class="alert alert-danger">Digite um comentário!</div>');
        return;
      }

      $.ajax({
        url: "/ajax/comentarios?id="+id, // Arquivo PHP que processa os dados
        type: "POST",
        data: { comentario: comentario },
        dataType: "json",
        success: function (response) {
            $("#comentario").val(""); // Limpa o campo
            $("#modal-novo").hide();
            $("#comentarios").DataTable().ajax.reload(null, false);
        },
        error: function () {
          $("#comentario").val(""); // Limpa o campo
        },
      });
    });
  });

  
  

  /**
   * Toottips 
   */
document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });
});

  /**Download graficos */
