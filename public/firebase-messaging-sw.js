importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');
let config = {
        apiKey: "AIzaSyBGzuLCCMSABotWASYTSzYK3fAiQ39w5R8",
        authDomain: "shopperz-fe4ea.firebaseapp.com",
        projectId: "shopperz-fe4ea",
        storageBucket: "shopperz-fe4ea.appspot.com",
        messagingSenderId: "308737311204",
        appId: "1:308737311204:web:b7079c17fa6bf8d31bc7f1",
        measurementId: "G-T1CSXVXREN",
 };
firebase.initializeApp(config);
const messaging = firebase.messaging();
messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/required/firebase-logo.png'
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});
