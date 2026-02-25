const API_BASE_URL = "http://localhost:8080/api";
const TOKEN_KEY = 'token_ute';
const USER_KEY = 'usuario_actual';
const ID_KEY = 'id_usuario'; // Clave para guardar el ID
const ROL_KEY = 'rol_usuario'; // Clave para guardar el rol

async function peticionAutenticada(endpoint, metodo = 'GET', body = null) {
    const token = localStorage.getItem(TOKEN_KEY);

    if (!token) {
        window.location.href = 'index.php';
        return null;
    }

    const opciones = {
        method: metodo,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    };

    if (body) {
        opciones.body = JSON.stringify(body);
    }

    try {
        const respuesta = await fetch(`${API_BASE_URL}${endpoint}`, opciones);

        if (respuesta.status === 401 || respuesta.status === 403) {
            alert("Sesión expirada.");
            logout();
            return null;
        }

        return respuesta;
    } catch (error) {
        console.error("Error de conexión:", error);
        return null; // Retornamos null para manejarlo en el archivo PHP
    }
}

async function login(username, password) {
    try {
        const respuesta = await fetch(`${API_BASE_URL}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        if (respuesta.ok) {
            const datos = await respuesta.json();
            
            // --- GUARDADO DE LA SESIÓN COMPLETA (FIX CRÍTICO) ---
            localStorage.setItem(TOKEN_KEY, datos.jwt);
            localStorage.setItem(USER_KEY, username);
            
            // GUARDAMOS EL ID Y EL ROL QUE VIENEN DE LA API
            localStorage.setItem(ID_KEY, datos.idUsuario);
            localStorage.setItem(ROL_KEY, 'ROLE_' + datos.rol); // Añadimos 'ROLE_' para el sidebar
            
            return true;
        }
        return false;
    } catch (error) {
        console.error("Error en login:", error);
        return false;
    }
}

function logout() {
    localStorage.clear();
    window.location.href = 'index.php';
}

function verificarSesion() {
    if (localStorage.getItem(TOKEN_KEY)) {
        window.location.href = 'dashboard.php';
    }
}

// Función para decodificar el JWT y leer los datos (Rol)
function parseJwt(token) {
    try {
        const base64Url = token.split('.')[1];
        const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        const jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
        return JSON.parse(jsonPayload);
    } catch (e) {
        return null;
    }
}