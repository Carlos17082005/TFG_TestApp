const DOM = {  // Array con las variable del DOM
    ciclo: document.getElementById('ciclo').value,  // Esto es solo para recuperar el ciclo de forma sencilla
    selectModulo: document.getElementById('select_modulo'),
    selectTest: document.getElementById('select_test')
}

DOM.selectModulo.addEventListener('change', function() {  // Listener que eschucha unicamente cuando se hace un cambio en el desplegable
    const modulo = this.value;

    if (modulo === "") {  // Si no se selecciona ningun modulo mantiene el campo desactivado y se sale de la funcion
        DOM.selectTest.innerHTML = '<option value="">Selecciona un módulo</option>';
        DOM.selectTest.disabled = true;
        return;
    }

    // Si ha seleccionado un modulo hace una peticion al sevidor 
    // Manda el ciclo y el modulo
    // Recibe una respuesta JSON 
    fetch(`obtener_tests.php?ciclo=${DOM.ciclo}&modulo=${modulo}`)
        .then(respuesta => respuesta.json())
        .then(datos => {
            DOM.selectTest.innerHTML = ''; 
            
            if (!datos.error) {
                if (datos === 'vacio') {
                    DOM.selectTest.innerHTML = '<option value="">No hay tests disponibles para este módulo</option>';
                    DOM.selectTest.disabled = true;
                } else {
                    DOM.selectTest.innerHTML = '<option value="">Selecciona un test...</option>';
                    
                    datos.forEach(test => {
                        const opcion = document.createElement('option');
                        opcion.value = test.id_test;
                        opcion.textContent = test.nombre;
                        DOM.selectTest.appendChild(opcion);
                    });
                    
                    DOM.selectTest.disabled = false; 
                }
            } else {
                console.error("Error desde PHP:", datos.error);
            }
        })
        .catch(error => {
            console.error("Hubo un error con la petición Fetch:", error);
        });
});