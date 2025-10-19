<template> 
  <div class="container py-4">
    <div class="row g-4">
      <!-- Cột trái -->
      <div class="col-lg-8">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Phương thức thanh toán</h5>
            <span class="badge bg-primary">Bảo mật</span>
          </div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Chọn ví/cổng thanh toán</label>
              <div class="row g-3">
                <!-- MoMo -->
                <div class="col-md-4">
                  <div class="form-check border rounded p-3 h-100">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="gateway"
                      id="gw-momo"
                      value="momo"
                      v-model="selectedGateway"
                    />
                    <label class="form-check-label d-block" for="gw-momo">
                      <span>MoMo</span>
                    </label>
                  </div>
                </div>

                <!-- Ngân hàng -->
                <div class="col-md-4">
                  <div class="form-check border rounded p-3 h-100">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="gateway"
                      id="gw-card"
                      value="card"
                      v-model="selectedGateway"
                    />
                    <label class="form-check-label d-block" for="gw-card">
                      <span>Thẻ ngân hàng</span>
                    </label>
                  </div>
                </div>

                <!-- Thanh toán sau -->
                <div class="col-md-4">
                  <div class="form-check border rounded p-3 h-100">
                    <input
                      class="form-check-input"
                      type="radio"
                      name="gateway"
                      id="gw-later"
                      value="later"
                      v-model="selectedGateway"
                    />
                    <label class="form-check-label d-block" for="gw-later">
                      <span>Thanh toán sau</span>
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- Nút hành động -->
            <div class="d-flex gap-2">
              <button
                v-if="selectedGateway !== 'later'"
                class="btn btn-primary"
                @click="startPayment"
                :disabled="!canPay"
              >
                <i class="bx bx-credit-card me-1"></i> Thanh toán ngay
              </button>

              <button
                v-else
                class="btn btn-success"
                @click="payLater"
              >
                <i class="bx bx-time-five me-1"></i> Thanh toán sau
              </button>

              <button
                class="btn btn-outline-secondary"
                @click="resetSelection"
                :disabled="status.isProcessing"
              >
                Làm mới
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ Cột phải (Tóm tắt đơn hàng lấy từ query) -->
      <div class="col-lg-4">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h6 class="mb-0">Tóm tắt đơn hàng</h6>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-flush mb-3">
              <li class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Hành trình</span>
                  <span class="text-muted">{{ fromCity }} → {{ toCity }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <span class="fw-semibold">Ngày đi</span>
                  <span class="text-muted">{{ travelDate }}</span>
                </div>
              </li>
              <li class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Nhà vận hành</span>
                  <span class="text-muted">{{ company }}</span>
                </div>
              </li>
              <li class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Điểm đón</span>
                  <span class="text-muted">{{ pickupStation }}</span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                  <span class="fw-semibold">Điểm trả</span>
                  <span class="text-muted">{{ dropoffStation }}</span>
                </div>
              </li>
              <li class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span class="fw-semibold">Số hành khách</span>
                  <span class="text-muted">{{ passengers }}</span>
                </div>
              </li>
              <li class="list-group-item" v-if="selectedSeats && selectedSeats.length">
                <div class="d-flex justify-content-between">
                  <span>Ghế đã chọn</span>
                  <span class="text-muted">{{ selectedSeats.join(', ') }}</span>
                </div>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>Tổng thanh toán</span>
                <strong class="fs-5">{{ formatCurrency(total) }}</strong>
              </li>
            </ul>

            <div>
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Trạng thái</span>
                <span :class="statusBadgeClass">{{ status.label }}</span>
              </div>
              <div class="progress" role="progressbar" :aria-valuenow="status.progress" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-striped" :class="{ 'progress-bar-animated': status.isProcessing }" :style="{ width: status.progress + '%' }"></div>
              </div>
              <div v-if="status.detail" class="small text-muted mt-2">{{ status.detail }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal QR -->
    <div v-if="qrModal.visible" class="qr-modal-backdrop" @click="closeQrModal">
      <div class="qr-modal" @click.stop>
        <div class="qr-modal-header">
          <h5 class="mb-0">
            Thanh toán bằng {{ selectedGateway === 'momo' ? 'MoMo' : 'Ngân hàng' }}
          </h5>
          <button class="btn-close" @click="closeQrModal"></button>
        </div>

        <div class="qr-modal-body">
          <div class="text-center mb-3">
            <div class="qr-code-container">
              <div v-if="!qrModal.qrData" class="qr-code-placeholder">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Đang tạo mã QR...</span>
                </div>
                <p class="mt-2 text-muted">Đang tạo mã QR...</p>
              </div>

              <div v-else class="qr-code">
                <img
                  v-if="selectedGateway === 'momo'"
                  src="./scr/assets/images/qrmomo.png"
                  alt="QR Momo 0366818392"
                  width="280"
                />
                <img
                  v-else
                  src="https://img.vietqr.io/image/VCB-1037240068-compact.png?amount=150000&addInfo=Thanh+toan+ve+xe+DKMN"
                  alt="VietQR HO TRONG KIM"
                  width="300"
                />
              </div>
            </div>
          </div>

          <div class="payment-info">
            <div class="row g-2 mb-3">
              <div class="col-6">
                <div class="info-item">
                  <span class="label">Số tiền:</span>
                  <span class="value">{{ formatCurrency(total) }}</span>
                </div>
              </div>
              <div class="col-6">
                <div class="info-item">
                  <span class="label">Mã giao dịch:</span>
                  <span class="value">{{ qrModal.paymentId }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="instructions">
            <h6>Hướng dẫn thanh toán:</h6>
            <ol class="list-unstyled">
              <li><i class="bx bx-check-circle text-success me-2"></i>Mở ứng dụng {{ selectedGateway === 'momo' ? 'MoMo' : 'Ngân hàng' }}</li>
              <li><i class="bx bx-check-circle text-success me-2"></i>Chọn “Quét mã QR”</li>
              <li><i class="bx bx-check-circle text-success me-2"></i>Quét mã QR trên màn hình</li>
              <li><i class="bx bx-check-circle text-success me-2"></i>Xác nhận thanh toán</li>
            </ol>
          </div>

          <div class="qr-modal-footer">
            <div class="d-flex justify-content-between align-items-center">
              <span class="badge" :class="qrModal.statusClass">{{ qrModal.statusText }}</span>
              <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" @click="closeQrModal">Hủy</button>
                <button
                  class="btn btn-primary btn-sm"
                  @click="checkPaymentStatus"
                  :disabled="qrModal.isChecking"
                >
                  <span v-if="qrModal.isChecking" class="spinner-border spinner-border-sm me-1"></span>
                  Kiểm tra
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Modal Thanh toán sau -->
    <div v-if="laterModal.visible" class="qr-modal-backdrop" @click="closeLaterModal">
      <div class="qr-modal" @click.stop>
        <div class="qr-modal-header">
          <h5 class="mb-0">Xác nhận thanh toán sau</h5>
          <button class="btn-close" @click="closeLaterModal"></button>
        </div>

        <div class="qr-modal-body text-center">
          <i class="bx bx-time-five text-success fs-1 mb-3"></i>
          <p class="mb-3">
            Bạn đã chọn hình thức <strong>Thanh toán sau</strong>.<br />
            Vui lòng hoàn tất thanh toán khi nhận vé hoặc tại quầy của nhà xe.
          </p>
          <p class="text-muted small">
            Nhân viên DKMN sẽ liên hệ để xác nhận đơn hàng của bạn.
          </p>
        </div>

        <div class="qr-modal-footer text-end">
          <button class="btn btn-primary btn-sm" @click="closeLaterModal">Đã hiểu</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ThanhToan",
  data() {
    return {
      subtotal: 0,
      fromCity: "",
      toCity: "",
      travelDate: "",
      company: "",
      pickupStation: "",
      dropoffStation: "",
      passengers: 0,
      selectedSeats: [],
      total: 0,
      selectedGateway: "momo",
      status: {
        code: "idle",
        label: "Chưa thanh toán",
        detail: "",
        progress: 0,
        isProcessing: false,
      },
      qrModal: {
        visible: false,
        qrData: null,
        paymentId: null,
        statusText: "Đang chờ thanh toán",
        statusClass: "bg-warning text-dark",
        isChecking: false,
      },
      laterModal: { // ✅ thêm modal cho thanh toán sau
        visible: false,
      },
    };
  },
  mounted() {
    this.loadFromQuery();
  },
  methods: {
    // ✅ Lấy thông tin từ query router
    loadFromQuery() {
      const q = this.$route.query;
      this.fromCity = q.from || "";
      this.toCity = q.to || "";
      this.travelDate = q.date || "";
      this.company = q.company || "";
      this.pickupStation = q.pickupStation || "";
      this.dropoffStation = q.dropoffStation || "";
      this.passengers = q.passengers || 1;
      this.selectedSeats = q.seats ? q.seats.split(",") : [];
      this.subtotal = Number(q.total) || 0;
      this.total = this.subtotal;
    },
    formatCurrency(v) {
      return new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(v || 0);
    },
    setStatus(code, label, detail, progress, isProcessing) {
      this.status = { code, label, detail, progress, isProcessing };
    },
    async startPayment() {
      if (!this.canPay) return;
      this.setStatus("creating", "Đang khởi tạo", "Chuẩn bị thanh toán...", 20, true);
      await new Promise((r) => setTimeout(r, 500));
      const paymentId = "PM_" + Math.random().toString(36).slice(2);
      await this.showQrModal(paymentId);
      this.setStatus("pending", "Đang chờ thanh toán", "Đang tạo mã QR...", 50, true);
    },
    async showQrModal(paymentId) {
      this.qrModal.visible = true;
      this.qrModal.paymentId = paymentId;
      this.qrModal.qrData = true;
    },
    async checkPaymentStatus() {
      this.qrModal.isChecking = true;
      await new Promise((r) => setTimeout(r, 1000));
      this.qrModal.statusText = "Thanh toán thành công";
      this.qrModal.statusClass = "bg-success";
      this.setStatus("success", "Thành công", "Đã xác nhận thanh toán", 100, false);
      this.qrModal.isChecking = false;
    },
    closeQrModal() {
      this.qrModal.visible = false;
      this.qrModal.qrData = null;
    },
    payLater() {
      this.setStatus("success", "Thanh toán sau", "Khách sẽ thanh toán khi nhận vé", 100, false);
      this.laterModal.visible = true; // ✅ mở modal thay vì alert
    },
    closeLaterModal() {
      this.laterModal.visible = false;
    },
    resetSelection() {
      this.selectedGateway = "momo";
      this.setStatus("idle", "Chưa thanh toán", "", 0, false);
    },
  },
  computed: {
    canPay() {
      return !!this.selectedGateway && this.total > 0 && !this.status.isProcessing;
    },
    statusBadgeClass() {
      const c = this.status.code;
      if (c === "success") return "badge bg-success";
      if (c === "failed") return "badge bg-danger";
      if (c === "pending" || c === "creating") return "badge bg-warning text-dark";
      return "badge bg-secondary";
    },
  },
};
</script>

<style>
/* giữ nguyên CSS gốc */
.qr-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}
.qr-modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  width: 90%;
  max-width: 500px;
  overflow-y: auto;
}
.qr-modal-header,
.qr-modal-body,
.qr-modal-footer {
  padding: 20px 24px;
}
.qr-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e9ecef;
}
.qr-code-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 2px dashed #dee2e6;
}
</style>
