# Invoice Template – Surgical Analysis for A4 Paper

## A4 Reference
- **Size:** 210mm × 297mm
- **In points (pt):** 595.28pt × 841.89pt
- **Usable content width:** ~170mm (20mm margin left/right)
- **Usable content height:** ~277mm (10mm margin top/bottom)

---

## Section Breakdown (by height %)

| Section | Height (px @1131) | Height (mm) | % of page |
|---------|-------------------|-------------|-----------|
| 1. Header | 101px | ~26.5mm | **8.9%** |
| 2. Invoice | 152px | ~40mm | **13.4%** |
| 3. Product | 248px | ~65mm | **21.9%** |
| 4. Notes | 90px | ~24mm | **7.9%** |
| 5. Footer | 190px | ~50mm | **16.8%** |
| **Total content** | ~781px | ~206mm | **69%** |

*Remaining ~31% = inter-section spacing, margins, overflow.*

---

## 1. HEADER SECTION (Logo + Address)
**Box: 800×101px | ~170mm × 26.5mm**

| Element | Dimension | Notes |
|---------|------------|-------|
| Container padding | 30px all sides | Content inset from edges |
| Logo | 60×60px (or 50×60px) | V logo, 15px right of text |
| Company name | ~26px font, ~200px width | "VERITY GADGETS", bold blue |
| Tagline | 14px font, ~300px width | Gray, below name |
| Contact block | Right-aligned | Email + phone, icons ~8px from text |
| Gradient line | Full width × 6px | Blue→green bar |

---

## 2. INVOICE SECTION (Title + Bill To + Meta)
**Box: 800×152px | ~170mm × 40mm**

| Element | Dimension | Notes |
|---------|------------|-------|
| "INVOICE" title | 32px font | 30px padding from gradient |
| Title block | 800×62px | Includes padding |
| Bill To column | 55% width | Left aligned |
| Meta column | 40% width | Right aligned |
| Bill To heading | 16px font | Bold |
| Customer lines | 14px font, 8px line-spacing | Name bold |
| Status badge | ~22px height | Green, rounded, white text |

---

## 3. PRODUCT SECTION (Table + Totals)
**Box: 800×248px | ~170mm × 65mm**

| Element | Dimension | Notes |
|---------|------------|-------|
| Table header | 14px padding, 42px row height | Gradient bg |
| Product image | 70×70px | 15px right of text |
| Product name | 14px bold | "iPhone X" |
| Spec line | 12px gray | "256GB • Grade A • 95%" |
| Row padding | 18px vertical | |
| Totals block | 300px width, right-aligned | Subtotal, Waybill, TOTAL |
| TOTAL bar | ~40px height | Gradient, white bold text |

---

## 4. NOTES SECTION
**Box: 800×90px | ~170mm × 24mm**

| Element | Dimension | Notes |
|---------|------------|-------|
| Padding | 30px | |
| "Notes:" | 14px bold | |
| Bullets | 20px left indent | 14px text |
| Thank you | Bold green, 14px | |
| Tagline | 13px gray | "Verified Value. Visible Quality." |

---

## 5. FOOTER SECTION
**Box: 800×190px | ~170mm × 50mm**

| Element | Dimension | Notes |
|---------|------------|-------|
| Main block | 800×130px | 30px padding |
| Thank you (left) | Bold green | |
| QR code | 110×110px | Right aligned |
| "Scan for Support" | 10–12px gray | Below QR |
| Bottom bar | 6px height | Same gradient |
| Copyright | 14px, 20px padding | Centered gray |

---

## CSS Units for Implementation
- Use `mm` for A4 fidelity: `width: 170mm`, padding `7mm` (~30px equivalent)
- Or keep `px` for DomPDF: container `800px`, padding `30px`
- Gradient line: `height: 6px` (or `1.6mm`)
