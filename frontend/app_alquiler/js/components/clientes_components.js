const rows_clients = (grid, data) => {
  clear_grid(grid);
  if (data.length === 0) {
    empty_grid(grid, "No hay clientes registrados.");
    return;
  }
  data.forEach((item) => {
    grid.appendChild(
      grid_row("cols-6", [
        cell(item.id, "Código"),
        cell(item.nombre, "Nombre"),
        cell(item.telefono, "Teléfono"),
        cell(item.correo, "Email"),
        cell(item.numero_licencia, "N° Licencia"),
        actions_cell(
          button("Modificar", () => modificarCliente(item), "btn-edit"),
          link("Historial", "historial.html?cliente_id=" + item.id, "btn-info"),
          button("Borrar", () => borrarCliente(item.id), "btn-delete"),
        ),
      ]),
    );
  });
};
