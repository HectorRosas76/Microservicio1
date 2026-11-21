const reservas_url = "http://127.0.0.1:8071/reservas";

const jsonOptions = (method, body) => ({
  method: method,
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify(body),
});

// Llamada común a reservas_ms: 204 se entrega como lista vacía y los
// errores (409, 422, 404...) se entregan con el mensaje que manda el backend.
const callReservations = async (name, path, options, okStatus, fallback, callbacks) => {
  try {
    const response = await fetch(reservas_url + path, options);
    if (response.status == 204) {
      callbacks.success([]);
    } else if (response.status == okStatus) {
      const text = await response.text();
      callbacks.success(text ? JSON.parse(text) : null);
    } else {
      throw new Error(await responseError(response, fallback));
    }
  } catch (exception) {
    console.error(exception);
    callbacks.error(serviceError(exception));
  }
  console.info("Fin del servicio " + name);
};

const queryReservations = (callbacks) =>
  callReservations("queryReservations", "", {}, 200, "No se pudo consultar la lista de reservas", callbacks);

const saveNewReservation = (reservation, callbacks) =>
  callReservations("saveNewReservation", "", jsonOptions("post", reservation), 201, "No se pudo guardar la reserva", callbacks);

const updateReservation = (id, reservation, callbacks) =>
  callReservations("updateReservation", "/" + id, jsonOptions("put", reservation), 200, "No se pudo actualizar la reserva", callbacks);

const deleteReservation = (id, callbacks) =>
  callReservations("deleteReservation", "/" + id, { method: "delete" }, 200, "No se pudo eliminar la reserva", callbacks);

// PUT /reservas/{id}/devolucion
const returnReservation = (id, callbacks) =>
  callReservations("returnReservation", "/" + id + "/devolucion", { method: "put" }, 200, "No se pudo registrar la devolución", callbacks);

// GET /reservas/disponibles?desde=...&hasta=...
const queryAvailableVehicles = (desde, hasta, callbacks) =>
  callReservations(
    "queryAvailableVehicles",
    "/disponibles?desde=" + encodeURIComponent(desde) + "&hasta=" + encodeURIComponent(hasta),
    {}, 200, "No se pudo consultar la disponibilidad", callbacks);

// GET /reservas/cliente/{id}  y  GET /reservas/vehiculo/{id}
const queryClientHistory = (id, callbacks) =>
  callReservations("queryClientHistory", "/cliente/" + id, {}, 200, "No se pudo consultar el historial", callbacks);

const queryVehicleHistory = (id, callbacks) =>
  callReservations("queryVehicleHistory", "/vehiculo/" + id, {}, 200, "No se pudo consultar el historial", callbacks);
