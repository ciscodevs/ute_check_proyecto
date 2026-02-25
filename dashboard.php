<?php 
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/sidebar.php'; 
?>

<script>
    (function() {
        const rol = localStorage.getItem('rol_usuario');
        
        // Si es Alumno, lo mandamos a SU inicio
        if (rol === 'ROLE_ALUMNO') {
            window.location.href = 'alumno.php';
        } 
        // Si es Docente, lo mandamos a SU inicio
        else if (rol === 'ROLE_DOCENTE') {
            window.location.href = 'docente.php';
        }
        // Si no es Admin ni Prefecto, y llegó aquí, algo anda mal
        else if (rol !== 'ROLE_ADMIN' && rol !== 'ROLE_PREFECTO') {
            // (Opcional) Dejar pasar o redirigir
        }
    })();
</script>

<div class="dashboard-grid" style="display: block;"> <div class="welcome-banner" style="background: white; padding: 25px; border-radius: 8px; border-left: 5px solid var(--orange-primary); box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h2 style="margin: 0; color: #333;">Hola, <span id="dash-nombre-usuario" style="color: var(--orange-primary);">Administrador</span> 👋</h2>
        <p style="color: #666; margin-top: 5px;">Bienvenido al panel de control de UTE Check. Aquí tienes el resumen de hoy.</p>
    </div>

    <div class="overview-boxes" style="display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 40px;">
        
        <div class="box" style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-bottom: 4px solid #009688;">
            <div class="right-side">
                <div class="box-topic" style="color: #888; font-size: 14px; text-transform: uppercase;">Total Usuarios</div>
                <div class="number" id="count-usuarios" style="font-size: 35px; font-weight: 700; color: #333;">...</div>
            </div>
            <i class='fas fa-users' style="font-size: 40px; color: #009688; opacity: 0.2;"></i>
        </div>

        <div class="box" style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-bottom: 4px solid #F58220;">
            <div class="right-side">
                <div class="box-topic" style="color: #888; font-size: 14px; text-transform: uppercase;">Materias</div>
                <div class="number" id="count-materias" style="font-size: 35px; font-weight: 700; color: #333;">...</div>
            </div>
            <i class='fas fa-book' style="font-size: 40px; color: #F58220; opacity: 0.2;"></i>
        </div>

        <div class="box" style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-bottom: 4px solid #009688;">
            <div class="right-side">
                <div class="box-topic" style="color: #888; font-size: 14px; text-transform: uppercase;">Grupos</div>
                <div class="number" id="count-grupos" style="font-size: 35px; font-weight: 700; color: #333;">...</div>
            </div>
            <i class='fas fa-layer-group' style="font-size: 40px; color: #009688; opacity: 0.2;"></i>
        </div>

        <div class="box" style="flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-bottom: 4px solid #F58220;">
            <div class="right-side">
                <div class="box-topic" style="color: #888; font-size: 14px; text-transform: uppercase;">Alumnos</div>
                <div class="number" id="count-alumnos" style="font-size: 35px; font-weight: 700; color: #333;">...</div>
            </div>
            <i class='fas fa-user-graduate' style="font-size: 40px; color: #F58220; opacity: 0.2;"></i>
        </div>
    </div>

    <h3 style="font-size: 18px; color: #444; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
        <i class="fas fa-print" style="margin-right: 10px; color: #F58220;"></i> Generación de Reportes Rápidos
    </h3>
    
    <div class="reportes-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        
        <div class="card-reporte" onclick="descargarReporte('docentes')" 
             style="background: white; padding: 30px; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.3s; border: 1px solid transparent; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <i class="fas fa-chalkboard-teacher" style="font-size: 40px; color: #009688; margin-bottom: 15px;"></i>
            <h3 style="font-size: 16px; color: #333; font-weight: 500;">Directorio de Docentes</h3>
        </div>

        <div class="card-reporte" onclick="descargarReporte('alumnos')"
             style="background: white; padding: 30px; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.3s; border: 1px solid transparent; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <i class="fas fa-user-graduate" style="font-size: 40px; color: #F58220; margin-bottom: 15px;"></i>
            <h3 style="font-size: 16px; color: #333; font-weight: 500;">Directorio de Alumnos</h3>
        </div>

        <div class="card-reporte" onclick="window.location.href='grupos.php'"
             style="background: white; padding: 30px; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.3s; border: 1px solid transparent; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <i class="fas fa-calendar-alt" style="font-size: 40px; color: #009688; margin-bottom: 15px;"></i>
            <h3 style="font-size: 16px; color: #333; font-weight: 500;">Horario por Grupo</h3>
        </div>

        <div class="card-reporte" onclick="window.location.href='asistencia.php'"
             style="background: white; padding: 30px; border-radius: 12px; text-align: center; cursor: pointer; transition: 0.3s; border: 1px solid transparent; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <i class="fas fa-clipboard-list" style="font-size: 40px; color: #F58220; margin-bottom: 15px;"></i>
            <h3 style="font-size: 16px; color: #333; font-weight: 500;">Listas de Asistencia</h3>
        </div>

    </div>
</div>

<script>
    // Efecto Hover simple para las tarjetas
    document.querySelectorAll('.card-reporte').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            card.style.borderColor = '#F58220';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 2px 5px rgba(0,0,0,0.05)';
            card.style.borderColor = 'transparent';
        });
    });

    // Reloj
    function clock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-MX', { hour: 'numeric', minute: '2-digit', hour12: true });
        const el = document.getElementById('reloj-actual'); // ID corregido para que coincida con header.php
        if(el) el.innerText = timeString;
    }
    setInterval(clock, 1000);
    clock();

    // Cargar Datos
    document.addEventListener('DOMContentLoaded', async () => {
        const usuario = localStorage.getItem('usuario_actual');
        if(usuario) document.getElementById('dash-nombre-usuario').innerText = usuario;

        try {
            // Cargar usuarios
            const resUsers = await peticionAutenticada('/usuarios');
            if(resUsers && resUsers.ok) {
                const users = await resUsers.json();
                document.getElementById('count-usuarios').innerText = users.length;
                const alumnos = users.filter(u => u.rol === 'ALUMNO').length;
                document.getElementById('count-alumnos').innerText = alumnos;
            }

            // Cargar materias
            const resMat = await peticionAutenticada('/materias');
            if(resMat && resMat.ok) {
                const mat = await resMat.json();
                document.getElementById('count-materias').innerText = mat.length;
            }

            // Cargar grupos
            const resGrup = await peticionAutenticada('/grupos');
            if(resGrup && resGrup.ok) {
                const gru = await resGrup.json();
                document.getElementById('count-grupos').innerText = gru.length;
            }
        } catch (e) { console.error(e); }
    });

    // Descargar PDF
    async function descargarReporte(tipo) {
        const endpoint = `/reportes/${tipo}`;
        try {
            const token = localStorage.getItem('token_ute');
            const respuesta = await fetch(`http://localhost:8080/api${endpoint}`, {
                method: 'GET',
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if(respuesta.ok) {
                const blob = await respuesta.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Reporte_${tipo}.pdf`;
                document.body.appendChild(a);
                a.click();
                a.remove();
            } else {
                alert("No se pudo descargar el reporte.");
            }
        } catch(e) { 
            console.error(e); 
            alert("Error de conexión."); 
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>