// Utilidades compartidas por los servicios

const serviceError = (exception) =>
  exception instanceof TypeError
    ? "No se pudo conectar con el servicio. Verifica que esté encendido."
    : exception.message;

// Los errores de reservas llegan como {"error": "mensaje"}
const responseError = async (response, fallback) => {
  try {
    const data = await response.json();
    return data.error || fallback;
  } catch (exception) {
    return fallback;
  }
};
