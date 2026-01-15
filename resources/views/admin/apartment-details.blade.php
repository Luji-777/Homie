<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الشقة #{{ $apartment->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: fadeInUp 0.9s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .carousel-item img {
            height: 560px;
            object-fit: cover;
            border-bottom: 6px solid #764ba2;
        }

        .carousel-control-prev, .carousel-control-next {
            width: 60px;
            background: rgba(118, 75, 162, 0.6);
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }

        .cover-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .title-gradient {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.8rem;
            font-weight: 800;
        }

        .status-badge {
            padding: 12px 30px;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .info-grid {
            background: rgba(248, 249, 255, 0.7);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
        }

        .info-item {
            background: white;
            padding: 18px;
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        .description-box {
            background: linear-gradient(135deg, #f8f9ff, #e0e7ff);
            border-radius: 22px;
            padding: 30px;
            border-left: 6px solid #764ba2;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .btn-action {
            border-radius: 50px;
            padding: 14px 40px;
            font-weight: 800;
            font-size: 1.1rem;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .btn-action:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .btn-success { background: linear-gradient(135deg, #28a745, #20c997); border: none; }
        .btn-danger { background: linear-gradient(135deg, #ff416c, #ff4b2b); border: none; }
        .btn-secondary { background: linear-gradient(135deg, #6c757d, #495057); border: none; }

        .no-images {
            height: 400px;
            background: linear-gradient(135deg, #e0e7ff, #c3dafe);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: #667eea;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="glass-card">
        <!-- Carousel الصور -->
        @if($apartment->apartment_image->count() > 0)
            <div id="apartmentCarousel" class="carousel slide position-relative">
                <div class="carousel-inner">
                    @foreach($apartment->apartment_image as $index => $image)
                        <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                            <img src="{{ asset('storage/' . $image->image_path) }}" class="d-block w-100" alt="صورة الشقة">
                            @if($image->is_cover)
                                <div class="cover-badge text-white">
                                    <i class="bi bi-star-fill"></i> صورة الغلاف
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#apartmentCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#apartmentCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        @else
            <div class="no-images rounded">
                <div>
                    <i class="bi bi-image fs-1"></i><br>
                    لا توجد صور لهذه الشقة
                </div>
            </div>
        @endif

        <div class="card-body p-5">
            <div class="d-flex justify-content-between align-items-start mb-5">
                <div>
                    <h1 class="title-gradient mb-3">{{ $apartment->title }}</h1>
                    <p class="fs-5 text-muted mb-4">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <strong class="text-primary">{{ $apartment->area->city->name ?? 'غير محدد' }}</strong> -
                        <strong class="text-info">{{ $apartment->area->name ?? 'غير محدد' }}</strong> -
                        {{ $apartment->address }}
                    </p>
                </div>
                <div>
                    <span class="status-badge text-white {{ $apartment->is_approved ? 'bg-success' : 'bg-warning' }}">
                        {{ $apartment->is_approved ? 'مقبولة' : 'منتظرة الموافقة' }}
                    </span>
                </div>
            </div>

            <div class="info-grid mb-5">
                <div class="row g-4">
                    <div class="col-md-4"><div class="info-item text-center"><strong>المالك</strong><br><h4>{{ $apartment->owner->name ?? 'غير معروف' }}</h4></div></div>
                    <div class="col-md-4"><div class="info-item text-center"><strong>النوع</strong><br><h4>{{ ucfirst($apartment->type) }}</h4></div></div>
                    <div class="col-md-4"><div class="info-item text-center"><strong>المساحة</strong><br><h4>{{ $apartment->space }} م²</h4></div></div>
                    <div class="col-md-4"><div class="info-item text-center"><strong>السعر</strong><br><h4>{{ number_format($apartment->price) }} ل.س</h4></div></div>
                    <div class="col-md-4"><div class="info-item text-center"><strong>نوع الأجار</strong><br><h4>{{ ucfirst($apartment->rent_type) }}</h4></div></div>
                    <div class="col-md-4"><div class="info-item text-center"><strong>الطابق</strong><br><h4>{{ $apartment->floor }}</h4></div></div>
                    <div class="col-md-3"><div class="info-item text-center"><strong>الغرف</strong><br><h4>{{ $apartment->rooms }}</h4></div></div>
                    <div class="col-md-3"><div class="info-item text-center"><strong>غرف نوم</strong><br><h4>{{ $apartment->bedrooms }}</h4></div></div>
                    <div class="col-md-3"><div class="info-item text-center"><strong>الحمامات</strong><br><h4>{{ $apartment->bathrooms }}</h4></div></div>
                    <div class="col-md-3"><div class="info-item text-center"><strong>واي فاي / طاقة شمسية</strong><br><h4>{{ $apartment->wifi ? '✅' : '❌' }} / {{ $apartment->solar ? '✅' : '❌' }}</h4></div></div>
                </div>
            </div>

            <div class="description-box mb-5">
                <h3 class="fw-bold mb-3 text-primary">الوصف</h3>
                <p class="fs-5 lh-lg">{{ $apartment->discription ?? 'لا يوجد وصف متاح.' }}</p>
            </div>

            <div class="text-center">
                @if(!$apartment->is_approved)
                    <form action="{{ route('admin.approve.apartment', $apartment->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-action mx-3">
                            <i class="bi bi-check-circle-fill"></i> موافقة على الشقة
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.delete.apartment', $apartment->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-action mx-3" onclick="return confirm('متأكد 100% من حذف الشقة؟ هذا الإجراء لا رجعة فيه!')">
                        <i class="bi bi-trash-fill"></i> حذف الشقة
                    </button>
                </form>

                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-action">
                    <i class="bi bi-arrow-left-circle-fill"></i> العودة
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>