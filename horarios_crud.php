<?php 
// Incluimos la estructura modular
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
?>

<link rel="stylesheet" href="assets/css/crud.css">

<div class="home-content">
    
    <div class="crud-header">
        <h1><i class="far fa-calendar-alt"></i> Gestión de Horarios</h1>
        <button class="btn-add" onclick="abrirModal()">
            <i class="fas fa-plus"></i> Programar Clase
        </button>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px; border-left: 5px solid #009688;">
        <label style="font-weight: bold; color: #333;">📅 Ver agenda del Docente:</label>
        <div style="display: flex; gap: 10px; margin-top: 10px;">
            <select id="filtro-docente" onchange="cargarHorarioDocente()" class="form-control" style="flex: 1;">
                <option value="" selected disabled>-- Selecciona un Docente --</option>
            </select>
            <button class="btn-save" onclick="cargarHorarioDocente()" style="padding: 0 20px;">
                <i class="fas fa-search"></i> Ver
            </button>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr style="background-color: #333;">
                    <th>Día</th>
                    <th>Horario</th>
                    <th>Materia</th>
                    <th>Grupo (Semestre)</th>
                    <th>Salón</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-horarios-body">
                <tr><td colspan="6" style="text-align:center; padding: 30px; color: #666;">Selecciona un docente para ver su carga.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-horario" class="modal">
    <div class="modal-content" style="width: 650px; max-width: 95%;">
        <span class="close-modal" onclick="cerrarModal()">&times;</span>
        <h2 style="color: #009688; margin-top:0;">Programar Clase</h2>
        
        <form id="form-horario">
            
            <div style="background-color: #f4f6f9; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <h4 style="margin-top: 0; color: #555; border-bottom: 1px solid #ccc; padding-bottom: 5px;">1. Datos Académicos</h4>
                
                <div class="form-group">
                    <label>Docente:</label>
                    <select id="select-docente" required class="form-control"></select>
                </div>

                <div class="form-group" style="background: #e0f7fa; padding: 10px; border-radius: 5px;">
                    <label style="color: #006064;">Grupo:</label>
                    <select id="select-grupo" required onchange="cargarMateriasDelGrupo()" class="form-control" style="border-left: 4px solid #F58220;">
                        <option value="" disabled selected>-- Selecciona Grupo --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Materia (Asignada a este grupo):</label>
                    <select id="select-materia-grupo" required disabled class="form-control" style="background-color: #f9f9f9;">
                        <option value="" disabled selected>-- Primero selecciona el Grupo --</option>
                    </select>
                    <small style="color: #666;">* La lista está filtrada por las materias asignadas al grupo.</small>
                </div>
            </div>

            <div style="background-color: #e0f2f1; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0; color: #00695c; border-bottom: 1px solid #80cbc4; padding-bottom: 5px;">2. Tiempo y Lugar</h4>
                
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Día:</label>
                        <select id="dia" required class="form-control">
                            <option value="Lunes" selected>Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Salón:</label>
                        <input type="text" id="salon" required placeholder="Ej: A-10" class="form-control">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Hora Inicio:</label>
                        <input type="time" id="horaInicio" required class="form-control">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Hora Fin:</label>
                        <input type="time" id="horaFin" required class="form-control">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Horario</button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; display: block; }
    input[type="time"] { font-family: sans-serif; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        cargarCombosIniciales();
    });
    
    // --- CACHE ---
    let DOCENTES_CACHE = []; 
    let GRUPOS_CACHE = []; 

    // 1. CARGAR DOCENTES Y GRUPOS INICIALES
    async function cargarCombosIniciales() {
        // A. Docentes (Filtramos solo DOCENTES)
        const resUsuarios = await peticionAutenticada('/usuarios');
        if (resUsuarios && resUsuarios.ok) {
            const usuarios = await resUsuarios.json();
            DOCENTES_CACHE = usuarios.filter(u => u.rol === 'DOCENTE');
            
            let opciones = '<option value="" selected disabled>-- Selecciona Docente --</option>';
            DOCENTES_CACHE.forEach(d => {
                // FIX: Usamos d.username como nombre visible, ya que 'd.nombre' es undefined
                // Si tu DTO usa 'd.nombreCompleto', cámbialo a eso.
                const nombreVisible = d.nombreCompleto || d.username; 

                opciones += `<option value="${d.id}">${nombreVisible} (ID: ${d.id})</option>`; 
            });
            
            document.getElementById('filtro-docente').innerHTML = opciones;
            document.getElementById('select-docente').innerHTML = opciones;
        }

        // B. Grupos
        const resGrupos = await peticionAutenticada('/grupos');
        if (resGrupos && resGrupos.ok) {
            GRUPOS_CACHE = await resGrupos.json();
            let opciones = '<option value="" selected disabled>-- Selecciona Grupo --</option>';
            GRUPOS_CACHE.forEach(g => {
                opciones += `<option value="${g.id}">${g.nombre} (${g.grado}° ${g.turno})</option>`;
            });
            document.getElementById('select-grupo').innerHTML = opciones;
        }
    }

    // 2. CASCADA: AL ELEGIR GRUPO, CARGAR SUS MATERIAS ASIGNADAS (FILTRADAS)
    async function cargarMateriasDelGrupo() {
        const idGrupo = document.getElementById('select-grupo').value;
        const selectMateria = document.getElementById('select-materia-grupo');
        
        selectMateria.innerHTML = '<option>Buscando carga académica...</option>';
        selectMateria.disabled = true;

        if (!idGrupo) return;

        // Llama a la API de asignaciones que ya trae el JOIN FETCH (la Materia)
        const respuesta = await peticionAutenticada(`/asignaciones/grupo/${idGrupo}`);

        if (respuesta && respuesta.ok) {
            const asignaciones = await respuesta.json();
            
            if(asignaciones.length === 0) {
                selectMateria.innerHTML = '<option value="" disabled selected>⚠️ Este grupo no tiene materias asignadas</option>';
                return;
            }

            selectMateria.innerHTML = '<option value="" disabled selected>-- Selecciona Materia --</option>';
            
            asignaciones.forEach(a => {
                // Protección contra NULL
                const nombre = a.materia ? a.materia.nombre : 'Materia Corrupta';
                const semestre = a.materia ? a.materia.semestre : 'N/A';
                
                selectMateria.innerHTML += `<option value="${a.id}">
                    ${nombre} (${semestre}° Sem)
                </option>`;
            });
            
            selectMateria.disabled = false;
        } else {
            selectMateria.innerHTML = '<option>Error al cargar materias</option>';
        }
    }

    // 3. GUARDAR HORARIO (POST)
    document.getElementById('form-horario').addEventListener('submit', async (e) => {
        e.preventDefault();

        const horaInicio = document.getElementById('horaInicio').value;
        const horaFin = document.getElementById('horaFin').value;

        if (horaInicio >= horaFin) {
            alert("Error: La hora de inicio debe ser antes que la hora de fin.");
            return;
        }
        
        // CONSTRUCCIÓN DEL DTO DE JAVA
        const data = {
            idDocente: document.getElementById('select-docente').value,
            idMateriaGrupo: document.getElementById('select-materia-grupo').value,
            diaSemana: document.getElementById('dia').value,
            horaInicio: horaInicio + ":00", // Formato HH:mm:ss
            horaFin: horaFin + ":00",
            salon: document.getElementById('salon').value
        };

        const respuesta = await peticionAutenticada('/horarios', 'POST', data);

        if (respuesta && respuesta.ok) {
            alert("¡Horario creado exitosamente!");
            cerrarModal();
            const filtroActual = document.getElementById('filtro-docente').value;
            if (filtroActual == data.idDocente) {
                cargarHorarioDocente();
            }
        } else {
            const err = await respuesta.json();
            alert("Error: " + (err.mensaje || "Error desconocido al crear el horario."));
        }
    });

    // 4. VER HORARIO (TABLA)
    async function cargarHorarioDocente() {
        const idDocente = document.getElementById('filtro-docente').value;
        const tbody = document.getElementById('tabla-horarios-body');
        
        if (!idDocente) return;

        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando agenda...</td></tr>';

        // Llama al servicio que usa el JOIN FETCH (obtenerHorarioPorDocente)
        const respuesta = await peticionAutenticada(`/horarios/docente/${idDocente}`); 

        if (respuesta && respuesta.ok) {
            const horarios = await respuesta.json();
            tbody.innerHTML = '';

            if(horarios.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Este docente no tiene clases asignadas.</td></tr>';
                return;
            }

            horarios.forEach(h => {
                // Usamos protección contra NULL
                const nombreMateria = h.materiaGrupo && h.materiaGrupo.materia ? h.materiaGrupo.materia.nombre : 'ERROR';
                const nombreGrupo = h.materiaGrupo && h.materiaGrupo.grupo ? h.materiaGrupo.grupo.nombre : 'ERROR';
                const gradoGrupo = h.materiaGrupo && h.materiaGrupo.grupo ? h.materiaGrupo.grupo.grado : 'N/A';

                tbody.innerHTML += `
                    <tr>
                        <td><span class="badge docente">${h.diaSemana}</span></td>
                        <td>${h.horaInicio} - ${h.horaFin}</td>
                        <td><strong>${nombreMateria}</strong></td>
                        <td>${nombreGrupo} (${gradoGrupo}°)</td>
                        <td>${h.salon}</td>
                        <td>
                            <button class="btn-icon" onclick="alert('Implementar Delete')">
                                <i class="fas fa-trash" style="color:#ccc;"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }
    }

    // MODAL
    const modal = document.getElementById('modal-horario');
    function abrirModal() { modal.style.display = 'flex'; }
    function cerrarModal() { modal.style.display = 'none'; document.getElementById('form-horario').reset(); }
    window.onclick = function(e) { if (e.target == modal) cerrarModal(); }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>