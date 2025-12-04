function mostrarAlertaBootstrap(mensaje, tipo = 'info', duracion = 5000) {

    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow`;
    alertContainer.style.zIndex = "2000";
    alertContainer.style.minWidth = "280px";

    alertContainer.innerHTML = `
        <strong>
            ${tipo === 'success' ? '✔ Éxito' :
              tipo === 'danger' ? '✖ Error' :
              tipo === 'warning' ? '⚠ Atención' : 'ℹ Info'}:
        </strong>
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(alertContainer);

    setTimeout(() => {
        if (document.body.contains(alertContainer)) {
            const alerta = bootstrap.Alert.getOrCreateInstance(alertContainer);
            alerta.close();
        }
    }, duracion);
}
// BUSCADOR en tabla Categorías
document.addEventListener('DOMContentLoaded', () => {

    const inputBuscar = document.getElementById("buscarCategoria");
    const filas = document.querySelectorAll("#tablaCategorias tr");

    if (inputBuscar) {
        inputBuscar.addEventListener("keyup", function () {
            const filtro = this.value.toLowerCase();

            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                fila.style.display = texto.includes(filtro) ? "" : "none";
            });
        });
    }
});
// Confirmar eliminación con modal (si lo usas)

function confirmarEliminar(id, nombre) {

    const modalNombre = document.getElementById("categoriaEliminar");
    const btnConfirmar = document.getElementById("btnConfirmarEliminar");

    if (modalNombre) modalNombre.textContent = nombre;
    if (btnConfirmar) btnConfirmar.href = "eliminar.php?id=" + id;

    const modal = new bootstrap.Modal(document.getElementById("modalEliminar"));
    modal.show();
}

// Filtro de búsqueda en tiempo real para categorías
    const inputBuscar = document.getElementById('buscarCategoria');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('#tablaCategorias tr');
            
            filas.forEach(function(fila) {
                const texto = fila.textContent.toLowerCase();
                if (texto.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
