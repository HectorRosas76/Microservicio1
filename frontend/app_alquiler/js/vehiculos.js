const grid = document.getElementById("vehiculos-grid");
const formContent = document.getElementById("vehicle-form-content");
const registroBtn = document.getElementById("registro-btn");
const vehiculoForm = document.forms["vehiculo-form"];
const searchForm = document.forms["search-form"];
let vehiculos = [];
let id_vehiculo = null; // null = registrando, con valor = modificando

const mostrarVehiculos = () => {
  const texto = searchForm["search"].value.trim().toLowerCase();
  const filtrados = vehiculos.filter((item) =>
    [item.id, item.marca, item.modelo, item.anio, item.categoria, item.estado]
      .join(" ")
      .toLowerCase()
      .includes(texto),
  );
  rows_vehicles(grid, filtrados);
};

const consultarVehiculos = () => {
  queryVehicles({
    success: (data) => {
      vehiculos = data;
      mostrarVehiculos();
    },
    error: (text) => msg(true, "Error al consultar la lista de vehículos. " + text, true),
  });
};
consultarVehiculos();

const abrirFormulario = (vehiculo) => {
  id_vehiculo = vehiculo ? vehiculo.id : null;
  document.getElementById("form-title").textContent = vehiculo
    ? "Modificar vehículo"
    : "Registrar vehículo";
  vehiculoForm["marca"].value = vehiculo ? vehiculo.marca : "";
  vehiculoForm["modelo"].value = vehiculo ? vehiculo.modelo : "";
  vehiculoForm["anio"].value = vehiculo ? vehiculo.anio : "";
  vehiculoForm["categoria"].value = vehiculo ? vehiculo.categoria : "";
  vehiculoForm["estado"].value = vehiculo ? vehiculo.estado : "disponible";
  formContent.classList.remove("close");
};

const modificarVehiculo = (vehiculo) => abrirFormulario(vehiculo);

const borrarVehiculo = (id) => {
  confirmar("¿Desea borrar el registro?", "Borrar", "btn-delete", () => {
    deleteVehicle(id, {
      success: () => {
        msg(true, "Datos borrados");
        consultarVehiculos();
        cerrarConfirmacion();
      },
      error: (text) => {
        cerrarConfirmacion();
        msg(true, "Error al borrar los datos del vehículo. " + text, true);
      },
    });
  });
};

registroBtn.addEventListener("click", () => abrirFormulario(null));

vehiculoForm.addEventListener("reset", () => {
  formContent.classList.add("close");
  id_vehiculo = null;
});

searchForm.addEventListener("submit", (event) => {
  event.preventDefault();
  mostrarVehiculos();
});
searchForm["search"].addEventListener("input", mostrarVehiculos);

vehiculoForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const data = {
    marca: vehiculoForm["marca"].value,
    modelo: vehiculoForm["modelo"].value,
    anio: Number(vehiculoForm["anio"].value),
    categoria: vehiculoForm["categoria"].value,
    estado: vehiculoForm["estado"].value,
  };
  const editando = id_vehiculo !== null;
  const callbacks = {
    success: () => {
      msg(true, editando ? "Datos actualizados" : "Datos guardados");
      consultarVehiculos();
      vehiculoForm.reset();
    },
    error: (text) => msg(true, "Error al guardar los datos. " + text, true),
  };
  if (editando) {
    updateVehicle(id_vehiculo, data, callbacks);
  } else {
    saveNewVehicle(data, callbacks);
  }
});
