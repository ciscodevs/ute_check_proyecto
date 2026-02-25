<?php 
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
?>

<link rel="stylesheet" href="assets/css/crud.css">

<div class="home-content">
    
    <div class="crud-header">
        <h1><i class="fas fa-layer-group"></i> Gestión de Grupos</h1>
        <button class="btn-add" onclick="abrirModal()">
            <i class="fas fa-plus"></i> Nuevo Grupo
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr style="background-color: #009688;"> <th>ID</th>
                    <th>Nombre</th>
                    <th>Grado</th>
                    <th>Carrera</th>
                    <th>Turno</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-grupos-body">
                <tr><td colspan="6" style="text-align:center;">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-grupo" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="cerrarModal()">&times;</span>
        <h2 style="color: #009688;">Registrar Grupo</h2>
        
        <form id="form-grupo">
            <div class="form-group">
                <label>Nombre del Grupo:</label>
                <input type="text" id="nombre" required placeholder="Ej: 501-A">
            </div>
            
            <div class="form-group">
                <label>Carrera:</label>
                <input type="text" id="carrera" required placeholder="Ej: Ingeniería en Software">
            </div>

            <div class="form-group">
                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Grado (Cuatrimestre):</label>
                        <input type="number" id="grado" required min="1" max="12">
                    </div>
                    <div style="flex: 1;">
                        <label>Turno:</label>
                        <select id="turno" required>
                            <option value="Matutino">Matutino</option>
                            <option value="Vespertino">Vespertino</option>
                            <option value="Nocturno">Nocturno</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-save">Guardar Grupo</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', cargarGrupos);

    // 1. LISTAR
    async function cargarGrupos() {
        const tbody = document.getElementById('tabla-grupos-body');
        const respuesta = await peticionAutenticada('/grupos');

        if (respuesta && respuesta.ok) {
            const grupos = await respuesta.json();
            tbody.innerHTML = '';

            if (grupos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No hay grupos registrados.</td></tr>';
                return;
            }

            grupos.forEach(g => {
                tbody.innerHTML += `
                    <tr>
                        <td>${g.id}</td>
                        <td><strong>${g.nombre}</strong></td>
                        <td>${g.grado}°</td>
                        <td>${g.carrera}</td>
                        <td><span class="badge ${g.turno === 'Matutino' ? 'docente' : 'alumno'}">${g.turno}</span></td>
                        <td>
                            <button class="btn-icon delete" onclick="eliminarGrupo(${g.id})">
                                <i class="fas fa-trash" style="color:red;"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:red;">Error de conexión</td></tr>';
        }
    }

    // 2. GUARDAR
    document.getElementById('form-grupo').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const data = {
            nombre: document.getElementById('nombre').value,
            carrera: document.getElementById('carrera').value,
            grado: document.getElementById('grado').value,
            turno: document.getElementById('turno').value
        };

        const respuesta = await peticionAutenticada('/grupos', 'POST', data);
        
        if (respuesta && respuesta.ok) {
            alert("Grupo creado correctamente");
            cerrarModal();
            cargarGrupos();
        } else {
            alert("Error al crear el grupo.");
        }
    });

    // 3. ELIMINAR
    async function eliminarGrupo(id) {
        if(!confirm("¿Seguro de eliminar este grupo?")) return;

        const respuesta = await peticionAutenticada(`/grupos/${id}`, 'DELETE');
        if (respuesta && respuesta.ok) {
            alert("Grupo eliminado");
            cargarGrupos();
        } else {
            // Aquí saltará tu excepción personalizada de Java si tiene alumnos
            const error = await respuesta.json(); 
            alert("No se puede eliminar: " + (error.mensaje || "Error desconocido"));
        }
    }

    // MODAL
    const modal = document.getElementById('modal-grupo');
    function abrirModal() { modal.style.display = 'flex'; }
    function cerrarModal() { modal.style.display = 'none'; document.getElementById('form-grupo').reset(); }
    window.onclick = function(e) { if (e.target == modal) cerrarModal(); }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>