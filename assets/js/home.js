// home.js
let dataTable;

$(document).ready(function () {
  // Inicializar DataTable solo si no existe
  initDataTable();

  // Listener para el cambio de estado
  $("#estado").change(function () {
    let estadoId = $(this).val();
    if (estadoId) {
      $.ajax({
        url: "empleados.php",
        method: "GET",
        data: { estado: estadoId },
        success: function (response) {
          // Destruir la tabla existente si existe
          if (dataTable) {
            dataTable.destroy();
          }

          // Actualizar el contenido
          $("#empleadosContainer").html(response);

          // Reinicializar DataTable
          initDataTable();
        },
        error: function (xhr, status, error) {
          console.error("Error al cargar empleados:", error);
          alert("Error al cargar los empleados");
        },
      });
    } else {
      // Si no hay estado seleccionado, limpiar la tabla
      if (dataTable) {
        dataTable.clear().draw();
      }
    }
  });
});

function initDataTable() {
  dataTable = $("#table_empleados").DataTable({
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
    },
    destroy: true, // Permite reinicializar la tabla
    searching: true,
    processing: true,
    pageLength: 10,
    dom: "Bfrtip",
  });
}

// Función para eliminar empleado
function eliminarEmpleado(id) {
  if (confirm("¿Estás seguro de que deseas eliminar este empleado?")) {
    $.ajax({
      url: "acciones/delete.php",
      method: "POST",
      data: { id: id },
      success: function (response) {
        try {
          const result = JSON.parse(response);
          if (result.success) {
            // Recargar la tabla
            $("#estado").trigger("change");
          } else {
            alert("Error al eliminar el empleado");
          }
        } catch (e) {
          console.error("Error al procesar la respuesta:", e);
          alert("Error al procesar la respuesta del servidor");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error al eliminar:", error);
        alert("Error al eliminar el empleado");
      },
    });
  }
}
