const table = document.getElementById("estudiantes-table");
const formContent = document.getElementById("student-form-content");
const registroBtn = document.getElementById("registro-btn");
const estudianteForm = document.forms["estudiante-form"];
const borrarModal = document.getElementById("borrar-modal");
let id_estudiante = null;
const msgContent = document.getElementById("msg-content");

const msg = (show, text) => {
  if (show) {
    msgContent.getElementsByTagName("p")[0].textContent = text;
    msgContent.classList.remove("hidden");
    setTimeout(() => {
      msg(false, "");
    }, 2500);
  } else {
    msgContent.classList.add("hidden");
  }
};

const consultarEstudiantes = () => {
  queryStudents({
    success: (data) => trs_students(table, data),
    error: () => alert("Error al consultar la lista de estudiantes"),
  });
};
consultarEstudiantes();

const borrarEstudiante = (id) => {
  id_estudiante = id;
  borrarModal.classList.remove("close");
};

registroBtn.addEventListener("click", () => {
  formContent.classList.remove("close");
});

estudianteForm.addEventListener("reset", () => {
  formContent.classList.add("close");
});

estudianteForm.addEventListener("submit", (event) => {
  event.preventDefault();
  const data = {
    codigo: estudianteForm["cod"].value,
    nombre: estudianteForm["name"].value,
    email: estudianteForm["email"].value,
  };
  saveNewStudent(data, {
    success: (info) => {
      msg(true, "Datos guardados");
      consultarEstudiantes();
      estudianteForm.reset();
    },
    error: () => alert("Error al guardar los datos"),
  });
});

document.getElementById("cancelar-modal-btn").addEventListener("click", () => {
  borrarModal.classList.add("close");
});

document.getElementById("borrar-modal-btn").addEventListener("click", () => {
  deleteStudent(id_estudiante, {
    success: () => {
      msg(true, "Datos borrados");
      consultarEstudiantes();
      borrarModal.classList.add("close");
    },
    error: () => alert("Error al borrar los datos del estudiante"),
  });
});
