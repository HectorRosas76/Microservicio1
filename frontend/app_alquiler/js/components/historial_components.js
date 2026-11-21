// Días de alquiler (inicio y fin cuentan). Las fechas llegan como YYYY-MM-DD.
const dias_alquiler = (inicio, fin) => {
  const a = Date.parse(inicio + "T00:00:00Z");
  const b = Date.parse(fin + "T00:00:00Z");
  return Math.round((b - a) / 86400000) + 1;
};

// modo = "cliente" (muestra el vehículo) o "vehiculo" (muestra el cliente)
const rows_history = (grid, data, modo) => {
  grid.textContent = "";
  const otro = modo === "cliente" ? "Vehículo" : "Cliente";
  grid.appendChild(
    grid_row("cols-hist grid-head", [
      cell("Código"), cell(otro), cell("Inicio"), cell("Fin"), cell("Días"), cell("Estado"),
    ]),
  );
  if (data.length === 0) {
    empty_grid(grid, "Todavía no hay alquileres registrados.");
    return;
  }
  data.forEach((item) => {
    let nombre;
    if (modo === "cliente") {
      nombre = item.vehiculo && item.vehiculo.marca
        ? item.vehiculo.marca + " " + item.vehiculo.modelo
        : "ID " + item.vehiculo_id;
    } else {
      nombre = item.cliente && item.cliente.nombre
        ? item.cliente.nombre
        : "ID " + item.cliente_id;
    }
    grid.appendChild(
      grid_row("cols-hist", [
        cell(item.id, "Código"),
        cell(nombre, otro),
        cell(item.fecha_inicio, "Inicio"),
        cell(item.fecha_fin, "Fin"),
        cell(dias_alquiler(item.fecha_inicio, item.fecha_fin), "Días"),
        badge_cell(item.estado),
      ]),
    );
  });
};
