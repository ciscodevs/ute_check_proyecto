<?php 
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
$id_materia_grupo = $_GET['id'] ?? 0;
?>

<link rel="stylesheet" href="assets/css/alumno.css">
<link rel="stylesheet" href="assets/css/docentes.css"> 

<div class="home-content">
    <div class="container-fluid" style="padding: 0 30px;">
        
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
            <a href="mis_grupos.php" class="btn-back" style="text-decoration: none; color: #666;">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <div>
                <h2 id="titulo-materia" style="margin: 0; color: #333;">Cargando materia...</h2>
                <p id="subtitulo-grupo" style="margin: 0; color: #888;">Grupo: -- | Carrera: --</p>
            </div>
        </div>

        <div class="cards-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
            <div class="card" style="padding: 15px; text-align: center;">
                <div style="font-size: 0.8rem; color: #999;">TOTAL ALUMNOS</div>
                <div id="count-alumnos" style="font-size: 1.5rem; font-weight: 700;">--</div>
            </div>
            <div class="card" style="padding: 15px; text-align: center;">
                <div style="font-size: 0.8rem; color: #999;">PROMEDIO ASISTENCIA</div>
                <div id="avg-asistencia" style="font-size: 1.5rem; font-weight: 700; color: #2ecc71;">--%</div>
            </div>
            <div class="card" style="padding: 15px; text-align: center;">
                <div style="font-size: 0.8rem; color: #999;">ALUMNOS EN RIESGO</div>
                <div id="risk-alumnos" style="font-size: 1.5rem; font-weight: 700; color: #e74c3c;">--</div>
            </div>
        </div>

        <div class="alumnos-table-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4 style="margin: 0;">Lista de Alumnos Inscritos</h4>
                <button onclick="exportarLista()" class="btn-crud btn-add" style="background: #10b981;">
                    <i class="fas fa-file-excel"></i> Exportar Lista
                </button>
            </div>

            <div class="table-responsive-wrapper">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Matrícula</th>
                            <th>Asistencias</th>
                            <th>Faltas</th>
                            <th>% Progreso</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody id="lista-alumnos-body">
                        <tr><td colspan="6" style="text-align: center; padding: 40px;">Cargando lista de alumnos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const idMG = <?php echo $id_materia_grupo; ?>;

    document.addEventListener('DOMContentLoaded', () => {
        cargarDetalleGrupo();
    });

    async function cargarDetalleGrupo() {
        try {
            // 1. Obtener info de la materia y grupo
            // 2. Obtener lista de alumnos con sus estadísticas de la API
            const response = await peticionAutenticada(`/asistencias/docente/reporte-grupo/${idMG}`);
            
            if (response.ok) {
                const data = await response.json();
                renderizarTablaAlumnos(data.alumnos);
                document.getElementById('titulo-materia').innerText = data.materiaNombre;
                document.getElementById('subtitulo-grupo').innerText = `Grupo: ${data.grupoNombre} | ${data.carrera}`;
            }
        } catch (e) {
            console.error(e);
        }
    }

    function renderizarTablaAlumnos(alumnos) {
        const tbody = document.getElementById('lista-alumnos-body');
        tbody.innerHTML = '';

        alumnos.forEach(al => {
            const statusClass = al.faltas > 3 ? 'status-danger' : 'status-success';
            const statusText = al.faltas > 3 ? 'Riesgo' : 'Regular';

            tbody.innerHTML += `
                <tr>
                    <td>
                        <div class="user-info-cell">
                            <img src="assets/img/default-user.png" class="user-img-mini">
                            <span>${al.nombreCompleto}</span>
                        </div>
                    </td>
                    <td>${al.matricula}</td>
                    <td style="text-align:center;">${al.asistencias}</td>
                    <td style="text-align:center; font-weight:bold; color:${al.faltas > 3 ? '#e74c3c' : '#333'}">${al.faltas}</td>
                    <td style="width: 150px;">
                        <div style="font-size: 0.7rem;">${al.porcentaje}%</div>
                        <div class="stats-bar-container">
                            <div class="stats-bar-fill" style="width: ${al.porcentaje}%"></div>
                        </div>
                    </td>
                    <td><span class="status-pill ${statusClass}">${statusText}</span></td>
                </tr>
            `;
        });
    }

    function exportarLista() {
        alert("Generando archivo Excel del grupo " + idMG);
    }

    async function cargarListaAlumnosBD() {
    const urlParams = new URLSearchParams(window.location.search);
    const idMG = urlParams.get('id'); // El ID que pasamos por la URL

    try {
        const res = await peticionAutenticada(`/asistencias/reporte-grupo/${idMG}`);
        if (res.ok) {
            const data = await res.json(); // Esperamos { materiaNombre: "...", alumnos: [...] }
            
            document.getElementById('titulo-materia').innerText = data.materiaNombre;
            const tbody = document.getElementById('lista-alumnos-body');
            tbody.innerHTML = '';

            data.alumnos.forEach(alumno => {
                // Cálculo de estatus (Lógica de negocio solicitada)
                const porcentaje = (alumno.asistencias / data.clasesTotales) * 100;
                const enRiesgo = alumno.faltas >= 3;

                tbody.innerHTML += `
                    <tr>
                        <td>${alumno.nombreCompleto}</td>
                        <td>${alumno.matricula}</td>
                        <td class="text-center">${alumno.asistencias}</td>
                        <td class="text-center" style="color: ${enRiesgo ? 'red' : 'inherit'}">
                            ${alumno.faltas}
                        </td>
                        <td>
                            <div class="stats-bar-container">
                                <div class="stats-bar-fill" style="width: ${porcentaje}%"></div>
                            </div>
                        </td>
                        <td>
                            <span class="status-pill ${enRiesgo ? 'status-danger' : 'status-success'}">
                                ${enRiesgo ? 'Riesgo' : 'Regular'}
                            </span>
                        </td>
                    </tr>`;
            });
        }
    } catch (e) { console.error("Error al cargar lista:", e); }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>