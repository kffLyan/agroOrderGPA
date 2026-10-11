const numberOf = (value) => {
    const parsed = typeof value === 'number' ? value : parseFloat(String(value ?? '').replace(/[^0-9.-]/g, ''));

    return Number.isFinite(parsed) ? parsed : 0;
};

const ton = (value, decimals = 2) =>
    numberOf(value).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });

const percent = (value, decimals = 1) =>
    numberOf(value).toLocaleString('id-ID', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });

export default function directorVolume(commodities = [], weeks = [], partners = []) {
    return {
        commodities: (Array.isArray(commodities) ? commodities : []).map((row) => ({
            code: String(row.code ?? ''),
            name: String(row.name ?? ''),
            grade: String(row.grade ?? ''),
            value: numberOf(row.value),
            farmer: numberOf(row.farmer),
            buffer: numberOf(row.buffer),
            headroom: numberOf(row.headroom),
            status: String(row.status ?? ''),
        })),
        weeks: (Array.isArray(weeks) ? weeks : []).map((row) => {
            const segments = Array.isArray(row.segments) ? row.segments : [];
            const tonsOf = (key) => segments.find((segment) => String(segment.key) === key)?.tons ?? 0;

            return {
                week: String(row.week ?? ''),
                total: numberOf(row.total),
                total_label: String(row.total_label ?? ''),
                demand: numberOf(tonsOf('demand')),
                yield: numberOf(tonsOf('yield')),
                reserve: numberOf(tonsOf('reserve')),
                label: String(row.label ?? ''),
                status: String(row.status ?? 'closed'),
                is_peak: Boolean(row.is_peak),
            };
        }),
        partners: (Array.isArray(partners) ? partners : []).map((row) => ({
            name: String(row.name ?? ''),
            sentra: String(row.sentra ?? ''),
            commitment: numberOf(row.commitment),
            priority: String(row.priority ?? ''),
        })),

        /* ---------- ringkasan neraca commodities ---------- */

        volumeTotal() {
            return this.commodities.reduce((total, row) => total + row.value, 0);
        },

        farmerVolume() {
            return this.commodities.reduce((total, row) => total + row.farmer, 0);
        },

        bufferVolume() {
            return this.commodities.reduce((total, row) => total + row.buffer, 0);
        },

        bufferShare() {
            const total = this.volumeTotal();

            return total > 0 ? (this.bufferVolume() / total) * 100 : 0;
        },

        farmerShare() {
            const total = this.volumeTotal();

            return total > 0 ? (this.farmerVolume() / total) * 100 : 0;
        },

        tightestCommodity() {
            return this.commodities.reduce(
                (best, row) => (row.headroom < (best?.headroom ?? Number.POSITIVE_INFINITY) ? row : best),
                null,
            );
        },

        /* ---------- grafik & alokasi B2B ---------- */

        weeklyTotal() {
            return this.weeks.reduce((total, row) => total + row.total, 0);
        },

        peakWeek() {
            return this.weeks.find((row) => row.is_peak)?.week ?? '-';
        },

        activeWeek() {
            return this.weeks.find((row) => row.status === 'running')?.week ?? '-';
        },

        commitmentTotal() {
            return this.partners.reduce((total, row) => total + row.commitment, 0);
        },

        /* ---------- aksi monitoring ---------- */

        inspectCommodity(row) {
            this.notify(
                `Neraca ${row.code}`,
                `${row.name} terealisasi ${ton(row.value)} Ton dengan headroom ${ton(row.headroom)} Ton (${row.status}).`,
                'info',
            );
        },

        inspectWeek(row) {
            this.notify(
                `Minggu ${row.week}`,
                `Permintaan kontrak ${ton(row.demand)} Ton, yield ${ton(row.yield)} Ton, dan reserve ${ton(row.reserve)} Ton.`,
                'info',
            );
        },

        inspectPartner(row) {
            this.notify(
                `Mitra ${row.name}`,
                `Komitmen ${ton(row.commitment)} Ton/minggu dari sentra ${row.sentra} (${row.priority}).`,
                'info',
            );
        },

        reconcile() {
            const tightest = this.tightestCommodity();

            this.notify(
                'Rekonsiliasi Neraca Komoditas',
                `Total ${ton(this.volumeTotal())} Ton terbagi ${percent(this.farmerShare())}% (${ton(this.farmerVolume())} Ton) dan ${percent(this.bufferShare())}% buffer (${ton(this.bufferVolume())} Ton). Sisa kuota paling ketat tercatat pada ${tightest?.code ?? '-'}.`,
                'success',
            );
        },

        /* ---------- toast ---------- */

        notify(title, message, tone = 'info') {
            this.$dispatch('gpa:toast', { title, message, tone });
        },
    };
}
