const td = (content) => {
  const component = document.createElement("td");
  component.innerHTML = content;
  return component;
};

const button = (content, onClick) => {
  const component = document.createElement("button");
  component.innerHTML = content;
  component.addEventListener("click", () => onClick());
  return component;
};

const trs_students = (table, data) => {
  const tbody = table.getElementsByTagName("tbody")[0];
  tbody.textContent = "";
  data.forEach((item, index) => {
    const tdNo = td(index + 1);
    const tdCod = td(item.codigo);
    const tdNombre = td(item.nombre);
    const tdEmail = td(item.email);

    const tdBotones = td("");
    tdBotones.innerHTML = "";
    tdBotones.appendChild(button("Borrar", () => borrarEstudiante(item.id)));

    const tr = document.createElement("tr");
    tr.appendChild(tdNo);
    tr.appendChild(tdCod);
    tr.appendChild(tdNombre);
    tr.appendChild(tdEmail);
    tr.appendChild(tdBotones);

    tbody.appendChild(tr);
  });
};
