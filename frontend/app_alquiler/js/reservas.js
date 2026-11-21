const grid = document.getElementById("reservas-grid");
const formContent = document.getElementById("reserva-form-content");
const registroBtn = document.getElementById("registro-btn");
const reservaForm = document.forms["reserva-form"];
const searchForm = document.forms["search-form"];
const campoEstado = document.getElementById("campo-estado");
let reservas = [];
let clientes = [];
let vehiculos = [];
let clients_names = {};
let vehicles_names = {};
let id_reserva = null; // null = registrando, con valor = modificando

const mostrarReservas = () => {
  const texto = searchForm["search"].value.trim().toLowerCase();
  const filtradas = reservas.filter((item) =>
    [
      item.id,
      clients_names[item.cliente_id],
      vehicles_names[item.vehiculo_id],
      item.fecha_inicio,
      item.fecha_fin,
      item.estado,
    ]
      .join(" ")
      .toLowerCase()
      .includes(texto),
  );
  rows_reservations(grid, filtradas);
};

const consultarReservas = () => {
  queryReservations({
    success: (data) => {
      reservas = data;
      mostrarReservas();
    },
    error: (text) => msg(true, "Error al consultar la lista de reservas. " + text, true),
  });
};

// Las reservas solo guardan ids: se consultan también clientes y vehículos
// para mostrar sus nombres y llenar los select del formulario.
const cargarDatos = () => {
  let pendientes = 3;
  const listo = () => {
    pendientes--;
    if (pendientes > 0) return;
    clientes.forEach((item) => (clients_names[item.id] = item.nombre));
    vehiculos.forEach((item) => (vehicles_names[item.id] = item.marca + " " + item.modelo));
    fill_select(reservaForm["cliente_id"], clientes, (item) => item.nombre);
    fill_select(
      reservaForm["vehiculo_id"],
      vehiculos,
      (item) => item.marca + " " + item.modelo + " (" + item.anio + ")",
    );
    mostrarReservas();
    abrirDesdeUrl();
  };
  const fallo = (text) => {
    msg(true, "Error al consultar los datos. " + text, true);
    listo();
  };
  queryClients({ success: (data) => { clientes = data; listo(); }, error: fallo });
  queryVehicles({ success: (data) => { vehiculos = data; listo(); }, error: fallo });
  queryReservations({ success: (data) => { reservas = data; listo(); }, error: fallo });
};
cargarDatos();

// disponibilidad.html envía: reservas.html?vehiculo_id=1&fecha_inicio=...&fecha_fin=...
const abrirDesdeUrl = () => {
  const params = new URLSearchParams(window.location.search);
  if (!params.has("vehiculo_id")) return;
  abrirFormulario(null, {
    vehiculo_id: params.get("vehiculo_id"),
    fecha_inicio: params.get("fecha_inicio") || "",
    fecha_fin: params.get("fecha_fin") || "",
  });
  window.history.replaceState(null, "", window.location.pathname);
};

const ajustarFin = () => {
  const inicio = reservaForm["fecha_inicio"];
  const fin = reservaForm["fecha_fin"];
  fin.min = inicio.value;
  if (fin.value && fin.value < inicio.value) fin.value = inicio.value;
};

const abrirFormulario = (reserva, valores = {}) => {
  id_reserva = reserva ? reserva.id : null;
  const datos = reserva || valores;
  document.getElementById("form-title").textContent = reserva
    ? "Modificar reserva"
    : "Registrar reserva";
  reservaForm["cliente_id"].value = datos.cliente_id || "";
  reservaForm["vehiculo_id"].value = datos.vehiculo_id || "";
  reservaForm["fecha_inicio"].value = datos.fecha_inicio || "";
  reservaForm["fecha_fin"].value = datos.fecha_fin || "";
  reservaForm["estado"].value = reserva ? reserva.estado : "activa";
  // Al crear, el backend siempre deja la reserva como "activa"
  campoEstado.style.display = reserva ? "" : "none";
  ajustarFin();
  formContent.classList.remove("close");
};

const modificarReserva = (reserva) => abrirFormulario(reserva);

const devolverReserva = (id) => {
  confirmar("¿Registrar la devolución de este vehículo?", "Devolver", "btn-success", () => {
    returnReservation(id, {
      success: () => {
        msg(true, "Vehículo devuelto. La reserva quedó completada");
        consultarReservas();
        cerrarConfirmacion();
      },
      error: (text) => {
        cerrarConfirmacion();
        msg(true, text, true);
      },
    });
  });
};

const borrarReserva = (id) => {
  confirmar("¿Desea borrar el registro?", "Borrar", "btn-delete", () => {
    deleteReservation(id, {
      success: () => {
        msg(true, "Datos borrados");
        consultarReservas();
        cerrarConfirmacion();
      },
      error: (text) => {
        cerrarConfirmacion();
        msg(true, text, true);
      },
    });
  });
};

registroBtn.addEventListener("click", () => abrirFormulario(null));

reservaForm.addEventListener("reset", () => {
  formContent.classList.add("close");
  id_reserva = null;
});

reservaForm["fecha_inicio"].addEventListener("change", ajustarFin);

searchForm.addEventListener("submit", (event) => {
  event.preventDefault();
  mostrarReservas();
});
searchForm["search"].addEventListener("input", mostrarReservas);

reservaForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const data = {
    cliente_id: Number(reservaForm["cliente_id"].value),
    vehiculo_id: Number(reservaForm["vehiculo_id"].value),
    fecha_inicio: reservaForm["fecha_inicio"].value,
    fecha_fin: reservaForm["fecha_fin"].value,
  };
  const editando = id_reserva !== null;
  if (editando) data.estado = reservaForm["estado"].value;
  const callbacks = {
    success: () => {
      msg(true, editando ? "Datos actualizados" : "Datos guardados");
      consultarReservas();
      reservaForm.reset();
    },
    // 409 (cruce de fechas, mantenimiento) y 422 (datos inválidos) traen el motivo
    error: (text) => msg(true, text, true),
  };
  if (editando) {
    updateReservation(id_reserva, data, callbacks);
  } else {
    saveNewReservation(data, callbacks);
  }
});
