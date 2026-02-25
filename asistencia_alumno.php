<?php
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<link rel="stylesheet" href="assets/css/crud.css">
<style>
    /* Estilos específicos para badges de estado */
    .badge.presente {
        background-color: #28a745;
    }

    /* Verde */
    .badge.falta {
        background-color: #dc3545;
    }

    /* Rojo */
    .badge.pendiente {
        background-color: #ffc107;
        color: #333;
    }

    /* Amarillo */
</style>

<div class="home-content">

    <div class="crud-header">
        <h1><i class="fas fa-clipboard-list"></i> Mi Historial de Asistencias</h1>
        <button class="btn-add" onclick="window.location.href='reportes.php'" style="background-color: #009688;">
            <i class="fas fa-file-pdf"></i> Descargar Reporte
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr style="background-color: #333;">
                    <th>Fecha</th>
                    <th>Hora de Registro</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Estatus</th>
                </tr>
            </thead>
            <tbody id="tabla-asistencias-body">
                <tr>
                    <td colspan="5" style="text-align:center;">Cargando historial...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', cargarHistorial);

    function getStatusClass(estatus) {
        if (estatus.toLowerCase().includes('presente') || estatus.toLowerCase().includes('exitoso')) {
            return 'presente';
        }
        if (estatus.toLowerCase().includes('falta')) {
            return 'falta';
        }
        if (estatus.toLowerCase().includes('pendiente')) {
            return 'pendiente';
        }
        return 'admin'; // Default color
    }

    async function cargarHistorial() {
        const idAlumno = localStorage.getItem('id_usuario'); // Obtenemos el ID del usuario logueado
        const tbody = document.getElementById('tabla-asistencias-body');

        if (!idAlumno) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error: No se encontró ID de usuario.</td></tr>';
            return;
        }

        // Endpoint: Asumimos que podemos obtener una lista de asistencias por ID de alumno.
        // Reutilizaremos el endpoint que creamos para el reporte, ya que es el más cercano.
        const respuesta = await peticionAutenticada(`/asistencias/alumno/${idAlumno}`)


        if (respuesta && respuesta.ok) {
            const historial = await respuesta.json();
            tbody.innerHTML = '';

            if (historial.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Aún no tienes asistencias registradas.</td></tr>';
                return;
            }

            historial.forEach(h => {
                const fecha = new Date(h.fecha).toLocaleDateString('es-MX');
                const hora = h.hora.substring(0, 5); // HH:mm
                const estatusClass = getStatusClass(h.estatus);

                tbody.innerHTML += `
                    <tr>
                        <td>${fecha}</td>
                        <td>${hora}</td>
                        <td><strong>${h.horarioAlumno.materiaGrupo.materia.nombre}</strong></td>
                        <td>${h.horarioAlumno.materiaGrupo.grupo.nombre}</td>
                        <td><span class="badge ${estatusClass}">${h.estatus}</span></td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center; color:red;">Error al cargar historial.</td></tr>';
        }
    }
    
</script>

<?php
// 1. Necesitas actualizar el link en alumno.php para que apunte a este archivo.
// 2. Necesitas verificar que tu API tenga el endpoint GET /api/asistencias/alumno/{idAlumno}
include __DIR__ . '/includes/footer.php';
?>