<?php 
// Incluimos la estructura modular
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
?>

<!-- Cargamos estilos de CRUD -->
<link rel="stylesheet" href="assets/css/crud.css">

<div class="home-content">
    <div class="crud-header">
        <h1><i class="fas fa-user-plus"></i> Inscripción de Alumnos</h1>
    </div>

    <!-- TARJETA PRINCIPAL DE INSCRIPCIÓN -->
    <div class="card" style="max-width: 600px; margin: 0 auto; padding: 30px; border-left: 5px solid #F58220;">
        <h3 style="color: #F58220; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
            Asignar Grupo Completo a Alumno
        </h3>
        <p style="color: #666; margin-bottom: 20px;">
            Esta acción enrolará al alumno en un grupo, copiando todos los horarios de las materias asignadas.
        </p>

        <form id="form-inscripcion">
            
            <div class="form-group">
                <label>1. Selecciona al Alumno:</label>
                <!-- Llenado con alumnos NO asignados -->
                <select id="select-alumno" required class="form-control" style="font-size: 16px;">
                    <option value="" disabled selected>Cargando alumnos...</option>
                </select>
                <small style="color: #999;">Solo se muestran alumnos que no tienen horarios asignados.</small>
            </div>

            <div class="form-group">
                <label>2. Selecciona el Grupo:</label>
                <!-- Llenado con todos los grupos -->
                <select id="select-grupo" required class="form-control" style="font-size: 16px;">
                    <option value="" disabled selected>Cargando grupos...</option>
                </select>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" class="btn-save" style="width: 100%; font-size: 18px;">
                    <i class="fas fa-check-circle"></i> Inscribir Alumno
                </button>
            </div>

        </form>
        
        <!-- Área de Mensajes de Resultado -->
        <div id="mensaje-resultado" style="margin-top: 20px; padding: 15px; border-radius: 5px; display: none; text-align: center;"></div>
    </div>
</div>

<!-- LÓGICA JAVASCRIPT -->
<script>
    document.addEventListener('DOMContentLoaded', cargarCombos);

    // 1. CARGAR COMBOS (Alumnos NO ASIGNADOS y Grupos)
    async function cargarCombos() {
        // Alumnos (Llama al endpoint filtrado /alumnos/unassigned)
        const resAlu = await peticionAutenticada('/alumnos/unassigned');
        const selectAlumno = document.getElementById('select-alumno');
        
        if (resAlu && resAlu.ok) {
            const usuariosUnassigned = await resAlu.json();
            
            let opts = '<option value="" selected disabled>-- Selecciona Alumno --</option>';
            if (usuariosUnassigned.length === 0) {
                 opts = '<option value="" disabled selected>⚠️ Todos los alumnos ya tienen horario</option>';
            } else {
                usuariosUnassigned.forEach(u => {
                    // Mostramos el username (porque el nombre completo era inestable)
                    opts += `<option value="${u.id}">${u.username} (${u.rol})</option>`; 
                });
            }
            selectAlumno.innerHTML = opts;
        } else {
            selectAlumno.innerHTML = '<option value="" disabled selected>❌ Error al cargar alumnos</option>';
        }

        // Grupos (Llama a /grupos)
        const resGrup = await peticionAutenticada('/grupos');
        const selectGrupo = document.getElementById('select-grupo');

        if (resGrup && resGrup.ok) {
            const grupos = await resGrup.json();
            let opts = '<option value="" selected disabled>-- Selecciona Grupo --</option>';
            grupos.forEach(g => {
                opts += `<option value="${g.id}">${g.nombre} (${g.grado}° ${g.turno})</option>`;
            });
            selectGrupo.innerHTML = opts;
        } else {
            selectGrupo.innerHTML = '<option value="" disabled selected>❌ Error al cargar grupos</option>';
        }
    }

    // 2. PROCESAR INSCRIPCIÓN (POST a /horarios/alumno/inscripcion-grupo)
    document.getElementById('form-inscripcion').addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = document.querySelector('.btn-save');
        const msg = document.getElementById('mensaje-resultado');
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscribiendo...';
        btn.disabled = true;
        msg.style.display = 'none';

        const data = {
            idAlumno: document.getElementById('select-alumno').value,
            idGrupo: document.getElementById('select-grupo').value
        };

        // Llama al endpoint de copia masiva de horarios
        const respuesta = await peticionAutenticada('/horarios/alumno/inscripcion-grupo', 'POST', data);

        try {
            if (respuesta && respuesta.ok) {
                const texto = await respuesta.text(); 
                
                msg.style.display = 'block';
                msg.style.backgroundColor = '#d4edda';
                msg.style.color = '#155724';
                msg.innerText = "✅ " + texto;
                
                // Si la inscripción fue exitosa, recargamos la lista para que el alumno desaparezca
                cargarCombos(); 

            } else {
                const errorJson = respuesta ? await respuesta.json() : {mensaje: "Error de conexión"};
                
                msg.style.display = 'block';
                msg.style.backgroundColor = '#f8d7da';
                msg.style.color = '#721c24';
                msg.innerText = "❌ Error: " + (errorJson.message || errorJson.mensaje || "Error desconocido");
            }
        } catch (error) {
             msg.style.display = 'block';
             msg.style.backgroundColor = '#f8d7da';
             msg.style.color = '#721c24';
             msg.innerText = "❌ Error interno del servidor.";
        }

        btn.innerHTML = originalText;
        btn.disabled = false;
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>