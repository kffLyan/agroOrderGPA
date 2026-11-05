import './bootstrap';

import Alpine from 'alpinejs';
// Shared, Dialogs & Global Loading Progress Bar
import './components/shared/gpa-dialog';
import './components/shared/gpa-progress';
import './components/shared/gpa-lazy';
import gpaToast from './components/shared/gpa-toast';
import registrationForm from './components/shared/formulir-registrasi';

// 1. Klien
import klienDashboard from './components/klien/dasbor';
import klienCatalog from './components/klien/katalog';
import klienCart from './components/klien/keranjang';
import klienOrders from './components/klien/pesanan';
import klienDocuments from './components/klien/dokumen';
import klienPaymentProof from './components/klien/bukti-pembayaran';

// 2. Sekretaris
import sekretarisDashboard from './components/sekretaris/dasbor';
import sekretarisVerification from './components/sekretaris/verifikasi';
import sekretarisManualOrder from './components/sekretaris/pesanan-manual';
import sekretarisInventory from './components/sekretaris/inventaris';
import sekretarisDispatch from './components/sekretaris/pengiriman';
import sekretarisInvoicing from './components/sekretaris/faktur';
import sekretarisPayments from './components/sekretaris/pembayaran';
import sekretarisReports from './components/sekretaris/laporan';

// 3. Koordinator
import koordinatorDashboard from './components/koordinator/dasbor';
import koordinatorConsole from './components/koordinator/konsol';
import koordinatorWeighing from './components/koordinator/penimbangan';
import koordinatorStock from './components/koordinator/stok';
import koordinatorDispatch from './components/koordinator/pengiriman';
import koordinatorHarvestPlan from './components/koordinator/rencana-panen';
import koordinatorMonitoring from './components/koordinator/pemantauan';
import koordinatorReturns from './components/koordinator/retur';

// 4. Armada
import armadaTasks from './components/armada/tugas';
import armadaDispatch from './components/armada/pengiriman';
import armadaPod from './components/armada/bukti-terima';
import armadaStatus from './components/armada/status';

// 5. Direktur
import direkturDashboard from './components/direktur/dasbor';
import direkturSales from './components/direktur/penjualan';
import direkturVolume from './components/direktur/volume';
import direkturReceivables from './components/direktur/piutang';
import direkturApproval from './components/direktur/persetujuan';
import direkturReport from './components/direktur/laporan';
import direkturTataKelola from './components/direktur/tata-kelola';
import direkturPaymentChannels from './components/direktur/kanal-pembayaran';
import direkturAccess from './components/direktur/hak-akses';

window.Alpine = Alpine;
window.direkturPaymentChannels = direkturPaymentChannels;

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
Alpine.data('direkturTataKelola', direkturTataKelola);
Alpine.data('direkturGovernance', direkturTataKelola);
Alpine.data('directorGovernance', direkturTataKelola);
Alpine.data('direkturPaymentChannels', direkturPaymentChannels);
Alpine.data('directorPaymentChannels', direkturPaymentChannels);
Alpine.data('direkturAccess', direkturAccess);
Alpine.data('directorAccess', direkturAccess);

window.gpaToast = (title, message, tone = 'info') => {
    window.dispatchEvent(new CustomEvent('gpa:toast', { detail: { title, message, tone } }));
};
window.run = window.gpaToast;

Alpine.magic('toast', () => window.gpaToast);

Alpine.start();

