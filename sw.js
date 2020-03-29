    //Asignar un nombre y versión al cache
    const CACHE_NAME = 'cache_prueba'
    const cacheDinamico = 'cache_prueba2'
    const urlsToCache = [  
        '/',
        './',
        './index.html',
        // 'https://fonts.googleapis.com/css?family=Montserrat:400,500|Poppins:400,500,600,700|Roboto:400,500',
        // 'https://cdn.materialdesignicons.com/4.4.95/css/materialdesignicons.min.css',
        // 'https://unpkg.com/sleek-dashboard/dist/assets/css/sleek.min.css',
        'https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js',
        'http://localhost/IndexedDb/assets/template/dist/assets/img/logo_cash.png',
        'manifest.json',
        'scriptdb.js'
    ]

//durante la fase de instalación, generalmente se almacena en caché los activos estáticos
self.addEventListener('install', e => {

    e.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            console.log("cache")
            cache.addAll(urlsToCache)
        })
        .catch(err => console.log('Falló registro de cache', err))
    )
})

//una vez que se instala el SW, se activa y busca los recursos para hacer que funcione sin conexión
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                .map(key => caches.delete(key)) //Eliminamos lo que ya no se necesita en cache
            )
        })
        // Le indica al SW activar el cache actual
        .then(() => self.clients.claim())
        .catch(err => console.log("Fallo al activar"))
    )
})

//cuando el navegador recupera una url
self.addEventListener('fetch', e => {
    //Responder ya sea con el objeto en caché o continuar y buscar la url real
    e.respondWith(
        caches.match(e.request).then(cacheRes => {
            //recuperando cache
            if (cacheRes) {
                console.log('no hay conexion')
            }
            return cacheRes || fetch(e.request).then(fetchRes => {
                return caches.open(cacheDinamico).then(cache => {
                    cache.put(e.request.url, fetchRes.clone())
                    return fetchRes;
                })
            })
        })
    )
})