<?php $__env->startSection('content'); ?>
    <section id="top" class="relative mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-emerald-100/60 via-transparent to-amber-100/70"></div>
        <div class="relative">
            <p class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-slate-800">Katalog Resmi</p>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Katalog Produk Pakan Ternak Berkualitas</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-600 sm:text-base">Pilih kategori, cari produk, lalu lihat detail nutrisi.</p>
        </div>
    </section>

    <section id="katalog" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-slate-900">Kategori Produk</h2>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">
                    Total: <?php echo e($totalProducts); ?>

                </span>
            </div>
            <p class="mt-1 text-xs text-slate-600 sm:text-sm">Pilih kategori untuk menampilkan daftar produk.</p>
        </div>

        <div class="grid gap-3 p-4 sm:grid-cols-3 sm:p-6 lg:grid-cols-5">
            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('categories.show', $category->id)); ?>" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-300/80">
                    <div class="flex items-start justify-between gap-2">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-100">
                            <?php echo $__env->make('partials.category-icon', [
                                'icon' => $category->icon,
                                'alt' => $category->name,
                                'imgClass' => 'h-5 w-5 object-contain',
                                'textClass' => 'text-[10px] font-semibold text-emerald-700',
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </span>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?php echo e($category->products_count); ?></span>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-900"><?php echo e($category->name); ?></p>
                    <p class="mt-1 text-xs text-slate-500">Lihat produk</p>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="col-span-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    Belum ada kategori.
                </p>
            <?php endif; ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\productsaf-laravel\resources\views/catalog/home.blade.php ENDPATH**/ ?>