const grid = document.getElementById("clientes-grid");
const formContent = document.getElementById("client-form-content");
const registroBtn = document.getElementById("registro-btn");
const clienteForm = document.forms["cliente-form"];
const searchForm = document.forms["search-form"];
let clientes = [];
let id_cliente = null; // null = registrando, con valor = modificando

const mostrarClientes = () => {
  const texto = searchForm["search"].value.trim().toLowerCase();
  const filtrados = clientes.filter((item) =>
    [item.id, item.nombre, item.telefono, item.correo, item.numero_licencia]
      .join(" ")
      .toLowerCase()
      .includes(texto),
  );
  rows_clients(grid, filtrados);
};

const consultarClientes = () => {
  queryClients({
    success: (data) => {
      clientes = data;
      mostrarClientes();
    },
    error: (text) => msg(true, "Error al consultar la lista de clientes. " + text, true),
  });
};
consultarClientes();

const abrirFormulario = (cliente) => {
  id_cliente = cliente ? cliente.id : null;
  document.getElementById("form-title").textContent = cliente
    ? "Modificar cliente"
    : "Registrar cliente";
  clienteForm["nombre"].value = cliente ? cliente.nombre : "";
  clienteForm["telefono"].value = cliente ? cliente.telefono : "";
  clienteForm["correo"].value = cliente ? cliente.correo : "";
  clienteForm["numero_licencia"].value = cliente ? cliente.numero_licencia : "";
  formContent.classList.remove("close");
};

const modificarCliente = (cliente) => abrirFormulario(cliente);

const borrarCliente = (id) => {
  confirmar("¿Desea borrar el registro?", "Borrar", "btn-delete", () => {
    deleteClient(id, {
      success: () => {
        msg(true, "Datos borrados");
        consultarClientes();
        cerrarConfirmacion();
      },
      error: (text) => {
        cerrarConfirmacion();
        msg(true, "Error al borrar los datos del cliente. " + text, true);
      },
    });
  });
};

registroBtn.addEventListener("click", () => abrirFormulario(null));

clienteForm.addEventListener("reset", () => {
  formContent.classList.add("close");
  id_cliente = null;
});

searchForm.addEventListener("submit", (event) => {
  event.preventDefault();
  mostrarClientes();
});
searchForm["search"].addEventListener("input", mostrarClientes);

clienteForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const data = {
    nombre: clienteForm["nombre"].value,
    telefono: clienteForm["telefono"].value,
    correo: clienteForm["correo"].value,
    numero_licencia: clienteForm["numero_licencia"].value,
  };
  const editando = id_cliente !== null;
  const callbacks = {
    success: () => {
      msg(true, editando ? "Datos actualizados" : "Datos guardados");
      consultarClientes();
      clienteForm.reset();
    },
    error: (text) => msg(true, "Error al guardar los datos. " + text, true),
  };
  if (editando) {
    updateClient(id_cliente, data, callbacks);
  } else {
    saveNewClient(data, callbacks);
  }
});
