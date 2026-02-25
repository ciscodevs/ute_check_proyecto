<?php 
// Incluimos la estructura modular del Dashboard
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
?>

<link rel="stylesheet" href="assets/css/crud.css">

<div class="home-content">
    
    <div class="crud-header">
        <h1><i class="fas fa-chalkboard-teacher"></i> Carga Académica</h1>
        
        <button class="btn-add" id="btn-nueva-asignacion" onclick="abrirModal()" style="opacity: 0.5; pointer-events: none;">
            <i class="fas fa-plus"></i> Asignar Materia
        </button>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px; border-left: 5px solid #009688;">
        <label style="font-weight: bold; font-size: 16px; color: #333;">1. Selecciona el Grupo a configurar:</label>
        <select id="select-grupo-filtro" onchange="cargarDatosDelGrupo()" style="width: 100%; padding: 10px; margin-top: 10px; border: 2px solid #ddd; border-radius: 5px; font-size: 15px;">
            <option value="" selected disabled>-- Cargando Grupos --</option>
        </select>
    </div>

    <div class="table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee;">
            <h3 id="titulo-tabla" style="margin: 0; display:none; color: #333;">Materias del Grupo</h3>
            <span id="badge-semestre" class="badge alumno" style="display: none; font-size: 14px;"></span>
        </div>
        
        <table>
            <thead>
                <tr style="background-color: #333;">
                    <th>ID Asig</th>
                    <th>Materia</th>
                    <th>Clave</th>
                    <th>Créditos</th>
                    <th>Semestre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-asignaciones-body">
                <tr><td colspan="6" style="text-align:center; padding: 30px; color: #666;">Selecciona un grupo arriba para ver su carga académica.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-asignacion" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="cerrarModal()">&times;</span>
        <h2 style="color: #009688;">Agregar Materia</h2>
        
        <form id="form-asignacion">
            <div style="background: #f4f6f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <p style="margin: 0; color: #333;">Grupo: <strong id="lbl-nombre-grupo" style="color: #000;">...</strong></p>
            </div>
            
            <div class="form-group">
                <label>Selecciona la Materia:</label>
                <select id="select-materia" required style="border: 2px solid #009688;">
                    <option value="" disabled selected>-- Cargando todas las materias --</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Asignación</button>
            </div>
        </form>
    </div>
</div>

<script>
    // IMPORTANTE: api.js se incluye en footer.php, no ponerlo aquí

    document.addEventListener('DOMContentLoaded', cargarListaGrupos);

    // 1. CARGAR GRUPOS EN EL SELECT (Orquestador de la página)
    async function cargarListaGrupos() {
        const select = document.getElementById('select-grupo-filtro');
        const respuesta = await peticionAutenticada('/grupos'); 

        if (respuesta && respuesta.ok) {
            const grupos = await respuesta.json();
            
            select.innerHTML = '<option value="" selected disabled>-- Selecciona un Grupo --</option>';
            
            grupos.forEach(g => {
                const option = document.createElement('option');
                option.value = g.id;
                option.text = `${g.nombre} - ${g.carrera} (${g.turno})`;
                select.appendChild(option);
            });
        }
        
        // Cargar TODAS las materias al inicio para el modal
        cargarTodasLasMaterias();
    }
    
    // 2. CARGAR TODAS LAS MATERIAS (Para el modal de asignación)
    async function cargarTodasLasMaterias() {
        const select = document.getElementById('select-materia');
        const respuesta = await peticionAutenticada('/materias');
        
        if (respuesta && respuesta.ok) {
            const materias = await respuesta.json();
            select.innerHTML = '<option value="" disabled selected>-- Selecciona una Materia --</option>';
            
            materias.forEach(m => {
                select.innerHTML += `<option value="${m.id}">${m.clave} - ${m.nombre} (${m.semestre}° Sem)</option>`;
            });
        }
    }


    // 3. ORQUESTADOR: CUANDO CAMBIA EL GRUPO SELECCIONADO
    function cargarDatosDelGrupo() {
        const selectGrupo = document.getElementById('select-grupo-filtro');
        const idGrupo = selectGrupo.value;

        if(!idGrupo) return;

        // A. Habilitar Interfaz y actualizar etiquetas
        const btn = document.getElementById('btn-nueva-asignacion');
        btn.style.opacity = "1";
        btn.style.pointerEvents = "auto";
        document.getElementById('titulo-tabla').style.display = 'block';
        document.getElementById('lbl-nombre-grupo').innerText = selectGrupo.options[selectGrupo.selectedIndex].text;

        // B. Cargar la tabla
        cargarTablaAsignaciones(idGrupo);
    }

    // 4. CARGAR TABLA (Usando el endpoint de JOIN FETCH)
    async function cargarTablaAsignaciones(idGrupo) {
        const tbody = document.getElementById('tabla-asignaciones-body');
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando materias asignadas...</td></tr>';

        // Llama al repositorio con JOIN FETCH
        const respuesta = await peticionAutenticada(`/asignaciones/grupo/${idGrupo}`); 

        if (respuesta && respuesta.ok) {
            const asignaciones = await respuesta.json();
            tbody.innerHTML = '';

            if (asignaciones.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 20px;">Este grupo aún no tiene materias asignadas.</td></tr>';
                return;
            }

            asignaciones.forEach(a => {
                // ↓↓↓ PROTECCIÓN CRÍTICA CONTRA NULL (Lazy Loading Fix) ↓↓↓
                const nombreMateria = a.materia ? a.materia.nombre : 'ERROR DE CARGA';
                const claveMateria = a.materia ? a.materia.clave : 'N/A';
                const creditosMateria = a.materia ? a.materia.creditos : 'N/A';
                const semestreMateria = a.materia ? a.materia.semestre : 'N/A';

                tbody.innerHTML += `
                    <tr>
                        <td>${a.id}</td>
                        <td><strong>${nombreMateria}</strong></td>
                        <td>${claveMateria}</td>
                        <td>${creditosMateria}</td>
                        <td>${semestreMateria}°</td>
                        <td>
                            <button class="btn-icon" onclick="eliminarAsignacion(${a.id})" title="Quitar materia">
                                <i class="fas fa-trash" style="color:red; cursor: pointer;"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
             tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color: red;">Error al conectar con la API para las asignaciones.</td></tr>';
        }
    }

    // 5. GUARDAR ASIGNACIÓN (POST)
    document.getElementById('form-asignacion').addEventListener('submit', async (e) => {
        e.preventDefault();

        const idGrupo = document.getElementById('select-grupo-filtro').value;
        const idMateria = document.getElementById('select-materia').value;

        // DTO enviado a la API
        const data = {
            idGrupo: parseInt(idGrupo),
            idMateria: parseInt(idMateria)
        };

        const respuesta = await peticionAutenticada('/asignaciones', 'POST', data);

        if (respuesta && respuesta.ok) {
            alert("Materia asignada correctamente");
            cerrarModal();
            // Recarga la tabla con el grupo actual
            cargarTablaAsignaciones(idGrupo); 
        } else {
            const err = await respuesta.json();
            alert("Error: " + (err.mensaje || "Ocurrió un error inesperado al guardar."));
        }
    });

    // 6. ELIMINAR ASIGNACIÓN (DELETE)
    async function eliminarAsignacion(id) {
        if(!confirm("¿Estás seguro de quitar esta materia del grupo? Esto podría afectar la programación de horarios.")) return;

        const respuesta = await peticionAutenticada(`/asignaciones/${id}`, 'DELETE');
        
        if(respuesta && respuesta.ok) {
            alert("Asignación eliminada correctamente.");
            const idGrupo = document.getElementById('select-grupo-filtro').value;
            cargarTablaAsignaciones(idGrupo);
        } else {
            const err = await respuesta.json();
            alert("No se pudo eliminar: " + (err.mensaje || "Hubo un error al intentar eliminar la asignación."));
        }
    }

    // --- LÓGICA DEL MODAL ---
    const modal = document.getElementById('modal-asignacion');
    function abrirModal() { modal.style.display = 'flex'; }
    function cerrarModal() { modal.style.display = 'none'; }
    window.onclick = function(e) { if (e.target == modal) cerrarModal(); }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>