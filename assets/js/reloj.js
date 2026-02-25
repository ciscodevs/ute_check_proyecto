// Esta función auxiliar convierte la hora actual en un texto de período
function getPeriodoClase(minutos) {
    // Antes de la 1ra clase (7:00 -> 420 min)
    if (minutos < 420) { return "No Clase"; }
    
    // 1ra (7:00 - 7:50)
    if (minutos < 470) { return "1ra"; } // 7*60+50
    
    // 2da (7:50 - 8:40)
    if (minutos < 520) { return "2da"; } // 8*60+40
    
    // 3ra (8:40 - 9:30)
    if (minutos < 570) { return "3ra"; } // 9*60+30
    
    // RECESO (9:30 - 10:10)
    if (minutos < 610) { return "RECESO"; } // 10*60+10
    
    // 4ta (10:10 - 11:00)
    if (minutos < 660) { return "4ta"; } // 11*60
    
    // 5ta (11:00 - 11:50)
    if (minutos < 710) { return "5ta"; } // 11*60+50
    
    // 6ta (11:50 - 12:40)
    if (minutos < 760) { return "6ta"; } // 12*60+40
    
    // 7ma (12:40 - 1:30)
    if (minutos < 810) { return "7ma"; } // 13*60+30
    
    // 8va (1:30 - 2:20)
    if (minutos < 860) { return "8va"; } // 14*60+20
    
    // Después de las 2:20 PM
    return "No Clase";
}

function actualizarReloj() {
    const ahora = new Date();
    
    // --- Parte 1: Actualizar la hora (Reloj) ---
    let horas = ahora.getHours();
    const ampm = horas >= 12 ? 'p.m.' : 'a.m.';
    horas = horas % 12;
    horas = horas ? horas : 12; 
    let minutos = ahora.getMinutes();
    minutos = minutos < 10 ? '0' + minutos : minutos; 
    const tiempoString = horas + ':' + minutos + ' ' + ampm;
    
    const elementoReloj = document.getElementById('reloj-actual');
    if (elementoReloj) {
        elementoReloj.textContent = tiempoString;
    }

    // --- Parte 2: Actualizar el período de clase ---
    const totalMinutos = (ahora.getHours() * 60) + ahora.getMinutes();
    const periodoActual = getPeriodoClase(totalMinutos);
    
    const elementoPeriodo = document.getElementById('periodo-actual');
    if (elementoPeriodo) {
        elementoPeriodo.textContent = periodoActual;
    }
}

// Llama a la función una vez al cargar
actualizarReloj();
// Y luego la actualiza cada segundo
setInterval(actualizarReloj, 1000);