<?php $__env->startSection('content'); ?>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-4 py-4 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <a href="<?php echo e($backHref); ?>" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-white hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-amber-300/80">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-arrow-left'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    Back
                </a>
                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200"><?php echo e($filteredCount); ?> produk</span>
            </div>
            <h1 class="mt-3 text-lg font-semibold text-slate-900"><?php echo e($title); ?></h1>
            <p class="text-xs text-slate-600 sm:text-sm"><?php echo e($subtitle); ?></p>
            <?php if($categoryMeta): ?>
                <div class="mt-1 inline-flex items-center gap-2 rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">
                    <?php echo $__env->make('partials.category-icon', [
                        'icon' => $categoryMeta['icon'],
                        'alt' => $categoryMeta['name'],
                        'imgClass' => 'h-4 w-4 text-sky-700',
                        'textClass' => 'text-[10px] font-semibold text-sky-700',
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <span><?php echo e($categoryMeta['name']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="border-b border-slate-200 bg-white p-4 sm:p-6">
            <form method="GET" action="<?php echo e($basePath); ?>" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Search</label>
                    <input
                        type="text"
                        name="q"
                        value="<?php echo e($query); ?>"
                        placeholder="Search code, name, description..."
                        class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80"
                    />
                </div>

                <?php if($categories->count() > 0): ?>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Category</label>
                        <select name="category" class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80">
                            <option value="">All Categories</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($category->id); ?>" <?php if($categoryFilter === $category->id): echo 'selected'; endif; ?>><?php echo e($category->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Sack Color</label>
                    <select name="sackColor" class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80">
                        <option value="">All Colors</option>
                        <?php $__currentLoopData = $sackColors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($color); ?>" <?php if($sackColorFilter === $color): echo 'selected'; endif; ?>><?php echo e($color); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Rows</label>
                        <select name="pageSize" class="min-h-[44px] w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80">
                            <?php $__currentLoopData = [6, 12, 24, 48]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($size); ?>" <?php if($pageSize === $size): echo 'selected'; endif; ?>><?php echo e($size); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <input type="hidden" name="page" value="1">
                        <button type="submit" class="min-h-[44px] w-full rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Apply</button>
                    </div>
                </div>
            </form>

            <div class="mt-3 flex justify-end">
                <a href="<?php echo e($basePath); ?>" class="text-xs font-semibold text-slate-600 hover:text-slate-900">Reset filters</a>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <?php if($products->count() === 0): ?>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">No products found for current filters.</div>
            <?php else: ?>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('products.show', $product->id)); ?>?returnTo=<?php echo e(urlencode(request()->fullUrl())); ?>" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-emerald-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-300/80">
                            <div class="flex items-start gap-4">
                                <div class="w-16 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                    <img src="<?php echo e($product->image?->system_path ?? 'https://placehold.co/120x180/e2e8f0/334155?text=No+Image'); ?>" alt="<?php echo e($product->code); ?>" class="h-20 w-16 object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-emerald-700"><?php echo e($product->code); ?></p>
                                    <p class="mt-0.5 line-clamp-2 text-sm font-semibold text-slate-900"><?php echo e($product->name); ?></p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <?php if (isset($component)) { $__componentOriginalb01c2d1b999a0ab3daac8e7f1edd913f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb01c2d1b999a0ab3daac8e7f1edd913f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sack-color-badge','data' => ['color' => $product->sack_color,'variant' => 'outline','class' => 'px-2 py-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sack-color-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->sack_color),'variant' => 'outline','class' => 'px-2 py-0.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb01c2d1b999a0ab3daac8e7f1edd913f)): ?>
<?php $attributes = $__attributesOriginalb01c2d1b999a0ab3daac8e7f1edd913f; ?>
<?php unset($__attributesOriginalb01c2d1b999a0ab3daac8e7f1edd913f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb01c2d1b999a0ab3daac8e7f1edd913f)): ?>
<?php $component = $__componentOriginalb01c2d1b999a0ab3daac8e7f1edd913f; ?>
<?php unset($__componentOriginalb01c2d1b999a0ab3daac8e7f1edd913f); ?>
<?php endif; ?>
                                        <?php if($product->category): ?>
                                            <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800"><?php echo e($product->category->name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mt-2 line-clamp-2 text-xs text-slate-600"><?php echo e($product->description); ?></p>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <p class="text-sm text-slate-600">
                Showing <span class="font-semibold text-slate-900"><?php echo e($products->count()); ?></span>
                of <span class="font-semibold text-slate-900"><?php echo e($filteredCount); ?></span> products
                (<?php echo e($totalCount); ?> total)
            </p>
            <?php echo e($products->onEachSide(1)->links()); ?>

        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\productsaf-laravel\resources\views/catalog/products.blade.php ENDPATH**/ ?>