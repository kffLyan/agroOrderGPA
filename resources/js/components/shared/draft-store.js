const DRAFT_KEY = 'gpa.draft.po';
const EMPTIED_KEY = 'gpa.cart.emptied';
const SUBMITTED_KEY = 'gpa.cart.submitted';
const SYNC_EVENT = 'gpa:draft-synced';

function getDraftKey() {
    const metaUid = typeof document !== 'undefined' ? document.querySelector('meta[name="user-id"]')?.getAttribute('content') : null;
    const uid = (typeof window !== 'undefined' ? window.GPA_USER_ID : null) || metaUid;
    return uid ? `gpa.draft.po.${uid}` : DRAFT_KEY;
}

/**
 * Draft PO disimpan di localStorage supaya badge Keranjang di sidebar tetap
 * konsisten ketika pengguna berpindah antara Dashboard, Katalog, dan Keranjang.
 */
export function readDraft() {
    try {
        const key = getDraftKey();
        const raw = window.localStorage.getItem(key) || window.localStorage.getItem(DRAFT_KEY);

        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

export function writeDraft(lines) {
    try {
        const key = getDraftKey();
        window.localStorage.setItem(key, JSON.stringify(lines));
    } catch {
        // Mode privat / storage penuh: draft tetap berjalan di memori Alpine.
    }

    window.dispatchEvent(new CustomEvent(SYNC_EVENT));
}

export function onDraftSynced(handler) {
    window.addEventListener(SYNC_EVENT, handler);

    return () => window.removeEventListener(SYNC_EVENT, handler);
}

/**
 * Penanda keranjang yang sengaja dikosongkan pengguna, supaya baris contoh
 * pada halaman Keranjang tidak muncul lagi setelah aksi "Kosongkan".
 */
export function isCartEmptied() {
    try {
        return window.localStorage.getItem(EMPTIED_KEY) === '1';
    } catch {
        return false;
    }
}

export function setCartEmptied(value) {
    try {
        if (value) {
            window.localStorage.setItem(EMPTIED_KEY, '1');
        } else {
            window.localStorage.removeItem(EMPTIED_KEY);
        }
    } catch {
        // Abaikan: penanda hanya memengaruhi pratinjau.
    }
}

/**
 * Status PO setelah "Submit PO" ditekan, supaya label status pada halaman
 * Keranjang tetap konsisten ketika pengguna memuat ulang halaman.
 */
export function isCartSubmitted() {
    try {
        return window.localStorage.getItem(SUBMITTED_KEY) === '1';
    } catch {
        return false;
    }
}

export function setCartSubmitted(value) {
    try {
        if (value) {
            window.localStorage.setItem(SUBMITTED_KEY, '1');
        } else {
            window.localStorage.removeItem(SUBMITTED_KEY);
        }
    } catch {
        // Abaikan: penanda hanya memengaruhi pratinjau.
    }
}