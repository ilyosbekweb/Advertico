-- Faqat ESKI backend bilan ishlab turgan serverlar uchun.
-- Eski "applications" jadvalidagi arizalarni saqlab qolib, uni yangi tuzilishga keltiradi.
--
-- Tartib:
--   1) Eski arizalar jadvalini zaxiralang (phpMyAdmin → Export).
--   2) Ushbu faylni import qiling — u eski jadvalni applications_legacy nomiga o'tkazadi.
--   3) schema.sql va seed.sql ni import qiling (yangi jadvallar yaratiladi).
--   4) 002_import_legacy_applications.sql ni import qiling — eski arizalar ko'chiriladi.

RENAME TABLE applications TO applications_legacy;
