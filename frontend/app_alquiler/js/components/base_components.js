// Componentes compartidos por todas las páginas

const cell = (content, label) => {
  const component = document.createElement("div");
  component.className = "grid-cell";
  if (label) component.setAttribute("data-label", label);
  component.textContent = content ?? "";
  return component;
};

const badge = (estado) => {
  const component = document.createElement("span");
  component.className = "badge badge-" + estado;
  component.textContent = estado;
  return component;
};

const badge_cell = (estado) => {
  const component = cell("", "Estado");
  component.appendChild(badge(estado));
  return component;
};

const button = (content, onClick, classes = "") => {
  const component = document.createElement("button");
  component.type = "button";
  component.className = "btn btn-sm " + classes;
  component.textContent = content;
  component.addEventListener("click", () => onClick());
  return component;
};

const link = (content, href, classes = "") => {
  const component = document.createElement("a");
  component.className = "btn btn-sm " + classes;
  component.href = href;
  component.textContent = content;
  return component;
};

const actions_cell = (...items) => {
  const component = cell("", "Acciones");
  component.classList.add("grid-actions");
  items.forEach((item) => component.appendChild(item));
  return component;
};

const grid_row = (cols, cells) => {
  const component = document.createElement("div");
  component.className = "grid-row " + cols;
  cells.forEach((item) => component.appendChild(item));
  return component;
};

// Deja solo el encabezado de la grilla
const clear_grid = (grid) => {
  grid
    .querySelectorAll(".grid-row:not(.grid-head), .grid-empty")
    .forEach((item) => item.remove());
};

const empty_grid = (grid, text) => {
  const component = document.createElement("div");
  component.className = "grid-empty";
  component.textContent = text;
  grid.appendChild(component);
};

// Llena un <select> conservando la primera opción ("Seleccionar")
const fill_select = (select, data, text) => {
  while (select.options.length > 1) select.remove(1);
  data.forEach((item) => {
    const option = document.createElement("option");
    option.value = item.id;
    option.textContent = text(item);
    select.appendChild(option);
  });
};

// Mensaje emergente (antes vivía en estudiantes.js)
const msgContent = document.getElementById("msg-content");
let msgTimer = null;

const msg = (show, text, error = false) => {
  if (!msgContent) return;
  if (show) {
    msgContent.getElementsByTagName("p")[0].textContent = text;
    msgContent.classList.toggle("error", error);
    msgContent.classList.remove("hidden");
    clearTimeout(msgTimer);
    msgTimer = setTimeout(() => msg(false, ""), error ? 4500 : 2500);
  } else {
    msgContent.classList.add("hidden");
  }
};

// Modal de confirmación (borrar, devolver)
const confirmModal = document.getElementById("confirm-modal");
let confirmAction = null;

const confirmar = (text, buttonText, buttonClass, action) => {
  confirmModal.getElementsByTagName("h4")[0].textContent = text;
  const ok = document.getElementById("confirmar-modal-btn");
  ok.textContent = buttonText;
  ok.className = "btn " + buttonClass;
  confirmAction = action;
  confirmModal.classList.remove("close");
};

const cerrarConfirmacion = () => {
  confirmAction = null;
  confirmModal.classList.add("close");
};

if (confirmModal) {
  document
    .getElementById("cancelar-modal-btn")
    .addEventListener("click", cerrarConfirmacion);
  document
    .getElementById("confirmar-modal-btn")
    .addEventListener("click", () => {
      if (confirmAction) confirmAction();
    });
}
