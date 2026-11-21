// clients_names y vehicles_names se arman en reservas.js ({id: nombre})
const rows_reservations = (grid, data) => {
  clear_grid(grid);
  if (data.length === 0) {
    empty_grid(grid, "No hay reservas registradas.");
    return;
  }
  data.forEach((item) => {
    const buttons = [button("Modificar", () => modificarReserva(item), "btn-edit")];
    if (item.estado === "activa") {
      buttons.push(button("Devolver", () => devolverReserva(item.id), "btn-success"));
    }
    buttons.push(button("Borrar", () => borrarReserva(item.id), "btn-delete"));

    grid.appendChild(
      grid_row("cols-7", [
        cell(item.id, "Código"),
        cell(clients_names[item.cliente_id] ?? "ID " + item.cliente_id, "Cliente"),
        cell(vehicles_names[item.vehiculo_id] ?? "ID " + item.vehiculo_id, "Vehículo"),
        cell(item.fecha_inicio, "Fecha Inicio"),
        cell(item.fecha_fin, "Fecha Fin"),
        badge_cell(item.estado),
        actions_cell(...buttons),
      ]),
    );
  });
};
