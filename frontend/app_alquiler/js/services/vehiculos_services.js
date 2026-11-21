const vehiculos_url = "http://127.0.0.1:8061/vehiculos";

const queryVehicles = async (callbacks) => {
  try {
    const response = await fetch(vehiculos_url);
    if (response.status == 204) {
      callbacks.success([]);
    } else if (response.status == 200) {
      const data = await response.json();
      callbacks.success(data);
    } else {
      throw new Error("No se pudo consultar la lista de vehículos");
    }
  } catch (exception) {
    console.error(exception);
    callbacks.error(serviceError(exception));
  }
  console.info("Fin del servicio queryVehicles");
};

const saveNewVehicle = async (item, callbacks) => {
  try {
    const response = await fetch(vehiculos_url, {
      method: "post",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(item),
    });
    if (response.status == 201) {
      const data = await response.json();
      callbacks.success(data);
    } else {
      throw new Error(await responseError(response, "No se pudo guardar el registro"));
    }
  } catch (exception) {
    console.error(exception);
    callbacks.error(serviceError(exception));
  }
  console.info("Fin del servicio saveNewVehicle");
};

const updateVehicle = async (id, item, callbacks) => {
  try {
    const response = await fetch(vehiculos_url + "/" + id, {
      method: "put",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(item),
    });
    if (response.status == 200) {
      const data = await response.json();
      callbacks.success(data);
    } else {
      throw new Error(await responseError(response, "No se pudo actualizar el registro"));
    }
  } catch (exception) {
    console.error(exception);
    callbacks.error(serviceError(exception));
  }
  console.info("Fin del servicio updateVehicle");
};

const deleteVehicle = async (id, callbacks) => {
  try {
    const response = await fetch(vehiculos_url + "/" + id, {
      method: "delete",
    });
    if (response.status == 200) {
      callbacks.success();
    } else {
      throw new Error(await responseError(response, "No se pudo eliminar el registro"));
    }
  } catch (exception) {
    console.error(exception);
    callbacks.error(serviceError(exception));
  }
  console.info("Fin del servicio deleteVehicle");
};
