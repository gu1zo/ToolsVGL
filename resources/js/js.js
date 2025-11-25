/**
* DataTable
 */
$(document).ready(function () {
  $('#table').DataTable({
      paging: true,        // Ativa a paginação
      searching: true,     // Ativa a pesquisa
      ordering: true,      // Permite ordenação nas colunas
      info: true,          // Exibe informações sobre os registros
      autoWidth: false,    
      responsive: true,    // Torna a tabela responsiva
      language: {
          url: "/resources/json/datatable-pt-br.json"  // Tradução para português
      },
      columnDefs: [
          { width: "8px", targets: 0 }  // Ajusta a largura da primeira coluna
      ],
      createdRow: function (row, data, dataIndex) {
        $(row).find('td').eq(0).addClass('default');  // Adiciona a classe à primeira coluna (status)
      }
  });
});
$(document).ready(function () {
  $('#massiva').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
      responsive: true,
      language: {
          url: "/resources/json/datatable-pt-br.json"
      },
      columnDefs: [
          { width: "8px", targets: 0 }
      ],
      createdRow: function (row, data, dataIndex) {
          $(row).find('td').eq(0).addClass('default');
      },
      order: [[2, 'desc']] 
  });
});

$(document).ready(function () {
  const urlParams = new URLSearchParams(window.location.search);
  let tipo = urlParams.get('tipo');

  $("#agendados").DataTable({
      searching: false,
      paging: true,
      pageLength: 15,
      info: false,
      order: [[2, 'asc']],
      autoWidth: false,
      language: {
          emptyTable: "Nenhum agendamento encontrado.",
          url: "/resources/json/datatable-pt-br.json"
      },
      ajax: {
          type: 'GET',
          url: '/ajax/agendados',
          data: { tipo: tipo },
          dataSrc: ''
      },
      columns: [
          { data: 'id' },
          { data: 'protocolo' },
          { data: 'data' },
          { data: 'observacao' },
          { data: 'usuario' },
          { data: null, orderable: false } // Coluna do botão
      ],
      columnDefs: [
          { width: "8px", targets: 0 }
      ],
      createdRow: function (row, data) {
          $(row).find('td').addClass('text-center');
          $(row).find('td').eq(0).addClass('default');

          // Adiciona o botão de exclusão na última coluna
          $(row).find('td').eq(5).html(`
              <button class="btn btn-danger btn-sm" onclick="alterarStatus(${data.id})">
                  Excluir
              </button>
          `);
          $(row).find('td').eq(0).html('');
      }
  });
});
$(document).ready(function () {
  const urlParams = new URLSearchParams(window.location.search);
  let tipo = urlParams.get('tipo');
  $("#btnSalvar").click(function () {
    var protocolo = $("#protocolo").val().trim();
    var data = $("#data").val().trim();
    var observacao = $("#observacao").val().trim();
    var tipo = $("#tipo").val().trim();
    
    if (protocolo === "") {
      $("#mensagem").html('<div class="alert alert-danger">Digite o protocolo!</div>');
      return;
    }
    if (data === "") {
      $("#mensagem").html('<div class="alert alert-danger">Selecione a data!</div>');
      return;
    }

    $("#modal-novo").modal("hide");
    $.ajax({
      url: "/ajax/agendados",
      type: "POST",
      data: { 
        protocolo: protocolo,
        data: data,
        observacao: observacao,
        tipo: tipo
       },
      dataType: "json",
      success: function (response) {
          $("#protocolo").val(""); // Limpa o campo
          $("#data").val(""); // Limpa o campo
          $("#observacao").val(""); // Limpa o campo
          $("#agendados").DataTable().ajax.reload(null, false);
      },
      error: function () {
        $("#protocolo").val(""); // Limpa o campo
        $("#data").val(""); // Limpa o campo
        $("#observacao").val(""); // Limpa o campo
        $("#mensagem").html('<div class="alert alert-danger">Erro ao cadastrar!</div>');
      return;
      },
    });
  });
});
// Função para alterar o status do agendamento
function alterarStatus(id) {
  if (confirm("Tem certeza que deseja excluir este agendamento?")) {
      $.ajax({
          url: "/ajax/agendados/excluir",
          type: "POST",
          data: { id: id },
          success: function (response) {
              $("#agendados").DataTable().ajax.reload(null, false);
          },
          error: function () {
              alert("Erro ao excluir o agendamento.");
          }
      });
  }
}


$(document).ready(function () {
  var usuarioNaFila = false;
  var usuarioPrimeiroFila = false;

  function verificarFila() {
      $.get("/ajax/fila/usuario", function(response) {
          usuarioNaFila = response.naFila;
          usuarioPrimeiroFila = response.isFirst;
          atualizarBotoes();
      });
  }

  var tabelaFila = $("#fila").DataTable({
      searching: false,
      paging: true,
      order: [[2, 'asc']],
      pageLength: 15,
      info: false,
      autoWidth: false,
      language: {
          emptyTable: "Nenhum usuário na fila.",
          url: "/resources/json/datatable-pt-br.json"
      },
      ajax: {
          type: 'GET',
          url: '/ajax/fila',
          dataSrc: '',
          complete: function() {
              verificarFila();
          }
      },
      columns: [
          { data: 'id' },
          { data: 'usuario' },
          { data: 'posicao' },
          { data: 'entrada' }
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

  function atualizarBotoes() {
      $("#entrarFila").toggleClass("d-none", usuarioNaFila);
      $("#sairFila").toggleClass("d-none", !usuarioNaFila);
      $("#passarVez").toggleClass("d-none", !usuarioPrimeiroFila);
      if (usuarioPrimeiroFila) {
        $("#title").html("É A SUA VEZ > ToolsVGL");
    }
  }

  $("#entrarFila").click(function() {
      $.post("/ajax/fila/entrar", function() {
          tabelaFila.ajax.reload();
      });
  });

  $("#sairFila").click(function() {
      $.post("/ajax/fila/sair", function() {
          tabelaFila.ajax.reload();
      });
  });

  $("#passarVez").click(function() {
      $.post("/ajax/fila/passar", function() {
          tabelaFila.ajax.reload();
      });
  });

  setInterval(function() {
    verificarFila();
    tabelaFila.ajax.reload(null, false);  // Recarrega os dados sem resetar a página
}, 500);
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

document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("toggle-theme");
    const icon = document.getElementById("theme-icon");
    const body = document.body;
    const htmlElement = document.documentElement; // Pega o <html>

    // Verifica a preferência salva no localStorage
    if (localStorage.getItem("theme") === "dark") {
        body.classList.add("dark-mode");
        htmlElement.setAttribute('data-bs-theme', 'dark');
        icon.classList.add("bi-moon"); // Lua para modo escuro
    } else {
        htmlElement.setAttribute('data-bs-theme', 'light');
        icon.classList.add("bi-sun"); // Sol para modo claro
    }

    toggleButton.addEventListener("click", function () {
        body.classList.toggle("dark-mode");
        
        // Altera o atributo data-bs-theme no <html>
        if (htmlElement.getAttribute('data-bs-theme') === 'dark') {
            htmlElement.setAttribute('data-bs-theme', 'light');
            icon.classList.remove("bi-sun");
            icon.classList.add("bi-moon"); // Altera para o ícone de lua
        } else {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            icon.classList.remove("bi-moon");
            icon.classList.add("bi-sun"); // Altera para o ícone de sol
        }

        // Salva a preferência no localStorage
        if (body.classList.contains("dark-mode")) {
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
    });
});
$(document).ready(function() {
    $('.equipe').select2({
        placeholder: "Selecione a Equipe", 
        multiple: false,                  
        closeOnSelect: true,               
        theme: "bootstrap-5",          
        search:true
        });
        $('.equipe').next('.select2-container').find('.select2-selection').addClass('shadow');
});
    

$(document).ready(function () {
  var selecoes = new Set(); 

  var tabela = $('#notas').DataTable({
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
        $(row).find('td').eq(0).addClass('default');  // Adiciona a classe à primeira coluna (status)
      }
  });

  $("#notas tbody input[type='checkbox'][name='notas[]']").each(function () {
      if ($(this).is(":checked")) {
          selecoes.add($(this).val());
      }
  });

  // Atualizar os checkboxes ao mudar de página ou usar a barra de pesquisa
  tabela.on("draw.dt", function () {
      $("#notas tbody input[type='checkbox'][name='notas[]']").each(function () {
          var id = $(this).val();
          $(this).prop("checked", selecoes.has(id)); // Mantém a seleção
      });
  });

  // Capturar clique nos checkboxes individuais
  $("#notas tbody").on("change", 'input[type="checkbox"][name="notas[]"]', function () {
      var id = $(this).val();
      if ($(this).is(":checked")) {
          selecoes.add(id);
      } else {
          selecoes.delete(id);
      }
  });

  // Antes de enviar o formulário, cria inputs ocultos com os IDs selecionados
  $("#formNotas").on("submit", function () {
      $("#inputsHidden").empty(); // Limpa os inputs ocultos
      selecoes.forEach(function (id) {
          $("#inputsHidden").append('<input type="hidden" name="notas[]" value="' + id + '">');
      });
  });
});


$(document).ready(function () {
  // Inicialização do Select2 com AJAX
  $("#tecnicos").select2({
    ajax: {
      url: "/ajax/tecnicos", 
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
    placeholder: "Selecione um Técnico", 
    multiple: false,        
    closeOnSelect: true, 
    theme: "bootstrap-5"   
  });
  $('#tecnicos').next('.select2-container').find('.select2-selection').addClass('shadow');
});