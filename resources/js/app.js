import './bootstrap';

import Alpine from 'alpinejs';
import './components/gpa-dialog';
import gpaToast from './components/gpa-toast';
import registrationForm from './components/registration-form';
import clientDashboard from './components/client-dashboard';
import clientCatalog from './components/client-catalog';
import clientCart from './components/client-cart';
import clientOrders from './components/client-orders';
import clientDocuments from './components/client-documents';
import clientPaymentProof from './components/client-payment-proof';
import secretaryDashboard from './components/staff-dashboard';
import secretaryVerification from './components/staff-verification';
import secretaryManualOrder from './components/staff-manual-order';
import secretaryInventory from './components/staff-inventory';
import secretaryDispatch from './components/staff-dispatch';
import secretaryInvoicing from './components/staff-invoice';
import secretaryPayments from './components/staff-payment';
import secretaryReports from './components/staff-report';
import coordinatorDashboard from './components/coordinator-dashboard';
import coordinatorConsole from './components/coordinator-console';
import coordinatorWeighing from './components/coordinator-weighing';
import coordinatorStock from './components/coordinator-stock';
import coordinatorDispatch from './components/coordinator-dispatch';
import coordinatorHarvestPlan from './components/coordinator-harvest-plan';
import coordinatorMonitoring from './components/coordinator-monitoring';
import coordinatorReturns from './components/coordinator-returns';
import armadaTasks from './components/armada-tasks';
import armadaDispatch from './components/armada-dispatch';
import armadaPod from './components/armada-pod';
import armadaStatus from './components/armada-status';
import directorDashboard from './components/director-dashboard';
import directorSales from './components/director-sales';
import directorVolume from './components/director-volume';
import directorReceivables from './components/director-receivables';
import directorApproval from './components/director-approval';
import directorReport from './components/director-report';
import directorGovernance from './components/director-governance';
import directorAccess from './components/director-access';

window.Alpine = Alpine;

Alpine.data('gpaToast', gpaToast);
Alpine.data('registrationForm', registrationForm);
Alpine.data('clientDashboard', clientDashboard);
Alpine.data('clientCatalog', clientCatalog);
Alpine.data('clientCart', clientCart);
Alpine.data('clientOrders', clientOrders);
Alpine.data('clientDocuments', clientDocuments);
Alpine.data('clientPaymentProof', clientPaymentProof);
Alpine.data('secretaryDashboard', secretaryDashboard);
Alpine.data('secretaryVerification', secretaryVerification);
Alpine.data('secretaryManualOrder', secretaryManualOrder);
Alpine.data('secretaryInventory', secretaryInventory);
Alpine.data('secretaryDispatch', secretaryDispatch);
Alpine.data('secretaryInvoicing', secretaryInvoicing);
Alpine.data('secretaryPayments', secretaryPayments);
Alpine.data('secretaryReports', secretaryReports);
Alpine.data('coordinatorDashboard', coordinatorDashboard);
Alpine.data('coordinatorConsole', coordinatorConsole);
Alpine.data('coordinatorWeighing', coordinatorWeighing);
Alpine.data('coordinatorStock', coordinatorStock);
Alpine.data('coordinatorDispatch', coordinatorDispatch);
Alpine.data('coordinatorHarvestPlan', coordinatorHarvestPlan);
Alpine.data('coordinatorMonitoring', coordinatorMonitoring);
Alpine.data('coordinatorReturns', coordinatorReturns);
Alpine.data('armadaTasks', armadaTasks);
Alpine.data('armadaDispatch', armadaDispatch);
Alpine.data('armadaPod', armadaPod);
Alpine.data('armadaStatus', armadaStatus);
Alpine.data('directorDashboard', directorDashboard);
Alpine.data('directorSales', directorSales);
Alpine.data('directorVolume', directorVolume);
Alpine.data('directorReceivables', directorReceivables);
Alpine.data('directorApproval', directorApproval);
Alpine.data('directorReport', directorReport);
Alpine.data('directorGovernance', directorGovernance);
Alpine.data('directorAccess', directorAccess);

window.gpaToast = (title, message, tone = 'info') => {
    window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
};
window.run = window.gpaToast;

Alpine.magic('toast', () => window.gpaToast);

Alpine.start();

