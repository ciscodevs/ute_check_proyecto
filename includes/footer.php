</div> </section> </div> </div> <script src="assets/js/api.js"></script>

<script>
    // --- 1. DEFINICIÓN DE BLOQUES DE HORARIO FIJOS ---
    const BLOQUES_HORA_FIJOS = [
        { inicio: "07:00:00", fin: "07:50:00", label: "1ª Clase", tipo: "CLASE" },
        { inicio: "07:50:00", fin: "08:40:00", label: "2ª Clase", tipo: "CLASE" },
        { inicio: "08:40:00", fin: "09:30:00", label: "3ª Clase", tipo: "CLASE" },
        { inicio: "09:30:00", fin: "10:10:00", label: "RECESO", tipo: "RECESO" },
        { inicio: "10:10:00", fin: "11:00:00", label: "4ª Clase", tipo: "CLASE" },
        { inicio: "11:00:00", fin: "11:50:00", label: "5ª Clase", tipo: "CLASE" },
        { inicio: "11:50:00", fin: "12:40:00", label: "6ª Clase", tipo: "CLASE" },
        { inicio: "12:40:00", fin: "13:30:00", label: "7ª Clase", tipo: "CLASE" },
        { inicio: "13:30:00", fin: "14:20:00", label: "8ª Clase", tipo: "CLASE" }
    ];

    document.addEventListener("DOMContentLoaded", () => {
        
        // --- A. GESTIÓN DE ROLES Y PERMISOS (RBAC) ---
        const userRole = localStorage.getItem('rol_usuario');

        if (userRole) {
            // 1. Ocultar botones
            const menuItems = document.querySelectorAll('.role-based');
            menuItems.forEach(item => {
                const allowedRoles = item.getAttribute('data-roles');
                if (!allowedRoles.includes(userRole)) {
                    item.style.display = 'none';
                }
            });

            // 2. Redirigir botón "Inicio" 
            const linkInicio = document.querySelector('a[href="dashboard.php"]');
            if (linkInicio) {
                if (userRole === 'ROLE_ALUMNO') {
                    linkInicio.href = 'alumno.php'; 
                } else if (userRole === 'ROLE_DOCENTE') {
                    linkInicio.href = 'docente.php'; 
                }
            }
        }

        // --- B. MOSTRAR NOMBRE DE USUARIO ---
        const username = localStorage.getItem('usuario_actual');
        const lblSidebarUser = document.getElementById('sidebar-user');
        if (username && lblSidebarUser) {
            lblSidebarUser.innerText = username;
        }
        
        // --- C. ANIMACIÓN DEL SIDEBAR ---
        const sidebar = document.querySelector(".sidebar");
        const closeBtn = document.querySelector("#btn-menu");
        if (closeBtn) {
            closeBtn.addEventListener("click", () => {
                sidebar.classList.toggle("open");
                if (sidebar.classList.contains("open")) {
                    closeBtn.classList.replace("fa-bars", "fa-arrow-left");
                } else {
                    closeBtn.classList.replace("fa-arrow-left", "fa-bars");
                }
            });
        }
        
        // --- D. INICIAR RELOJ Y ESTADO ---
        setInterval(actualizarRelojYEstado, 1000); 
        actualizarRelojYEstado();
    });

    // --- FUNCIONES DE RELOJ Y API ---

    function actualizarRelojYEstado() {
        const now = new Date();
        
        // 1. Actualizar Hora del Reloj
        const timeString = now.toLocaleTimeString('es-MX', { hour: 'numeric', minute: '2-digit', hour12: true });
        const elReloj = document.getElementById('reloj-global');
        if (elReloj) elReloj.innerText = timeString;

        // 2. Verificar el Estado de la Clase (Solo se llama cada 10 segundos)
        const segundos = now.getSeconds();
        if (segundos % 10 === 0) { 
             checkClaseStatus();
        }
    }

    async function checkClaseStatus() {
        const estadoDiv = document.getElementById('texto-estado');
        const navHeader = document.querySelector('.home-section nav');
        if (!estadoDiv || !navHeader) return;

        const now = new Date();
        const horaActual = now.toTimeString().substring(0, 8); 
        
        let estadoEncontrado = "LIBRE";
        let descripcionEncontrada = "No hay clases programadas ahora.";
        let colorFondo = '#34495e'; 

        // 1. Verificación: ¿Terminó la jornada? (FIN DE JORNADA)
        if (horaActual > '14:20:00' && now.getDay() !== 6 && now.getDay() !== 0) { // Día 6 es Sábado, 0 es Domingo
             estadoEncontrado = "FIN DE JORNADA";
             descripcionFinal = "El horario académico ha concluido.";
             colorFondo = '#1d1b31';
        } else {
            // 2. Encontrar el bloque de tiempo fijo actual
            const bloqueActual = BLOQUES_HORA_FIJOS.find(bloque => {
                return horaActual >= bloque.inicio && horaActual < bloque.fin;
            });

            if (bloqueActual) {
                const horaFin = bloqueActual.fin.substring(0, 5); // HH:mm
                
                if (bloqueActual.tipo === 'RECESO') {
                    estadoEncontrado = "RECESO";
                    descripcionEncontrada = "Tiempo de descanso. Termina: " + horaFin;
                    colorFondo = '#F58220'; // Naranja (Receso)
                } else {
                    // Si es un bloque de clase, consultamos la API para ver si el usuario tiene clase
                    const endpoint = '/horarios/status/me'; 
                    const res = await peticionAutenticada(endpoint);
                    
                    if (res && res.ok) {
                        const statusAPI = await res.json();
                        
                        if (statusAPI.estado === 'CLASE ACTIVA') {
                            estadoEncontrado = bloqueActual.label;
                            descripcionEncontrada = `${statusAPI.descripcion} (${statusAPI.salon}). Fin: ${horaFin}`;
                            colorFondo = '#008a7b'; // Verde Teal Oscuro (Clase Activa)
                        } else {
                            estadoEncontrado = bloqueActual.label;
                            descripcionEncontrada = `LIBRE en este bloque. Fin: ${horaFin}`;
                            colorFondo = '#1d1b31'; // Gris oscuro (Libre en horario de clase)
                        }
                    } else {
                        estadoEncontrado = bloqueActual.label;
                        descripcionEncontrada = `Error al cargar horario.`;
                    }
                }
            }
        }
        
        // 3. Actualizar la Interfaz
        navHeader.style.backgroundColor = colorFondo;
        estadoDiv.innerHTML = `
            <span style="font-weight: 700;">${estadoEncontrado}</span><br>
            <span style="font-size: 0.8em; opacity: 0.8;">${descripcionEncontrada}</span>
        `;
    }
</script>

</body>
</html>