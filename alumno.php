<?php
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/css/alumno.css">
<link rel="stylesheet" href="assets/css/crud.css">
<link rel="stylesheet" href="assets/css/horarios.css">

<div class="home-content">

    <div class="cards-grid">

        <div class="left-column-stack">

            <div class="card schedule-card">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; color: var(--cyan-accent);">
                    <span>Horario Semanal:</span>
                    <button id="btn-sincronizar" class="btn-refresh" onclick="sincronizarMiHorario()" title="Sincronizar nuevas materias">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>

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
                        <tbody id="tabla-horario-alumno">
                            <tr>
                                <td colspan="6" style="text-align:center; padding: 30px;">Cargando horario...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card exams-card">
                <div class="card-title" style="color: #F58220;">Exámenes programados:</div>
                <div class="exams-card-content" style="color: #666; padding: 10px; background: #f9f9f9; border-left: 4px solid #F58220;">
                    No hay exámenes programados para esta semana.
                </div>
            </div>

        </div>

        <div class="card event-card">
            <div class="card-title">Eventos UTE</div>

            <div class="carousel-container" id="events-carousel">
                <div class="carousel-slide">
                    <div class="carousel-item"><img src="assets/img/1.png" alt="Evento 1"></div>
                    <div class="carousel-item"><img src="assets/img/2.png" alt="Evento 2"></div>
                    <div class="carousel-item"><img src="assets/img/3.png" alt="Evento 3"></div>
                    <div class="carousel-item"><img src="assets/img/4.png" alt="Evento 4"></div>
                </div>

                <button class="carousel-nav prev" id="carousel-prev"><i class="fas fa-chevron-left"></i></button>
                <button class="carousel-nav next" id="carousel-next"><i class="fas fa-chevron-right"></i></button>

                <div class="carousel-indicators">
                    <div class="carousel-indicator active" data-slide-to="0"></div>
                    <div class="carousel-indicator" data-slide-to="1"></div>
                    <div class="carousel-indicator" data-slide-to="2"></div>
                    <div class="carousel-indicator" data-slide-to="3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. DEFINICIÓN DE BLOQUES DE HORARIO
    const BLOQUES = [{
            inicio: "07:00:00",
            fin: "07:50:00",
            label: "7:00 - 7:50"
        },
        // ... (Tu array de bloques de horario se mantiene aquí) ...
        {
            inicio: "07:50:00",
            fin: "08:40:00",
            label: "7:50 - 8:40"
        },
        {
            inicio: "08:40:00",
            fin: "09:30:00",
            label: "8:40 - 9:30"
        },
        {
            inicio: "09:30:00",
            fin: "10:10:00",
            label: "RECESO (9:30 - 10:10)",
            tipo: "RECESO"
        },
        {
            inicio: "10:10:00",
            fin: "11:00:00",
            label: "10:10 - 11:00"
        },
        {
            inicio: "11:00:00",
            fin: "11:50:00",
            label: "11:00 - 11:50"
        },
        {
            inicio: "11:50:00",
            fin: "12:40:00",
            label: "11:50 - 12:40"
        },
        {
            inicio: "12:40:00",
            fin: "13:30:00",
            label: "12:40 - 1:30"
        },
        {
            inicio: "13:30:00",
            fin: "14:20:00",
            label: "1:30 - 2:20"
        }
    ];
    const DIAS = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];

    document.addEventListener('DOMContentLoaded', () => {
        cargarHorarioAlumno();
        initCarousel();
    });

    // 2. LÓGICA DE CARGA DINÁMICA
    async function cargarHorarioAlumno() {
        // ... (Tu lógica de fetch Horario y Perfil se mantiene) ...
        const idUsuario = localStorage.getItem('id_usuario');
        const rol = localStorage.getItem('rol_usuario');

        if (!idUsuario) return;

        construirTablaVacia();

        const endpointHorario = (rol === 'ROLE_ALUMNO') ? `/horarios/alumno/${idUsuario}` : `/horarios/docente/${idUsuario}`;
        const endpointPerfil = `/usuarios/perfil`;

        const [resHorarios] = await Promise.all([
            peticionAutenticada(endpointHorario),
            peticionAutenticada(endpointPerfil) // Mantenemos la llamada de perfil para la sesión
        ]);

        if (resHorarios && resHorarios.ok) {
            const horarios = await resHorarios.json();

            if (horarios.length > 0) {
                pintarHorarioGrid(horarios, rol);
                // NOTA: La función pintarInfoUsuario ya fue eliminada para limpiar el código.
            } else {
                document.getElementById('tabla-horario-alumno').innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 50px;">No se encontraron horarios asignados.</td></tr>';
            }
        }
    }

    // 3. CONSTRUIR EL ESQUELETO HTML DE LA TABLA (Genera las celdas con IDs)
    function construirTablaVacia() {
        const tbody = document.getElementById('tabla-horario-alumno');
        let html = '';

        BLOQUES.forEach(bloque => {
            if (bloque.tipo === 'RECESO') {
                html += `<tr><td colspan="6" class="recess-row">${bloque.label}</td></tr>`;
            } else {
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

    // 4. LLENAR LA TABLA GRID CON DATOS REALES
    function pintarHorarioGrid(horarios, rol) {
        horarios.forEach(h => {
            const dia = h.diaSemana;
            const hora = h.horaInicio;
            const idCelda = `celda-${dia}-${hora}`;
            const celda = document.getElementById(idCelda);

            if (celda) {
                const materia = h.materiaGrupo.materia;
                const grupo = h.materiaGrupo.grupo;

                const lineaPrincipal = rol === 'ROLE_DOCENTE' ? `${grupo.nombre} (${grupo.grado}°)` : `${materia.nombre}`;
                const lineaSecundaria = rol === 'ROLE_DOCENTE' ? `${materia.nombre} / Salón: ${h.salon}` : `${grupo.nombre} / Salón: ${h.salon}`;

                const contenido = `
                    <div style="font-weight: 700; font-size:0.95rem;">${lineaPrincipal}</div>
                    <div style="font-size:0.75rem; color:#666;">${lineaSecundaria}</div>
                `;

                celda.innerHTML = contenido;
                if (materia.nombre.toLowerCase().includes('asesoria')) {
                    celda.classList.add('asesoria');
                } else {
                    celda.style.backgroundColor = "#e0f2f1";
                }
            }
        });
        // Llenar tabla de nomenclatura (si existiera la función)
    }

    // 5. LÓGICA DEL CARRUSEL (Mantenida)
    function initCarousel() {
        const slideContainer = document.querySelector('.carousel-slide');
        const slides = document.querySelectorAll('.carousel-item');
        const prevBtn = document.getElementById('carousel-prev');
        const nextBtn = document.getElementById('carousel-next');
        const indicators = document.querySelectorAll('.carousel-indicator');

        let counter = 0;
        const size = slides.length;

        function updateCarousel() {
            slideContainer.style.transform = 'translateX(' + (-counter * 100) + '%)';
            indicators.forEach(ind => ind.classList.remove('active'));
            if (indicators[counter]) indicators[counter].classList.add('active');
        }

        nextBtn.addEventListener('click', () => {
            counter++;
            if (counter >= size) counter = 0;
            updateCarousel();
        });

        prevBtn.addEventListener('click', () => {
            counter--;
            if (counter < 0) counter = size - 1;
            updateCarousel();
        });

        indicators.forEach((ind, index) => {
            ind.addEventListener('click', () => {
                counter = index;
                updateCarousel();
            });
        });

        setInterval(() => {
            counter++;
            if (counter >= size) counter = 0;
            updateCarousel();
        }, 5000);
    }

    // NUEVA FUNCIÓN PARA LLAMAR AL ENDPOINT DE SINCRONIZACIÓN
async function sincronizarMiHorario() {
    const idUsuario = localStorage.getItem('id_usuario');
    const btn = document.getElementById('btn-sincronizar');
    
    if (!idUsuario) return;

    // Efecto visual de carga
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sincronizando...';

    try {
        // Llamamos al nuevo endpoint que creamos en Java
        const response = await fetch(`http://localhost:8080/api/horarios/alumno/sincronizar/${idUsuario}`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token_ute')
            }
        });

        if (response.ok) {
            const mensaje = await response.text();
            alert(mensaje); // "Sincronización exitosa. Se agregaron X materias"
            
            // Recargamos el horario para mostrar las nuevas materias
            cargarHorarioAlumno(); 
        } else {
            const error = await response.text();
            alert("Error: " + error);
        }
    } catch (error) {
        console.error("Error en la petición:", error);
        alert("No se pudo conectar con el servidor para sincronizar.");
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar';
    }
}


    // 6. DESCARGAR PDF (Función de botón)
    function descargarHorarioPDF() {
        alert(`Llamando al endpoint de reportes.`);
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>