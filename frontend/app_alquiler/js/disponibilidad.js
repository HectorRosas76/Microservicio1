const filtroForm = document.forms["disponibilidad-form"];
const grid = document.getElementById("disponibles-grid");
const panel = document.getElementById("disponibles-panel");
const titulo = document.getElementById("disponibles-titulo");
const alerta = document.getElementById("disponibles-error");

// Fecha de hoy en la zona horaria del equipo (YYYY-MM-DD)
const hoy = () => {
  const d = new Date();
  return (
    d.getFullYear() +
    "-" + String(d.getMonth() + 1).padStart(2, "0") +
    "-" + String(d.getDate()).padStart(2, "0")
  );
};

const ajustarHasta = () => {
  const desde = filtroForm["desde"];
  const hasta = filtroForm["hasta"];
  hasta.min = desde.value;
  if (hasta.value && hasta.value < desde.value) hasta.value = desde.value;
};

const consultarDisponibles = () => {
  const desde = filtroForm["desde"].value;
  const hasta = filtroForm["hasta"].value || desde;
  queryAvailableVehicles(desde, hasta, {
    success: (data) => {
      alerta.hidden = true;
      panel.hidden = false;
      titulo.textContent =
        "Vehículos disponibles " +
        (data.desde === data.hasta ? "el " + data.desde : "del " + data.desde + " al " + data.hasta) +
        " (" + data.vehiculos.length + ")";
      rows_available(grid, data.vehiculos, data.desde, data.hasta);
    },
    error: (text) => {
      panel.hidden = true;
      alerta.textContent = text;
      alerta.hidden = false;
    },
  });
};

filtroForm["desde"].value = hoy();
filtroForm["hasta"].value = hoy();
ajustarHasta();
filtroForm["desde"].addEventListener("change", ajustarHasta);
filtroForm.addEventListener("submit", (event) => {
  event.preventDefault();
  consultarDisponibles();
});
consultarDisponibles();
