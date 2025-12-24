(function () {
    if (!window.firebase || !window.firebase.messaging) {
        return;
    }

    var appConfig = window.AppConfig || {};
    var vapidKey = appConfig.fcm ? appConfig.fcm.vapidKey : '';
    if (!vapidKey) {
        console.warn('FCM VAPID key missing.');
        return;
    }

    var firebaseConfig = {
        apiKey: 'AIzaSyAw4EH-GEqUUpnmRJ4XfN2AFUwmd5XoaFY',
        authDomain: 'shiftscheduler-37b31.firebaseapp.com',
        projectId: 'shiftscheduler-37b31',
        storageBucket: 'shiftscheduler-37b31.firebasestorage.app',
        messagingSenderId: '461867929776',
        appId: '1:461867929776:web:cfce1f8f4be7a74f51ab75',
        measurementId: 'G-DKCL8P1284'
    };

    firebase.initializeApp(firebaseConfig);
    var messaging = firebase.messaging();

    function sendTokenToBackend(token) {
        if (!token) {
            return;
        }

        var payload = new FormData();
        payload.append('action', 'save_fcm_token');
        payload.append('csrf_token', appConfig.csrfToken || '');
        payload.append('token', token);
        payload.append('device_type', /Mobi|Android/i.test(navigator.userAgent) ? 'mobile_web' : 'desktop_web');

        fetch('/index.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: payload
        }).catch(function (error) {
            console.warn('Unable to save FCM token', error);
        });
    }

    function requestPermissionAndToken() {
        if (!('Notification' in window)) {
            return;
        }

        if (Notification.permission === 'granted') {
            getToken();
            return;
        }

        Notification.requestPermission().then(function (permission) {
            if (permission === 'granted') {
                getToken();
            }
        }).catch(function (error) {
            console.warn('Notification permission error', error);
        });
    }

    function getToken() {
        navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function (registration) {
            return messaging.getToken({
                vapidKey: vapidKey,
                serviceWorkerRegistration: registration
            });
        }).then(function (token) {
            sendTokenToBackend(token);
        }).catch(function (error) {
            console.warn('FCM token error', error);
        });
    }

    messaging.onMessage(function (payload) {
        if (!payload || !payload.notification) {
            return;
        }

        if (Notification.permission !== 'granted') {
            return;
        }

        new Notification(payload.notification.title || 'Shift Scheduler', {
            body: payload.notification.body || ''
        });
    });

    requestPermissionAndToken();
})();
