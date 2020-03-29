if ('serviceWorker' in navigator) {	
    navigator.serviceWorker.register('sw.js')
    .then(reg => console.log('Registro de Service Worker exitoso', reg))
    .catch(err => console.warn('Error al tratar de registrar el Service Worker', err))
}

const indexed = indexedDB
const form = document.getElementById('formulario')
if(indexed && form){
    let db
    const request = indexed.open('base', 1)
    request.onsuccess = () =>{
        db = request.result
        console.log('OPEN', db)
        // readData()
    }
    request.onupgradeneeded = () =>{
        db = request.result
        console.log('CREATE', db)
        const objectStore = db.createObjectStore('tabla',{ autoIncrement : true })
    }
    request.onerror = (error) => {
        console.log('Error', error)
    }

    const addData = (data) => {
        const transaction = db.transaction(['tabla'], 'readwrite')
        const objectStore = transaction.objectStore('tabla')
        const request = objectStore.add(data)
    }
    const readData = () => {
        const transaction = db.transaction(['tabla'], 'readonly')
        const objectStore = transaction.objectStore('tabla')
        const request = objectStore.openCursor()
        const fragment = document.createDocumentFragment()
        var prueba = [];
        request.onsuccess = (e) =>{
            const cursor = e.target.result
            if(cursor){
                const tr = document.createElement('tr')
                const nombre = document.createElement('td')
                nombre.textContent = cursor.value.nombre
                const apellido = document.createElement('td')
                apellido.textContent = cursor.value.apellido

                tr.insertCell().textContent = cursor.value.nombre
                tr.insertCell().textContent = cursor.value.apellido
                prueba.push(cursor.value);
                fragment.appendChild(tr)
                // console.log(cursor.value)
                cursor.continue()
            } else{
                $("#tabla").html(fragment);
                console.log(prueba);
                // tabla.textContent = ''
                // tabla.appendChild(fragment)
            }
        }
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault()
        const data = {
            nombre:e.target.nombre.value,
            apellido:e.target.apellido.value
        }
        addData(data);
        e.target.nombre.value = ''
        e.target.apellido.value = ''
        readData()
        // console.log(data)
    })

    const check_alive = () =>{
        var a;
        $.ajax({
                url: 'http://rest.smycode.com/index.php?action=combos',
                type: 'GET',
                xhrFields: {
                    withCredentials: true
                },
                async: false,
                beforeSend:function(){
                },
                success: function(data){
                    //Ready for MySQL insertion.
                    console.log(data);
                    console.log("hay conexion");
                    a=0;
                },
                error: function(data) {
                    //Go in the indexDB
                    console.log(data);
                    console.log("No hay conexion");
                    a=1;
                }
        });
        return a;
    }

    const peticion = (time) =>{
        controlador = setInterval(function(){
            if(check_alive()==0) {
                readData()
            }
            else {
                alert('no se puede conectar al servidor')
            }
        }, time);
    }


    if(navigator.onLine) {
        peticion(10000)
    } else {
        alert('no hay internet');
    }

    addEventListener('online', (e) => {
        peticion(2000)
    })

    addEventListener('offline', (e) => {
        clearTimeout(controlador);
        $("#tabla").html('');
    })
}