# Smart Chat - افزونه چت هوشمند وردپرس

یک افزونه چت هوشمند برای وردپرس با طراحی مینیمال و پشتیبانی کامل از فارسی/RTL

## ویژگی‌ها

- **طراحی مینیمال و مدرن**: رابط کاربری زیبا با انیمیشن‌های نرم
- **پشتیبانی کامل از فارسی**: تمام متن‌ها و رابط کاربری به فارسی
- **پشتیبانی از RTL**: طراحی مناسب برای زبان‌های راست به چپ
- **چندین حالت پاسخگویی**: داخلی، خارجی، یا ترکیبی
- **اتصال به API خارجی**: پشتیبانی از OpenAI، Rasa، DialogFlow و API سفارشی
- **جستجوی هوشمند**: جستجو در محتوای داخلی سایت
- **قابلیت شخصی‌سازی**: تنظیم رنگ‌ها، جایگاه، متن‌ها و غیره
- **امنیت بالا**: Rate limiting، nonce verification، sanitization کامل
- **عملکرد بهینه**: بارگذاری سبک و سریع

## نصب

1. فایل‌های افزونه را در پوشه `/wp-content/plugins/smart-chat/` کپی کنید
2. افزونه را از طریق منوی 'افزونه‌ها' در وردپرس فعال کنید
3. تنظیمات را از طریق 'تنظیمات > Smart Chat' انجام دهید

## ساختار فایل‌ها

```
smart-chat/
├── smart-chat.php              # فایل اصلی افزونه
├── uninstall.php               # اسکریپت حذف
├── readme.txt                  # فایل readme وردپرس
├── includes/                   # کلاس‌های PHP
│   ├── class-loader.php        # کلاس اصلی بارگذاری
│   ├── class-admin.php         # مدیریت تنظیمات ادمین
│   ├── class-rest.php          # API REST
│   ├── class-router.php        # مدیریت درخواست‌های AJAX
│   ├── class-rate-limit.php    # محدودیت نرخ درخواست
│   └── Providers/              # Provider های چت‌بات
│       ├── interface-provider.php
│       └── class-provider-mock.php
├── assets/                     # فایل‌های فرانت‌اند
│   ├── css/widget.css          # استایل‌های ویجت
│   └── js/
│       ├── widget.js           # جاوااسکریپت ویجت
│       └── admin.js            # جاوااسکریپت ادمین
├── templates/                  # قالب‌های HTML
│   └── widget.php              # قالب ویجت چت
├── languages/                  # فایل‌های ترجمه
│   └── smart-chat.pot         # فایل ترجمه
└── tests/                      # فایل‌های تست
    ├── phpunit.xml
    └── test-basic.php
```

## استفاده

### حالت داخلی
افزونه در حالت داخلی از محتوای موجود در سایت برای پاسخگویی استفاده می‌کند.

### حالت خارجی
برای استفاده از API خارجی:
1. نوع Provider را انتخاب کنید
2. کلید API را وارد کنید
3. آدرس Endpoint را مشخص کنید

### حالت ترکیبی
ترکیب پاسخ‌های داخلی و خارجی با وزن‌دهی قابل تنظیم

## توسعه

### افزودن Provider جدید
برای افزودن Provider جدید، کلاس `Provider_Interface` را پیاده‌سازی کنید:

```php
class Provider_Custom implements Provider_Interface {
    public function get_response($message) {
        // پیاده‌سازی منطق Provider
        return [
            'message' => 'پاسخ از Provider سفارشی',
            'sources' => [],
            'confidence' => 0.8
        ];
    }
    
    public function is_available() {
        return true;
    }
    
    public function get_name() {
        return 'Provider سفارشی';
    }
    
    public function get_description() {
        return 'توضیحات Provider';
    }
}
```

### تست
برای اجرای تست‌ها:
```bash
cd tests
phpunit
```

## مجوز

این پروژه تحت مجوز GPL-2.0-or-later منتشر شده است.

## نویسنده

**hoseinmos** - [GitHub](https://github.com/hoseinmos)

## پشتیبانی

برای گزارش مشکلات یا پیشنهادات، لطفاً یک Issue در GitHub ایجاد کنید.
