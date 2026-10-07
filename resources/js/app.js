import './bootstrap';

import Alpine from 'alpinejs';
import gpaToast from './components/gpa-toast';
import registrationForm from './components/registration-form';
import clientDashboard from './components/client-dashboard';
import clientCatalog from './components/client-catalog';
import clientCart from './components/client-cart';
import clientOrders from './components/client-orders';
import clientDocuments from './components/client-documents';
import clientPaymentProof from './components/client-payment-proof';

window.Alpine = Alpine;

Alpine.data('gpaToast', gpaToast);
Alpine.data('registrationForm', registrationForm);
Alpine.data('clientDashboard', clientDashboard);
Alpine.data('clientCatalog', clientCatalog);
Alpine.data('clientCart', clientCart);
Alpine.data('clientOrders', clientOrders);
Alpine.data('clientDocuments', clientDocuments);
Alpine.data('clientPaymentProof', clientPaymentProof);

window.gpaToast = (title, message, tone = 'info') => {
    window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
};
window.run = window.gpaToast;

Alpine.magic('toast', () => window.gpaToast);

Alpine.start();
