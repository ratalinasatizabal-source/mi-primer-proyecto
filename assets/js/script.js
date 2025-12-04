function mostrarAlerta(mensaje, tipo = 'info', duracion = 4000) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo} alert-dismissible fade show shadow`;
    alerta.style.minWidth = '280px';
    
    let icono = '';
    let titulo = '';
    
    switch(tipo) {
        case 'success':
            icono = 'fa-check-circle';
            titulo = 'Éxito';
            break;
        case 'danger':
            icono = 'fa-exclamation-triangle';
            titulo = 'Error';
            break;
        case 'warning':
            icono = 'fa-exclamation-circle';
            titulo = 'Advertencia';
            break;
        default:
            icono = 'fa-info-circle';
            titulo = 'Información';
    }
    
    alerta.innerHTML = `
        <i class="fas ${icono} me-2"></i>
        <strong>${titulo}:</strong> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    alertContainer.appendChild(alerta);

    // Auto-eliminar después del tiempo especificado
    setTimeout(() => {
        if (alerta.parentNode) {
            const bsAlert = new bootstrap.Alert(alerta);
            bsAlert.close();
        }
    }, duracion);
}

// ===== ALERTAS AUTOMÁTICAS DEL SISTEMA =====
function verificarAlertasSistema() {
    const body = document.body;
    const totalLibros = parseInt(body.dataset.libros) || 0;
    const totalAutores = parseInt(body.dataset.autores) || 0;
    const prestamosActivos = parseInt(body.dataset.activos) || 0;
    const prestamosVencidos = parseInt(body.dataset.vencidos) || 0;
    const stockBajo = parseInt(body.dataset.stockBajo) || 0;
    const prestamosProximos = parseInt(body.dataset.proximos) || 0;
    const librosAgotados = parseInt(body.dataset.agotados) || 0;
    const autoresSinLibros = parseInt(body.dataset.autoresSinLibros) || 0;
    const prestamosMes = parseInt(body.dataset.prestamosMes) || 0;

    // Alertas prioritarias
    if (prestamosVencidos > 0) {
        mostrarAlerta(`Tienes ${prestamosVencidos} préstamo(s) vencido(s) que requieren atención`, 'danger', 6000);
    }

    if (librosAgotados > 0) {
        mostrarAlerta(`${librosAgotados} libro(s) están agotados`, 'warning', 5000);
    }

    if (stockBajo > 0) {
        mostrarAlerta(`${stockBajo} libro(s) tienen stock bajo (<5 unidades)`, 'warning', 5000);
    }

    // Alertas informativas
    if (prestamosProximos > 0) {
        mostrarAlerta(`${prestamosProximos} préstamo(s) vencen en los próximos 3 días`, 'info', 4000);
    }

    if (totalLibros === 0) {
        mostrarAlerta('No hay libros registrados. ¡Agrega tu primer libro!', 'info', 5000);
    }

    if (totalAutores === 0) {
        mostrarAlerta('No hay autores registrados. Puedes agregar uno en la sección de Autores.', 'info', 5000);
    }

    if (autoresSinLibros > 0) {
        mostrarAlerta(`${autoresSinLibros} autor(es) no tienen libros asignados`, 'info', 4000);
    }

    // Mensaje de bienvenida
    if (totalLibros > 0 && prestamosVencidos === 0 && librosAgotados === 0) {
        mostrarAlerta('Sistema cargado correctamente. ¡Todo está al día!', 'success', 3000);
    }
}

// ===== VALIDACIÓN DE IMÁGENES =====
function validarImagen(archivo) {
    const errores = [];
    
    if (archivo) {
        const tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
        const tamanoMaximo = 2 * 1024 * 1024; // 2MB
        
        if (!tiposPermitidos.includes(archivo.type)) {
            errores.push('Solo se permiten imágenes JPG, PNG o GIF');
        }
        
        if (archivo.size > tamanoMaximo) {
            errores.push('La imagen no puede ser mayor a 2MB');
        }
    }
    
    return errores;
}

// ===== VISTA PREVIA DE IMÁGENES =====
function previewImage(input, previewId = 'imagePreview') {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const errores = validarImagen(file);
        if (errores.length > 0) {
            mostrarAlerta(errores.join(', '), 'danger');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}
// ===== CONFIRMACIÓN DE ELIMINACIÓN MEJORADA =====
function confirmarEliminacion(id, nombre, tipo = 'elemento', urlBase = '') {
    if (confirm(`¿Estás seguro de eliminar el ${tipo} "${nombre}"? Esta acción es irreversible.`)) {
        if (urlBase) {
            // Para préstamos que usan procesar.php con parámetros GET
            window.location.href = `${urlBase}?accion=eliminar&id=${id}`;
        } else {
            // Para otros elementos que usan eliminar.php directo
            window.location.href = `eliminar.php?id=${id}`;
        }
    }
    return false;
}

// ===== FILTRADO Y BÚSQUEDA =====
function inicializarFiltros(config) {
    const {
        inputBusqueda,
        filtros,
        elementos,
        atributos
    } = config;

    function filtrarElementos() {
        const searchTerm = inputBusqueda ? inputBusqueda.value.toLowerCase() : '';
        
        elementos.forEach(elemento => {
            let coincide = true;
            
            // Búsqueda general
            if (searchTerm) {
                coincide = atributos.some(atributo => {
                    const valor = elemento.getAttribute(atributo) || '';
                    return valor.toLowerCase().includes(searchTerm);
                });
            }
            
            // Filtros específicos
            filtros.forEach(filtro => {
                if (coincide) {
                    const valorFiltro = filtro.elemento.value;
                    const atributoFiltro = filtro.atributo;
                    
                    if (valorFiltro) {
                        const valorElemento = elemento.getAttribute(atributoFiltro);
                        coincide = valorElemento === valorFiltro || 
                                  (filtro.tipo === 'number' && parseInt(valorElemento) === parseInt(valorFiltro)) ||
                                  (filtro.tipo === 'contains' && valorElemento && valorElemento.includes(valorFiltro));
                    }
                }
            });
            
            elemento.style.display = coincide ? '' : 'none';
        });
    }

    // Event listeners
    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', filtrarElementos);
    }
    
    filtros.forEach(filtro => {
        filtro.elemento.addEventListener('change', filtrarElementos);
        if (filtro.tipo === 'number' || filtro.tipo === 'text') {
            filtro.elemento.addEventListener('input', filtrarElementos);
        }
    });
}

// ===== CAMBIO DE VISTA (LISTA/CUADRICULA) =====
function inicializarVistas(config) {
    const {
        btnLista,
        btnCuadricula,
        vistaLista,
        vistaCuadricula
    } = config;

    function cambiarVista(vista) {
        if (vista === 'lista') {
            vistaLista.style.display = 'block';
            vistaCuadricula.style.display = 'none';
            btnLista.classList.add('vista-activa');
            btnCuadricula.classList.remove('vista-activa');
        } else {
            vistaLista.style.display = 'none';
            vistaCuadricula.style.display = 'flex';
            btnLista.classList.remove('vista-activa');
            btnCuadricula.classList.add('vista-activa');
        }
        
        // Guardar preferencia
        localStorage.setItem('preferenciaVista', vista);
    }

    btnLista.addEventListener('click', () => cambiarVista('lista'));
    btnCuadricula.addEventListener('click', () => cambiarVista('cuadricula'));

    // Cargar preferencia guardada
    const preferencia = localStorage.getItem('preferenciaVista') || 'lista';
    cambiarVista(preferencia);
}

// ===== INICIALIZACIÓN AL CARGAR LA PÁGINA =====
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
    
    // Verificar alertas del sistema si hay datos
    if (document.body.dataset.libros !== undefined) {
        verificarAlertasSistema();
    }
    
    // Manejar errores de imágenes
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG' && e.target.classList.contains('book-cover')) {
            e.target.src = '../assets/imagen/default-book.png';
        }
    }, true);
});

// ===== UTILIDADES FECHA =====
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES');
}

function diasRestantes(fechaFutura) {
    const hoy = new Date();
    const fecha = new Date(fechaFutura);
    const diffTime = fecha - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

// Funciones para Autores
function confirmarEliminacion() {
    return confirm('¿Estás seguro de que deseas eliminar este autor? Esta acción no se puede deshacer.');
}

// Validación de formularios
function validarFormularioAutor() {
    const nombre = document.querySelector('input[name="nombre"]').value.trim();
    const apellido = document.querySelector('input[name="apellido"]').value.trim();
    
    if (nombre === '' || apellido === '') {
        alert('Por favor, complete los campos obligatorios (Nombre y Apellido)');
        return false;
    }
    
    return true;
}

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-dismiss alerts después de 5 segundos
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
