<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>DKMN - Xác nhận đặt vé</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fb; margin: 0; padding: 0; color: #1f2933; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.15); }
        .header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 32px; text-align: center; }
        .header h1 { margin: 0; font-size: 26px; letter-spacing: 1px; }
        .content { padding: 32px; }
        .section { margin-bottom: 24px; }
        .section h3 { margin: 0 0 12px 0; font-size: 16px; letter-spacing: 0.5px; text-transform: uppercase; color: #2563eb; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: #f9fafc; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .row span { display: block; }
        .total { font-size: 18px; font-weight: 700; color: #0f172a; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; background: #e0f2fe; color: #0369a1; margin-left: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 0; }
        th { font-size: 13px; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; }
        .seat-row { border-bottom: 1px solid #e2e8f0; }
        .footer { text-align: center; padding: 16px; font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div style="padding: 24px;">
        <div class="container">
            <div class="header">
                <h1>DKMN</h1>
                <p>Hệ thống đặt vé xe khách - tàu hỏa - máy bay</p>
            </div>
            <div class="content">
                <div class="section">
                    <h3>Xin chào, {{ $data['customer']['name'] }}</h3>
                    <p>Cảm ơn bạn đã đặt vé tại DKMN. Đơn hàng của bạn đã được xác nhận. Chi tiết chuyến đi nằm bên dưới.</p>
                </div>

                <div class="section">
                    <h3>Thông tin chuyến đi</h3>
                    <div class="card">
                        <div class="row">
                            <span><strong>Tuyến</strong></span>
                            <span>{{ $data['trip']['route'] }}</span>
                        </div>
                        <div class="row">
                            <span><strong>Nhà vận hành</strong></span>
                            <span>{{ $data['trip']['operator'] }} <span class="badge">{{ $data['trip']['vehicle'] }}</span></span>
                        </div>
                        <div class="row">
                            <span><strong>Khởi hành</strong></span>
                            <span>{{ $data['trip']['departure'] }}</span>
                        </div>
                        <div class="row">
                            <span><strong>Dự kiến đến</strong></span>
                            <span>{{ $data['trip']['arrival'] }}</span>
                        </div>
                        <div class="row">
                            <span><strong>Điểm đón</strong></span>
                            <span>{{ $data['trip']['pickup'] }}</span>
                        </div>
                        <div class="row">
                            <span><strong>Điểm trả</strong></span>
                            <span>{{ $data['trip']['dropoff'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3>Chi tiết vé</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Ghế</th>
                                <th>Giá vé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['seats'] as $seat)
                                <tr class="seat-row">
                                    <td>{{ $seat['label'] }}</td>
                                    <td>{{ number_format($seat['price']) }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <h3>Thanh toán</h3>
                    <div class="card">
                        <div class="row">
                            <span>Tạm tính</span>
                            <span>{{ number_format($data['totals']['subtotal']) }} đ</span>
                        </div>
                        <div class="row">
                            <span>Giảm giá</span>
                            <span>{{ number_format($data['totals']['discount']) }} đ</span>
                        </div>
                        <div class="row total">
                            <span>Tổng thanh toán</span>
                            <span>{{ number_format($data['totals']['total']) }} đ</span>
                        </div>
                        <div class="row" style="margin-top: 12px;">
                            <span>Phương thức</span>
                            <span>{{ $data['payment']['method'] }} · {{ $data['payment']['status'] }}</span>
                        </div>
                        <div class="row">
                            <span>Mã đơn</span>
                            <span>{{ $data['customer']['bookingCode'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3>Thông tin khách hàng</h3>
                    <div class="card">
                        <div class="row">
                            <span>Họ tên</span>
                            <span>{{ $data['customer']['name'] }}</span>
                        </div>
                        <div class="row">
                            <span>Số điện thoại</span>
                            <span>{{ $data['customer']['phone'] }}</span>
                        </div>
                        <div class="row">
                            <span>Email</span>
                            <span>{{ $data['customer']['email'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer">
                © {{ date('Y') }} DKMN · Đây là email tự động, vui lòng không trả lời.
            </div>
        </div>
    </div>
</body>
</html>
