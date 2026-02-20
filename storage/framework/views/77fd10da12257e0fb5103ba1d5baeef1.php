<?php $__env->startSection('content'); ?>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50/60 px-4 py-4 sm:px-6">
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
                <?php echo e($backLabel); ?>

            </a>
            <div class="mt-3">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-emerald-700"><?php echo e($product->code); ?></p>
                    <h1 class="mt-1 text-lg font-semibold text-slate-900"><?php echo e($product->name); ?></h1>
                    <p class="text-xs text-slate-600">Detail produk & kandungan nutrisi.</p>
                    <?php if (isset($component)) { $__componentOriginalb01c2d1b999a0ab3daac8e7f1edd913f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb01c2d1b999a0ab3daac8e7f1edd913f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sack-color-badge','data' => ['color' => $product->sack_color,'variant' => 'outline','class' => 'mt-2']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sack-color-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->sack_color),'variant' => 'outline','class' => 'mt-2']); ?>
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
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 px-4 py-8 sm:px-6">
                    <div class="mx-auto mb-4 flex w-full max-w-4xl justify-start">
                        <div class="rounded-full bg-sky-100/90 px-3 py-1 text-xs font-semibold text-sky-800 shadow-sm ring-1 ring-sky-200">
                            <?php echo e($product->category->name); ?>

                        </div>
                    </div>
                    <img src="<?php echo e($product->image?->system_path ?? 'https://placehold.co/300x450/e2e8f0/334155?text=No+Image'); ?>" alt="<?php echo e($product->code); ?>" class="mx-auto h-auto w-full object-contain drop-shadow-md" style="max-width: 14rem;" loading="eager">
                </div>

                <div class="p-6 sm:p-8">
                    <div class="mb-6">
                        <p class="text-sm text-slate-600"><?php echo e($product->description); ?></p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-emerald-50 p-4 text-center">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-tag'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto text-emerald-700','style' => 'width:1.25rem;height:1.25rem;stroke-width:2.5;']); ?>
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
                            <p class="mt-1 text-[10px] text-slate-500">Kode</p>
                            <p class="text-sm font-extrabold text-slate-900"><?php echo e($product->code); ?></p>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-4 text-center">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-box'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto text-amber-800','style' => 'width:1.25rem;height:1.25rem;stroke-width:2.5;']); ?>
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
                            <p class="mt-1 text-[10px] text-slate-500">Warna Karung</p>
                            <p class="mt-1">
                                <?php if (isset($component)) { $__componentOriginalb01c2d1b999a0ab3daac8e7f1edd913f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb01c2d1b999a0ab3daac8e7f1edd913f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.sack-color-badge','data' => ['color' => $product->sack_color,'variant' => 'outline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('sack-color-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->sack_color),'variant' => 'outline']); ?>
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
                            </p>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background-color: #e0f2fe;">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-layers'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto text-sky-800','style' => 'width:1.25rem;height:1.25rem;stroke-width:2.5;']); ?>
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
                            <p class="mt-1 text-[10px] text-slate-500">Kategori</p>
                            <p class="text-sm font-extrabold text-slate-900"><?php echo e($product->category->name); ?></p>
                        </div>
                    </div>

                    <div class="mt-14 pb-3 sm:mt-16" style="margin-top: 1.5rem;">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('lucide-clipboard-list'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-emerald-700']); ?>
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
                            Kandungan Nutrisi
                        </h3>

                        <div class="mt-8 rounded-2xl border border-slate-200 shadow-sm" style="margin-top: 1.5rem;">
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed">
                                <colgroup>
                                    <col class="w-[62%] sm:w-[68%]">
                                    <col class="w-[38%] sm:w-[32%]">
                                </colgroup>
                                <thead>
                                    <tr class="bg-emerald-700 text-white">
                                        <th class="px-4 py-3 text-left text-sm font-semibold sm:px-8 sm:py-4 sm:text-base">Parameter</th>
                                        <th class="px-4 py-3 text-right text-sm font-semibold sm:px-8 sm:py-4 sm:text-base">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $product->nutritions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $nutrition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr
                                            class="border-t border-slate-200 <?php echo e($loop->even ? 'bg-emerald-50/40' : 'bg-white'); ?>"
                                            <?php if($loop->even): ?> style="background-color: rgb(236 253 245 / 0.4);" <?php endif; ?>
                                        >
                                            <td class="break-words px-4 py-3 text-sm leading-6 text-slate-600 sm:px-8 sm:py-4 sm:text-base sm:leading-7"><?php echo e($nutrition->label); ?></td>
                                            <td class="px-4 py-3 text-right text-sm font-semibold leading-6 text-slate-900 sm:px-8 sm:py-4 sm:text-base sm:leading-7 sm:whitespace-nowrap"><?php echo e($nutrition->value); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="2" class="px-4 py-6 text-sm text-slate-600 sm:px-8 sm:py-7 sm:text-base">Tidak ada data nutrisi.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\productsaf-laravel\resources\views/catalog/product-detail.blade.php ENDPATH**/ ?>