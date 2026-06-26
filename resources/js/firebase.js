/*
|------------------------------------------------------------------------------
| resources/js/firebase.js  —  Cliente Firebase Auth
|------------------------------------------------------------------------------
|
| Encapsula el SDK de Firebase para que el resto del proyecto consuma solo:
|   initAuth(), getIdToken(), getCurrentUser(), signInEmail(), signOut()
|
| La config se inyecta desde el layout via <meta name="firebase-*"> para no
| hardcodear llaves en el bundle. Todas las llaves Firebase del lado cliente
| son públicas por diseño; la seguridad real vive en la verificación del
| ID token en el middleware de Laravel (kreait).
|
*/

import { initializeApp } from 'firebase/app';
import {
    getAuth,
    onAuthStateChanged,
    signInWithEmailAndPassword,
    signOut as fbSignOut,
    setPersistence,
    browserLocalPersistence,
} from 'firebase/auth';

function readConfig() {
    const m = (k) => document.querySelector(`meta[name="firebase-${k}"]`)?.content ?? '';
    return {
        apiKey: m('api-key'),
        authDomain: m('auth-domain'),
        projectId: m('project-id'),
        storageBucket: m('storage-bucket'),
        messagingSenderId: m('sender-id'),
        appId: m('app-id'),
    };
}

let _app = null;
let _auth = null;
let _ready = null;

export function initAuth() {
    if (_auth) return _auth;
    const cfg = readConfig();
    if (!cfg.apiKey) {
        console.warn('[firebase] sin config; ¿faltan los <meta name="firebase-*">?');
        return null;
    }
    _app = initializeApp(cfg);
    _auth = getAuth(_app);

    // Resuelve una sola vez cuando Firebase determina el estado inicial.
    _ready = new Promise((resolve) => {
        setPersistence(_auth, browserLocalPersistence).finally(() => {
            const unsub = onAuthStateChanged(_auth, (user) => {
                unsub();
                resolve(user);
            });
        });
    });
    return _auth;
}

export async function getCurrentUser() {
    if (!_auth) initAuth();
    if (_ready) await _ready;
    return _auth?.currentUser ?? null;
}

export async function getIdToken(forceRefresh = false) {
    const user = await getCurrentUser();
    if (!user) return null;
    return user.getIdToken(forceRefresh);
}

export async function signInEmail(email, password) {
    if (!_auth) initAuth();
    const cred = await signInWithEmailAndPassword(_auth, email, password);
    return cred.user.getIdToken();
}

export async function signOut() {
    if (!_auth) initAuth();
    return fbSignOut(_auth);
}
