<?php 
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
?>

<link rel="stylesheet" href="assets/css/alumno.css">
<link rel="stylesheet" href="assets/css/crud.css"> 
<link rel="stylesheet" href="assets/css/horarios.css">

<div class="home-content">
    
    <div class="page-header" style="padding-left: 0;">
        <h1 class="page-title"><i class="far fa-calendar-alt"></i> Mi Horario de Clases</h1>
        <div class="action-buttons">
            <button class="btn btn-primary" onclick="descargarHorarioPDF()"><i class="fas fa-print"></i> Imprimir</button>
        </div>
    </div>

    <div class="card user-info-card" style="margin-bottom: 20px;">
        <div class="user-info-grid">
            <p><strong>Periodo:</strong> <span id="info-periodo">Septiembre - Diciembre 2025</span></p>
            <p><strong>Matrícula/No. Empl:</strong> <span id="info-matricula">Cargando...</span></p>
            <p><strong>Usuario:</strong> <span id="info-alumno">Cargando...</span></p>
            <p><strong>Carrera/Depto:</strong> <span id="info-carrera">...</span></p>
            <p><strong>Grupo:</strong> <span id="info-grupo">...</span></p>
            <p><strong>Rol:</strong> <span id="info-rol">...</span></p>
        </div>
    </div>

    <div class="card schedule-card">
        <div class="card-title" style="color: var(--cyan-accent);">Horario Semanal:</div>
        
        <div class="table-responsive-wrapper">
            <table class="schedule-table" id="tabla-visual">
                <thead>
                    <tr>
                        <th class="col-hora">Hora</th>
                        <th>Lunes</th>
                        <th>Martes</th>
                        <th>Miércoles</th>
                        <th>Jueves</th>
                        <th>Viernes</th>
                    </tr>
                </thead>
                <tbody id="horario-body">
                    </tbody>
            </table>
        </div>
    </div>

    <div class="details-grid" style="margin-top: 25px;">
        </div>

</div>

<script>
    // 1. DEFINICIÓN DE BLOQUES DE HORARIO (Estructura fija según tu diseño)
    const BLOQUES = [
        { inicio: "07:00:00", fin: "07:50:00", label: "7:00 - 7:50" },
        { inicio: "07:50:00", fin: "08:40:00", label: "7:50 - 8:40" },
        { inicio: "08:40:00", fin: "09:30:00", label: "8:40 - 9:30" },
        { inicio: "09:30:00", fin: "10:10:00", label: "RECESO (9:30 - 10:10)", tipo: "RECESO" },
        { inicio: "10:10:00", fin: "11:00:00", label: "10:10 - 11:00" },
        { inicio: "11:00:00", fin: "11:50:00", label: "11:00 - 11:50" },
        { inicio: "11:50:00", fin: "12:40:00", label: "11:50 - 12:40" },
        { inicio: "12:40:00", fin: "13:30:00", label: "12:40 - 1:30" },
        { inicio: "13:30:00", fin: "14:20:00", label: "1:30 - 2:20" }
    ];
    const DIAS = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];

    document.addEventListener('DOMContentLoaded', cargarDatosCompletos);

    // 2. FUNCIÓN PRINCIPAL QUE GESTIONA LA CARGA DE DATOS
    async function cargarDatosCompletos() {
        const idUsuario = localStorage.getItem('id_usuario');
        const rol = localStorage.getItem('rol_usuario');
        
        if (!idUsuario) return; 

        // 1. Dibuja el esqueleto de la tabla
        construirTablaVacia(); 

        // 2. Determinar endpoint y cargar datos personales
        const esAlumno = rol === 'ROLE_ALUMNO';
        const endpoint = esAlumno ? `/horarios/alumno/${idUsuario}` : `/horarios/docente/${idUsuario}`;
        const endpointPerfil = `/usuarios/perfil`;

        const [resHorarios, resPerfil] = await Promise.all([
            peticionAutenticada(endpoint),
            peticionAutenticada(endpointPerfil)
        ]);

        if (resHorarios && resHorarios.ok && resPerfil && resPerfil.ok) {
            const horarios = await resHorarios.json();
            const perfil = await resPerfil.json();
            
            // 3. Pintar la info del usuario (Matrícula, etc.)
            pintarInfoUsuario(perfil); 
            
            // 4. Pintar el grid
            if (horarios.length > 0) {
                pintarHorarioGrid(horarios, rol); 
            } else {
                document.getElementById('horario-body').innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 50px;">No se encontraron horarios asignados.</td></tr>';
            }
        }
    }

    // 3. CONSTRUIR EL ESQUELETO HTML DE LA TABLA
    function construirTablaVacia() {
        const tbody = document.getElementById('horario-body');
        let html = '';

        BLOQUES.forEach(bloque => {
            if (bloque.tipo === 'RECESO') {
                html += `<tr><td colspan="6" class="recess-row">${bloque.label}</td></tr>`;
            } else {
                // Creamos una fila con IDs únicos para cada celda: ej "celda-Lunes-07:00:00"
                html += `
                    <tr>
                        <td><strong>${bloque.label}</strong></td>
                        ${DIAS.map(dia => `<td id="celda-${dia}-${bloque.inicio}"></td>`).join('')}
                    </tr>
                `;
            }
        });
        tbody.innerHTML = html;
    }

    // 4. LLENAR LA TABLA GRID CON DATOS REALES (Mapeo de lista a grid)
    function pintarHorarioGrid(horarios, rol) {
        horarios.forEach(h => {
            const dia = h.diaSemana;
            const hora = h.horaInicio;
            const idCelda = `celda-${dia}-${hora}`;
            const celda = document.getElementById(idCelda);

            // Evitar crash si el horario está fuera de los bloques definidos
            if (celda) { 
                const materia = h.materiaGrupo.materia;
                const grupo = h.materiaGrupo.grupo;
                
                // Si es Docente, mostramos el Grupo/Semestre. Si es Alumno, solo la materia.
                const lineaPrincipal = rol === 'ROLE_DOCENTE' ? `${grupo.nombre} (${grupo.grado}°)` : `${materia.nombre}`;
                const lineaSecundaria = rol === 'ROLE_DOCENTE' ? `${materia.nombre} (${h.salon})` : `Salón: ${h.salon}`;

                const contenido = `
                    <div style="font-weight: 700; font-size:0.95rem;">${lineaPrincipal}</div>
                    <div style="font-size:0.75rem; color:#666;">${lineaSecundaria}</div>
                `;

                celda.innerHTML = contenido;
                // Aplicar estilos especiales
                if (materia.nombre.toLowerCase().includes('asesoria')) {
                    celda.classList.add('asesoria');
                } else {
                    celda.style.backgroundColor = "#e0f2f1"; // Color claro de fondo
                }
            }
        });
    }

    // 5. LLENAR DATOS PERSONALES DEL ENCABEZADO
    function pintarInfoUsuario(perfil) {
        document.getElementById('info-periodo').innerText = 'Septiembre - Diciembre 2025';
        document.getElementById('info-alumno').innerText = perfil.nombreCompleto || perfil.username;
        document.getElementById('info-rol').innerText = perfil.rol;
        
        // Asignación condicional de detalles
        if (perfil.rol === 'ALUMNO') {
            document.getElementById('info-matricula').innerText = perfil.matricula || 'N/A';
            document.getElementById('info-carrera').innerText = perfil.carrera || 'N/A';
            document.getElementById('info-grupo').innerText = 'N/A (Cargar desde otra consulta)'; // Necesita consulta extra de grupos
        } else if (perfil.rol === 'DOCENTE') {
            document.getElementById('info-matricula').innerText = perfil.numEmpleado || 'N/A';
            document.getElementById('info-carrera').innerText = perfil.departamento || 'N/A';
            document.getElementById('info-grupo').innerText = 'N/A';
        }
    }

    // 6. DESCARGAR PDF (Función de botón)
    function descargarHorarioPDF() {
        const id = localStorage.getItem('id_usuario');
        const rol = localStorage.getItem('rol_usuario');
        const tipo = (rol === 'ROLE_ALUMNO') ? 'alumno' : 'docente';

        alert(`Llamando al endpoint de reportes: /reportes/horario/${tipo}/${id}.`);
        // Lógica final de descarga (usando la función descargarPDF de api.js)
    }

</script>

<?php include __DIR__ . '/includes/footer.php'; ?>