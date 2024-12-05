let dataTable;
let selectedEstado = "";
let pieChart = null;
let barChart = null;

function mostrarEstadisticas() {
  $.ajax({
    url: "acciones/obtenerEstadisticas.php",
    type: "GET",
    success: function (response) {
      try {
        const stats = JSON.parse(response);

        // Actualizar métricas generales
        $("#totalEmpleados").text(stats.totalEmpleados);
        $("#promedioGlobal").text(stats.promedioGlobal);
        $("#totalEstados").text(stats.totalEstados);

        // Preparar datos para la gráfica de pastel
        const pieData = {
          labels: stats.distribucionEstados.map((item) => item.estado),
          datasets: [
            {
              data: stats.distribucionEstados.map((item) => item.total),
              backgroundColor: generarColores(stats.distribucionEstados.length),
            },
          ],
        };

        // Preparar datos para la gráfica de barras
        const barData = {
          labels: stats.promediosPorEstado.map((item) => item.estado),
          datasets: [
            {
              label: "Promedio de Calificaciones",
              data: stats.promediosPorEstado.map((item) => item.promedio),
              backgroundColor: "rgba(54, 162, 235, 0.5)",
              borderColor: "rgba(54, 162, 235, 1)",
              borderWidth: 1,
            },
          ],
        };

        // Actualizar gráficas
        actualizarGraficas(pieData, barData);

        // Mostrar modal
        $("#modalEstadisticas").modal("show");
      } catch (e) {
        console.error("Error:", e);
        alert("Error al procesar las estadísticas");
      }
    },
  });
}

function cerrarSesion() {
  if (confirm("¿Está seguro que desea cerrar sesión?")) {
    window.location.href = "cerrar_sesion.php";
  }
}

function actualizarGraficas(pieData, barData) {
  // Destruir gráficas existentes si las hay
  if (pieChart) pieChart.destroy();
  if (barChart) barChart.destroy();

  // Crear gráfica de pastel
  const pieCtx = document.getElementById("pieChart").getContext("2d");
  pieChart = new Chart(pieCtx, {
    type: "pie",
    data: pieData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          position: "right",
        },
      },
    },
  });

  // Crear gráfica de barras
  const barCtx = document.getElementById("barChart").getContext("2d");
  barChart = new Chart(barCtx, {
    type: "bar",
    data: barData,
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          max: 10,
        },
      },
    },
  });
}

function exportarEmpleados() {
  if (!selectedEstado) {
    alert("Por favor seleccione un estado primero");
    return;
  }

  // Crear una URL con el estado seleccionado
  const url = `acciones/exportar.php?estado=${selectedEstado}`;

  // Crear un enlace temporal y simular clic
  const a = document.createElement("a");
  a.href = url;
  a.download = "empleados.csv"; // El nombre real será asignado por el servidor
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

function generarColores(cantidad) {
  const colores = [];
  for (let i = 0; i < cantidad; i++) {
    const hue = (i * 360) / cantidad;
    colores.push(`hsl(${hue}, 70%, 60%)`);
  }
  return colores;
}

$(document).ready(function () {
  // Recuperar el estado seleccionado del localStorage si existe
  selectedEstado = localStorage.getItem("selectedEstado") || "";
  if (selectedEstado) {
    $("#estado").val(selectedEstado);
    cargarEmpleadosPorEstado(selectedEstado);
  }

  // Inicializar DataTable
  initDataTable();

  // Listener para el cambio de estado
  $("#estado").change(function () {
    selectedEstado = $(this).val();
    // Guardar el estado seleccionado en localStorage
    localStorage.setItem("selectedEstado", selectedEstado);
    if (selectedEstado) {
      cargarEmpleadosPorEstado(selectedEstado);
    } else {
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
    destroy: true,
    searching: true,
    processing: true,
    pageLength: 10,
    dom: "Bfrtip",
  });
}

function cargarEmpleadosPorEstado(estadoId) {
  $.ajax({
    url: "empleados.php",
    method: "GET",
    data: { estado: estadoId },
    success: function (response) {
      if (dataTable) {
        dataTable.destroy();
      }
      $("#empleadosContainer").html(response);
      initDataTable();
    },
    error: function (xhr, status, error) {
      console.error("Error al cargar empleados:", error);
      alert("Error al cargar los empleados");
    },
  });
}

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
            // Recargar la tabla manteniendo el estado seleccionado
            if (selectedEstado) {
              cargarEmpleadosPorEstado(selectedEstado);
            }
            alert("Empleado eliminado correctamente");
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

function editarEmpleado(id) {
  $.ajax({
    url: "acciones/obtenerEmpleado.php",
    type: "GET",
    data: { id: id },
    success: function (response) {
      try {
        const empleado = JSON.parse(response);

        // Llenar el formulario con los datos del empleado
        $("#nombre").val(empleado.nombre);
        $("#apellido").val(empleado.apellido);
        $("#edad").val(empleado.edad);
        $(`input[name="sexo"][value="${empleado.sexo}"]`).prop("checked", true);
        $("#correo").val(empleado.correo);
        $("#estado_empleado").val(empleado.id_estado);
        $("#departamento").val(empleado.id_departamento);
        $("#ocupacion").val(empleado.ocupacion);

        // Agregar el ID para la actualización
        if (!$('input[name="id"]').length) {
          $("<input>")
            .attr({
              type: "hidden",
              name: "id",
              value: empleado.id_usuarios,
            })
            .appendTo("#empleadoForm");
        } else {
          $('input[name="id"]').val(empleado.id_usuarios);
        }

        // Cambiar el texto del botón
        $('button[type="submit"]').text("Actualizar empleado");

        // Mostrar la imagen actual si existe
        if (empleado.avatar) {
          if ($("#currentAvatar").length === 0) {
            const avatarPreview = `
                          <div id="currentAvatar" class="mt-2">
                              <img src="acciones/fotos_empleados/${empleado.avatar}" 
                                   class="rounded-circle" 
                                   width="50" height="50">
                              <small class="text-muted ms-2">Avatar actual</small>
                          </div>`;
            $("#avatar").after(avatarPreview);
          } else {
            $("#currentAvatar img").attr(
              "src",
              `acciones/fotos_empleados/${empleado.avatar}`
            );
          }
        }

        // Hacer scroll al formulario
        $("html, body").animate(
          {
            scrollTop: $("#empleadoForm").offset().top - 20,
          },
          500
        );
      } catch (e) {
        console.error("Error:", e);
        alert("Error al cargar los datos del empleado");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en la petición:", error);
      alert("Error al obtener los datos del empleado");
    },
  });
}

// Función para actualizar la tabla después de agregar/editar un empleado
function actualizarTabla() {
  if (selectedEstado) {
    cargarEmpleadosPorEstado(selectedEstado);
  }
}

function abrirModalCalificaciones(empleadoId, nombreEmpleado) {
  $("#modalCalificacionesLabel").text("Evaluar a " + nombreEmpleado);
  $("#empleado_id").val(empleadoId);
  limpiarFormularioCalificacion();
  cargarCalificaciones(empleadoId);
  $("#modalCalificaciones").modal("show");
}

function limpiarFormularioCalificacion() {
  $("#calificacionForm")[0].reset();
  $("#calificacion_id").val("");
  $("#mes").val(new Date().getMonth() + 1);
  $("#anio").val(new Date().getFullYear());
}

function cargarCalificaciones(empleadoId) {
  $.ajax({
    url: "acciones/obtenerCalificaciones.php",
    type: "GET",
    data: { empleado_id: empleadoId },
    success: function (response) {
      try {
        const calificaciones = JSON.parse(response);
        const tbody = $("#calificacionesBody");
        tbody.empty();

        calificaciones.forEach(function (cal) {
          const mes = obtenerNombreMes(cal.mes);
          tbody.append(`
                      <tr>
                          <td>${mes}</td>
                          <td>${cal.anio}</td>
                          <td>${cal.calificacion}</td>
                          <td>${cal.comentarios || ""}</td>
                          <td>
                              <button class="btn btn-warning btn-sm" onclick="editarCalificacion(${JSON.stringify(
                                cal
                              ).replace(/"/g, "&quot;")})">
                                  <i class="bi bi-pencil"></i>
                              </button>
                          </td>
                      </tr>
                  `);
        });
      } catch (e) {
        console.error("Error:", e);
        alert("Error al cargar las calificaciones");
      }
    },
  });
}

function obtenerNombreMes(numeroMes) {
  const meses = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ];
  return meses[numeroMes - 1];
}

function editarCalificacion(calificacion) {
  $("#calificacion_id").val(calificacion.id_evaluacion);
  $("#mes").val(calificacion.mes);
  $("#anio").val(calificacion.anio);
  $("#calificacion").val(calificacion.calificacion);
  $("#comentarios").val(calificacion.comentarios);
}

// Cuando el documento esté listo
$(document).ready(function () {
  // Manejar el envío del formulario de calificaciones
  $("#calificacionForm").on("submit", function (e) {
    e.preventDefault();

    const formData = {
      id: $("#calificacion_id").val(),
      empleado_id: $("#empleado_id").val(),
      mes: $("#mes").val(),
      anio: $("#anio").val(),
      calificacion: $("#calificacion").val(),
      comentarios: $("#comentarios").val(),
    };

    $.ajax({
      url: "acciones/guardarCalificacion.php",
      type: "POST",
      data: formData,
      success: function (response) {
        try {
          const result = JSON.parse(response);
          if (result.success) {
            alert("Calificación guardada correctamente");
            limpiarFormularioCalificacion();
            cargarCalificaciones($("#empleado_id").val());
            // Actualizar la tabla principal si es necesario
            if (selectedEstado) {
              cargarEmpleadosPorEstado(selectedEstado);
            }
          } else {
            alert("Error al guardar la calificación");
          }
        } catch (e) {
          console.error("Error:", e);
          alert("Error al procesar la respuesta");
        }
      },
    });
  });

  $("#empleadoForm").on("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    $.ajax({
      url: "acciones/updateEmpleado.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        try {
          const result = JSON.parse(response);
          if (result.success) {
            // Mostrar mensaje de éxito
            alert("Empleado actualizado correctamente");

            // Limpiar el formulario
            $("#empleadoForm")[0].reset();
            $("#currentAvatar").remove();
            $('input[name="id"]').remove();
            $('button[type="submit"]').text("Agregar empleado");

            // Recargar la tabla de empleados si hay un estado seleccionado
            if (selectedEstado) {
              cargarEmpleadosPorEstado(selectedEstado);
            }
          } else {
            alert(
              "Error al actualizar: " + (result.error || "Error desconocido")
            );
          }
        } catch (e) {
          console.error("Error:", e);
          alert("Error al procesar la respuesta del servidor");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
        alert("Error al enviar el formulario");
      },
    });
  });
});
