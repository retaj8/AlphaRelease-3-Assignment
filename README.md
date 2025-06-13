# AlphaRelease-6-Assignment
# ProjectCMT

## نبذة عن المشروع

مشروع **ProjectCMT** هو نظام إدارة مشاريع  للطلاب والمشرفين، يتيح إضافة وتحديث وحذف المشاريع، وربط الطلاب والمشرفين بالمشاريع، مع إمكانية البحث والتقارير. تم تطوير النظام باستخدام PHP وSQLite مع واجهات اختبار باستخدام PHPUnit.

---

## فريق العمل

- **[رتاج محمود الهادى 2210144738]**  
- **[مرام معتوق صالح2200107165]**  
 


---

## متطلبات التشغيل

- **PHP 8.1 أو أحدث**
- **Composer**
- **WAMP Server** (أو أي سيرفر محلي يدعم PHP وSQLite)
- **Git** (اختياري لإدارة النسخ)
- **PHPUnit** (لإجراء اختبارات الوحدة)

---

## خطوات تثبيت WAMP Server

1. قم بتحميل WAMP Server من الموقع الرسمي:  
   [https://www.wampserver.com/en/](https://www.wampserver.com/en/)
2. ثبّت البرنامج باتباع التعليمات.
3. بعد التثبيت، شغّل WAMP وتأكد أن الأيقونة باللون الأخضر في شريط المهام.

---

## طريقة تشغيل المشروع

1. **استنساخ المشروع من GitHub:**
   ```bash
   git clone https://[github.com/[https://github.com/retaj8/AlphaRelease-3-Assignment.git]
   ```
   ```
   C:\wamp64\www\projectCMT
   ```

2. **تثبيت الاعتمادات (Composer):**
   افتح الطرفية داخل مجلد المشروع ثم نفذ:
   ```bash
   composer install
   ```

3. **تجهيز قاعدة البيانات:**
   - عند تشغيل الاختبارات أو أول تشغيل، سيتم إنشاء قاعدة بيانات SQLite تلقائيًا في مجلد المشروع (حسب الكود).

4. **تشغيل المشروع:**
   - افتح المتصفح وادخل على:
     ```
     http://localhost/projectCMT
     ```

---

## تثبيت أداة الاختبار PHPUnit

1. **عن طريق Composer (موصى به):**
   ```bash
   composer require --dev phpunit/phpunit ^9
   ```

2. **أو تحميلها يدوياً:**
   - من الموقع الرسمي: [https://phpunit.de/getting-started/phpunit-9.html](https://phpunit.de/getting-started/phpunit-9.html)

---

## تشغيل اختبارات الوحدة

من داخل مجلد المشروع، نفذ:
```bash
php vendor/bin/phpunit
```
أو لتوليد تقرير تغطية برمجية:
```bash
php vendor/bin/phpunit --coverage-html coverage
```
سيتم إنشاء مجلد `coverage` يحوي تقرير التغطية البرمجية.

---

## ملاحظات هامة

- تأكد من تفعيل إضافة **pdo_sqlite** في إعدادات PHP.
- إذا أردت استخدام Xdebug لتقارير التغطية، فعّل الإضافة في ملف php.ini.
- جميع الأكواد والاختبارات موجودة في مجلد `tests`.

---

## بنية المشروع

```
projectCMT/
│
├── Class/                # كلاس المشروع والعمليات
├── tests/                # اختبارات PHPUnit
├── vendor/               # مكتبات Composer
├── composer.json         # إعدادات Composer
├── phpunit.xml           # إعدادات PHPUnit
├── README.md             # هذا الملف
└── ...
```

---



