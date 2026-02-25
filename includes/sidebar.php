<?php
// Detectar página actual para resaltar el menú
$paginaActual = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="logo-details">
        <i class='fas fa-bars' id="btn-menu"></i>
    </div>

    <ul class="nav-list">

        <li class="role-based" data-roles="ROLE_ADMIN,ROLE_DOCENTE,ROLE_ALUMNO,ROLE_PREFECTO">
            <a href="dashboard.php" class="<?php echo ($paginaActual == 'dashboard.php' || $paginaActual == 'alumno.php' || $paginaActual == 'docente.php') ? 'active' : ''; ?>">
                <i class='fas fa-home'></i>
                <span class="links_name">Inicio</span>
            </a>
            <span class="tooltip">Inicio</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN,ROLE_PREFECTO">
            <a href="usuarios.php" class="<?php echo ($paginaActual == 'usuarios.php') ? 'active' : ''; ?>">
                <i class='fas fa-users'></i>
                <span class="links_name">Usuarios</span>
            </a>
            <span class="tooltip">Usuarios</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN">
            <a href="materias.php" class="<?php echo ($paginaActual == 'materias.php') ? 'active' : ''; ?>">
                <i class='fas fa-book'></i>
                <span class="links_name">Materias</span>
            </a>
            <span class="tooltip">Materias</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN">
            <a href="grupos_crud.php" class="<?php echo ($paginaActual == 'grupos_crud.php') ? 'active' : ''; ?>">
                <i class='fas fa-layer-group'></i>
                <span class="links_name">Grupos</span>
            </a>
            <span class="tooltip">Grupos</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN">
            <a href="asignaciones.php" class="<?php echo ($paginaActual == 'asignaciones.php') ? 'active' : ''; ?>">
                <i class='fas fa-chalkboard-teacher'></i>
                <span class="links_name">Carga Académica</span>
            </a>
            <span class="tooltip">Asignar Materias</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN">
            <a href="horarios_crud.php" class="<?php echo ($paginaActual == 'horarios_crud.php') ? 'active' : ''; ?>">
                <i class='far fa-calendar-alt'></i>
                <span class="links_name">Mi Horario</span>
            </a>
            <span class="tooltip">Horarios</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN">
            <a href="inscripciones.php" class="<?php echo ($paginaActual == 'inscripciones.php') ? 'active' : ''; ?>">
                <i class='fas fa-user-plus'></i>
                <span class="links_name">Inscripciones</span>
            </a>
            <span class="tooltip">Inscribir Alumno</span>
        </li>

        <li class="role-based" data-roles="ROLE_ALUMNO">
            <a href="alumno_horario.php" class="<?php echo ($paginaActual == 'alumno_horario.php') ? 'active' : ''; ?>">
                <i class='far fa-calendar-alt'></i>
                <span class="links_name">Mi Horario</span>
            </a>
            <span class="tooltip">Horario</span>
        </li>

        <li class="role-based" data-roles="ROLE_ALUMNO">
            <a href="asistencia_alumno.php" class="<?php echo ($paginaActual == 'asistencia_alumno.php') ? 'active' : ''; ?>">
                <i class='fas fa-history'></i>
                <span class="links_name">Historial Asistencia</span>
            </a>
            <span class="tooltip">Historial</span>
        </li>

        <li class="role-based" data-roles="ROLE_PREFECTO">
            <a href="tomar_asistencia.php" class="<?php echo ($paginaActual == 'tomar_asistencia.php') ? 'active' : ''; ?>">
                <i class='fas fa-clipboard-check'></i>
                <span class="links_name">Tomar Asistencia</span>
            </a>
            <span class="tooltip">Check-in</span>
        </li>

        <li class="role-based" data-roles="ROLE_ALUMNO,ROLE_PREFECTO">
            <a href="ver_docentes.php" class="<?php echo ($paginaActual == 'ver_docentes.php') ? 'active' : ''; ?>">
                <i class='fas fa-chalkboard-teacher'></i>
                <span class="links_name">Directorio Docentes</span>
            </a>
            <span class="tooltip">Docentes</span>
        </li>

        <li class="role-based" data-roles="ROLE_ALUMNO">
            <a href="mi_perfil.php" class="<?php echo ($paginaActual == 'mi_perfil.php') ? 'active' : ''; ?>">
                <i class='fas fa-user-circle'></i>
                <span class="links_name">Mi Información</span>
            </a>
            <span class="tooltip">Perfil</span>
        </li>

        <li class="logout-section">
            <a href="#" onclick="logout()">
                <i class='fas fa-sign-out-alt'></i>
                <span class="links_name">Cerrar Sesión</span>
            </a>
            <span class="tooltip">Salir</span>
        </li>

        <li class="role-based" data-roles="ROLE_DOCENTE">
            <a href="mis_grupos.php" class="<?php echo ($paginaActual == 'mis_grupos.php' || $paginaActual == 'detalle_grupo.php') ? 'active' : ''; ?>">
                <i class='fas fa-users-rectangle'></i>
                <span class="links_name">Mis Grupos</span>
            </a>
            <span class="tooltip">Gestión de Grupos</span>
        </li>

        <li class="role-based" data-roles="ROLE_DOCENTE">
            <a href="reportes_docente.php" class="<?php echo ($paginaActual == 'reportes_docente.php') ? 'active' : ''; ?>">
                <i class='fas fa-file-invoice'></i>
                <span class="links_name">Reportes Globales</span>
            </a>
            <span class="tooltip">Reportes</span>
        </li>

        <li class="role-based" data-roles="ROLE_ADMIN">
            <a href="reportes_admin.php" class="class=" <?php echo ($paginaActual == 'reportes_admin.php') ? 'active' : ''; ?>">
                <i class='fas fa-file-contract'></i>
                <span class="links_name">Reportes</span>
            </a>
        </li>
    </ul>
</div>

<section class="home-section">

    <nav>
        <div class="nav-left">
            <span class="ute-brand">UTE</span>
            <div class="nav-separator"></div>
            <div class="ute-subtitle">
                <span>Universidad Tecnológica</span>
                <span>General Mariano Escobedo</span>
            </div>
        </div>

        <div class="nav-right">
            <span class="status-indicator" id="texto-estado">No Clase</span>
            <span id="reloj-global">00:00 p.m.</span>
        </div>
    </nav>

    <div class="main-content">