# Contributing to WarungKu Web

Terima kasih telah tertarik untuk berkontribusi ke WarungKu Web! 🎉

## 📋 Code of Conduct

Proyek ini mengikuti [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/). Dengan berpartisipasi, Anda diharapkan untuk mematuhi kode etik ini.

## 🚀 Getting Started

1. Fork repository ini
2. Clone fork Anda: `git clone https://github.com/riofach/warungku_web.git`
3. Buat branch baru: `git checkout -b feature/your-feature-name`
4. Lakukan perubahan
5. Commit dengan pesan yang jelas: `git commit -m "feat: add new feature"`
6. Push ke branch Anda: `git push origin feature/your-feature-name`
7. Buat Pull Request

## 📝 Commit Message Convention

Kami menggunakan [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- `feat`: Fitur baru
- `fix`: Bug fix
- `docs`: Perubahan dokumentasi
- `style`: Formatting (tidak mengubah logic)
- `refactor`: Refactoring kode
- `test`: Menambah atau memperbaiki test
- `chore`: Maintenance tasks

### Contoh

```
feat(checkout): add QRIS payment integration
fix(cart): fix session persistence issue
docs: update API documentation
```

## 🧪 Testing

Pastikan semua test pass sebelum membuat PR:

```bash
php artisan test
./vendor/bin/pint --test
```

## 📏 Code Style

- Gunakan [Laravel Pint](https://laravel.com/docs/pint) untuk formatting
- Ikuti [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Gunakan type hints dan return types

## 📂 Folder Structure

Ikuti struktur folder yang sudah ada:

```
app/
├── Http/
│   ├── Controllers/    # Request handlers
│   └── Requests/       # Form requests
├── Models/             # Eloquent models
└── Services/           # Business logic

resources/views/
├── layouts/            # Base layouts
├── components/         # Blade components
└── [feature]/          # Feature-specific views
```

## ⚠️ Important Rules

### Stock Reduction

**JANGAN** mengurangi stok di controller checkout. Stock reduction **HANYA** boleh dilakukan di `WebhookController` setelah payment success.

```php
// ❌ WRONG - di CheckoutController
$item->stock -= $quantity;

// ✅ CORRECT - di WebhookController setelah payment success
DB::transaction(function () use ($order) {
    foreach ($order->orderItems as $orderItem) {
        $orderItem->item->decrement('stock', $orderItem->quantity);
    }
});
```

## 🤝 Pull Request Guidelines

1. Update README.md jika diperlukan
2. Pastikan semua test pass
3. Pastikan tidak ada breaking changes tanpa diskusi terlebih dahulu
4. Gunakan deskripsi yang jelas pada PR

## 💬 Need Help?

Jika ada pertanyaan, silakan buat issue baru dengan label `question`.

Terima kasih! 🙏
