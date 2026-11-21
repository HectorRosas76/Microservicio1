const domain_url = "http://127.0.0.1:8081/estudiantes";

const queryStudents = async (callbacks) => {
  try {
    const response = await fetch(domain_url);
    const data = await response.json();
    callbacks.success(data);
  } catch (exception) {
    console.error(exception);
    callbacks.error();
  }
  console.info("Fin del servicio queryStudents");
};

const queryStudent = async (id) => {};

const saveNewStudent = async (student, callbacks) => {
  try {
    const response = await fetch(domain_url, {
      method: "post",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(student),
    });
    if (response.status == 201) {
      const data = await response.json();
      callbacks.success(data);
    } else {
      throw new Error("No se pudo guardar el registro");
    }
  } catch (exception) {
    console.error(exception);
    callbacks.error();
  }
  console.info("Fin del servicio saveNewStudent");
};

const updateStudent = async (id, student) => {};

const deleteStudent = async (id, callbacks) => {
  try {
    const response = await fetch(domain_url + "/" + id, {
      method: "delete",
    });
    if (response.status == 200) {
      callbacks.success();
    } else {
      throw new Error("No se pudo eliminar el registro");
    }
  } catch (exception) {
    callbacks.error(exception);
  }
  console.info("Fin del servicio deleteStudent");
};
