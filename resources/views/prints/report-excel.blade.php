{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
{!! '<?mso-application progid="Excel.Sheet"?>' !!}
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">

 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Title>Laporan Resmi Penjualan Eksekutif Koperasi GPA</Title>
  <Author>{{ $namaDirektur ?? 'Ahmad Sanusi, S.P.' }}</Author>
  <Company>Koperasi Produsen Agro Green Pasundan (GPA)</Company>
  <Created>{{ date('Y-m-d\TH:i:s\Z') }}</Created>
 </DocumentProperties>

 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1A202C"/>
  </Style>

  {{-- Judul & Subjudul Dokumen --}}
  <Style ss:ID="DocTitle">
   <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#153A01"/>
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="DocSubtitle">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#4A5568"/>
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
  </Style>

  {{-- Meta Baris (Periode, Tanggal, Otoritas) --}}
  <Style ss:ID="MetaLabel">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Bold="1" ss:Color="#2D3748"/>
   <Interior ss:Color="#F8FAFC" ss:Pattern="Solid"/>
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
  </Style>
  <Style ss:ID="MetaValue">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Color="#1A202C"/>
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
  </Style>

  {{-- Section Header --}}
  <Style ss:ID="SectionHeader">
   <Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#153A01"/>
   <Interior ss:Color="#EAF5E9" ss:Pattern="Solid"/>
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#153A01"/>
   </Borders>
  </Style>

  {{-- Header Tabel (Hijau Tua Korporat GPA) --}}
  <Style ss:ID="TableHeader">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#153A01" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   </Borders>
  </Style>

  {{-- Teks Sel Standar (Auto Wrap agar TIDAK ADA kata terpotong) --}}
  <Style ss:ID="CellText">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Color="#1A202C"/>
   <Alignment ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>

  <Style ss:ID="CellTextCenter">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Color="#1A202C"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>

  <Style ss:ID="CellTextCode">
   <Font ss:FontName="Consolas" ss:Size="9.5" ss:Bold="1" ss:Color="#153A01"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="0"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>

  {{-- Format Angka Desimal & Persentase --}}
  <Style ss:ID="CellNumber">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Color="#1A202C"/>
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <NumberFormat ss:Format="#,##0.00"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>

  <Style ss:ID="CellPercent">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Color="#153A01" ss:Bold="1"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>

  {{-- Format Mata Uang Asli Excel (Rp #,##0 tanpa overflow #########) --}}
  <Style ss:ID="CellCurrency">
   <Font ss:FontName="Calibri" ss:Size="9.5" ss:Bold="1" ss:Color="#1A202C"/>
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <NumberFormat ss:Format="&quot;Rp &quot;#,##0"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   </Borders>
  </Style>

  {{-- Baris Total Akuntansi (Garis Ganda Bawah) --}}
  <Style ss:ID="TotalLabel">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#153A01"/>
   <Alignment ss:Horizontal="Right" ss:Vertical="Center" ss:WrapText="1"/>
   <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#0F172A"/>
    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="3" ss:Color="#0F172A"/>
   </Borders>
  </Style>

  <Style ss:ID="TotalCurrency">
   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#153A01"/>
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="&quot;Rp &quot;#,##0"/>
   <Borders>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#0F172A"/>
    <Border ss:Position="Bottom" ss:LineStyle="Double" ss:Weight="3" ss:Color="#0F172A"/>
   </Borders>
  </Style>

  {{-- Status Badges Berwarna (WrapText Aktif) --}}
  <Style ss:ID="StatusSuccess">
   <Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#166534"/>
   <Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BBF7D0"/></Borders>
  </Style>

  <Style ss:ID="StatusWarning">
   <Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#9A3412"/>
   <Interior ss:Color="#FFEDD5" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FED7AA"/></Borders>
  </Style>

  <Style ss:ID="StatusInfo">
   <Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#1E40AF"/>
   <Interior ss:Color="#DBEAFE" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#BFDBFE"/></Borders>
  </Style>

  <Style ss:ID="StatusDanger">
   <Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#991B1B"/>
   <Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/>
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FECACA"/></Borders>
  </Style>
 </Styles>

 {{-- =====================================================================
      WORKSHEET 1: JURNAL PENJUALAN & TRANSAKSI
      ===================================================================== --}}
 <Worksheet ss:Name="Jurnal Penjualan">
  <Table ss:DefaultRowHeight="22">
   {{-- Lebar kolom luas & proporsional agar SELURUH teks terlihat tanpa terpotong --}}
   <Column ss:Width="40"/>   {{-- No --}}
   <Column ss:Width="195"/>  {{-- No. PO / Nama Komoditas (Muat nama komoditas 32 karakter) --}}
   <Column ss:Width="110"/>  {{-- Tanggal / Total Bobot kg --}}
   <Column ss:Width="310"/>  {{-- Entitas Pembeli / Rata-rata Harga (Muat nama PT 46 karakter penuh) --}}
   <Column ss:Width="155"/>  {{-- Kategori Klien / Total Nilai Penjualan --}}
   <Column ss:Width="380"/>  {{-- Komoditas & Netto (Muat multi-item sayur 120 karakter) --}}
   <Column ss:Width="175"/>  {{-- Metode Bayar (Muat Kasir Tunai / TOP Tempo 30D) --}}
   <Column ss:Width="170"/>  {{-- Status Transaksi (Muat Selesai Diterima Dock / Menunggu Verifikasi) --}}
   <Column ss:Width="165"/>  {{-- Total Nilai Transaksi (Rp) --}}

   {{-- Header Dokumen --}}
   <Row ss:Height="26">
    <Cell ss:MergeAcross="8" ss:StyleID="DocTitle"><Data ss:Type="String">KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell ss:MergeAcross="8" ss:StyleID="DocSubtitle"><Data ss:Type="String">REKAPITULASI RESMI PENJUALAN EKSEKUTIF, DISTRIBUSI KOMODITAS &amp; JURNAL TRANSAKSI KONSOLIDASI</Data></Cell>
   </Row>
   <Row ss:Height="8"/>

   {{-- Metadata Info --}}
   <Row ss:AutoFitHeight="1">
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Cakupan Periode:</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="MetaValue"><Data ss:Type="String">{{ $periodName ?? 'Semua Riwayat' }}</Data></Cell>
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Tanggal Ekspor:</Data></Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="MetaValue"><Data ss:Type="String">{{ date('d/m/Y H:i') }} WIB</Data></Cell>
   </Row>
   <Row ss:AutoFitHeight="1">
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Filter Entitas:</Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="MetaValue"><Data ss:Type="String">{{ $clientFilterName ?? 'Konsolidasi Mitra B2B &amp; Klien Reguler' }}</Data></Cell>
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Status Audit:</Data></Cell>
    <Cell ss:MergeAcross="2" ss:StyleID="MetaValue"><Data ss:Type="String">DISAHKAN // SISTEM RESMI GPA</Data></Cell>
   </Row>
   <Row ss:Height="12"/>

   {{-- KPI Ringkasan Eksekutif --}}
   <Row ss:Height="22">
    <Cell ss:MergeAcross="8" ss:StyleID="SectionHeader"><Data ss:Type="String">RINGKASAN METRIK KINERJA EKSEKUTIF</Data></Cell>
   </Row>
   <Row ss:Height="24">
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Total Omzet Transaksi:</Data></Cell>
    <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ (float) ($totalRevenue ?? 0) }}</Data></Cell>
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Total Tonase Terjual:</Data></Cell>
    <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ (float) ($totalTonnageKg ?? 0) }}</Data></Cell>
    <Cell ss:StyleID="MetaLabel"><Data ss:Type="String">Volume PO:</Data></Cell>
    <Cell ss:StyleID="CellTextCenter"><Data ss:Type="String">{{ count($orders ?? []) }} Pesanan</Data></Cell>
   </Row>
   <Row ss:Height="14"/>

   {{-- Bagian 1: Distribusi Penjualan Per Komoditas --}}
   <Row ss:Height="22">
    <Cell ss:MergeAcross="5" ss:StyleID="SectionHeader"><Data ss:Type="String">1. DISTRIBUSI REALISASI PENJUALAN PER KOMODITAS SAYURAN</Data></Cell>
   </Row>
   <Row ss:Height="24">
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Nama Komoditas Sayuran</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Total Bobot (Kg)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Rata-rata Harga (Rp)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Total Nilai Penjualan (Rp)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Pangsa Omzet</Data></Cell>
   </Row>

   @php $comIdx = 1; @endphp
   @foreach($commoditySummary ?? [] as $com)
     @php
       $weight = (float) ($com->total_weight ?? 0);
       $sales = (float) ($com->total_sales ?? 0);
       $avgPrice = $weight > 0 ? ($sales / $weight) : 0;
       $totRev = (float) ($totalRevenue ?? 0);
       $sharePct = $totRev > 0 ? round(($sales / $totRev) * 100, 1) : 0;
       $comName = $com->product->name ?? ('Komoditas #' . ($com->product_id ?? '-'));
     @endphp
     <Row ss:AutoFitHeight="1">
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="Number">{{ $comIdx++ }}</Data></Cell>
      <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $comName }}</Data></Cell>
      <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ $weight }}</Data></Cell>
      <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ round($avgPrice) }}</Data></Cell>
      <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ $sales }}</Data></Cell>
      <Cell ss:StyleID="CellPercent"><Data ss:Type="String">{{ $sharePct }}%</Data></Cell>
     </Row>
   @endforeach

   <Row ss:Height="16"/>

   {{-- Bagian 2: Rincian Lengkap Buku Transaksi Pemesanan --}}
   <Row ss:Height="22">
    <Cell ss:MergeAcross="8" ss:StyleID="SectionHeader"><Data ss:Type="String">2. BUKU JURNAL TRANSAKSI PEMESANAN LENGKAP (KONSOLIDASI B2B &amp; REGULER)</Data></Cell>
   </Row>
   <Row ss:Height="26">
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No. Pesanan (PO)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Tanggal</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Entitas Pembeli</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Kategori Klien</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Rincian Komoditas &amp; Netto</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Metode Bayar</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Status Transaksi</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Total Nilai Transaksi (Rp)</Data></Cell>
   </Row>

   @php
     $orderIdx = 1;
     $orderSum = 0;
   @endphp
   @foreach($orders ?? [] as $order)
     @php
       $grand = (float) $order->grand_total;
       $orderSum += $grand;
       $buyerName = $order->user?->company_name ?: ($order->user?->name ?: 'Klien');
       $clientCat = $order->client_type_label ?? ($order->user?->client_type_label ?? ($order->user?->is_b2b ? 'Mitra B2B Kontrak' : 'Pelanggan Reguler'));
       $payMethod = $order->payment_method_label ?? ($order->payment?->method_label ?? str_replace('_', ' ', $order->payment_method ?? 'Kredit Tempo TOP'));
       $statusLabel = $order->status_label ?? str_replace('_', ' ', $order->status);

       $itemStrings = [];
       foreach ($order->orderItems as $item) {
           $pName = $item->product?->name ?? 'Sayuran';
           $itemStrings[] = $pName . ' (' . number_format($item->ordered_qty, 1, ',', '.') . ' kg)';
       }
       $commodityDetails = implode(', ', $itemStrings);

       $statusStyle = match ($order->status) {
           'SELESAI', 'LUNAS'     => 'StatusSuccess',
           'SELESAI_CATATAN'      => 'StatusWarning',
           'SIAP_KIRIM', 'DALAM_PENGIRIMAN' => 'StatusInfo',
           'BATAL'                => 'StatusDanger',
           default                => 'CellTextCenter',
       };
     @endphp
     <Row ss:AutoFitHeight="1">
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="Number">{{ $orderIdx++ }}</Data></Cell>
      <Cell ss:StyleID="CellTextCode"><Data ss:Type="String">{{ $order->order_number }}</Data></Cell>
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="String">{{ $order->created_at->format('d/m/Y') }}</Data></Cell>
      <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $buyerName }}</Data></Cell>
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="String">{{ $clientCat }}</Data></Cell>
      <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $commodityDetails }}</Data></Cell>
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="String">{{ $payMethod }}</Data></Cell>
      <Cell ss:StyleID="{{ $statusStyle }}"><Data ss:Type="String">{{ $statusLabel }}</Data></Cell>
      <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ $grand }}</Data></Cell>
     </Row>
   @endforeach

   {{-- Baris Total Jurnal Penjualan --}}
   <Row ss:Height="24">
    <Cell ss:MergeAcross="7" ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL KESELURUHAN OMZET TRANSAKSI PEMESANAN:</Data></Cell>
    <Cell ss:StyleID="TotalCurrency"><Data ss:Type="Number">{{ $orderSum }}</Data></Cell>
   </Row>
  </Table>
 </Worksheet>

 {{-- =====================================================================
      WORKSHEET 2: BUKU BESAR REKAPITULASI KLIEN & PIUTANG (AR)
      ===================================================================== --}}
 <Worksheet ss:Name="Rekap Klien &amp; Piutang">
  <Table ss:DefaultRowHeight="22">
   <Column ss:Width="40"/>   {{-- No --}}
   <Column ss:Width="320"/>  {{-- Entitas Pelanggan (Muat 46 karakter) --}}
   <Column ss:Width="150"/>  {{-- Kategori Klien (Muat Mitra B2B Kontrak) --}}
   <Column ss:Width="155"/>  {{-- No. Kontrak / ID --}}
   <Column ss:Width="95"/>   {{-- Frekuensi PO --}}
   <Column ss:Width="120"/>  {{-- Volume Tera kg --}}
   <Column ss:Width="160"/>  {{-- Tagihan Bruto (Rp) --}}
   <Column ss:Width="165"/>  {{-- Kas Masuk / Settled (Rp) --}}
   <Column ss:Width="160"/>  {{-- Sisa Piutang Berjalan (Rp) --}}
   <Column ss:Width="165"/>  {{-- Ketentuan Tenor (Muat Tunai / QRIS Instan) --}}
   <Column ss:Width="140"/>  {{-- Status Pelunasan --}}

   {{-- Header Dokumen Sheet 2 --}}
   <Row ss:Height="26">
    <Cell ss:MergeAcross="10" ss:StyleID="DocTitle"><Data ss:Type="String">BUKU BESAR REKAPITULASI PELANGGAN &amp; STATUS PIUTANG (AR)</Data></Cell>
   </Row>
   <Row ss:Height="20">
    <Cell ss:MergeAcross="10" ss:StyleID="DocSubtitle"><Data ss:Type="String">MONITORING KINERJA OMZET MITRA B2B &amp; KLIEN REGULER KOPERASI AGRO GREEN PASUNDAN</Data></Cell>
   </Row>
   <Row ss:Height="12"/>

   <Row ss:Height="26">
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Entitas Mitra / Pelanggan</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Kategori</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">No. Kontrak / Ref ID</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Frekuensi PO</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Volume Tera (Kg)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Tagihan Bruto (Rp)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Kas Masuk / Settled (Rp)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Sisa Piutang (Rp)</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Ketentuan Tenor</Data></Cell>
    <Cell ss:StyleID="TableHeader"><Data ss:Type="String">Status</Data></Cell>
   </Row>

   @php
     $cIdx = 1;
     $totGross = 0;
     $totSettled = 0;
     $totRec = 0;
     $totVol = 0;
     $totPo = 0;
   @endphp
   @foreach($dataKlien ?? [] as $row)
     @php
       $gross = (float) ($row['gross'] ?? 0);
       $settled = (float) ($row['settled'] ?? 0);
       $receivable = (float) ($row['receivable'] ?? 0);
       $vol = (float) ($row['volume_kg'] ?? 0);
       $po = (int) ($row['po_count'] ?? 0);

       $totGross += $gross;
       $totSettled += $settled;
       $totRec += $receivable;
       $totVol += $vol;
       $totPo += $po;

       $statusStyle = ($receivable <= 0) ? 'StatusSuccess' : 'StatusWarning';
     @endphp
     <Row ss:AutoFitHeight="1">
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="Number">{{ $cIdx++ }}</Data></Cell>
      <Cell ss:StyleID="CellText"><Data ss:Type="String">{{ $row['name'] ?? '-' }}</Data></Cell>
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="String">{{ $row['category'] ?? 'Mitra B2B' }}</Data></Cell>
      <Cell ss:StyleID="CellTextCode"><Data ss:Type="String">{{ $row['code'] ?? '-' }}</Data></Cell>
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="Number">{{ $po }}</Data></Cell>
      <Cell ss:StyleID="CellNumber"><Data ss:Type="Number">{{ $vol }}</Data></Cell>
      <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ $gross }}</Data></Cell>
      <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ $settled }}</Data></Cell>
      <Cell ss:StyleID="CellCurrency"><Data ss:Type="Number">{{ $receivable }}</Data></Cell>
      <Cell ss:StyleID="CellTextCenter"><Data ss:Type="String">{{ $row['term_label'] ?? 'TOP 30 Hari' }}</Data></Cell>
      <Cell ss:StyleID="{{ $statusStyle }}"><Data ss:Type="String">{{ $row['status_label'] ?? 'Belum Lunas' }}</Data></Cell>
     </Row>
   @endforeach

   {{-- Baris Total Rekap Klien & Piutang --}}
   <Row ss:Height="24">
    <Cell ss:MergeAcross="3" ss:StyleID="TotalLabel"><Data ss:Type="String">TOTAL KONSOLIDASI PIUTANG KLIEN:</Data></Cell>
    <Cell ss:StyleID="TotalLabel"><Data ss:Type="Number">{{ $totPo }}</Data></Cell>
    <Cell ss:StyleID="TotalLabel"><Data ss:Type="Number">{{ $totVol }}</Data></Cell>
    <Cell ss:StyleID="TotalCurrency"><Data ss:Type="Number">{{ $totGross }}</Data></Cell>
    <Cell ss:StyleID="TotalCurrency"><Data ss:Type="Number">{{ $totSettled }}</Data></Cell>
    <Cell ss:StyleID="TotalCurrency"><Data ss:Type="Number">{{ $totRec }}</Data></Cell>
    <Cell ss:MergeAcross="1" ss:StyleID="TotalLabel"><Data ss:Type="String"></Data></Cell>
   </Row>
  </Table>
 </Worksheet>
</Workbook>
