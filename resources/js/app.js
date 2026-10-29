import './bootstrap';

import Alpine from 'alpinejs';
// Shared & Dialogs
import './components/shared/gpa-dialog';
import gpaToast from './components/shared/gpa-toast';
import registrationForm from './components/shared/registration-form';

// 1. Klien
import klienDashboard from './components/klien/dashboard';
import klienCatalog from './components/klien/catalog';
import klienCart from './components/klien/cart';
import klienOrders from './components/klien/orders';
import klienDocuments from './components/klien/documents';
import klienPaymentProof from './components/klien/payment-proof';

// 2. Sekretaris
import sekretarisDashboard from './components/sekretaris/dashboard';
import sekretarisVerification from './components/sekretaris/verification';
import sekretarisManualOrder from './components/sekretaris/manual-order';
import sekretarisInventory from './components/sekretaris/inventory';
import sekretarisDispatch from './components/sekretaris/dispatch';
import sekretarisInvoicing from './components/sekretaris/invoice';
import sekretarisPayments from './components/sekretaris/payment';
import sekretarisReports from './components/sekretaris/report';

// 3. Koordinator
import koordinatorDashboard from './components/koordinator/dashboard';
import koordinatorConsole from './components/koordinator/console';
import koordinatorWeighing from './components/koordinator/weighing';
import koordinatorStock from './components/koordinator/stock';
import koordinatorDispatch from './components/koordinator/dispatch';
import koordinatorHarvestPlan from './components/koordinator/harvest-plan';
import koordinatorMonitoring from './components/koordinator/monitoring';
import koordinatorReturns from './components/koordinator/returns';

// 4. Armada
import armadaTasks from './components/armada/tasks';
import armadaDispatch from './components/armada/dispatch';
import armadaPod from './components/armada/pod';
import armadaStatus from './components/armada/status';

// 5. Direktur
import direkturDashboard from './components/direktur/dashboard';
import direkturSales from './components/direktur/sales';
import direkturVolume from './components/direktur/volume';
import direkturReceivables from './components/direktur/receivables';
import direkturApproval from './components/direktur/approval';
import direkturReport from './components/direktur/report';
import direkturGovernance from './components/direktur/governance';
import direkturAccess from './components/direktur/access';

window.Alpine = Alpine;

// Registrasi Alpine Components (Dukungan nama Bahasa Indonesia & Alias kompatibilitas)
Alpine.data('gpaToast', gpaToast);
Alpine.data('registrationForm', registrationForm);

// Klien
Alpine.data('klienDashboard', klienDashboard);
Alpine.data('clientDashboard', klienDashboard);
Alpine.data('klienCatalog', klienCatalog);
Alpine.data('clientCatalog', klienCatalog);
Alpine.data('klienCart', klienCart);
Alpine.data('clientCart', klienCart);
Alpine.data('klienOrders', klienOrders);
Alpine.data('clientOrders', klienOrders);
Alpine.data('klienDocuments', klienDocuments);
Alpine.data('clientDocuments', klienDocuments);
Alpine.data('klienPaymentProof', klienPaymentProof);
Alpine.data('clientPaymentProof', klienPaymentProof);

// Sekretaris
Alpine.data('sekretarisDashboard', sekretarisDashboard);
Alpine.data('secretaryDashboard', sekretarisDashboard);
Alpine.data('sekretarisVerification', sekretarisVerification);
Alpine.data('secretaryVerification', sekretarisVerification);
Alpine.data('sekretarisManualOrder', sekretarisManualOrder);
Alpine.data('secretaryManualOrder', sekretarisManualOrder);
Alpine.data('sekretarisInventory', sekretarisInventory);
Alpine.data('secretaryInventory', sekretarisInventory);
Alpine.data('sekretarisDispatch', sekretarisDispatch);
Alpine.data('secretaryDispatch', sekretarisDispatch);
Alpine.data('sekretarisInvoicing', sekretarisInvoicing);
Alpine.data('secretaryInvoicing', sekretarisInvoicing);
Alpine.data('sekretarisPayments', sekretarisPayments);
Alpine.data('secretaryPayments', sekretarisPayments);
Alpine.data('sekretarisReports', sekretarisReports);
Alpine.data('secretaryReports', sekretarisReports);

// Koordinator
Alpine.data('koordinatorDashboard', koordinatorDashboard);
Alpine.data('coordinatorDashboard', koordinatorDashboard);
Alpine.data('koordinatorConsole', koordinatorConsole);
Alpine.data('coordinatorConsole', koordinatorConsole);
Alpine.data('koordinatorWeighing', koordinatorWeighing);
Alpine.data('coordinatorWeighing', koordinatorWeighing);
Alpine.data('koordinatorStock', koordinatorStock);
Alpine.data('coordinatorStock', koordinatorStock);
Alpine.data('koordinatorDispatch', koordinatorDispatch);
Alpine.data('coordinatorDispatch', koordinatorDispatch);
Alpine.data('koordinatorHarvestPlan', koordinatorHarvestPlan);
Alpine.data('coordinatorHarvestPlan', koordinatorHarvestPlan);
Alpine.data('koordinatorMonitoring', koordinatorMonitoring);
Alpine.data('coordinatorMonitoring', koordinatorMonitoring);
Alpine.data('koordinatorReturns', koordinatorReturns);
Alpine.data('coordinatorReturns', koordinatorReturns);

// Armada
Alpine.data('armadaTasks', armadaTasks);
Alpine.data('armadaDispatch', armadaDispatch);
Alpine.data('armadaPod', armadaPod);
Alpine.data('armadaStatus', armadaStatus);

// Direktur
Alpine.data('direkturDashboard', direkturDashboard);
Alpine.data('directorDashboard', direkturDashboard);
Alpine.data('direkturSales', direkturSales);
Alpine.data('directorSales', direkturSales);
Alpine.data('direkturVolume', direkturVolume);
Alpine.data('directorVolume', direkturVolume);
Alpine.data('direkturReceivables', direkturReceivables);
Alpine.data('directorReceivables', direkturReceivables);
Alpine.data('direkturApproval', direkturApproval);
Alpine.data('directorApproval', direkturApproval);
Alpine.data('direkturReport', direkturReport);
Alpine.data('directorReport', direkturReport);
Alpine.data('direkturGovernance', direkturGovernance);
Alpine.data('directorGovernance', direkturGovernance);
Alpine.data('direkturAccess', direkturAccess);
Alpine.data('directorAccess', direkturAccess);

window.gpaToast = (title, message, tone = 'info') => {
    window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
};
window.run = window.gpaToast;

Alpine.magic('toast', () => window.gpaToast);

Alpine.start();

