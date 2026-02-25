<?php
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/css/alumno.css">
<link rel="stylesheet" href="assets/css/crud.css">
<link rel="stylesheet" href="assets/css/horarios.css">
<link rel="stylesheet" href="assets/css/docentes.css">

<div class="home-content">
    <div class="cards-grid">

        <div class="left-column-stack">

            <div class="card schedule-card" style="border-left: 5px solid var(--cyan-accent);">
                <div class="card-title" style="color: var(--cyan-accent); display: flex; justify-content: space-between; align-items: center;">
                    Monitor de Clase Actual
                    <span id="status-lector" class="badge-status">⌛ Cargando...</span>
                </div>

                <div class="current-class-info">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2 id="current-materia" style="margin: 0; color: #333; font-size: 1.5rem;">Verificando horario...</h2>
                            <p id="current-detalles" style="margin: 5px 0; color: #666;">--</p>
                        </div>
                        <div style="text-align: center;">
                            <div id="attendance-counter" class="attendance-count" style="font-size: 2.2rem; font-weight: 800; color: var(--cyan-accent);">-- / --</div>
                            <small style="color: #999; font-weight: 600;">ESCANEOS QR</small>
                        </div>
                    </div>
                </div>

                <div class="card-title" style="font-size: 0.9rem; margin-top: 10px; color: #555;">Resumen del Día:</div>
                <div class="table-responsive-wrapper">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Clase</th>
                                <th>Horario</th>
                                <th>Asistencia</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="historial-dia-docente">
                            <tr>
                                <td colspan="4" style="text-align:center; padding: 20px;">No hay registros previos hoy.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card exams-card">
                <div class="metrics-container">
                    <div class="metric-item">
                        <div class="circle-metric" id="metric-asistencia" style="color: #2ecc71; border: 4px solid #2ecc71;">--%</div>
                        <div class="metric-label">Asistencia Global</div>
                    </div>
                    <div class="metric-item">
                        <div class="circle-metric" id="metric-faltas" style="color: #e74c3c; border: 4px solid #e74c3c;">--</div>
                        <div class="metric-label">Faltas Totales</div>
                    </div>
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
                </div>
                <button class="carousel-nav prev" id="carousel-prev"><i class="fas fa-chevron-left"></i></button>
                <button class="carousel-nav next" id="carousel-next"><i class="fas fa-chevron-right"></i></button>
                <div class="carousel-indicators">
                    <div class="carousel-indicator active" data-slide-to="0"></div>
                    <div class="carousel-indicator" data-slide-to="1"></div>
                    <div class="carousel-indicator" data-slide-to="2"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. SEGURIDAD
        const token = localStorage.getItem('token_ute');
        const rol = localStorage.getItem('rol_usuario');
        const idDocente = localStorage.getItem('id_usuario');

        if (!token || rol !== 'ROLE_DOCENTE') {
            window.location.href = 'index.php';
            return;
        }

        // 2. INICIALIZAR FUNCIONES
        cargarMonitorClase(idDocente);   // Monitor de arriba
        cargarHistorialDia(idDocente);   // <--- ¡ESTO FALTABA! (Llena la tabla)
        cargarMisGrupos(idDocente);      // Tarjetas de grupos
        cargarMetricasGlobales(idDocente);

        if (typeof initCarousel === "function") initCarousel();
        else initCarouselLocal();
    });

    // --- 1. MONITOR EN VIVO ---
    async function cargarMonitorClase(id) {
        const txtMateria = document.getElementById('current-materia');
        const txtDetalles = document.getElementById('current-detalles');
        const txtContador = document.getElementById('attendance-counter');
        const badgeEstado = document.getElementById('status-lector');

        try {
            const response = await peticionAutenticada(`/asistencias/docente/monitor/${id}`);
            if (response.ok) {
                const data = await response.json();
                if (data.hayClase) {
                    txtMateria.innerText = data.materia;
                    txtMateria.style.color = "#333";
                    txtDetalles.innerText = `Grupo: ${data.grupo} | Salón: ${data.salon} | ${data.horario}`;
                    txtContador.innerText = `${data.alumnosPresentes} / ${data.alumnosTotales}`;
                    
                    if(badgeEstado) {
                        badgeEstado.innerHTML = "🟢 Lector Activo";
                        badgeEstado.className = "badge-status badge-success";
                        badgeEstado.style.backgroundColor = "#d4edda";
                        badgeEstado.style.color = "#155724";
                    }
                } else {
                    txtMateria.innerText = "Hora Libre";
                    txtMateria.style.color = "#999";
                    txtDetalles.innerText = "No tienes clases asignadas en este momento.";
                    txtContador.innerText = "-- / --";
                    if(badgeEstado) {
                        badgeEstado.innerHTML = "⚪ Inactivo";
                        badgeEstado.style.backgroundColor = "#e2e3e5";
                        badgeEstado.style.color = "#383d41";
                    }
                }
            }
        } catch (error) { console.error(error); }
    }

    // --- 2. HISTORIAL DEL DÍA (TABLA) ---
    async function cargarHistorialDia(id) {
        const tbody = document.getElementById('historial-dia-docente');
        
        try {
            // Llama al endpoint que creamos hace unos pasos
            const response = await peticionAutenticada(`/asistencias/docente/historial/${id}`);
            
            if (response.ok) {
                const clases = await response.json();
                tbody.innerHTML = ''; // Limpiar mensaje de "No hay registros"

                if (clases.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay clases hoy.</td></tr>';
                    return;
                }

                clases.forEach(c => {
                    // Colores según estado
                    let badgeColor = '#6c757d'; // Pendiente (Gris)
                    let badgeBg = '#e2e3e5';
                    
                    if(c.estado === 'En Curso') { 
                        badgeColor = '#155724'; badgeBg = '#d4edda'; // Verde
                    }
                    if(c.estado === 'Finalizada') {
                        badgeColor = '#004085'; badgeBg = '#cce5ff'; // Azul
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td style="font-weight: 600; color: #444;">${c.materia}</td>
                            <td>${c.horario}</td>
                            <td style="text-align: center;">${c.asistencia}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="background-color: ${badgeBg}; color: ${badgeColor}; padding: 5px 10px; border-radius: 10px; font-size: 0.8em;">
                                    ${c.estado}
                                </span>
                            </td>
                        </tr>
                    `;
                });
            }
        } catch (e) {
            console.error("Error historial:", e);
        }
    }

    // --- 3. GRUPOS ---
    async function cargarMisGrupos(id) {
        const container = document.getElementById('container-mis-grupos');
        if (!container) return;
        try {
            const response = await peticionAutenticada(`/horarios/docente/${id}`);
            if (response.ok) {
                const horarios = await response.json();
                const gruposUnicos = {};
                horarios.forEach(h => {
                    const mg = h.materiaGrupo;
                    const idMG = mg.id || mg.id_materia_grupo;
                    if (idMG && !gruposUnicos[idMG]) {
                        mg.idSeguro = idMG;
                        gruposUnicos[idMG] = mg;
                    }
                });
                container.innerHTML = '';
                Object.values(gruposUnicos).forEach(mg => {
                    const nombreGrupo = mg.grupo.nombre || mg.grupo.nombre_grupo;
                    container.innerHTML += `
                        <div class="grupo-card" onclick="location.href='detalle_grupo.php?id=${mg.idSeguro}'">
                            <h3>${mg.materia.nombre}</h3>
                            <p>Grupo: ${nombreGrupo} | ${mg.grupo.carrera}</p>
                            <div class="grupo-info-footer">
                                <span><i class="fas fa-user-graduate"></i> Ver Lista</span>
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    `;
                });
            }
        } catch (e) { console.error(e); }
    }

    // --- 4. UTILIDADES ---
    async function cargarMetricasGlobales(id) {
        // Aquí puedes conectar tus endpoints de métricas reales cuando los tengas
        document.getElementById('metric-asistencia').innerText = "94%";
        document.getElementById('metric-faltas').innerText = "8";
    }

    // LÓGICA DEL CARRUSEL (LOCAL)
    function initCarouselLocal() {
        const slideContainer = document.querySelector('.carousel-slide');
        if(!slideContainer) return;
        
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

        setInterval(() => {
            counter++;
            if (counter >= size) counter = 0;
            updateCarousel();
        }, 5000);
    }

    async function cargarMetricasGlobales(id) {
        // Aquí conectarás luego tus métricas
        document.getElementById('metric-asistencia').innerText = "94%";
        document.getElementById('metric-faltas').innerText = "8";
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>