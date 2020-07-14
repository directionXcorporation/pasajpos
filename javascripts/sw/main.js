if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/sw.js', {updateViaCache: 'all', scope: '/'}).then(function(registration) {
      // Registration was successful
      console.log('ServiceWorker registration successful with scope: ', registration.scope);
    }, function(err) {
      // registration failed
      console.log('ServiceWorker registration failed: ', err);
      alert('Application might not work properly when offline. Please use a modern browser, refresh the page, and contact system administrator if not resolved.');
    });
  });
}