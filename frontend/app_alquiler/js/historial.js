const clienteForm = document.forms["cliente-form"];
const vehiculoForm = document.forms["vehiculo-form"];
const grid = document.getElementById("historial-grid");
const panel = document.getElementById("historial-panel");
const titulo = document.getElementById("historial-titulo");
const aviso = document.getElementById("historial-aviso");
const alerta = document.getElementById("historial-error");
let clientes = [];
let vehiculos = [];
let clients_names = {};
let vehicles_names = {};

const mostrarHistorial = (modo, id, data) => {
  aviso.hidden = true;
  alerta.hidden = true;
  panel.hidden = false;
  titulo.textContent =
    modo === "cliente"
      ? "Historial del cliente: " + (clients_names[id] ?? "ID " + id)
      : "Historial del vehículo: " + (vehicles_names[id] ?? "ID " + id);
  rows_history(grid, data, modo);
};

const mostrarError = (text) => {
  aviso.hidden = true;
  panel.hidden = true;
  alerta.textContent = text;
  alerta.hidden = false;
};

const consultarHistorialCliente = (id) => {
  queryClientHistory(id, {
    success: (data) => mostrarHistorial("cliente", id, data),
    error: mostrarError,
  });
};

const consultarHistorialVehiculo = (id) => {
  queryVehicleHistory(id, {
    success: (data) => mostrarHistorial("vehiculo", id, data),
    error: mostrarError,
  });
};

// historial.html?cliente_id=1  o  historial.html?vehiculo_id=1
const abrirDesdeUrl = () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get("cliente_id")) {
    clienteForm["cliente_id"].value = params.get("cliente_id");
    consultarHistorialCliente(params.get("cliente_id"));
  } else if (params.get("vehiculo_id")) {
    vehiculoForm["vehiculo_id"].value = params.get("vehiculo_id");
    consultarHistorialVehiculo(params.get("vehiculo_id"));
  }
};

const cargarDatos = () => {
  let pendientes = 2;
  const listo = () => {
    pendientes--;
    if (pendientes > 0) return;
    clientes.forEach((item) => (clients_names[item.id] = item.nombre));
    vehiculos.forEach((item) => (vehicles_names[item.id] = item.marca + " " + item.modelo));
    fill_select(clienteForm["cliente_id"], clientes, (item) => item.nombre);
    fill_select(
      vehiculoForm["vehiculo_id"],
      vehiculos,
      (item) => item.marca + " " + item.modelo + " (" + item.anio + ")",
    );
    abrirDesdeUrl();
  };
  queryClients({ success: (data) => { clientes = data; listo(); }, error: (text) => { mostrarError(text); listo(); } });
  queryVehicles({ success: (data) => { vehiculos = data; listo(); }, error: (text) => { mostrarError(text); listo(); } });
};
cargarDatos();

clienteForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const id = clienteForm["cliente_id"].value;
  vehiculoForm["vehiculo_id"].value = "";
  consultarHistorialCliente(id);
});

vehiculoForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const id = vehiculoForm["vehiculo_id"].value;
  clienteForm["cliente_id"].value = "";
  consultarHistorialVehiculo(id);
});
