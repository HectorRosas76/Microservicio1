const rows_vehicles = (grid, data) => {
  clear_grid(grid);
  if (data.length === 0) {
    empty_grid(grid, "No hay vehículos registrados.");
    return;
  }
  data.forEach((item) => {
    grid.appendChild(
      grid_row("cols-7", [
        cell(item.id, "Código"),
        cell(item.marca, "Marca"),
        cell(item.modelo, "Modelo"),
        cell(item.anio, "Año"),
        cell(item.categoria, "Categoría"),
        badge_cell(item.estado),
        actions_cell(
          button("Modificar", () => modificarVehiculo(item), "btn-edit"),
          link("Historial", "historial.html?vehiculo_id=" + item.id, "btn-info"),
          button("Borrar", () => borrarVehiculo(item.id), "btn-delete"),
        ),
      ]),
    );
  });
};
