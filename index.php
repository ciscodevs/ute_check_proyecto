<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UTE Check</title>
    
    <link rel="icon" href="assets/img/educacion.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="assets/css/login.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <img src="assets/img/educacion.png" alt="Logo" class="login-icon">
    
    <div class="login-container">
        
        <h2>Iniciar Sesión</h2> 
        
        <form id="login-form">

            <div class="form-group">
                <input type="text" id="usuario" name="usuario" placeholder="Usuario" required>
            </div>
            
            <div class="form-group">
                <input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required>
            </div>
            
            <div>
                <button type="submit" class="login-button">Ingresar</button>
            </div>
            
            <div id="mensaje-error" style="color: red; text-align: center; margin-top: 10px; display: none;">
                Usuario o contraseña incorrectos
            </div>

        </form>
    </div>
    
    <div class="login-shadow"></div>

    <script src="assets/js/api.js"></script>

    <script>
        // A. Verificar si ya hay sesión activa al cargar la página
        // Si el usuario ya tiene token, lo mandamos directo al dashboard sin que se loguee de nuevo.
        verificarSesion();

        // B. Manejar el envío del formulario
        const loginForm = document.getElementById('login-form');
        const errorDiv = document.getElementById('mensaje-error');
        const btnSubmit = document.querySelector('.login-button');

        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault(); // Evita que la página se recargue

            // Efecto visual de carga
            btnSubmit.innerText = "Verificando...";
            btnSubmit.disabled = true;
            errorDiv.style.display = 'none';

            const usuario = document.getElementById('usuario').value;
            const contrasena = document.getElementById('contrasena').value;

            // Llamamos a la función login() que está dentro de api.js
            const exito = await login(usuario, contrasena);

           if (exito) {
            
                // Leemos el rol que api.js guardó en el navegador
                const rol = localStorage.getItem('rol_usuario'); // Ej: "ROLE_ALUMNO"
                

                if (rol === 'ROLE_ADMIN' || rol === 'ROLE_PREFECTO') {
                    window.location.href = 'dashboard.php';
                } 
                else if (rol === 'ROLE_DOCENTE') {
                    window.location.href = 'docente.php'; // <--- Nueva página
                } 
                else if (rol === 'ROLE_ALUMNO') {
                    window.location.href = 'alumno.php'; // <--- Nueva página
                } 
                else {
                    // Fallback por si acaso
                    window.location.href = 'dashboard.php';
                }
            } else {
                // Si falló, mostramos error y restauramos el botón
                errorDiv.style.display = 'block';
                errorDiv.innerText = "Credenciales incorrectas o error de conexión.";
                btnSubmit.innerText = "Ingresar";
                btnSubmit.disabled = false;
            }
        });
    </script>
</body>
</html>