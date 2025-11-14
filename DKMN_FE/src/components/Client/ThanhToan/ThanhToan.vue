    <template>
      <div class="container py-4">
        <div class="row g-4">
          <!-- Cột trái -->
          <div class="col-lg-8">
            <div class="card shadow-sm h-100">
              <div
                class="card-header bg-white d-flex align-items-center justify-content-between"
              >
                <h5 class="mb-0">Phương thức thanh toán</h5>
                <span class="badge bg-primary">Bảo mật</span>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label class="form-label fw-semibold"
                    >Chọn ví/cổng thanh toán</label
                  >
                  <div class="row g-3">
                    <!-- 💳 Thẻ thanh toán MoMo -->
                    <div class="col-md-4">
                      <div
                        class="form-check momo-card border rounded-4 p-3 h-100 text-center shadow-sm"
                        :class="{ active: selectedGateway === 'momo' }"
                        @click="selectedGateway = 'momo'"
                      >
                        <input
                          class="form-check-input d-none"
                          type="radio"
                          name="gateway"
                          id="gw-momo"
                          value="momo"
                          v-model="selectedGateway"
                        />
                        <label class="form-check-label d-block" for="gw-momo">
                          <img
                            src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png"
                            alt="MoMo Logo"
                            width="70"
                            class="mb-2"
                          />
                          <h6 class="fw-semibold text-dark mb-0">Ví MoMo</h6>
                          <small class="text-muted">Thanh toán nhanh chóng</small>
                        </label>
                      </div>
                    </div>

                    <!-- 💳 Thẻ thanh toán Ngân hàng khác -->
                    <div class="col-md-4">
                      <div
                        class="form-check other-bank-card border rounded-4 p-3 h-100 text-center shadow-sm"
                        :class="{ active: selectedGateway === 'other-bank' }"
                        @click="selectedGateway = 'other-bank'"
                      >
                        <input
                          class="form-check-input d-none"
                          type="radio"
                          name="gateway"
                          id="gw-other-bank"
                          value="other-bank"
                          v-model="selectedGateway"
                        />
                        <label class="form-check-label d-block" for="gw-other-bank">
                          <img
                            src="https://cdn-icons-png.flaticon.com/512/3208/3208667.png"
                            alt="Ngân hàng"
                            width="70"
                            height="70"
                            class="mb-2"
                          />
                          <h6 class="fw-semibold text-dark mb-0">Ngân hàng</h6>
                          <small class="text-muted"
                            >Thanh toán qua Visa / Master / JCB</small
                          >
                        </label>
                      </div>
                    </div>

                    <!-- ⏰ Thanh toán sau -->
                    <div class="col-md-4">
                      <div
                        class="form-check later-card border rounded-4 p-3 h-100 text-center shadow-sm"
                        :class="{ active: selectedGateway === 'later' }"
                        @click="selectedGateway = 'later'"
                      >
                        <input
                          class="form-check-input d-none"
                          type="radio"
                          name="gateway"
                          id="gw-later"
                          value="later"
                          v-model="selectedGateway"
                        />
                        <label class="form-check-label d-block" for="gw-later">
                          <div class="later-icon mb-2">
                            <img
                              src="https://cdn-icons-png.flaticon.com/512/2920/2920244.png"
                              alt="Thanh toán sau"
                              width="70"
                              height="70"
                            />
                          </div>
                          <h6 class="fw-semibold text-dark mb-0">Thanh toán sau</h6>
                          <small class="text-muted"
                            >Trả tiền sau khi hoàn tất chuyến đi</small
                          >
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- 🔘 Nút thanh toán chung -->
                <div class="d-flex gap-2">
                  <button
                    class="btn btn-primary"
                    @click="startPayment"
                    :disabled="!canPay"
                  >
                    <i class="bx bx-credit-card me-1"></i> Thanh toán ngay
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
                  <li
                    class="list-group-item"
                    v-if="selectedSeats && selectedSeats.length"
                  >
                    <div class="d-flex justify-content-between">
                      <span>Ghế đã chọn</span>
                      <span class="text-muted">{{ selectedSeats.join(", ") }}</span>
                    </div>
                  </li>
                  <li
                    class="list-group-item d-flex justify-content-between align-items-center"
                  >
                    <span>Tổng thanh toán</span>
                    <strong class="fs-5">{{ formatCurrency(total) }}</strong>
                  </li>
                </ul>

                <div>
                  <div
                    class="d-flex justify-content-between align-items-center mb-2"
                  >
                    <span class="fw-semibold">Trạng thái</span>
                    <span :class="statusBadgeClass">{{ status.label }}</span>
                  </div>
                  <div
                    class="progress"
                    role="progressbar"
                    :aria-valuenow="status.progress"
                    aria-valuemin="0"
                    aria-valuemax="100"
                  >
                    <div
                      class="progress-bar progress-bar-striped"
                      :class="{ 'progress-bar-animated': status.isProcessing }"
                      :style="{ width: status.progress + '%' }"
                    ></div>
                  </div>
                  <div v-if="status.detail" class="small text-muted mt-2">
                    {{ status.detail }}
                  </div>
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
                Thanh toán bằng
                {{ selectedGateway === "momo" ? "MoMo" : "Ngân hàng" }}
              </h5>
              <button class="btn-close" @click="closeQrModal"></button>
            </div>

            <div class="qr-modal-body">
              <div class="text-center mb-3">
                <div class="qr-code-container">
                  <div v-if="qrModal.isPreparing" class="qr-code-placeholder">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Đang tạo mã QR...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tạo mã QR...</p>
                  </div>

                  <div v-else class="qr-code">
                    <img
                      v-if="qrModal.qrData?.qrImageUrl"
                      :src="qrModal.qrData.qrImageUrl"
                      :alt="`QR ${qrGatewayLabel}`"
                      :width="selectedGateway === 'momo' ? 280 : 300"
                    />
                    <img
                      v-else-if="qrImageSrc"
                      :src="qrImageSrc"
                      :alt="`QR ${qrGatewayLabel}`"
                      :width="selectedGateway === 'momo' ? 280 : 300"
                    />
                    <p v-else class="text-muted small mb-0">
                      Đang chuẩn bị mã QR...
                    </p>
                  </div>
                </div>
              </div>

              <div class="payment-info">
                <div class="row g-2 mb-3">
                  <div class="col-6">
                    <div class="info-item">
                      <span class="label">Số tiền:</span>
                      <span class="value">{{ formatCurrency(qrModal.amount || total) }}</span>
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
                  <li>
                    <i class="bx bx-check-circle text-success me-2"></i>Mở ứng dụng
                    {{ selectedGateway === "momo" ? "MoMo" : "Ngân hàng" }}
                  </li>
                  <li>
                    <i class="bx bx-check-circle text-success me-2"></i>Chọn “Quét
                    mã QR”
                  </li>
                  <li>
                    <i class="bx bx-check-circle text-success me-2"></i>Quét mã QR
                    trên màn hình
                  </li>
                  <li>
                    <i class="bx bx-check-circle text-success me-2"></i>Xác nhận
                    thanh toán
                  </li>
                </ol>
              </div>

              <div class="qr-modal-footer">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="badge" :class="qrModal.statusClass">{{
                    qrModal.statusText
                  }}</span>
                  <div class="d-flex gap-2">
                    <button
                      class="btn btn-outline-secondary btn-sm"
                      @click="closeQrModal"
                    >
                      Hủy
                    </button>
                    <button
                      class="btn btn-primary btn-sm"
                      @click="checkPaymentStatus"
                      :disabled="qrModal.isChecking"
                    >
                      <span
                        v-if="qrModal.isChecking"
                        class="spinner-border spinner-border-sm me-1"
                      ></span>
                      Hoàn tất
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ✅ Modal Thanh toán sau -->
        <div
          v-if="laterModal.visible"
          class="qr-modal-backdrop"
          @click="closeLaterModal"
        >
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
              <button class="btn btn-primary btn-sm" @click="closeLaterModal">
                Đã hiểu
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <script>
import api from "../../../services/api";
const ORDER_ENDPOINT = "/dkmn/don-hang";
const PAYMENT_ENDPOINT = "/dkmn/thanh-toan";
const PAYMENT_QR_INIT_ENDPOINT = "/dkmn/payments/qr/init";
const PAYMENT_ONBOARD_ENDPOINT = "/dkmn/payments/onboard/confirm";
const TRIP_SEATS_ENDPOINT = (id) => `/dkmn/chuyen-di/${id}/ghe`;
const LEGACY_TICKET_KEY = "dkmn:lastTicket";
const TICKET_STORE_KEY = "dkmn:tickets";
const USER_INFO_KEY = "userInfo";
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
          seatIds: [],
          seatLookup: {},
          seatMapLoaded: false,
          bookingId: null,
          ticketId: null,
          isBooking: false,
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
            amount: 0,
            currency: "VND",
            providerRef: null,
            statusText: "Đang chờ thanh toán",
            statusClass: "bg-warning text-dark",
            isChecking: false,
            isPreparing: false,
          },
          laterModal: { visible: false },
        };
      },
      mounted() {
        this.loadFromQuery();
        this.syncSeatIdentifiers();
      },
      methods: {
        // ✅ Lấy thông tin từ query router
        saveTicketToLocal(paymentId) {
          const ticket = {
            paymentId: paymentId || ("PM_" + Math.random().toString(36).slice(2)),
            gateway: this.selectedGateway,
            total: this.total,
            createdAt: Date.now(),
            from: this.fromCity,
            to: this.toCity,
            date: this.travelDate,
            passengers: this.passengers,
            seats: this.selectedSeats,
            pickupStation: this.pickupStation,
            dropoffStation: this.dropoffStation,
            company: this.company,
            ticketId: this.ticketId,
            tripId:
              this.$route.query.tripId ||
              "TRIP_" + Math.random().toString(36).slice(2), // dY"1 thA?m dA?ng nA?y
          };
          this.persistTicketForOwner(ticket);
        },
        

        persistTicketForOwner(ticket) {
          if (!ticket) return;
          try {
            localStorage.setItem(LEGACY_TICKET_KEY, JSON.stringify(ticket));
          } catch (error) {
            console.warn("cannot update legacy ticket cache", error);
          }
          const ownerKey = this.getTicketOwnerKey();
          if (!ownerKey) return;
          const store = this.readTicketStore();
          store[ownerKey] = ticket;
          try {
            localStorage.setItem(TICKET_STORE_KEY, JSON.stringify(store));
          } catch (error) {
            console.warn("cannot persist ticket store", error);
          }
        },

        readTicketStore() {
          try {
            const raw = localStorage.getItem(TICKET_STORE_KEY);
            if (!raw) return {};
            const parsed = JSON.parse(raw);
            if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
              return parsed;
            }
            return {};
          } catch (error) {
            return {};
          }
        },

        getTicketOwnerKey() {
          const info = this.getCurrentUserInfo();
          if (!info || typeof info !== "object") return null;
          const id =
            info.id ||
            info.user_id ||
            info.ma ||
            info.ma_nguoi_dung ||
            info.code ||
            info.slug;
          if (id) return "id:" + id;
          const email = info.email || info.username || info.ten_dang_nhap;
          if (email) return "email:" + String(email).toLowerCase();
          const phone = info.so_dien_thoai || info.phone || info.dien_thoai;
          if (phone) return "phone:" + String(phone);
          return null;
        },

        getCurrentUserInfo() {
          if (this.userInfo && Object.keys(this.userInfo).length) {
            return this.userInfo;
          }
          try {
            const raw = localStorage.getItem(USER_INFO_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
          } catch (error) {
            return null;
          }
        },

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
        const rawSeatIds = q.seatIds || "";
        if (Array.isArray(rawSeatIds)) {
          this.seatIds = rawSeatIds.filter(Boolean);
        } else {
        this.seatIds = rawSeatIds
            ? String(rawSeatIds)
                .split(",")
                .map((value) => value.trim())
                .filter(Boolean)
            : [];
        }
        this.subtotal = Number(q.total) || 0;
        this.total = this.subtotal;
      },
      normalizeSeatLabelKey(value) {
        if (value === undefined || value === null) return "";
        return `LABEL:${String(value).trim().toUpperCase()}`;
      },
      buildSeatIdentifierList() {
        const identifiers = new Map();
        const pushId = (raw) => {
          const key = String(raw ?? "").trim();
          if (!key) return;
          identifiers.set(key, true);
        };

        this.seatIds.forEach((value) => {
          const trimmed = String(value ?? "").trim();
          if (!trimmed) return;
          const mapped = this.seatLookup[`ID:${trimmed}`] || trimmed;
          pushId(mapped);
        });

        this.selectedSeats.forEach((label) => {
          const lookupKey = this.normalizeSeatLabelKey(label);
          if (!lookupKey) return;
          const mapped = this.seatLookup[lookupKey];
          if (mapped) {
            pushId(mapped);
          } else {
            // Fallback to original label so BE can resolve via so_ghe
            pushId(lookupKey.replace(/^LABEL:/, ""));
          }
        });

        return Array.from(identifiers.keys());
      },
      async syncSeatIdentifiers(force = false) {
        const tripId = Number(this.$route.query.tripId) || null;
        if (!tripId) {
          this.seatIds = this.buildSeatIdentifierList();
          return;
        }

        if (!force && this.seatMapLoaded) {
          this.seatIds = this.buildSeatIdentifierList();
          return;
        }

        try {
          const { data } = await api.get(TRIP_SEATS_ENDPOINT(tripId));
          const seats = data?.data?.seats || [];
          const lookup = {};

          seats.forEach((seat) => {
            const rawId =
              seat.id ??
              seat.code ??
              seat.ma_ghe ??
              seat.so_ghe ??
              seat.label;
            if (rawId !== undefined && rawId !== null) {
              const idString = String(rawId).trim();
              lookup[`ID:${idString}`] = idString;
              lookup[this.normalizeSeatLabelKey(idString)] = idString;
            }

            [seat.label, seat.code, seat.ma_ghe, seat.so_ghe].forEach(
              (labelCandidate) => {
                if (labelCandidate === undefined || labelCandidate === null) {
                  return;
                }
                const key = this.normalizeSeatLabelKey(labelCandidate);
                lookup[key] = String(seat.id ?? labelCandidate).trim();
              }
            );
          });

          this.seatLookup = lookup;
          this.seatMapLoaded = true;
        } catch (error) {
          console.warn("Cannot sync seat identifiers", error);
        } finally {
          this.seatIds = this.buildSeatIdentifierList();
        }
      },
      bookingPayload() {
        const name =
          this.userInfo?.ho_ten ||
          this.userInfo?.name ||
          this.$route.query.customerName ||
          "Khách lẻ";
        const phone =
          this.userInfo?.so_dien_thoai ||
          this.$route.query.customerPhone ||
          "";
        const email =
          this.userInfo?.email ||
          this.$route.query.customerEmail ||
          "";
      return {
        tripId: Number(this.$route.query.tripId) || null,
        seatIds: this.seatIds,
        seatLabels: this.selectedSeats,
        total: this.total,
        passengers: Number(this.passengers || 1),
        from: this.fromCity,
        to: this.toCity,
          pickupStation: this.pickupStation,
          dropoffStation: this.dropoffStation,
          company: this.company,
          gateway: this.selectedGateway,
          customerName: name,
          customerPhone: phone,
          customerEmail: email,
        };
      },
      normalizeGatewayCode(code) {
        const normalized = (code || "").toString().toLowerCase();
        if (normalized === "later") {
          return "tra_sau";
        }
        if (normalized === "other-bank" || normalized === "bank") {
          return "ngan_hang";
        }
        return normalized || "khac";
      },
      async recordPayment(gateway, status, amountOverride) {
        if (!this.bookingId) return;
        const payloadAmount = Number(amountOverride ?? this.total) || 0;
        const payload = {
          donHangId: this.bookingId,
          congThanhToan: this.normalizeGatewayCode(gateway),
          soTien: payloadAmount,
          trangThai: status,
          maGiaoDich:
            this.qrModal.paymentId ||
            `${gateway}-${Date.now()}`.toUpperCase(),
          thoiDiemThanhToan: new Date().toISOString(),
        };

        try {
          await api.post(PAYMENT_ENDPOINT, payload);
        } catch (error) {
          console.warn("payment record failed", error);
        }
      },
      formatCurrency(v) {
        return new Intl.NumberFormat("vi-VN", {
          style: "currency",
          currency: "VND",
        }).format(v || 0);
      },
      setStatus(code, label, detail, progress, isProcessing) {
        this.status = { code, label, detail, progress, isProcessing };
      },

        // ✅ Nút thanh toán dùng chung cho tất cả
        async startPayment() {
          if (!this.canPay) return;
          await this.syncSeatIdentifiers();
          if (!this.seatIds.length) {
            const message = "Chưa chọn ghế, vui lòng quay lại để chọn ghế.";
            this.$toast?.error?.(message) || alert(message);
            return;
          }

          this.isBooking = true;
          this.setStatus(
            "creating",
            "Đang khởi tạo",
            "Chuẩn bị thanh toán...",
            20,
            true
          );
          await new Promise((r) => setTimeout(r, 500));

          try {
            const payload = this.bookingPayload();
            const { data } = await api.post(ORDER_ENDPOINT, payload);
            const bookingData = data?.data || data;
            this.bookingId = bookingData?.donHang?.id || bookingData?.id || null;
            this.ticketId =
              bookingData?.ticket?.id || bookingData?.ticketId || null;
          } catch (error) {
            const message =
              error.response?.data?.message || "Không thể lưu đơn hàng.";
            this.setStatus("failed", "Lỗi đặt vé", message, 0, false);
            this.$toast?.error?.(message);
            return;
          } finally {
            this.isBooking = false;
          }

          if (!this.ticketId) {
            const message = "Không thể xác định vé để thanh toán.";
            this.setStatus("failed", "Lỗi thanh toán", message, 0, false);
            this.$toast?.error?.(message);
            return;
          }

          if (this.selectedGateway === "later") {
            await this.handleOnboardPayment();
            return;
          }

          await this.prepareQrPayment();
        },

        // ✅ Đóng modal QR
        closeQrModal() {
          this.resetQrModal();
        },

        // ✅ Đóng modal Thanh toán sau
        closeLaterModal() {
          this.laterModal.visible = false;
        },

        async checkPaymentStatus() {
          if (this.qrModal.isChecking) return;
          this.qrModal.isChecking = true;
          try {
            await this.finalizeQrPayment();
          } catch (error) {
            const message =
              error?.response?.data?.message ||
              "Không thể xác nhận thanh toán. Vui lòng thử lại.";
            this.$toast?.error?.(message) || alert(message);
          } finally {
            this.qrModal.isChecking = false;
          }
        },

        async finalizeQrPayment() {
          if (!this.ticketId) {
            throw new Error("Không tìm thấy vé để xác nhận");
          }

          try {
            await api.post(PAYMENT_ONBOARD_ENDPOINT, {
              ticketId: this.ticketId,
              operatorId: "CLIENT_AUTO",
              note: `Auto confirm via ${this.selectedGateway || "qr"}`,
              method: "QR",
              provider: this.normalizeGatewayCode(this.selectedGateway),
            });
          } catch (error) {
            console.warn("Auto confirm payment failed", error);
          }

          await this.recordPayment(this.selectedGateway, "thanh_cong");

          this.setStatus(
            "success",
            "Thành công",
            "Đã xác nhận thanh toán",
            100,
            false
          );
          this.qrModal.statusText = "Thanh toán thành công";
          this.qrModal.statusClass = "bg-success";
          this.saveTicketToLocal(this.qrModal.paymentId);
          this.closeQrModal();
          this.$toast?.success?.("Thanh toán thành công!");
          this.$router.push("/client-ve-da-dat");
        },
        async prepareQrPayment() {
          this.resetQrModal();
          this.qrModal.visible = true;
          this.qrModal.isPreparing = true;
          this.setStatus(
            "pending",
            "Đang chờ thanh toán",
            "Đang tạo mã QR...",
            35,
            true
          );

          try {
            const channel = this.resolveQrChannel(this.selectedGateway);
            const { data } = await api.post(PAYMENT_QR_INIT_ENDPOINT, {
              ticketId: this.ticketId,
              channel,
              testMode: false,
            });
            const payload = data?.data || data;

            this.qrModal.paymentId = payload?.paymentId || this.qrModal.paymentId;
            this.qrModal.qrData = payload;
            this.qrModal.amount =
              Number(payload?.amount) || this.total || this.qrModal.amount;
            this.qrModal.currency = payload?.currency || "VND";
            this.qrModal.providerRef = payload?.providerRef || null;
            this.qrModal.statusText = "Đang chờ thanh toán";
            this.qrModal.statusClass = "bg-warning text-dark";
            this.setStatus(
              "pending",
              "Đang chờ thanh toán",
              "Quét mã QR để thanh toán",
              60,
              false
            );
          } catch (error) {
            const message =
              error.response?.data?.message ||
              "Không thể tạo mã QR thanh toán.";
            this.setStatus("failed", "Lỗi thanh toán", message, 0, false);
            this.$toast?.error?.(message);
            this.resetQrModal();
          } finally {
            this.qrModal.isPreparing = false;
          }
        },

        async handleOnboardPayment() {
          this.setStatus(
            "pending",
            "Thanh toán khi lên xe",
            "Đang ghi nhận thanh toán...",
            60,
            true
          );

          try {
            await api.post(PAYMENT_ONBOARD_ENDPOINT, {
              ticketId: this.ticketId,
              operatorId: "CLIENT_APP",
              note: "Thanh toán khi lên xe",
            });
            await this.recordPayment("tra_sau", "thanh_cong");
            this.setStatus(
              "success",
              "Thanh toán sau",
              "Khách đã thanh toán khi lên xe",
              100,
              false
            );
            this.laterModal.visible = true;
            this.saveTicketToLocal(this.qrModal.paymentId);
            this.$router.push("/client-ve-da-dat");
          } catch (error) {
            const message =
              error.response?.data?.message ||
              "Không thể xác nhận thanh toán khi lên xe.";
            this.setStatus("failed", "Lỗi thanh toán", message, 0, false);
            this.$toast?.error?.(message);
          }
        },

        resetQrModal() {
          this.qrModal.visible = false;
          this.qrModal.qrData = null;
          this.qrModal.paymentId = null;
          this.qrModal.amount = 0;
          this.qrModal.currency = "VND";
          this.qrModal.providerRef = null;
          this.qrModal.statusText = "Đang chờ thanh toán";
          this.qrModal.statusClass = "bg-warning text-dark";
          this.qrModal.isChecking = false;
          this.qrModal.isPreparing = false;
        },

        resolveQrChannel(gateway) {
          const normalized = (gateway || "").toString().toLowerCase();
          if (normalized === "momo") {
            return "momo";
          }
          return "vietqr";
        },
      },

      computed: {
        canPay() {
          return (
            !!this.selectedGateway &&
            this.total > 0 &&
            !this.status.isProcessing &&
            !this.isBooking
          );
        },
        statusBadgeClass() {
          const c = this.status.code;
          if (c === "success") return "badge bg-success";
          if (c === "failed") return "badge bg-danger";
          if (c === "pending" || c === "creating")
            return "badge bg-warning text-dark";
          return "badge bg-secondary";
        },
    qrGatewayLabel() {
      if (this.selectedGateway === "momo") {
        return "MoMo";
      }
      if (this.selectedGateway === "other-bank") {
        return "Ngân hàng";
      }
      return "QR";
    },
    qrImageSrc() {
      if (!this.selectedGateway) {
        return "";
      }

      const amount = Math.round(Number(this.total) || 0);
      if (amount <= 0) {
        return "";
      }

      const label = "Thanh toan ve xe DKMN";
      const encoded = encodeURIComponent(label);
      return `https://img.vietqr.io/image/VCB-1037240068-compact.png?amount=${amount}&addInfo=${encoded}`;
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

    /* 💳 CSS cho thẻ MoMo */
    .momo-card {
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      border: 2px solid #dee2e6;
      transform: scale(1);
    }

    .momo-card:hover {
      border-color: #0056d2;
      box-shadow: 0 0 10px rgba(0, 86, 210, 0.2);
      transform: scale(1.02);
    }

    /* 🔵 Khi được chọn (active) */
    .momo-card.active {
      border-color: #0056d2;
      background: rgba(0, 86, 210, 0.05);
      box-shadow: 0 0 12px rgba(0, 86, 210, 0.3);
      transform: scale(1.03);
    }

    .momo-card.active h6 {
      color: #0056d2;
    }

    .momo-card img {
      transition: 0.3s;
    }

    .momo-card.active img {
      transform: scale(1.1);
    }

    .other-bank-card {
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      border: 2px solid #dee2e6;
      transform: scale(1);
    }

    .other-bank-card:hover {
      border-color: #0056d2;
      box-shadow: 0 0 10px rgba(0, 86, 210, 0.15);
      transform: scale(1.02);
    }

    .other-bank-card.active {
      border-color: #0056d2;
      background: rgba(0, 86, 210, 0.05);
      box-shadow: 0 0 14px rgba(0, 86, 210, 0.25);
      transform: scale(1.03);
    }

    .other-bank-card.active h6 {
      color: #0056d2;
    }

    .other-bank-card img {
      transition: 0.3s;
      filter: grayscale(20%);
    }

    .other-bank-card:hover img,
    .other-bank-card.active img {
      transform: scale(1.1);
      filter: grayscale(0%);
    }

    /* 💳 Bank Card */
    .bank-card {
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      border: 2px solid #dee2e6;
      transform: scale(1);
    }

    .bank-card:hover {
      border-color: #0056d2;
      box-shadow: 0 0 10px rgba(0, 86, 210, 0.2);
      transform: scale(1.02);
    }

    /* 🔵 Khi được chọn (active) */
    .bank-card.active {
      border-color: #0056d2;
      background: rgba(0, 86, 210, 0.05);
      box-shadow: 0 0 12px rgba(0, 86, 210, 0.3);
      transform: scale(1.03);
    }

    .bank-card.active h6 {
      color: #0056d2;
    }

    .bank-card img {
      transition: 0.3s;
    }

    .bank-card.active img {
      transform: scale(1.1);
    }

    .later-card {
      cursor: pointer;
      transition: all 0.3s ease;
      background: #fff;
      border: 2px solid #dee2e6;
      transform: scale(1);
    }

    .later-card:hover {
      border-color: #0056d2;
      box-shadow: 0 0 10px rgba(0, 86, 210, 0.15);
      transform: scale(1.02);
    }

    .later-card.active {
      border-color: #0056d2;
      background: rgba(0, 86, 210, 0.05);
      box-shadow: 0 0 14px rgba(0, 86, 210, 0.25);
      transform: scale(1.03);
    }

    .later-card.active h6 {
      color: #0056d2;
    }

    .later-card img {
      transition: 0.3s;
      filter: grayscale(20%);
    }

    .later-card:hover img,
    .later-card.active img {
      transform: scale(1.1);
      filter: grayscale(0%);
    }
    </style>



