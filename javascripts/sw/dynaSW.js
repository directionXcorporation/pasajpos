var CACHE_NAME = '{{_CACHE_NAME}}';
var requestsWithoutCredentials = [
    {{_URLS_TO_CACHE}}
    '/',
    '/fonts/MaterialIcons-Regular.woff2',
    '/fonts/FontAwesome.otf',
    '/fonts/Material-Design-Iconic-Font.woff2',
    '/fonts/MaterialIcons-Regular.eot',
    '/fonts/MaterialIcons-Regular.ijmap',
    '/fonts/MaterialIcons-Regular.svg',
    '/fonts/MaterialIcons-Regular.ttf',
    '/fonts/MaterialIcons-Regular.woff',
    '/fonts/fontawesome-webfont.eot',
    '/fonts/fontawesome-webfont.svg',
    '/fonts/fontawesome-webfont.ttf',
    '/fonts/fontawesome-webfont.woff',
    '/fonts/fontawesome-webfont.woff2',
    '/fonts/ui-grid.eot',
    '/fonts/ui-grid.svg',
    '/fonts/ui-grid.ttf',
    '/fonts/ui-grid.woff',
    '/images/logo_animated.svg',
    '/uploads/receipts/logo.png',
    '/favicon.ico',
    '/images/logo.svg',
    '/manifest.json'
];

self.addEventListener('error', function(e) {
  console.error(e);
});

self.addEventListener('install', function(event) {
    self.skipWaiting();
    // Perform install steps
    event.waitUntil(
        caches.open(CACHE_NAME)
        .then(function(cache) {
        console.log('Opened cache');
        return cache.addAll(requestsWithoutCredentials);
      }).catch(function(e){
        // installation failed :(
        console.log('ServiceWorker installation failed: ', e);
        console.error(e);
      })
    );
}, function(err) {
    // installation failed :(
    console.log('ServiceWorker installation failed: ', err);
    console.error(err);
});


self.addEventListener('activate', function(event) {
    event.waitUntil(async function() {
    // Feature-detect
    if (self.registration.navigationPreload) {
      // Enable navigation preloads!
      await self.registration.navigationPreload.disable();
    }
  }());
}, function(err) {
    // activation failed :(
    console.log('ServiceWorker activation failed: ', err);
    console.error(err);
});

self.addEventListener('fetch', function(event) {
  event.respondWith(caches.match(event.request).then(function(response) {
    // caches.match() always resolves
    // but in case of success response will have value
    if (response !== undefined) {
      return response;
    } else {
      return fetch(event.request, {credentials: 'include'}).then(function (response) {
        return response;
      }).catch(function () {
        return caches.match('/');
      });
    }
  }));
});

/*
self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request, {'ignoreSearch': true})
      .then(function(response) {
          console.log("here10");
          
        if(event.request.method === 'GET'){
            console.log(event.request);
            return fetch(event.request, {}).then(function(fetchResponseP) {
                console.log("here2");
                if(!fetchResponseP || fetchResponseP.status !== 200) {
                    console.log("here3");
                    console.log("!fetchResponseP");
                    if (response) {
                        return response;
                    }else{
                        window.location("/");
                        return new Response("Network error happened while getting cached data. Refresh the page and it might rsolve the issue.", {"status" : 408, "headers" : {"Content-Type" : "text/plain"}});
                    }
                }else{
                    console.log("here4");
                    if (response && fetchResponseP.status === 200) {
                        let responseToCache = fetchResponseP.clone();
                        caches.open(CACHE_NAME).then(function(cache) {
                            console.log("cache updated");
                            cache.put(event.request, responseToCache);
                        });
                    }
                    console.log("fetchResponseP");
                    return fetchResponseP;
                }
            }).catch((error) =>  {
                console.log("here5");
                console.log(error);
                caches.match(event.request)
                .then(function(response) {
                    if (response) {
                        return response;
                    }else{
                        console.error('Failed to fetch', error);
                        console.error(error);
                        console.error(response);
                        window.location("/");
                        return new Response("Network error happened while getting cached data. refresh the page and it might resolve the issue.", {"status" : 408, "headers" : {"Content-Type" : "text/plain"}});
                    }
                })
            })
        }else{
            return;
        }
      }).catch((error) =>  {
          console.log("here6");
          console.error('Failed to fetch and read from cache', error);
          console.error(error);
          window.location("/");
          return new Response("Network error happened while searching in cached pages. refresh the page and it might resolve the issue.", {"status" : 408, "headers" : {"Content-Type" : "text/plain"}});
      })
  );
}, function(err) {
    console.log("here7");
        console.log('ServiceWorker fetch failed: ', err);
        console.error(err);
        return fetch(event.request, {});
        // fetch failed :(
});
*/
/*
workbox.setConfig({ debug: true });
workbox.core.skipWaiting();
workbox.core.clientsClaim();
workbox.core.setCacheNameDetails({
  prefix: 'PASAJ',
  suffix: 'v1'
});
workbox.precaching.precacheAndRoute([
    '/fonts/MaterialIcons-Regular.woff2',
    '/fonts/FontAwesome.otf',
    '/fonts/Material-Design-Iconic-Font.woff2',
    '/fonts/MaterialIcons-Regular.eot',
    '/fonts/MaterialIcons-Regular.ijmap',
    '/fonts/MaterialIcons-Regular.svg',
    '/fonts/MaterialIcons-Regular.ttf',
    '/fonts/MaterialIcons-Regular.woff',
    '/fonts/fontawesome-webfont.eot',
    '/fonts/fontawesome-webfont.svg',
    '/fonts/fontawesome-webfont.ttf',
    '/fonts/fontawesome-webfont.woff',
    '/fonts/fontawesome-webfont.woff2',
    '/fonts/ui-grid.eot',
    '/fonts/ui-grid.svg',
    '/fonts/ui-grid.ttf',
    '/fonts/ui-grid.woff',
    '/images/logo_animated.svg',
    '/uploads/receipts/logo.png',
    '/favicon.ico',
    '/images/icon.jpg',
    '/index.php',
    {{_URLS_TO_CACHE_WITH_CREDENTIALS}}
  { url: '/', revision: '567999999999' }
], {
  directoryIndex: null,
  cleanUrls: false
});
*/