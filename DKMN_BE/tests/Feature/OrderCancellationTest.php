<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesTicketData;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTicketData;

    public function test_user_can_cancel_pending_order()
    {
        $ticket = $this->createTicketWithSeats([150000]);
        $user = $this->authenticateUser($ticket);

        $response = $this->postJson("/api/me/orders/{$ticket->donHang->id}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Hủy đơn hàng thành công.',
            ]);

        $this->assertDatabaseHas('don_hangs', [
            'id' => $ticket->donHang->id,
            'trang_thai' => 'da_huy',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'CANCELLED',
        ]);

        foreach ($ticket->donHang->chiTietDonHang as $detail) {
            $this->assertDatabaseHas('ghes', [
                'id' => $detail->ghe_id,
                'trang_thai' => 'trong',
            ]);
        }
    }

    public function test_user_cannot_cancel_completed_order()
    {
        $ticket = $this->createTicketWithSeats([150000]);
        $user = $this->authenticateUser($ticket);

        // Mark as completed
        $ticket->donHang->update(['trang_thai' => 'hoan_tat']);

        $response = $this->postJson("/api/me/orders/{$ticket->donHang->id}/cancel");

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Không thể hủy đơn hàng ở trạng thái hiện tại.',
            ]);
    }

    public function test_user_cannot_cancel_others_order()
    {
        $ticket = $this->createTicketWithSeats([150000]);
        $owner = $this->authenticateUser($ticket);

        // Create another user and act as them
        $otherUser = NguoiDung::create([
            'ho_ten' => 'Other User',
            'email' => 'other@test.com',
            'mat_khau' => bcrypt('password'),
            'so_dien_thoai' => '0987654321',
        ]);
        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/me/orders/{$ticket->donHang->id}/cancel");

        $response->assertStatus(403);
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
