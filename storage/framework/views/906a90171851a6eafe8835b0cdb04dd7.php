<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');

body {
    font-family: 'Cairo', sans-serif;
    min-height: 100vh;
    background: linear-gradient(120deg, #667eea, #764ba2);
    background-size: 400% 400%;
    animation: gradientMove 10s ease infinite;
}

/* حركة الخلفية */
@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* الكارد الرئيسي */
.container {
    background: rgba(255, 255, 255, 0.95);
    padding: 35px;
    border-radius: 22px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
    animation: fadeUp 0.8s ease;
}

/* دخول ناعم */
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* العنوان */
h1 {
    font-weight: 800;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: glow 2s infinite alternate;
}

@keyframes glow {
    from { text-shadow: 0 0 10px rgba(118, 75, 162, 0.4); }
    to   { text-shadow: 0 0 20px rgba(118, 75, 162, 0.8); }
}

/* التبويبات */
.nav-tabs {
    border: none;
    gap: 8px;
}

.nav-tabs .nav-link {
    background: linear-gradient(135deg, #f1f3ff, #e0e7ff);
    border: none;
    border-radius: 16px;
    font-weight: 700;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.35);
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
}

/* محتوى التبويب */
.tab-pane {
    margin-top: 15px;
    padding: 25px;
    border-radius: 18px;
    background: #f8f9ff;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* الجدول */
.table {
    border-radius: 18px;
    overflow: hidden;
}

.table thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.table thead th {
    color: #fff;
    border: none;
    text-align: center;
}

.table tbody td {
    text-align: center;
    vertical-align: middle;
}

/* حركة الصفوف */
.table-hover tbody tr {
    transition: 0.25s ease;
}

.table-hover tbody tr:hover {
    background: linear-gradient(90deg, #edf1ff, #f8eaff);
    transform: scale(1.01);
}

/* الأزرار */
.btn {
    border-radius: 25px;
    padding: 6px 16px;
    font-weight: 700;
    transition: all 0.3s ease;
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
}

.btn-danger {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    border: none;
}

.btn:hover {
    transform: scale(1.08);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* البادجات */
.badge {
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
}

/* رسالة النجاح */
.alert-success {
    border-radius: 18px;
    font-weight: 700;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.6); }
    70% { box-shadow: 0 0 0 12px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}

/* موبايل */
@media (max-width: 768px) {
    h1 { font-size: 1.6rem; }
}
</style>



</head>
<body class="bg-light">
    <div class="container mt-5">
    <h1 class="text-center mb-4">لوحة تحكم الأدمن</h1>
            <!-- شريط البحث -->
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form action="<?php echo e(route('admin.dashboard')); ?>" method="GET" class="d-flex">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control me-2" 
                    placeholder="ابحث بالاسم أو رقم الموبايل..." 
                    value="<?php echo e(request('search')); ?>"
                    style="border-radius: 25px; padding: 12px 20px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 25px; padding: 12px 24px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- التبويبات -->
    <ul class="nav nav-tabs mb-4" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                المستخدمين المنتظرين
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                جميع المستخدمين
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pending-apartments-tab" data-bs-toggle="tab" data-bs-target="#pending-apartments" type="button" role="tab">
                الشقق المنتظرة
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-apartments-tab" data-bs-toggle="tab" data-bs-target="#all-apartments" type="button" role="tab">
                جميع الشقق
            </button>
        </li>
    </ul>

    <div class="tab-content" id="userTabContent">
        <!-- تبويب المستخدمين المنتظرين -->
        <div class="tab-pane fade show active" id="pending" role="tabpanel">
            <h3>المستخدمين المنتظرين الموافقة</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>الاسم</th>
                            <th>رقم الموبايل</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pendingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->phone_number); ?></td>
                                <td>منتظر</td>
                                <td>
                                    <form action="/admin/approve/<?php echo e($user->id); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-success btn-sm">موافقة</button>
                                    </form>
                                    <form action="/admin/delete/<?php echo e($user->id); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center">لا يوجد مستخدمين منتظرين</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- تبويب جميع المستخدمين -->
        <div class="tab-pane fade" id="all" role="tabpanel">
            <h3>جميع المستخدمين</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>الاسم</th>
                            <th> رقم الموبايل</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $allUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->phone_number); ?></td>
                                <td>
                                    <?php if($user->is_verified): ?>
                                        <span class="badge bg-success">مقبول</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">منتظر</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!$user->is_verified): ?>
                                        <form action="/admin/approve/<?php echo e($user->id); ?>" method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-success btn-sm">موافقة</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="/admin/delete/<?php echo e($user->id); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center">لا يوجد مستخدمين</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- تبويب الشقق المنتظرة -->
        <div class="tab-pane fade" id="pending-apartments" role="tabpanel">
            <h3>الشقق المنتظرة الموافقة</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID الشقة</th>
                            <th>العنوان</th>
                            <th>المالك</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pendingApartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apartment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($apartment->id); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.apartment.details', $apartment->id)); ?>" class="text-primary fw-bold">
                                        <?php echo e($apartment->title); ?>

                                    </a>
                                </td>                                <td><?php echo e($apartment->owner->name ?? 'غير معروف'); ?></td>
                                <td><span class="badge bg-warning">منتظر</span></td>
                                <td>
                                    <form action="<?php echo e(route('admin.approve.apartment', $apartment->id)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-success btn-sm">موافقة</button>
                                    </form>
                                    <form action="<?php echo e(route('admin.delete.apartment', $apartment->id)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف الشقة؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">لا توجد شقق منتظرة الموافقة</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- تبويب جميع الشقق -->
        <div class="tab-pane fade" id="all-apartments" role="tabpanel">
            <h3>جميع الشقق</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID الشقة</th>
                            <th>العنوان</th>
                            <th>المالك</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $allApartments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $apartment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($apartment->id); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.apartment.details', $apartment->id)); ?>" class="text-primary fw-bold">
                                        <?php echo e($apartment->title); ?>

                                    </a>
                                </td>
                                <td><?php echo e($apartment->owner->name ?? 'غير معروف'); ?></td>
                                <td>
                                    <?php if($apartment->is_approved): ?>
                                        <span class="badge bg-success">مقبولة</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">منتظر</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!$apartment->is_approved): ?>
                                        <form action="<?php echo e(route('admin.approve.apartment', $apartment->id)); ?>" method="POST" style="display:inline;">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="btn btn-success btn-sm">موافقة</button>
                                        </form>
                                    <?php endif; ?>
                                    <form action="<?php echo e(route('admin.delete.apartment', $apartment->id)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف الشقة؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">لا توجد شقق</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php /**PATH C:\Users\TAHA ALSOUS\Desktop\course laravel\HomieProject\resources\views/dashboard.blade.php ENDPATH**/ ?>