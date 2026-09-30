# KIBLAT DOKUMENTASI & DEVELOPMENT
# AgroOrder GPA

> Dokumen ini menjadi **master guideline / kiblat** untuk seluruh dokumen proyek AgroOrder GPA.
> Semua dokumen turunan seperti SRS, Use Case, Activity Diagram, ERD, Class Diagram, Flowchart, UI/UX, API, Test Case, hingga dokumentasi development harus mengacu dan konsisten terhadap dokumen ini.

---

## 0. IDENTITAS PROYEK

| Parameter | Keterangan |
|---|---|
| **Nama Produk** | **AgroOrder GPA** |
| **Jenis Sistem** | Web Application terintegrasi untuk rantai pasok & e-commerce agribisnis |
| **Mitra / Klien** | Koperasi / Kelompok Tani Mandiri GPA |
| **Tim** | TEAM-065 — Rekayasa Perangkat Lunak |
| **Target** | UKK dan implementasi operasional GPA |
| **Backend** | PHP + Laravel |
| **Frontend** | Blade + Tailwind CSS + Alpine.js / Vanilla JS |
| **Database** | MySQL / MariaDB |
| **Dokumen Induk** | PRD AgroOrder GPA |

**Sumber utama:** PRD menetapkan identitas proyek, target demonstrasi, serta siklus rilis sebagai fondasi pengembangan. 

---

# 1. ATURAN UTAMA: PRD ADALAH SOURCE OF TRUTH

Semua dokumen setelah PRD wajib mengikuti keputusan yang sudah ditetapkan dalam PRD.

### Prinsip Konsistensi

1. **Jangan membuat fitur baru** yang tidak ada dalam scope tanpa mencatatnya sebagai perubahan requirement.
2. **Jangan menghapus fitur Must Have** hanya karena implementasinya belum dibuat.
3. Nama aktor, modul, status, field, dan alur harus konsisten antar-dokumen.
4. Bila terjadi perubahan requirement, update PRD / Change Log terlebih dahulu.
5. ERD harus mengikuti kebutuhan bisnis, bukan sebaliknya.
6. Flowchart harus menggambarkan alur yang benar-benar akan diterapkan di sistem.
7. Use Case, User Story, Acceptance Criteria, Flowchart, API, dan Test Case harus dapat ditelusuri satu sama lain.
8. Dokumen teknis tidak boleh bertentangan dengan batasan **In-Scope**, **Out-of-Scope**, dan **Constraints**.

---

# 2. HIRARKI DOKUMENTASI PROYEK

Urutan dokumen yang digunakan sebagai kiblat development:

```text
PRD
 │
 ├── SRS / Functional Requirements
 │    │
 │    ├── Use Case Diagram
 │    ├── Use Case Specification
 │    ├── User Story
 │    └── Acceptance Criteria / Gherkin
 │
 ├── Business Process / As-Is & To-Be
 │    │
 │    ├── Business Flow
 │    └── Flowchart
 │
 ├── System Design
 │    │
 │    ├── System Architecture
 │    ├── Database Design
 │    │    ├── ERD
 │    │    ├── Data Dictionary
 │    │    └── Database Schema
 │    │
 │    └── Class / Component Design
 │
 ├── UI/UX Design
 │    │
 │    ├── Information Architecture
 │    │    ├── Sitemap
 │    │    ├── Wireframe
 │    │    └── UI Design
 │    │
 ├── Technical Specification
 │    │    │
 │    │    ├── Routing
 │    │    ├── API / Endpoint
 │    │    ├── Validation
 │    │    ├── Authorization / RBAC
 │    │    └── File / PDF Processing
 │    │
 ├── Development
 │    │    │
 │    │    ├── Repository & Branching
 │    │    ├── Migration
 │    │    ├── Model
 │    │    ├── Controller
 │    │    ├── Service / Business Logic
 │    │    ├── View
 │    │    └── Integration
 │    │
 └── Quality Assurance
      │
      ├── Test Case
      ├── Functional Test
      ├── Integration Test
      ├── UAT
      ├── Bug / Issue Tracking
      └── Deployment Checklist
```

---

# 3. MASTER TRACEABILITY MATRIX

Setiap requirement utama harus mempunyai dokumen desain, implementasi, dan pengujian.

| Requirement / Modul | Use Case | Flowchart | ERD | UI | Backend | Test Case | Status |
|---|---|---|---|---|---|---|---|
| Auth & RBAC | UC-ACC | FLOW-ACC | User/Role | Login | Auth + Middleware | TC-ACC | ⬜ |
| Katalog | UC-CAT | FLOW-CAT | Product/Stock | Catalog | Product Module | TC-CAT | ⬜ |
| Pemesanan | UC-ORD | FLOW-ORD | Order/Order Detail | Order Form | Order Module | TC-ORD | ⬜ |
| Stok & Supply | UC-SUP | FLOW-SUP | Harvest/Buffer Stock | Stock Dashboard | Supply Module | TC-SUP | ⬜ |
| Timbangan | UC-WGH | FLOW-WGH | Weighing | Weighing Form | Weighing Logic | TC-WGH | ⬜ |
| Surat Jalan | UC-LOG | FLOW-LOG | Delivery/Delivery Doc | Driver Dashboard | Logistics Module | TC-LOG | ⬜ |
| PoD | UC-POD | FLOW-POD | Proof of Delivery | PoD Form | Upload/Storage | TC-POD | ⬜ |
| Faktur | UC-INV | FLOW-INV | Invoice | Invoice Page | Billing Module | TC-INV | ⬜ |
| Laporan | UC-REP | FLOW-REP | Reporting Sources | Dashboard | Report Module | TC-REP | ⬜ |
| Approval Kontrak | UC-CON | FLOW-CON | Contract | Approval Page | Contract Module | TC-CON | ⬜ |

> **Aturan:** tidak boleh ada requirement penting yang “hilang” ketika masuk ke tahap desain atau development.

---

# 4. DOKUMEN #01 — SRS / SYSTEM REQUIREMENTS SPECIFICATION

## Tujuan

Mengubah PRD menjadi requirement sistem yang dapat diterjemahkan langsung menjadi fitur.

## Isi Minimum

```text
1. Pendahuluan
2. Ruang Lingkup
3. Aktor & Hak Akses
4. Functional Requirements
5. Non-Functional Requirements
6. Business Rules
7. Use Case Reference
8. Validation Rules
9. Acceptance Criteria
10. Traceability Matrix
```

## Output

```text
/docs/01-srs.md
```

---

# 5. DOKUMEN #02 — USE CASE DIAGRAM & SPECIFICATION

PRD menetapkan lima kelompok pengguna utama:

```text
KLIEN
SEKRE / ADMIN
KOORDINATOR LAPANGAN
ARMADA LOGISTIK
DIREKTUR / OWNER
```

## Yang Harus Dibuat

### Use Case Diagram

Memetakan hubungan actor → use case.

### Use Case Specification

Setiap use case minimal memiliki:

```text
ID
Nama Use Case
Aktor
Tujuan
Precondition
Trigger
Main Flow
Alternative Flow
Exception Flow
Postcondition
Business Rules
Related Requirement
```

## Output

```text
/docs/02-use-case.md
/docs/diagrams/use-case.puml
```

---

# 6. DOKUMEN #03 — USER STORY & ACCEPTANCE CRITERIA

Format standar:

```text
Sebagai [aktor]
Saya ingin [aksi]
Agar [tujuan]
```

Setiap user story harus mempunyai acceptance criteria.

Contoh struktur:

```gherkin
Feature: Pengecekan stok

Scenario: Stok tersedia
  Given user berada di katalog
  When user memilih produk
  Then sistem menampilkan status stok
```

## Aturan

User Story tidak boleh berdiri sendiri. Setiap story harus dapat dilacak ke:

```text
PRD
   ↓
Requirement
   ↓
Use Case
   ↓
User Story
   ↓
Acceptance Criteria
   ↓
Implementation
   ↓
Test Case
```

---

# 7. DOKUMEN #04 — BUSINESS PROCESS & FLOWCHART

Flowchart dibuat dari alur bisnis, bukan dari struktur kode.

## Level Flowchart

### Level 1 — System Overview

```text
Klien
  ↓
Katalog
  ↓
Pesanan
  ↓
Verifikasi
  ↓
Panen / Buffer Stock
  ↓
Packing & Timbangan
  ↓
Surat Jalan
  ↓
Pengiriman
  ↓
PoD
  ↓
Selesai
  ↓
Faktur / Laporan
```

### Level 2 — Flow Per Modul

Minimal dibuat:

```text
FLOW-AUTH
FLOW-CATALOG
FLOW-ORDER
FLOW-SUPPLY
FLOW-WEIGHING
FLOW-DELIVERY
FLOW-POD
FLOW-INVOICE
FLOW-REPORT
FLOW-CONTRACT
```

### Setiap Flowchart Wajib Menjawab

```text
Siapa yang menjalankan?
Apa inputnya?
Apa validasinya?
Apa keputusan sistem?
Apa outputnya?
Ke mana proses berikutnya?
Apa kondisi gagal/error?
```

## Output

```text
/docs/04-flowchart.md
/docs/diagrams/flow-*.puml
```

---

# 8. DOKUMEN #05 — SYSTEM ARCHITECTURE

Arsitektur mengikuti pendekatan MVC dan RESTful Routing yang ditetapkan PRD.

```text
                 CLIENT
                    │
             Browser / Mobile
                    │
                 HTTPS
                    │
            Laravel Application
          ┌─────────┼─────────┐
          │         │         │
       Routes    Controllers  Middleware
          │         │         │
          └─────────┼─────────┘
                    │
             Service / Logic
                    │
                 Models
                    │
               Eloquent ORM
                    │
              MySQL / MariaDB
                    │
       ┌────────────┼────────────┐
       │            │            │
      PDF        Excel       File Storage
```

## Dokumen Wajib

```text
/docs/05-system-architecture.md
/docs/diagrams/system-architecture.puml
```

---

# 9. DOKUMEN #06 — ERD / DATABASE DESIGN

## Prinsip

ERD harus diturunkan dari:

```text
Requirement
    ↓
Use Case
    ↓
Business Flow
    ↓
Data yang dibutuhkan setiap proses
    ↓
Entity
    ↓
Relationship
    ↓
ERD
```

## Kandidat Entity Utama

> Daftar ini merupakan **starting point**, bukan keputusan final. Entity final harus divalidasi lagi terhadap seluruh requirement.

```text
users
roles
products
product_prices
stocks
harvests
buffer_stocks
orders
order_items
weighings
deliveries
vehicles
drivers
proof_of_deliveries
contracts
contract_prices
invoices
invoice_items
payments
compensations
reports / audit_logs
```

## ERD Wajib Menjelaskan

```text
Primary Key
Foreign Key
Cardinality
Nullable / Required
Unique Constraint
Index
Status Field
Timestamp
Audit Field
```

## Output

```text
/docs/06-database-design.md
/docs/diagrams/erd.puml
/database/schema.sql
/database/data-dictionary.md
```

---

# 10. DOKUMEN #07 — DATA DICTIONARY

Untuk setiap tabel:

| Field | Type | Null | Key | Default | Description |
|---|---|---|---|---|---|
| id | BIGINT | NO | PK | - | Primary identifier |
| ... | ... | ... | ... | ... | ... |

## Rule

Nama field harus konsisten antara:

```text
ERD
↓
Migration
↓
Model
↓
Controller
↓
Form Request
↓
View
↓
Test
```

---

# 11. DOKUMEN #08 — CLASS / COMPONENT DESIGN

Dokumen ini menjembatani ERD dan kode Laravel.

## Minimal Memuat

```text
Model
Controller
Middleware
Request Validation
Service
Repository (jika digunakan)
Policy / Authorization
Event / Job (jika digunakan)
```

## Contoh Relasi Konseptual

```text
OrderController
      ↓
OrderService
      ↓
Order Model
      ↓
OrderItem Model
      ↓
Product / Stock Model
```

## Output

```text
/docs/08-class-component.md
/docs/diagrams/class-diagram.puml
```

---

# 12. DOKUMEN #09 — UI/UX & INFORMATION ARCHITECTURE

UI harus mengikuti actor dan workflow.

## Struktur Halaman Minimum

```text
PUBLIC
├── Landing Page
├── Catalog
├── Product Detail
├── Login / Register

CLIENT
├── Dashboard
├── Orders
├── Order Detail
├── Payment Confirmation
├── Invoice

SEKRE
├── Dashboard
├── Orders
├── Products
├── Stock / Weighing
├── Delivery Documents
├── Invoice
├── Reports
└── User Management

KOORDINATOR
├── Dashboard
├── Harvest Stock
├── Buffer Stock
├── Order Monitoring
└── Weighing

DRIVER
├── Dashboard
├── Delivery Manifest
├── Delivery Detail
└── PoD Upload

DIRECTOR
├── Executive Dashboard
├── Sales Analytics
├── Contract Approval
└── Reports
```

## Prinsip UI

```text
Role-based
Mobile-first
Responsive
Low-bandwidth friendly
Touch target jelas
Status mudah dikenali
Form tidak berlebihan
Error message jelas
```

## Output

```text
/docs/09-ui-ux.md
/docs/diagrams/sitemap.md
/design/wireframe/
/design/ui/
```

---

# 13. DOKUMEN #10 — TECHNICAL SPECIFICATION

Berisi aturan teknis implementasi.

## Routing

```text
Public Routes
Auth Routes
Client Routes
Sekre Routes
Coordinator Routes
Driver Routes
Director Routes
```

## Authorization

RBAC minimal:

```text
client
sekre
coordinator
logistics
owner
```

## Validation

Contoh:

```text
quantity > 0
minimum order terpenuhi
stock mencukupi
actual_weight wajib sebelum Surat Jalan
PoD wajib sebelum status Selesai
invoice hanya dapat dibuat dari delivery yang selesai
```

## File Handling

Dokumen yang perlu diperhatikan:

```text
Surat Jalan PDF
Invoice PDF
PoD Image
Excel Report
```

---

# 14. DOKUMEN #11 — API / ENDPOINT SPECIFICATION

Walaupun sistem berbasis Blade dapat menggunakan server-rendered forms, dokumentasi endpoint tetap diperlukan untuk interaksi AJAX / integrasi internal.

Format:

| Method | Endpoint | Role | Request | Response | Validation |
|---|---|---|---|---|---|
| GET | `/products` | Public | - | Product list | - |
| POST | `/orders` | Client/Sekre | Order payload | Order | Stock + quantity |
| PATCH | `/stocks/{id}` | Coordinator | Stock qty | Stock | Positive number |
| POST | `/orders/{id}/weighing` | Sekre/Coordinator | Actual weight | Updated order | Weight required |
| POST | `/deliveries/{id}/pod` | Driver | Image | PoD | Image required |

## Output

```text
/docs/11-api-specification.md
```

---

# 15. DOKUMEN #12 — DEVELOPMENT BLUEPRINT

Tahap ini mulai menerjemahkan seluruh dokumen menjadi kode.

## Urutan Implementasi

```text
1. Repository
2. Environment
3. Database Connection
4. Authentication
5. Role & Permission
6. Migration
7. Model & Relationship
8. Seeder
9. Middleware / Policy
10. Route
11. Request Validation
12. Controller
13. Service / Business Logic
14. Blade View
15. JavaScript Interaction
16. PDF / Excel
17. File Upload
18. Dashboard & Reporting
19. Integration
20. Testing
```

---

# 16. STRUKTUR DEVELOPMENT LARAVEL

Struktur konseptual:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Middleware/
│
├── Models/
├── Services/
├── Policies/
└── Helpers/

resources/
├── views/
├── css/
└── js/

database/
├── migrations/
├── seeders/
└── factories/

routes/
├── web.php
└── api.php

storage/
└── app/
    └── public/

public/
```

> Struktur aktual dapat disesuaikan dengan kebutuhan repository selama prinsip pemisahan tanggung jawab tetap terjaga.

---

# 17. DEFINITION OF READY (DOR)

Sebuah fitur baru boleh mulai dikerjakan apabila:

```text
[ ] Requirement sudah jelas
[ ] Aktor sudah jelas
[ ] Use Case sudah ada
[ ] Flow sudah ada
[ ] Entity/data yang dibutuhkan sudah diketahui
[ ] UI/wireframe sudah cukup jelas
[ ] Acceptance Criteria tersedia
[ ] Dependency sudah diketahui
```

---

# 18. DEFINITION OF DONE (DOD)

Sebuah fitur dianggap selesai apabila:

```text
[ ] Database selesai
[ ] Migration berhasil
[ ] Model & relationship benar
[ ] Authorization benar
[ ] Validation tersedia
[ ] Backend logic selesai
[ ] UI selesai
[ ] Error handling tersedia
[ ] Acceptance Criteria terpenuhi
[ ] Test Case lulus
[ ] Tidak ada bug blocker
[ ] Dokumentasi diperbarui
```

---

# 19. TESTING STRATEGY

Testing mengikuti requirement dan acceptance criteria.

## Level Testing

```text
Unit Test
    ↓
Feature Test
    ↓
Integration Test
    ↓
End-to-End Test
    ↓
UAT
```

## Test Case Format

| ID | Feature | Scenario | Precondition | Steps | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|---|
| TC-ORD-001 | Order | Stok cukup | Product tersedia | Input order | Order berhasil | - | ⬜ |
| TC-ORD-002 | Order | Stok kurang | Stock < order | Submit | Ditolak | - | ⬜ |

---

# 20. BUG / ISSUE TRACKING

Format issue:

| ID | Modul | Bug | Severity | Reproduction | Expected | Actual | Status |
|---|---|---|---|---|---|---|---|
| BUG-001 | Order | ... | High | ... | ... | ... | Open |

### Severity

```text
Blocker
Critical
High
Medium
Low
```

---

# 21. DEVELOPMENT WORKFLOW PER FITUR

Gunakan pola berikut setiap kali mengerjakan fitur baru:

```text
PRD
 ↓
Requirement
 ↓
Use Case
 ↓
Flowchart
 ↓
ERD / Data Impact
 ↓
UI / Wireframe
 ↓
Technical Spec
 ↓
Migration
 ↓
Model
 ↓
Validation
 ↓
Controller / Service
 ↓
View / JS
 ↓
Test
 ↓
Code Review
 ↓
Done
```

---

# 22. GIT WORKFLOW

Branch utama:

```text
main
└── development
    ├── feature/auth
    ├── feature/catalog
    ├── feature/order
    ├── feature/supply
    ├── feature/weighing
    ├── feature/logistics
    ├── feature/invoice
    └── feature/report
```

## Commit Convention

```text
feat: tambah modul pemesanan
fix: perbaiki validasi stok
refactor: rapikan OrderService
docs: update ERD
style: perbaiki tampilan katalog
test: tambah test pemesanan
chore: update dependency
```

---

# 23. ROADMAP DEVELOPMENT

Mengikuti roadmap PRD sebagai baseline:

```text
Sprint 0
├── Finalisasi PRD / SRS
├── Use Case
├── Acceptance Criteria
└── Validasi kebutuhan

Sprint 1
├── Repository
├── Migration
├── Authentication
├── Multi-role
└── Navigation

Sprint 2
├── Landing Page
├── Catalog
├── Stock Display
└── Online Order

Sprint 3
├── Manual Order
├── Harvest Stock
├── Buffer Stock
└── Inventory Sync

Sprint 4
├── Actual Weighing
├── PDF Surat Jalan
├── Driver Manifest
└── PoD

Sprint 5
├── Monthly Invoice
├── Compensation
├── Executive Dashboard
├── Contract Approval
└── Excel/PDF Report

Sprint 6
├── Functional Test
├── E2E Test
├── Responsive Test
└── Security Test

Sprint 7
├── UAT
├── UKK Simulation
└── Production Deployment
```

---

# 24. CHANGE MANAGEMENT

Setiap perubahan requirement harus dicatat.

| ID | Tanggal | Perubahan | Alasan | Dampak | Dokumen Terpengaruh | Approval |
|---|---|---|---|---|---|---|
| CR-001 | YYYY-MM-DD | ... | ... | ... | PRD, ERD, UI | ... |

## Alur Perubahan

```text
Permintaan Perubahan
        ↓
Analisis Dampak
        ↓
Approval
        ↓
Update PRD
        ↓
Update Requirement
        ↓
Update ERD / Flow / UI
        ↓
Update Code
        ↓
Update Test
        ↓
Regression Test
```

---

# 25. STRUKTUR FOLDER DOKUMENTASI

Disarankan:

```text
docs/
│
├── 00-master-kiblat.md
├── 01-prd.md
├── 02-srs.md
├── 03-use-case.md
├── 04-user-story.md
├── 05-flowchart.md
├── 06-system-architecture.md
├── 07-database-design.md
├── 08-data-dictionary.md
├── 09-class-component.md
├── 10-ui-ux.md
├── 11-technical-specification.md
├── 12-api-specification.md
├── 13-development.md
├── 14-testing.md
├── 15-uat.md
├── 16-deployment.md
├── 17-risk-register.md
├── 18-change-log.md
│
└── diagrams/
    ├── use-case.puml
    ├── activity-*.puml
    ├── flow-*.puml
    ├── erd.puml
    ├── class-diagram.puml
    └── architecture.puml
```

---

# 26. CHECKLIST SEBELUM CODING

```text
[ ] PRD approved
[ ] Scope jelas
[ ] Aktor jelas
[ ] Requirement jelas
[ ] Use Case selesai
[ ] Flowchart selesai
[ ] ERD cukup stabil
[ ] Data Dictionary tersedia
[ ] UI/wireframe tersedia
[ ] Acceptance Criteria tersedia
[ ] Branch dibuat
[ ] Task development dibuat
```

---

# 27. CHECKLIST SEBELUM DEMO / SIDANG UKK

```text
[ ] Semua Must Have selesai
[ ] Auth dan RBAC berjalan
[ ] Order end-to-end berjalan
[ ] Stok real-time berjalan
[ ] Timbangan riil memengaruhi total
[ ] Surat Jalan PDF dapat dibuat
[ ] Driver dapat melihat manifes
[ ] PoD dapat diunggah
[ ] Faktur dapat dibuat
[ ] Dashboard direktur berjalan
[ ] Export Excel/PDF berjalan
[ ] Test Case utama lulus
[ ] UAT selesai
[ ] Deployment berhasil
[ ] Database backup tersedia
[ ] Dokumentasi sinkron dengan aplikasi
```

---

# 28. ATURAN EMAS DOKUMENTASI

> **Satu requirement → satu alur → satu desain data → satu implementasi → satu pengujian.**

Jangan membuat ERD, flowchart, UI, atau kode secara terpisah tanpa menelusurinya kembali ke requirement.

```text
PRD
 ↓
WHAT
(Apa yang sistem harus lakukan?)
 ↓
USE CASE / USER STORY
 ↓
HOW THE USER WORKS
(Bagaimana user menjalankan proses?)
 ↓
FLOWCHART
 ↓
HOW THE SYSTEM IS STRUCTURED
(Bagaimana sistem menyimpan & memproses data?)
 ↓
ERD + ARCHITECTURE + CLASS DESIGN
 ↓
HOW IT IS IMPLEMENTED
 ↓
LARAVEL CODE
 ↓
HOW WE PROVE IT WORKS
 ↓
TESTING + UAT
 ↓
DEPLOYMENT
```

---

# 29. STATUS MASTER PROJECT

| Dokumen / Artefak | Status | Last Update | PIC |
|---|---|---|---|
| PRD | ✅ Baseline | September 2026 | Project Lead |
| SRS | ⬜ | - | - |
| Use Case | ⬜ | - | - |
| User Story | ✅ Draft di PRD | September 2026 | - |
| Flowchart | ⬜ | - | - |
| Architecture | ⬜ | - | - |
| ERD | ⬜ | - | - |
| Data Dictionary | ⬜ | - | - |
| Class Diagram | ⬜ | - | - |
| UI/UX | ⬜ | - | - |
| Technical Spec | ⬜ | - | - |
| API Spec | ⬜ | - | - |
| Development | ⬜ | - | - |
| Testing | ⬜ | - | - |
| UAT | ⬜ | - | - |
| Deployment | ⬜ | - | - |

---

# 30. NEXT DOCUMENT PRIORITY

Untuk melanjutkan dari PRD yang ada, urutan paling aman adalah:

```text
01. SRS final
      ↓
02. Use Case Diagram + Specification
      ↓
03. Business Flow / Flowchart
      ↓
04. ERD
      ↓
05. Data Dictionary
      ↓
06. System Architecture
      ↓
07. Class / Component Design
      ↓
08. UI/UX & Sitemap
      ↓
09. Technical Specification
      ↓
10. Development Blueprint
      ↓
11. Coding
      ↓
12. Testing
      ↓
13. UAT
      ↓
14. Deployment
```

**Dokumen ini adalah master acuan.** Setiap dokumen baru harus menambahkan referensi silang ke dokumen induk dan memperbarui status pada bagian `29. Status Master Project`.
