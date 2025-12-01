<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTicketData;
use Tests\TestCase;

class RatingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTicketData;

    public function test_user_can_rate_completed_order()
    {
        $ticket = $this->createTicketWithSeats([150000]);
        $user = $this->authenticateUser($ticket);

        // Mark as completed
        $ticket->donHang->update(['trang_thai' => 'hoan_tat']);

        $response = $this->postJson('/api/ratings', [
            'tripId' => $ticket->trip_id,
            'rating' => 5,
            'comment' => 'Great trip!',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Đã ghi nhận đánh giá. Hệ thống sẽ duyệt trong thời gian sớm nhất.',
            ]);

        $this->assertDatabaseHas('danh_gias', [
            'nguoi_dung_id' => $user->id,
            'chuyen_di_id' => $ticket->trip_id,
            'diem' => 5,
            'nhan_xet' => 'Great trip!',
        ]);
    }

    public function test_user_cannot_rate_pending_order()
    {
        $ticket = $this->createTicketWithSeats([150000]);
        $user = $this->authenticateUser($ticket);

        // Status is pending by default

        $response = $this->postJson('/api/ratings', [
            'tripId' => $ticket->trip_id,
            'rating' => 5,
            'comment' => 'Premature rating',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Đơn hàng chưa hoàn tất, không thể đánh giá.',
            ]);
    }

    public function test_user_cannot_rate_twice()
    {
        $ticket = $this->createTicketWithSeats([150000]);
        $user = $this->authenticateUser($ticket);
        $ticket->donHang->update(['trang_thai' => 'hoan_tat']);

        // First rating
        $this->postJson('/api/ratings', [
            'tripId' => $ticket->trip_id,
            'rating' => 5,
        ]);

        // Second rating
        $response = $this->postJson('/api/ratings', [
            'tripId' => $ticket->trip_id,
            'rating' => 4,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => 'Bạn đã đánh giá chuyến đi này.',
            ]);
    }

    private function authenticateUser($ticket)
    {
        $user = NguoiDung::create([
            'ho_ten' => 'Test User',
            'email' => 'test' . Str::random(5) . '@test.com',
            'mat_khau' => bcrypt('password'),
            'so_dien_thoai' => '0123456789',
        ]);

        $ticket->donHang->update(['nguoi_dung_id' => $user->id]);

        Sanctum::actingAs($user);

        return $user;
    }
}
