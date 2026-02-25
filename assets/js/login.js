// Esperamos a que el HTML cargue completamente
document.addEventListener('DOMContentLoaded', () => {
    
    const loginForm = document.getElementById('login-form');

    loginForm.addEventListener('submit', async (event) => {
        // 1. Evitar que la página se recargue al dar clic en "Ingresar"
        event.preventDefault();

        // 2. Obtener los datos de las cajas de texto
        const usuario = document.getElementById('usuario').value;
        const contrasena = document.getElementById('contrasena').value;
        
        // Nota: El 'tipo_usuario' (select) no es estrictamente necesario para el Login de la API
        // porque la API ya sabe qué rol tiene el usuario al buscarlo en la BD.
        // Pero podemos guardarlo si lo necesitas para lógica visual en el frontend.

        // 3. Preparar el JSON para enviar (Debe coincidir con LoginRequest.java)
        const datosLogin = {
            username: usuario,
            password: contrasena
        };

        try {
            // 4. Hacer la petición POST a tu API Java
            const respuesta = await fetch('http://localhost:8080/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datosLogin)
            });

            // 5. Verificar si el login fue exitoso
            if (respuesta.ok) {
                const datos = await respuesta.json();
                
                // ¡ÉXITO! Guardamos el Token JWT en el navegador
                // Esto es como guardar la credencial en la billetera
                localStorage.setItem('token_ute', datos.jwt);
                
                // Opcional: Guardar el nombre de usuario para mostrarlo luego
                localStorage.setItem('usuario_actual', usuario);

                // 6. Redirigir al Dashboard (Cambia esto por tu archivo real)
                alert("¡Bienvenido! Acceso concedido.");
                window.location.href = "dashboard.php"; 

            } else {
                // Error 401 o 403
                alert("Error: Usuario o contraseña incorrectos.");
            }

        } catch (error) {
            console.error("Error de conexión:", error);
            alert("No se pudo conectar con el servidor. Verifica que la API esté corriendo.");
        }
    });
});