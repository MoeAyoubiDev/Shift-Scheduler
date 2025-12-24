importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: 'AIzaSyAw4EH-GEqUUpnmRJ4XfN2AFUwmd5XoaFY',
    authDomain: 'shiftscheduler-37b31.firebaseapp.com',
    projectId: 'shiftscheduler-37b31',
    storageBucket: 'shiftscheduler-37b31.firebasestorage.app',
    messagingSenderId: '461867929776',
    appId: '1:461867929776:web:cfce1f8f4be7a74f51ab75',
    measurementId: 'G-DKCL8P1284'
});

var messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
    if (!payload || !payload.notification) {
        return;
    }

    self.registration.showNotification(payload.notification.title || 'Shift Scheduler', {
        body: payload.notification.body || ''
    });
});
