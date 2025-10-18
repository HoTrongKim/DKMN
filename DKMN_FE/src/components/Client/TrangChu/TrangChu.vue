/<template>
  <div class="container-fluid p-0">
    <!-- Hero Section with Search -->
    <section class="hero position-relative">
      <!-- Decorative background -->
      <span class="blob blob-1"></span>
      <span class="blob blob-2"></span>
      <span class="blob blob-3"></span>
      <span class="hero__grid"></span>
      <div class="hero__overlay"></div>

      <!-- Content -->
      <div class="hero__content">
        <div class="hero__glass shadow-lg">
          <div class="text-center mb-4">
            <!-- <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-semibold mb-3 px-3 py-2">
       <h5 class="text-black">DKMN</h5>
     </span> -->
            <h1 class="hero__title">
              Tìm và đặt vé <span class="text-primary">dễ dàng</span>
            </h1>
            <p class="hero__subtitle text-white">
              Hệ thống đặt vé xe khách, tàu hỏa, máy bay với giá tốt nhất
            </p>
          </div>

          <!-- 🔍 Search Form -->
          <div id="search" class="search-form">
            <div class="row g-3">
              <!-- Vehicle Type -->
              <div class="col-md-3">
                <label class="form-label text-white fw-semibold"
                  >Loại phương tiện</label
                >
                <select v-model="searchForm.vehicleType" class="form-select">
                  <option value="">Chọn phương tiện</option>
                  <option value="bus">Xe khách</option>
                  <option value="train">Tàu hỏa</option>
                  <option value="plane">Máy bay</option>
                </select>
              </div>

              <!-- From -->
              <div class="col-md-3">
                <label class="form-label text-white fw-semibold">Nơi đi</label>
                <select v-model="searchForm.from" class="form-select">
                  <option value="" disabled>Chọn nơi đi</option>
                  <option v-for="city in cities" :key="city" :value="city">
                    {{ city }}
                  </option>
                </select>
              </div>

              <!-- To -->
              <div class="col-md-3">
                <label class="form-label text-white fw-semibold">Nơi đến</label>
                <select v-model="searchForm.to" class="form-select">
                  <option value="" disabled>Chọn nơi đến</option>
                  <option v-for="city in cities" :key="city" :value="city">
                    {{ city }}
                  </option>
                </select>
              </div>

              <!-- Departure Date -->
              <div class="col-md-3">
                <label class="form-label text-white fw-semibold">Ngày đi</label>
                <input
                  v-model="searchForm.departureDate"
                  type="date"
                  class="form-control"
                />
              </div>
            </div>

            <div class="row g-3 mt-2">
              <!-- Pickup/Drop-off Stations (for buses) -->
              <div
                class="col-md-3"
                v-if="['bus','train','plane'].includes(searchForm.vehicleType) && searchForm.from"
              >
                <label class="form-label text-white fw-semibold">{{ pickupLabel }}</label>
                <select v-model="searchForm.pickupStation" class="form-select">
                  <option value="" disabled>Chọn điểm đón</option>
                  <option
                    v-for="station in availablePickupStations"
                    :key="'pickup-' + station"
                    :value="station"
                  >
                    {{ station }}
                  </option>
                </select>
              </div>

              <div
                class="col-md-3"
                v-if="['bus','train','plane'].includes(searchForm.vehicleType) && searchForm.to"
              >
                <label class="form-label text-white fw-semibold">{{ dropoffLabel }}</label>
                <select v-model="searchForm.dropoffStation" class="form-select">
                  <option value="" disabled>Chọn điểm trả</option>
                  <option
                    v-for="station in availableDropoffStations"
                    :key="'dropoff-' + station"
                    :value="station"
                  >
                    {{ station }}
                  </option>
                </select>
              </div>

              <!-- Return Date -->
              <div class="col-md-3">
                <label class="form-label text-white fw-semibold"
                  >Ngày về (tùy chọn)</label
                >
                <input
                  v-model="searchForm.returnDate"
                  type="date"
                  class="form-control"
                />
              </div>

              <!-- Passengers -->
              <div class="col-md-3">
                <label class="form-label text-white fw-semibold"
                  >Số hành khách</label
                >
                <select v-model="searchForm.passengers" class="form-select">
                  <option value="1">1 hành khách</option>
                  <option value="2">2 hành khách</option>
                  <option value="3">3 hành khách</option>
                  <option value="4">4 hành khách</option>
                  <option value="5">5 hành khách</option>
                  <option value="5+">Khác</option>
                </select>
                <div v-if="searchForm.passengers === '5+'" class="mt-2">
                  <input
                    v-model.number="customPassengers"
                    type="number"
                    min="6"
                    class="form-control"
                    placeholder="Nhập số hành khách "
                  />
                </div>
              </div>

              <!-- Company/Carrier selection -->
              <div
                class="col-md-3"
                v-if="['bus','train','plane'].includes(searchForm.vehicleType)"
              >
                <label class="form-label text-white fw-semibold">{{ companyLabel }}</label>
                <select v-model="searchForm.company" class="form-select">
                  <option value="">Tất cả</option>
                  <option
                    v-for="name in availableCompanies"
                    :key="'co-' + name"
                    :value="name"
                  >
                    {{ name }}
                  </option>
                </select>
              </div>

              <!-- Search Button -->
              <div class="col-md-6 d-flex align-items-end">
                <button
                  @click="searchTrips"
                  class="btn btn-primary btn-lg w-100 search-btn"
                  :disabled="!isSearchValid"
                >
                  <i class="bx bx-search me-2"></i>
                  Tìm chuyến
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Seat Selection Modal -->
    <div v-if="seatModal.visible" class="seat-modal-backdrop">
      <div class="seat-modal">
        <div
          class="p-3 border-bottom d-flex justify-content-between align-items-center"
        >
          <h6 class="mb-0">
            Chọn ghế - {{ seatModal.trip?.company }} ({{
              seatModal.trip?.vehicleType
            }})
          </h6>
          <button
            class="btn btn-sm btn-outline-secondary"
            @click="cancelSeatSelection"
          >
            Đóng
          </button>
        </div>
        <div class="p-3">
          <div class="mb-3 text-muted small">
            Có thể chọn tối đa {{ maxSelectableSeats }} ghế.
          </div>
          
          <!-- Seat Status Legend -->
          <div class="seat-legend mb-3">
            <div class="legend-item">
              <div class="legend-color seat-empty"></div>
              <span>Ghế trống</span>
            </div>
            <div class="legend-item">
              <div class="legend-color seat-selected"></div>
              <span>Đang chọn</span>
            </div>
            <div class="legend-item">
              <div class="legend-color seat-booked"></div>
              <span>Đã đặt</span>
            </div>
            <div class="legend-item">
              <div class="legend-color seat-unavailable"></div>
              <span>Không bán</span>
            </div>
          </div>
          
          <!-- Bus Seat Layout Container -->
          <div class="bus-seat-container">.driver-icon
            <!-- Two Column Layout -->
            <div class="two-column-layout">
              <!-- Left Column: Lower Deck -->
              <div class="deck-column lower-deck">
                <div class="deck-title">TẦNG DƯỚI</div>
                
                 <!-- Driver Row -->
<!-- Driver Row -->
<div class="driver-row">
  <div class="driver-icon">
    <img
      src="https://icons.veryicon.com/png/o/miscellaneous/icheyong/steering-wheel-14.png"
      alt="Steering Wheel Icon"
      width="36"
      height="36"
    />
  </div>
</div>

     
                <!-- Seat Matrix -->
                <div class="seat-matrix">
                  <div
                    v-for="row in seatModal.layout.slice(0, 4)"
                    :key="'lower-r' + row[0].id"
                    class="seat-row"
                  >
                    <!-- Left Column -->
                    <div class="seat-column">
                      <div
                        v-for="seat in row.slice(0, 2)"
                        :key="seat.id"
                        class="seat-btn"
                        :class="{
                          'seat-empty': seat.available && !seatModal.seatsSelected.includes(seat.id),
                          'seat-selected': seatModal.seatsSelected.includes(seat.id),
                          'seat-unavailable': !seat.available,
                          'seat-booked': seat.booked
                        }"
                        @click="toggleSeat(seat)"
                        :title="seat.label"
                      >
                        {{ seat.label }}
                      </div>
                    </div>
                    
                    <!-- Aisle -->
                    <div class="aisle"></div>
                    
                    <!-- Right Column -->
                    <div class="seat-column">
                      <div
                        v-for="seat in row.slice(2, 4)"
                        :key="seat.id"
                        class="seat-btn"
                        :class="{
                          'seat-empty': seat.available && !seatModal.seatsSelected.includes(seat.id),
                          'seat-selected': seatModal.seatsSelected.includes(seat.id),
                          'seat-unavailable': !seat.available,
                          'seat-booked': seat.booked
                        }"
                        @click="toggleSeat(seat)"
                        :title="seat.label"
                      >
                        {{ seat.label }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Vertical Divider -->
              <div class="vertical-divider"></div>

              <!-- Right Column: Upper Deck -->
              <div class="deck-column upper-deck">
                <div class="deck-title">TẦNG TRÊN</div>
                
                <!-- Empty Driver Row for alignment -->
                <div class="driver-row empty"></div>
                
                <!-- Seat Matrix -->
                <div class="seat-matrix">
                  <div
                    v-for="row in seatModal.layout.slice(4)"
                    :key="'upper-r' + row[0].id"
                    class="seat-row"
                  >
                    <!-- Left Column -->
                    <div class="seat-column">
                      <div
                        v-for="seat in row.slice(0, 2)"
                        :key="seat.id"
                        class="seat-btn"
                        :class="{
                          'seat-empty': seat.available && !seatModal.seatsSelected.includes(seat.id),
                          'seat-selected': seatModal.seatsSelected.includes(seat.id),
                          'seat-unavailable': !seat.available,
                          'seat-booked': seat.booked
                        }"
                        @click="toggleSeat(seat)"
                        :title="seat.label"
                      >
                        {{ seat.label }}
                      </div>
                    </div>
                    
                    <!-- Aisle -->
                    <div class="aisle"></div>
                    
                    <!-- Right Column -->
                    <div class="seat-column">
                      <div
                        v-for="seat in row.slice(2, 4)"
                        :key="seat.id"
                        class="seat-btn"
                        :class="{
                          'seat-empty': seat.available && !seatModal.seatsSelected.includes(seat.id),
                          'seat-selected': seatModal.seatsSelected.includes(seat.id),
                          'seat-unavailable': !seat.available,
                          'seat-booked': seat.booked
                        }"
                        @click="toggleSeat(seat)"
                        :title="seat.label"
                      >
                        {{ seat.label }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="me-2">Đã chọn:</span>
              <strong>{{ seatModal.seatsSelected.length }}</strong>
              <span class="ms-2 text-muted"
                >Tổng:
                {{
                  formatPrice(
                    (seatModal.trip?.price || 0) *
                      seatModal.seatsSelected.length
                  )
                }}</span
              >
            </div>
            <div class="d-flex gap-2">
              <button
                class="btn btn-outline-secondary"
                @click="cancelSeatSelection"
              >
                Hủy
              </button>
              <button
                class="btn btn-primary"
                :disabled="seatModal.seatsSelected.length === 0"
                @click="confirmSeats"
              >
                Tiếp tục thanh toán
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Results Section -->
    <section v-if="showResults" class="results-section py-5">
      <div class="container">
        <div class="row">
          <!-- Filters Sidebar -->
          <div class="col-lg-3">
            <div class="filters-card">
              <h5 class="mb-3">Bộ lọc</h5>

              <!-- Time Filter -->
              <div class="filter-group mb-4">
                <h6>Giờ khởi hành</h6>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="morning"
                    v-model="filters.time.morning"
                  />
                  <label class="form-check-label" for="morning"
                    >Sáng (6h-12h)</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="afternoon"
                    v-model="filters.time.afternoon"
                  />
                  <label class="form-check-label" for="afternoon"
                    >Chiều (12h-18h)</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="evening"
                    v-model="filters.time.evening"
                  />
                  <label class="form-check-label" for="evening"
                    >Tối (18h-24h)</label
                  >
                </div>
              </div>

              <!-- Price Filter -->
              <div class="filter-group mb-4">
                <h6>Giá vé</h6>
                <div class="price-range">
                  <input
                    type="range"
                    class="form-range"
                    min="0"
                    max="1000000"
                    v-model="filters.price.max"
                  />
                  <div class="d-flex justify-content-between">
                    <span>0đ</span>
                    <span>{{ formatPrice(filters.price.max) }}</span>
                  </div>
                </div>
              </div>

              <!-- Other Filters -->
              <div class="filter-group mb-4">
                <h6>Khác</h6>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="available"
                    v-model="filters.available"
                  />
                  <label class="form-check-label" for="available"
                    >Còn ghế</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="freeCancel"
                    v-model="filters.freeCancel"
                  />
                  <label class="form-check-label" for="freeCancel"
                    >Hủy miễn phí</label
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Results -->
          <div class="col-lg-9">
            <!-- Sort Options -->
            <div class="sort-options mb-4">
              <div class="d-flex justify-content-between align-items-center">
                <h5>Kết quả tìm kiếm ({{ filteredTrips.length }} chuyến)</h5>
                <select v-model="sortBy" class="form-select w-auto">
                  <option value="default">Mặc định</option>
                  <option value="time-early">Giờ đi sớm</option>
                  <option value="time-late">Giờ đi muộn</option>
                  <option value="price-low">Giá thấp</option>
                  <option value="price-high">Giá cao</option>
                  <option value="rating">Đánh giá cao</option>
                </select>
              </div>
            </div>

            <!-- Trip Cards -->
            <div class="trip-cards">
              <div
                v-for="trip in sortedTrips"
                :key="trip.id"
                class="trip-card mb-3"
              >
                <div class="row g-0">
                  <div class="col-md-8">
                    <div class="trip-info p-3">
                      <div
                        class="d-flex justify-content-between align-items-start mb-2"
                      >
                        <div>
                          <h6 class="mb-1">{{ trip.company }}</h6>
                          <small class="text-muted">{{
                            trip.vehicleType
                          }}</small>
                        </div>
                        <div class="rating">
                          <i class="bx bxs-star text-warning"></i>
                          <span>{{ trip.rating }}</span>
                        </div>
                      </div>

                      <div class="trip-details">
                        <div class="d-flex align-items-center">
                          <div class="time-info">
                            <div class="departure-time">
                              {{ trip.departureTime }}
                            </div>
                            <div class="station">{{ trip.fromStation }}</div>
                          </div>

                          <div class="trip-route">
                            <div class="duration">{{ trip.duration }}</div>
                            <div class="route-line"></div>
                          </div>

                          <div class="time-info text-end">
                            <div class="arrival-time">
                              {{ trip.arrivalTime }}
                            </div>
                            <div class="station">{{ trip.toStation }}</div>
                          </div>
                        </div>
                      </div>

                      <div class="trip-features mt-2">
                        <span
                          v-if="trip.freeCancel"
                          class="badge bg-success me-2"
                          >Hủy miễn phí</span
                        >
                        <span
                          v-if="trip.availableSeats > 0"
                          class="badge bg-info me-2"
                          >{{ trip.availableSeats }} ghế trống</span
                        >
                        <span class="badge bg-secondary">{{
                          trip.seatType
                        }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="trip-price p-3 text-center">
                      <div class="price">{{ formatPrice(trip.price) }}</div>
                      <small class="text-muted">/ người</small>
                      <button
                        @click="selectTrip(trip)"
                        class="btn btn-primary w-100 mt-2"
                        :disabled="trip.availableSeats === 0"
                      >
                        {{ trip.availableSeats > 0 ? "Chọn chuyến" : "Hết vé" }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section py-5">
      <div class="container">
        <div class="row text-center mb-5">
          <div class="col-12">
            <h2 class="section-title">Tại sao chọn DKMN?</h2>
            <p class="section-subtitle">
              Những lý do khiến DKMN trở thành lựa chọn hàng đầu
            </p>
          </div>
        </div>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bx bx-search-alt"></i>
              </div>
              <h4>Tìm kiếm thông minh</h4>
              <p>Tìm chuyến đi phù hợp với nhiều bộ lọc và tùy chọn sắp xếp</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bx bx-shield-check"></i>
              </div>
              <h4>Đặt vé an toàn</h4>
              <p>Hệ thống thanh toán bảo mật và chính sách hủy/đổi linh hoạt</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="bx bx-support"></i>
              </div>
              <h4>Hỗ trợ 24/7</h4>
              <p>Đội ngũ chăm sóc khách hàng luôn sẵn sàng hỗ trợ bạn</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: "TrangChu",
  data() {
    return {
      cities: [
        "Hà Nội",
        "Hải Phòng",
        "Quảng Ninh",
        "Bắc Ninh",
        "Hưng Yên",
        "Hà Nam",
        "Nam Định",
        "Ninh Bình",
        "Thanh Hóa",
        "Nghệ An",
        "Hà Tĩnh",
        "Quảng Bình",
        "Quảng Trị",
        "Thừa Thiên Huế",
        "Đà Nẵng",
        "Quảng Nam",
        "Quảng Ngãi",
        "Bình Định",
        "Phú Yên",
        "Khánh Hòa",
        "Ninh Thuận",
        "Bình Thuận",
        "Kon Tum",
        "Gia Lai",
        "Đắk Lắk",
        "Đắk Nông",
        "Lâm Đồng",
        "Bình Phước",
        "Bình Dương",
        "Đồng Nai",
        "Bà Rịa - Vũng Tàu",
        "TP. Hồ Chí Minh",
        "Long An",
        "Cần Thơ",
      ],
      searchForm: {
        vehicleType: "",
        from: "",
        to: "",
        departureDate: "",
        returnDate: "",
        passengers: "1",
        pickupStation: "",
        dropoffStation: "",
        company: "",
      },
      stationsByCity: {
        "Hà Nội": [
          "Bến xe Giáp Bát",
          "Bến xe Mỹ Đình",
          "Bến xe Nước Ngầm",
          "Bến xe Gia Lâm",
        ],
        "Hải Phòng": ["Bến xe Niệm Nghĩa", "Bến xe Cầu Rào", "Bến xe Thượng Lý"],
        "Quảng Ninh": ["Bến xe Bãi Cháy", "Bến xe Cẩm Phả", "Bến xe Móng Cái"],
        "Bắc Ninh": ["Bến xe Bắc Ninh", "Bến xe Gia Bình"],
        "Hưng Yên": ["Bến xe Hưng Yên", "Bến xe Mỹ Hào"],
        "Hà Nam": ["Bến xe Phủ Lý"],
        "Nam Định": ["Bến xe Nam Định", "Bến xe Mỹ Lộc"],
        "Ninh Bình": ["Bến xe Ninh Bình", "Bến xe Kim Sơn"],
        "Thanh Hóa": ["Bến xe Thanh Hóa", "Bến xe phía Bắc Thanh Hóa"],
        "Nghệ An": ["Bến xe Vinh", "Bến xe phía Bắc Vinh"],
        "Hà Tĩnh": ["Bến xe Hà Tĩnh", "Bến xe Cẩm Xuyên"],
        "Quảng Bình": ["Bến xe Đồng Hới", "Bến xe Ba Đồn"],
        "Quảng Trị": ["Bến xe Đông Hà", "Bến xe Quảng Trị"],
        "Thừa Thiên Huế": ["Bến xe Huế", "Bến xe phía Nam Huế"],
        "Đà Nẵng": ["Bến xe Trung tâm", "Bến xe phía Bắc"],
        "Quảng Nam": ["Bến xe Tam Kỳ", "Bến xe Hội An"],
        "Quảng Ngãi": ["Bến xe Quảng Ngãi", "Bến xe Đức Phổ"],
        "Bình Định": ["Bến xe Quy Nhơn", "Bến xe Phù Cát"],
        "Phú Yên": ["Bến xe Tuy Hòa", "Bến xe Sông Cầu"],
        "Khánh Hòa": ["Bến xe Nha Trang", "Bến xe Ninh Hòa", "Bến xe Cam Ranh"],
        "Ninh Thuận": ["Bến xe Phan Rang", "Bến xe Ninh Sơn"],
        "Bình Thuận": ["Bến xe Phan Thiết", "Bến xe Bắc Bình"],
        "Kon Tum": ["Bến xe Kon Tum", "Bến xe Đắk Tô"],
        "Gia Lai": ["Bến xe Pleiku", "Bến xe An Khê"],
        "Đắk Lắk": ["Bến xe Buôn Ma Thuột", "Bến xe Ea Kar"],
        "Đắk Nông": ["Bến xe Gia Nghĩa", "Bến xe Đắk Mil"],
        "Lâm Đồng": ["Bến xe Đà Lạt", "Bến xe Bảo Lộc"],
        "Bình Phước": ["Bến xe Đồng Xoài", "Bến xe Chơn Thành"],
        "Bình Dương": ["Bến xe Bình Dương", "Bến xe Dĩ An"],
        "Đồng Nai": ["Bến xe Biên Hòa", "Bến xe Long Khánh"],
        "Bà Rịa - Vũng Tàu": ["Bến xe Vũng Tàu", "Bến xe Bà Rịa"],
        "TP. Hồ Chí Minh": [
          "Bến xe Miền Đông",
          "Bến xe Miền Tây",
          "Bến xe An Sương",
        ],
        "Long An": ["Bến xe Tân An", "Bến xe Bến Lức"],
        "Cần Thơ": ["Bến xe Cần Thơ", "Bến xe Ô Môn"],
        __default: ["Trung tâm"],
      },
      trainStationsByCity: {
        "Hà Nội": ["Ga Hà Nội", "Ga Long Biên"],
        "Hải Phòng": ["Ga Hải Phòng"],
        "Quảng Ninh": ["Ga Hạ Long", "Ga Cái Lân"],
        "Bắc Ninh": ["Ga Bắc Ninh"],
        "Hưng Yên": ["Ga Phố Nối"],
        "Hà Nam": ["Ga Phủ Lý"],
        "Nam Định": ["Ga Nam Định"],
        "Ninh Bình": ["Ga Ninh Bình"],
        "Thanh Hóa": ["Ga Thanh Hóa"],
        "Nghệ An": ["Ga Vinh"],
        "Hà Tĩnh": ["Ga Yên Trung"],
        "Quảng Bình": ["Ga Đồng Hới"],
        "Quảng Trị": ["Ga Đông Hà"],
        "Thừa Thiên Huế": ["Ga Huế"],
        "Đà Nẵng": ["Ga Đà Nẵng"],
        "Quảng Nam": ["Ga Tam Kỳ"],
        "Quảng Ngãi": ["Ga Quảng Ngãi"],
        "Bình Định": ["Ga Diêu Trì", "Ga Quy Nhơn"],
        "Phú Yên": ["Ga Tuy Hòa"],
        "Khánh Hòa": ["Ga Nha Trang"],
        "Ninh Thuận": ["Ga Tháp Chàm"],
        "Bình Thuận": ["Ga Phan Thiết", "Ga Mương Mán"],
        "Kon Tum": ["Ga Ngọc Hồi"],
        "Gia Lai": ["Ga Mang Yang"],
        "Đắk Lắk": ["Ga Buôn Hồ"],
        "Đắk Nông": ["Ga Đức Lập"],
        "Lâm Đồng": ["Ga Trại Mát"],
        "Bình Phước": ["Ga Đồng Xoài"],
        "Bình Dương": ["Ga Dĩ An", "Ga Sóng Thần"],
        "Đồng Nai": ["Ga Biên Hòa", "Ga Long Khánh"],
        "Bà Rịa - Vũng Tàu": ["Ga Phú Mỹ"],
        "TP. Hồ Chí Minh": ["Ga Sài Gòn"],
        "Long An": ["Ga Tân An"],
        "Cần Thơ": ["Ga Cái Răng"],
        __default: ["Ga trung tâm"],
      },
      airportsByCity: {
        "Hà Nội": ["Sân bay Nội Bài (HAN)"],
        "Hải Phòng": ["Sân bay Cát Bi (HPH)"],
        "Quảng Ninh": ["Sân bay Vân Đồn (VDO)"],
        "Bắc Ninh": ["Sân bay Nội Bài (HAN)"],
        "Hưng Yên": ["Sân bay Nội Bài (HAN)"],
        "Hà Nam": ["Sân bay Nội Bài (HAN)"],
        "Nam Định": ["Sân bay Nội Bài (HAN)"],
        "Ninh Bình": ["Sân bay Nội Bài (HAN)"],
        "Thanh Hóa": ["Sân bay Thọ Xuân (THD)"],
        "Nghệ An": ["Sân bay Vinh (VII)"],
        "Hà Tĩnh": ["Sân bay Vinh (VII)"],
        "Quảng Bình": ["Sân bay Đồng Hới (VDH)"],
        "Quảng Trị": ["Sân bay Phú Bài (HUI)"],
        "Thừa Thiên Huế": ["Sân bay Phú Bài (HUI)"],
        "Đà Nẵng": ["Sân bay Đà Nẵng (DAD)"],
        "Quảng Nam": ["Sân bay Chu Lai (VCL)"],
        "Quảng Ngãi": ["Sân bay Chu Lai (VCL)"],
        "Bình Định": ["Sân bay Phù Cát (UIH)"],
        "Phú Yên": ["Sân bay Tuy Hòa (TBB)"],
        "Khánh Hòa": ["Sân bay Cam Ranh (CXR)"],
        "Ninh Thuận": ["Sân bay Cam Ranh (CXR)"],
        "Bình Thuận": ["Sân bay Long Thành (LTN)", "Sân bay Cam Ranh (CXR)"],
        "Kon Tum": ["Sân bay Pleiku (PXU)"],
        "Gia Lai": ["Sân bay Pleiku (PXU)"],
        "Đắk Lắk": ["Sân bay Buôn Ma Thuột (BMV)"],
        "Đắk Nông": ["Sân bay Buôn Ma Thuột (BMV)"],
        "Lâm Đồng": ["Sân bay Liên Khương (DLI)"],
        "Bình Phước": ["Sân bay Tân Sơn Nhất (SGN)"],
        "Bình Dương": ["Sân bay Tân Sơn Nhất (SGN)"],
        "Đồng Nai": ["Sân bay Tân Sơn Nhất (SGN)", "Sân bay Long Thành (LTN)"],
        "Bà Rịa - Vũng Tàu": ["Sân bay Tân Sơn Nhất (SGN)"],
        "TP. Hồ Chí Minh": ["Sân bay Tân Sơn Nhất (SGN)"],
        "Long An": ["Sân bay Tân Sơn Nhất (SGN)"],
        "Cần Thơ": ["Sân bay Cần Thơ (VCA)"],
        __default: ["Sân bay gần nhất"],
      },
      busCompanies: [
        "Xe khách Phương Trang",
        "Xe khách Thành Bưởi",
        "Xe khách Kumho",
        "Xe khách Mai Linh",
      ],
      trainCompanies: [
        "Tàu hỏa SE1",
        "Tàu hỏa SE2",
        "Đường sắt Việt Nam",
      ],
      airlines: [
        "Vietnam Airlines",
        "Vietjet Air",
        "Bamboo Airways",
        "Vietravel Airlines",
      ],
      customPassengers: null,
      showResults: false,
      sortBy: "default",
      filters: {
        time: {
          morning: false,
          afternoon: false,
          evening: false,
        },
        price: {
          max: 1000000,
        },
        available: false,
        freeCancel: false,
      },
      userInfo: {},
      trips: [
        {
          id: 1,
          company: "Xe khách Phương Trang",
          vehicleType: "Xe khách",
          departureTime: "08:00",
          arrivalTime: "12:30",
          fromStation: "Bến xe Miền Tây",
          toStation: "Bến xe Đà Lạt",
          duration: "4h30",
          price: 250000,
          rating: 4.5,
          availableSeats: 15,
          seatType: "Ghế ngồi",
          freeCancel: true,
        },
        {
          id: 2,
          company: "Vietnam Airlines",
          vehicleType: "Máy bay",
          departureTime: "14:20",
          arrivalTime: "15:50",
          fromStation: "Sân bay Tân Sơn Nhất",
          toStation: "Sân bay Đà Lạt",
          duration: "1h30",
          price: 1200000,
          rating: 4.8,
          availableSeats: 8,
          seatType: "Hạng phổ thông",
          freeCancel: false,
        },
        {
          id: 3,
          company: "Tàu hỏa SE1",
          vehicleType: "Tàu hỏa",
          departureTime: "22:00",
          arrivalTime: "06:00+1",
          fromStation: "Ga Sài Gòn",
          toStation: "Ga Đà Lạt",
          duration: "8h00",
          price: 450000,
          rating: 4.2,
          availableSeats: 0,
          seatType: "Giường nằm",
          freeCancel: true,
        },
      ],
      seatModal: {
        visible: false,
        trip: null,
        seatsSelected: [],
        layout: [],
      },
    };
  },
  computed: {
    isLoggedIn() {
      return (
        localStorage.getItem("token") && Object.keys(this.userInfo).length > 0
      );
    },
    passengerCount() {
      if (this.searchForm.passengers === "5+") {
        const count = Number(this.customPassengers || 0);
        return isNaN(count) ? 0 : count;
      }
      return Number(this.searchForm.passengers || 0);
    },
    maxSelectableSeats() {
      const requested = this.passengerCount;
      const available = this.seatModal?.trip?.availableSeats ?? requested;
      return Math.max(0, Math.min(requested, available));
    },
    isSearchValid() {
      const baseValid =
        this.searchForm.vehicleType &&
        this.searchForm.from &&
        this.searchForm.to &&
        this.searchForm.departureDate;
      if (!baseValid) return false;
      if (this.searchForm.passengers === "5+") {
        return this.passengerCount >= 6;
      }
      return this.passengerCount >= 1;
    },
    filteredTrips() {
      let filtered = [...this.trips];

      // Filter by time
      if (
        this.filters.time.morning ||
        this.filters.time.afternoon ||
        this.filters.time.evening
      ) {
        filtered = filtered.filter((trip) => {
          const hour = parseInt(trip.departureTime.split(":")[0]);
          return (
            (this.filters.time.morning && hour >= 6 && hour < 12) ||
            (this.filters.time.afternoon && hour >= 12 && hour < 18) ||
            (this.filters.time.evening && hour >= 18 && hour < 24)
          );
        });
      }

      // Filter by price
      filtered = filtered.filter(
        (trip) => trip.price <= this.filters.price.max
      );

      // Filter by available seats
      if (this.filters.available) {
        filtered = filtered.filter((trip) => trip.availableSeats > 0);
      }

      // Filter by free cancel
      if (this.filters.freeCancel) {
        filtered = filtered.filter((trip) => trip.freeCancel);
      }

      // Filter by selected company/carrier
      if (this.searchForm.company) {
        filtered = filtered.filter((trip) => trip.company === this.searchForm.company);
      }

      return filtered;
    },
    sortedTrips() {
      const trips = [...this.filteredTrips];

      switch (this.sortBy) {
        case "time-early":
          return trips.sort((a, b) =>
            a.departureTime.localeCompare(b.departureTime)
          );
        case "time-late":
          return trips.sort((a, b) =>
            b.departureTime.localeCompare(a.departureTime)
          );
        case "price-low":
          return trips.sort((a, b) => a.price - b.price);
        case "price-high":
          return trips.sort((a, b) => b.price - a.price);
        case "rating":
          return trips.sort((a, b) => b.rating - a.rating);
        default:
          return trips;
      }
    },
    pickupLabel() {
      switch (this.searchForm.vehicleType) {
        case 'train':
          return 'Ga đón';
        case 'plane':
          return 'Sân bay đi';
        default:
          return 'Bến xe đón';
      }
    },
    dropoffLabel() {
      switch (this.searchForm.vehicleType) {
        case 'train':
          return 'Ga trả';
        case 'plane':
          return 'Sân bay đến';
        default:
          return 'Bến xe trả';
      }
    },
    availablePickupStations() {
      const city = this.searchForm.from;
      if (this.searchForm.vehicleType === 'train') {
        return this.trainStationsByCity[city] || this.trainStationsByCity.__default;
      }
      if (this.searchForm.vehicleType === 'plane') {
        return this.airportsByCity[city] || this.airportsByCity.__default;
      }
      return this.stationsByCity[city] || this.stationsByCity.__default;
    },
    availableDropoffStations() {
      const city = this.searchForm.to;
      if (this.searchForm.vehicleType === 'train') {
        return this.trainStationsByCity[city] || this.trainStationsByCity.__default;
      }
      if (this.searchForm.vehicleType === 'plane') {
        return this.airportsByCity[city] || this.airportsByCity.__default;
      }
      return this.stationsByCity[city] || this.stationsByCity.__default;
    },
    companyLabel() {
      switch (this.searchForm.vehicleType) {
        case 'train':
          return 'Chọn tàu/nhà tàu';
        case 'plane':
          return 'Chọn hãng bay';
        default:
          return 'Chọn nhà xe';
      }
    },
    availableCompanies() {
      const type = this.searchForm.vehicleType;
      if (type === 'train') return this.trainCompanies;
      if (type === 'plane') return this.airlines;
      return this.busCompanies;
    },
  },
  methods: {
    searchTrips() {
      if (!this.isSearchValid) return;
      this.trips = this.generateMockTrips();
      this.showResults = true;
      this.$nextTick(() => {
        const el = document.querySelector('.results-section');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
      });
    },
    selectTrip(trip) {
      // Open seat selection modal for this trip
      this.openSeatModal(trip);
    },
    formatPrice(price) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(price);
    },
    vehicleTypeLabel(typeKey) {
      switch (typeKey) {
        case 'bus':
          return 'Xe khách';
        case 'train':
          return 'Tàu hỏa';
        case 'plane':
          return 'Máy bay';
        default:
          return typeKey;
      }
    },
    getStationsForCity(city, typeKey) {
      if (typeKey === 'train') {
        return this.trainStationsByCity[city] || this.trainStationsByCity.__default;
      }
      if (typeKey === 'plane') {
        return this.airportsByCity[city] || this.airportsByCity.__default;
      }
      return this.stationsByCity[city] || this.stationsByCity.__default;
    },
    getCompaniesByType(typeKey) {
      if (typeKey === 'train') return this.trainCompanies;
      if (typeKey === 'plane') return this.airlines;
      return this.busCompanies;
    },
    randomBetween(min, max) {
      return Math.random() * (max - min) + min;
    },
    randomInt(min, max) {
      return Math.floor(this.randomBetween(min, max + 1));
    },
    addMinutesToTime(time, minutes) {
      const [h, m] = time.split(':').map((n) => parseInt(n, 10));
      const date = new Date(2000, 0, 1, h, m, 0, 0);
      date.setMinutes(date.getMinutes() + minutes);
      const hh = String(date.getHours()).padStart(2, '0');
      const mm = String(date.getMinutes()).padStart(2, '0');
      return `${hh}:${mm}`;
    },
    generateDepartureTime(typeKey) {
      // Bias time ranges a bit differently per type
      let startHour = 6;
      let endHour = 22;
      if (typeKey === 'plane') {
        startHour = 5; endHour = 21;
      } else if (typeKey === 'train') {
        startHour = 4; endHour = 23;
      }
      const hour = this.randomInt(startHour, endHour);
      const minute = this.randomInt(0, 1) ? 0 : 30;
      return `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
    },
    generateDurationMinutes(typeKey) {
      if (typeKey === 'plane') return this.randomInt(60, 180);
      if (typeKey === 'train') return this.randomInt(240, 720);
      return this.randomInt(120, 480); // bus
    },
    formatDuration(minutes) {
      const h = Math.floor(minutes / 60);
      const m = minutes % 60;
      return `${h}h${String(m).padStart(2, '0')}`;
    },
    generatePrice(typeKey, durationMinutes) {
      if (typeKey === 'plane') return this.randomInt(800000, 3000000);
      if (typeKey === 'train') return this.randomInt(250000, 1200000);
      // bus: scale a little by duration
      const base = 150000 + Math.floor((durationMinutes / 60) * 40000);
      return Math.min(1000000, Math.max(80000, base + this.randomInt(-20000, 100000)));
    },
    generateSeatType(typeKey) {
      if (typeKey === 'plane') return this.randomInt(0, 3) ? 'Hạng phổ thông' : 'Hạng thương gia';
      if (typeKey === 'train') return this.randomInt(0, 1) ? 'Ghế mềm' : 'Giường nằm';
      return this.randomInt(0, 1) ? 'Ghế ngồi' : 'Giường nằm';
    },
    generateMockTrips() {
      const typeKey = this.searchForm.vehicleType;
      const typeLabel = this.vehicleTypeLabel(typeKey);
      const fromCity = this.searchForm.from;
      const toCity = this.searchForm.to;
      const fromStations = this.getStationsForCity(fromCity, typeKey);
      const toStations = this.getStationsForCity(toCity, typeKey);
      const fromStation = this.searchForm.pickupStation || fromStations[0];
      const toStation = this.searchForm.dropoffStation || toStations[0];
      const companies = this.getCompaniesByType(typeKey);

      const desiredCompany = this.searchForm.company || null;
      const listSize = this.randomInt(4, 8);
      const results = [];
      for (let i = 0; i < listSize; i++) {
        const company = desiredCompany || companies[this.randomInt(0, companies.length - 1)];
        const departureTime = this.generateDepartureTime(typeKey);
        const durationMin = this.generateDurationMinutes(typeKey);
        const arrivalTime = this.addMinutesToTime(departureTime, durationMin);
        const price = this.generatePrice(typeKey, durationMin);
        const availableSeats = this.randomInt(0, 30);
        const rating = Math.round(this.randomBetween(3.5, 5) * 10) / 10;
        const freeCancel = this.randomInt(0, 1) === 1;
        const seatType = this.generateSeatType(typeKey);

        results.push({
          id: Date.now() + i,
          company,
          vehicleType: typeLabel,
          departureTime,
          arrivalTime,
          fromStation,
          toStation,
          duration: this.formatDuration(durationMin),
          price,
          rating,
          availableSeats,
          seatType,
          freeCancel,
        });
      }

      // Stable ordering before user sort: by departure time ascending
      results.sort((a, b) => a.departureTime.localeCompare(b.departureTime));
      return results;
    },
    openSeatModal(trip) {
      const maxRows = 8;
      const maxCols = 4;
      const totalSeats = maxRows * maxCols;
      const layout = [];
      let seatNumber = 1;
      
      for (let r = 0; r < maxRows; r++) {
        const row = [];
        for (let c = 0; c < maxCols; c++) {
          const id = seatNumber;
          const available = id <= trip.availableSeats;
          // Randomly assign some seats as booked or unavailable
          const randomStatus = Math.random();
          let booked = false;
          let unavailable = false;
          
          if (randomStatus < 0.1) {
            booked = true; // 10% chance of being booked
          } else if (randomStatus < 0.2) {
            unavailable = true; // 10% chance of being unavailable
          }
          
          row.push({ 
            id, 
            label: "S" + id, 
            available: available && !booked && !unavailable,
            booked,
            unavailable
          });
          seatNumber++;
        }
        layout.push(row);
      }
      
      this.seatModal = {
        visible: true,
        trip,
        seatsSelected: [],
        layout,
      };
    },
    toggleSeat(seat) {
      // Don't allow selection of booked or unavailable seats
      if (seat.booked || seat.unavailable) {
        return;
      }
      
      const maxSelect = this.maxSelectableSeats || 1;
      const idx = this.seatModal.seatsSelected.indexOf(seat.id);
      if (idx >= 0) {
        this.seatModal.seatsSelected.splice(idx, 1);
      } else {
        if (this.seatModal.seatsSelected.length >= maxSelect) return;
        this.seatModal.seatsSelected.push(seat.id);
      }
    },
    cancelSeatSelection() {
      this.seatModal.visible = false;
      this.seatModal.trip = null;
      this.seatModal.seatsSelected = [];
    },
    confirmSeats() {
      const selectedCount = this.seatModal.seatsSelected.length;
      if (selectedCount === 0) return;
      const trip = this.seatModal.trip;
      const total = trip.price * selectedCount;
      const seatsParam = this.seatModal.seatsSelected.join(",");
      this.cancelSeatSelection();
      this.$router.push({
        path: "/client-thanh-toan",
        query: {
          total: String(total),
          tripId: String(trip.id),
          seats: seatsParam,
          passengers: String(this.passengerCount),
          from: this.searchForm.from,
          to: this.searchForm.to,
          date: this.searchForm.departureDate,
          pickupStation: this.searchForm.pickupStation || "",
          dropoffStation: this.searchForm.dropoffStation || "",
          company: this.searchForm.company || "",
        },
      });
    },
    checkAuthStatus() {
      // Kiểm tra trạng thái đăng nhập từ localStorage
      const token = localStorage.getItem("token");
      const userInfo = localStorage.getItem("userInfo");

      if (token && userInfo) {
        try {
          this.userInfo = JSON.parse(userInfo);
        } catch (error) {
          console.error("Error parsing user info:", error);
          this.logout();
        }
      }
    },
    logout() {
      // Xóa thông tin đăng nhập
      localStorage.removeItem("token");
      localStorage.removeItem("userInfo");
      this.userInfo = {};

      // Thông báo đăng xuất thành công
      alert("Đăng xuất thành công!");

      // Reload trang để cập nhật UI
      this.$router.go(0);
    },
  },
  mounted() {
    // Kiểm tra trạng thái đăng nhập khi component được mount
    this.checkAuthStatus();
  },
};
</script>


<style>
/* 🔹 Modal nhanh khi chọn ghế (nền tối và khung pop-up) */
.seat-modal-backdrop {
  position: fixed;
  inset: 0; /* phủ toàn màn hình */
  background: rgba(0, 0, 0, 0.5); /* nền mờ đen */
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050; /* nổi lên trên cùng */
}
.seat-modal {
  background: #fff;
  width: 100%;
  max-width: 500px;
  border-radius: 8px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15); /* đổ bóng modal */
}

/* 🔹 Khung tổng thể chứa sơ đồ ghế */
.bus-seat-container {
  background: #f8f9fa;
  border: 1px solid #E0E0E0;
  border-radius: 1px;
  padding: 12px;
  margin: 0 auto;
  width: 100%;
  max-width: 450px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  position: relative;
}


/* 🔹 Ghi chú chú thích trạng thái ghế */
.seat-legend {
  display: flex;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
  padding: 8px;
  background: #fff;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  margin-bottom: 8px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #495057;
}

.legend-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

/* 🔸 Màu chú thích từng trạng thái ghế */
.legend-color.seat-empty { background: #FFFFFF; border-color: #CCCCCC; }
.legend-color.seat-selected { background: #28A745; border-color: #28A745; }
.legend-color.seat-booked { background: #DC3545; border-color: #DC3545; }
.legend-color.seat-unavailable { background: #ADB5BD; border-color: #ADB5BD; }

/* 🔹 Bố cục chia 2 tầng (2 cột trái – phải) */
.two-column-layout {
  display: grid;
  grid-template-columns: 1fr auto 1fr; /* tầng trái - đường giữa - tầng phải */
  gap: 16px;
  align-items: start;
}

/* 🔹 Khối tầng (deck) */
.deck-column {
  background: #fff;
  border-radius: 8px;
  padding: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
  position: relative;
}

/* Thanh màu trên cùng của mỗi tầng */
.deck-column::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  border-radius: 10px 10px 0 0;
}

/* Màu đại diện cho tầng dưới và tầng trên */
.lower-deck::before { background: linear-gradient(90deg, #0056d2 0%, #007bff 100%); }
.upper-deck::before { background: linear-gradient(90deg, #28a745 0%, #20c997 100%); }

/* 🔹 Tiêu đề tầng */
.deck-title {
  text-align: center;
  font-weight: bold;
  font-size: 12px;
  color: #333;
  margin-bottom: 8px;
  padding: 6px;
  background: #f8f9fa;
  border-radius: 4px;
  border: 1px solid #e9ecef;
  margin-top: 4px;
}

/* 🔹 Hàng tài xế */
.driver-row {
  display: flex;
  justify-content: left;
  margin-bottom: 8px;
  height: 32px;
}
.driver-row.empty { visibility: hidden; }

.driver-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  background: linear-gradient(135deg, #020407 0%, #bfc1c4 100%);
  border-radius: 50%;
  box-shadow: 0 4px 12px rgba(0, 86, 210, 0.3);
  border: 2px solid #fff;
}
.driver-icon i {
  font-size: 16px;
  color: #fff;
}

/* 🔹 Đường phân chia dọc giữa 2 tầng */
.vertical-divider {
  width: 1px;
  background: #E5E5E5;
  height: 100%;
  min-height: 200px;
}

/* 🔹 Bố cục hàng ghế */
.seat-matrix {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

/* Mỗi hàng gồm 2 cột ghế và 1 lối đi */
.seat-row {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 44px;
  align-items: center;
}

/* Cột ghế (trái hoặc phải) */
.seat-column {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

/* 🔹 Lối đi giữa các hàng ghế */
.aisle {
  width: 20px;
  height: 100%;
  background: #f8f9fa;
  border-radius: 2px;
  border: 1px dashed #dee2e6;
  position: relative;
}
.aisle::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 1px;
  height: 80%;
  background: #dee2e6;
}

/* 🔹 Nút ghế (Seat Button) */
.seat-btn {
  width: 32px;
  height: 42px;
  border-radius: 6px;
  border: 2px solid #CCCCCC;
  background: #FFFFFF;
  cursor: pointer;
  user-select: none;
  color: #333;
  font-weight: 600;
  font-size: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Hiệu ứng khi rê chuột */
.seat-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  z-index: 10;
}

/* 🔹 Trạng thái từng loại ghế */
.seat-empty { background: #FFFFFF; border-color: #CCCCCC; color: #333; }
.seat-empty:hover { border-color: #00d23f; box-shadow: 0 6px 20px rgba(0, 86, 210, 0.2); }

.seat-selected {
  background: #28a745;
  border-color: #28a745;
  color: #fff;
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(0, 86, 210, 0.4);
  animation: seatPulse 0.6s ease-in-out;
}

.seat-unavailable {
  background: #ADB5BD;
  border-color: #ADB5BD;
  color: #fff;
  cursor: not-allowed;
  opacity: 0.7;
}

.seat-booked {
  background: #DC3545;
  border-color: #DC3545;
  color: #fff;
  cursor: not-allowed;
  opacity: 0.8;
}

.seat-unavailable:hover,
.seat-booked:hover {
  transform: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* 🔹 Hiệu ứng chuyển động khi chọn ghế */
@keyframes seatPulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.15); }
  100% { transform: scale(1.05); }
}

/* 🔹 Hiệu ứng nhấn khi click chọn ghế */
.seat-btn { animation: seatClick 0.2s ease-in-out; }
@keyframes seatClick {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

/* 🔹 Hiệu ứng riêng cho ghế được chọn */
.seat-selected { animation: seatSelected 0.2s ease-in-out; }
@keyframes seatSelected {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1.05); }
}

/* 🔹 Giao diện responsive cho điện thoại */
@media (max-width: 768px) {
  .seat-modal { max-width: 95vw; margin: 10px; }
  .two-column-layout { grid-template-columns: 1fr; gap: 12px; }
  .vertical-divider { display: none; }
  .bus-seat-container { padding: 16px; max-width: 100%; }
  .seat-btn { width: 32px; height: 42px; font-size: 10px; }
  .aisle { width: 10px; }
  .driver-icon { width: 32px; height: 32px; }
  .driver-icon i { font-size: 16px; }
  .seat-legend { gap: 12px; padding: 8px; }
  .legend-item { font-size: 11px; }
  .legend-color { width: 14px; height: 14px; }
}

</style>
