const rows_available = (grid, data, desde, hasta) => {
  clear_grid(grid);
  if (data.length === 0) {
    empty_grid(grid, "No hay vehículos disponibles en ese periodo.");
    return;
  }
  data.forEach((item) => {
    const params =
      "?vehiculo_id=" + item.id +
      "&fecha_inicio=" + encodeURIComponent(desde) +
      "&fecha_fin=" + encodeURIComponent(hasta);
    grid.appendChild(
      grid_row("cols-avail", [
        cell(item.id, "Código"),
        cell(item.marca, "Marca"),
        cell(item.modelo, "Modelo"),
        cell(item.anio, "Año"),
        cell(item.categoria, "Categoría"),
        actions_cell(link("Reservar", "reservas.html" + params, "btn-success")),
      ]),
    );
  });
};
