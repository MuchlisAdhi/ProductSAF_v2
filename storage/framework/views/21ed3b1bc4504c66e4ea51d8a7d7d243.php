<?php $__env->startSection('content'); ?>
    <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-slate-900">Sign In</h1>
        <!-- <p class="mt-1 text-sm text-slate-600">Use seeded account: <span class="font-semibold">admin@sidoagung.com</span> / <span class="font-semibold">password123</span></p> -->

        <form method="POST" action="<?php echo e(route('login.submit')); ?>" class="mt-5 space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="next" value="<?php echo e($nextPath); ?>">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Email</label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo e(old('email')); ?>"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    required
                />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Password</label>
                <input
                    type="password"
                    name="password"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    required
                />
            </div>

            <button type="submit" class="w-full rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                Sign In
            </button>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\productsaf-laravel\resources\views/auth/login.blade.php ENDPATH**/ ?>