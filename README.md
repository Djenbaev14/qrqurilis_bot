🚀 Citizen Application Management System (CAMS)
Ushbu loyiha fuqarolar murojaatlarini qabul qilish, ularni tahlil qilish va tegishli tashkilotlarga (shirkatlarga) yo'naltirish jarayonini avtomatlashtirish uchun ishlab chiqilgan. Tizim Telegram Bot (Frontend) va Filament Admin Panel (Backend) dan iborat.

🛠 Texnologik stek
Framework: Laravel 11

Admin Panel: Filament v3 (TALL Stack)

Database: PostgreSQL / MySQL

Bot API: Telegram Bot API

Access Control: Spatie Laravel Permission

Media: Laravel Media Library / Custom Storage handling

✨ Asosiy imkoniyatlar
🤖 Telegram Bot (Citizen Interface)
Multimodal murojaat: Matn, rasm va video ko'rinishidagi murojaatlarni yuborish.

Album handling: Bir vaqtda yuborilgan bir nechta rasmlarni (Media Group) Queue va Cache yordamida bitta murojaatga jamlash.

Real-time notifications: Murojaat holati o'zgarganda (qabul qilindi, rad etildi, bajarildi) bot orqali tezkor xabar olish.

🏢 Admin Panel (Management Interface)
Multi-tenancy mantiqi: Shirkat adminlari faqat o'zlariga biriktirilgan murojaatlarni ko'rishadi.

Custom Infolists: Murojaat ma'lumotlarini chiroyli va qulay ko'rish uchun maxsus Infolist va Blade komponentlari.

Action System: Bir tugma bilan murojaatni rad etish (sababini ko'rsatgan holda) yoki shirkatga yo'naltirish.

Advanced Filtering: Hududlar, statuslar va vaqt bo'yicha mukammal filtrlash tizimi.

🏗 Arxitektura va Optimallashtirish
Loyiha davomida qo'llanilgan backend yechimlar:

DRY (Don't Repeat Yourself): O'ndan ortiq Resurslar va Page'lar uchun umumiy sxemalarni PHP Traitlar orqali markazlashtirganman.

Performance: Og'ir media fayllar va ko'p sonli so'rovlar bilan ishlashda Laravel Queue (Redis/Database driver) tizimidan foydalanilgan.

Security: Spatie Permission orqali har bir status va resurs uchun qat'iy ruxsatnomalar o'rnatilgan.

📦 O'rnatish
Repozitoriyani klon qiling:

Bash
git clone https://github.com/username/project-name.git
Kutubxonalarni o'rnating:

Bash
composer install
npm install && npm run build
.env faylini sozlang va Webhookni faollashtiring:

Bash
php artisan bot:set-webhook