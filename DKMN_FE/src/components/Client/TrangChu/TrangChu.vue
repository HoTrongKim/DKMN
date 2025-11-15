<template>
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
                  :disabled="!isSearchValid || isLoadingTrips"
                >
                  <span v-if="isLoadingTrips" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bx bx-search me-2"></i>
                  {{ isLoadingTrips ? "Đang tìm..." : "Tìm chuyến" }}
                </button>
              </div>
            </div>

            <div v-if="searchError" class="alert alert-danger mt-3 mb-0">
              {{ searchError }}
            </div>
          </div>
        </div>
      </div>
    </section>


    <!-- Seat Selection Modal -->
    <div v-if="seatModal.visible" class="seat-modal-backdrop">
      <div class="seat-modal">
        <div
          class="seat-modal__header d-flex justify-content-between align-items-center flex-wrap gap-3"
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
        <div class="seat-modal__body">
          <div class="seat-modal__summary" v-if="seatModal.trip">
            <div>
              <p class="seat-modal__summary-company">
                {{ seatModal.trip?.company }} ·
                {{ vehicleTypeLabel(seatModal.trip?.vehicleTypeKey) }}
              </p>
              <p class="seat-modal__summary-route">
                {{ seatModal.trip?.fromStation }} →
                {{ seatModal.trip?.toStation }}
              </p>
            </div>
            <div class="seat-modal__summary-time">
              <div class="summary-time-main">
                <span>Khởi hành</span>
                <strong>{{ seatModal.trip?.departureTime || "--:--" }}</strong>
              </div>
              <div class="summary-time-sub" v-if="seatModal.trip?.departureLabel">
                {{ seatModal.trip?.departureLabel }}
              </div>
            </div>
          </div>
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
          
 <!-- Seat Layout B (hiển thị theo loại phương tiện) -->
<div v-if="seatModal.trip?.vehicleTypeKey === 'bus'" class="bus-seat-container">
  <!-- Giữ nguyên layout xe khách 2 tầng -->
  <!-- Two Column Layout -->
  <div class="two-column-layout">
    <!-- Left Column: Lower Deck -->
    <div class="deck-column lower-deck">
      <div class="deck-title">TẦNG DƯỚI</div>

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
          v-for="(row, rowIndex) in lowerDeckRows"
          :key="'lower-r' + (row[0]?.id || rowIndex)"
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
                'seat-booked': seat.booked,
                'seat-aisle': seat.isAisle,
                'seat-placeholder': seat.isPlaceholder
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
                'seat-booked': seat.booked,
                'seat-aisle': seat.isAisle,
                'seat-placeholder': seat.isPlaceholder
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
          v-for="(row, rowIndex) in upperDeckRows"
          :key="'upper-r' + (row[0]?.id || rowIndex)"
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
                'seat-booked': seat.booked,
                'seat-aisle': seat.isAisle,
                'seat-placeholder': seat.isPlaceholder
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
                'seat-booked': seat.booked,
                'seat-aisle': seat.isAisle,
                'seat-placeholder': seat.isPlaceholder
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

<!-- Layout cho tàu hỏa / máy bay (dùng dữ liệu thực tế) -->
<div v-else class="generic-seat-container">
  <div v-if="!(seatModal.layout?.length)" class="text-center text-muted py-4">
    Chưa có dữ liệu ghế cho chuyến này.
  </div>
  <div v-else class="generic-seat-grid">
    <div
      class="seat-row"
      v-for="(row, rowIndex) in seatModal.layout"
      :key="'generic-row-' + rowIndex"
    >
      <div
        v-for="seat in row"
        :key="seat.id"
        class="seat-btn"
        :class="{
          'seat-empty': seat.available && !seatModal.seatsSelected.includes(seat.id),
          'seat-selected': seatModal.seatsSelected.includes(seat.id),
          'seat-unavailable': !seat.available,
          'seat-booked': seat.booked,
          'seat-aisle': seat.isAisle,
          'seat-placeholder': seat.isPlaceholder
        }"
        @click="toggleSeat(seat)"
        :title="seat.label"
      >
        {{ seat.label }}
      </div>
    </div>
  </div>
</div>
















<div class="seat-modal-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
  <div>
    <span class="me-2">Đã chọn:</span>
    <strong>{{ seatModal.seatsSelected.length }}</strong>
    <span class="ms-2 text-muted">
      Tổng:
      {{
        formatPrice(
          (seatModal.trip?.price || 0) *
            seatModal.seatsSelected.length
        )
      }}
    </span>
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
                    :min="priceLimits.min"
                    :max="priceLimits.max"
                    :step="priceLimits.step"
                    v-model.number="filters.price.max"
                  />
                  <div class="d-flex justify-content-between">
                    <span>{{ formatPrice(priceLimits.min) }}</span>
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

              <!-- Vehicle Type Filter (NEW) -->
              <div class="filter-group mb-4">
                <h6>Loại phương tiện</h6>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="filter-bus"
                    :value="'bus'"
                    v-model="filters.vehicleTypes"
                  />
                  <label class="form-check-label" for="filter-bus"
                    >Xe khách</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="filter-train"
                    :value="'train'"
                    v-model="filters.vehicleTypes"
                  />
                  <label class="form-check-label" for="filter-train"
                    >Tàu hỏa</label
                  >
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="filter-plane"
                    :value="'plane'"
                    v-model="filters.vehicleTypes"
                  />
                  <label class="form-check-label" for="filter-plane"
                    >Máy bay</label
                  >
                </div>
              </div>

              <!-- Company Filter (NEW - Checkbox list) -->
              <div class="filter-group mb-4" v-if="availableCompanies.length > 0">
                <h6>Nhà vận hành</h6>
                <div class="company-filter-list" style="max-height: 200px; overflow-y: auto;">
                  <div 
                    v-for="company in availableCompanies" 
                    :key="'filter-company-' + company"
                    class="form-check"
                  >
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :id="'filter-company-' + company"
                      :value="company"
                      v-model="filters.companies"
                    />
                    <label 
                      class="form-check-label" 
                      :for="'filter-company-' + company"
                      style="font-size: 0.9rem;"
                    >
                      {{ company }}
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Results -->
          <div class="col-lg-9">
            <!-- Sort Options -->
            <div class="sort-options mb-4">
              <div class="d-flex justify-content-between align-items-center">
                <h5>Kết quả tìm kiếm ({{ resultCount }} chuyến)</h5>
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
            <div
              v-if="hasRouteSummary"
              class="alert alert-light border border-1 route-summary-block mb-3"
            >
              <div class="d-flex flex-wrap align-items-center gap-3">
                <span class="fw-semibold text-dark">Hành trình:</span>
                <span class="text-primary fw-semibold">
                  {{ routeSummary.from || "-" }} → {{ routeSummary.to || "-" }}
                </span>
                <span v-if="routeSummary.date" class="text-muted small">
                  Ngày đi: {{ formatRouteDate(routeSummary.date) }}
                </span>
                <span
                  v-if="routeSummary.count !== null && routeSummary.count !== undefined"
                  class="badge bg-info text-dark"
                >
                  Có {{ routeSummary.count }} chuyến
                </span>
              </div>
            </div>

            <div
              v-if="hasSelectedStations"
              class="alert alert-secondary py-2 px-3 mb-3 d-flex flex-wrap gap-3 align-items-center station-summary"
            >
              <span class="fw-semibold text-dark">Bến đã chọn:</span>
              <span
                v-if="selectedStations.pickup"
                class="badge bg-primary text-white text-wrap"
              >
                Đi: {{ selectedStations.pickup }}
              </span>
              <span
                v-if="selectedStations.dropoff"
                class="badge bg-info text-dark text-wrap"
              >
                Đến: {{ selectedStations.dropoff }}
              </span>
            </div>

            <div
              v-if="selectedStations.fallback"
              class="alert alert-warning py-2 px-3 small mb-3"
            >
              Không có chuyến khớp chính xác bến đón/trả nên hiển thị các chuyến
              cùng tỉnh để bạn có thêm lựa chọn.
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
                            <div
                              class="station text-muted small"
                              v-if="trip.departureLabel"
                            >
                              {{ trip.departureLabel }}
                            </div>
                            <div class="station">{{ trip.fromStation }}</div>
                            <div class="station text-muted small" v-if="trip.pickupStationName">
                              Điểm đón: {{ trip.pickupStationName }}
                            </div>
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
                            <div class="station text-muted small" v-if="trip.dropoffStationName">
                              Điểm trả: {{ trip.dropoffStationName }}
                            </div>
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
                        <div class="price">{{ formatPrice(trip.displayPrice || trip.price) }}</div>
                        <small class="text-muted">/ người</small>
                        <button
                          @click="selectTrip(trip)"
                          class="btn btn-primary w-100 mt-2"
                          :disabled="isLoadingSeats"
                        >
                          Chọn chỗ
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

    <FeaturesSection />
  </div>
</template>

<script>
import api from "../../../services/api";
import FeaturesSection from "./FeaturesSection.vue";

const DEFAULT_CITY_FALLBACKS = [
  { id: 1, name: "Hà Nội" },
  { id: 32, name: "TP. Hồ Chí Minh" },
  { id: 15, name: "Đà Nẵng" },
];

const PRICE_FILTER_DEFAULTS = Object.freeze({
  min: 1000,
  max: 2000,
  step: 50,
});

const API_PREFIX = "/dkmn";
const TRIP_SEARCH_ENDPOINT =
  import.meta.env?.VITE_TRIP_SEARCH_ENDPOINT ||
  `${API_PREFIX}/chuyen-di/search`;
const CITIES_ENDPOINT = `${API_PREFIX}/tinh-thanh/get-data`;
const STATIONS_ENDPOINT = `${API_PREFIX}/tram/get-data`;
const OPERATORS_ENDPOINT = `${API_PREFIX}/nha-van-hanh/get-data`;
const SEATS_ENDPOINT = (id) => `${API_PREFIX}/chuyen-di/${id}/ghe`;

export default {
  name: "TrangChu",
  components: {
    FeaturesSection,
  },
  data() {
    return {
      cities: [],
      cityIdMap: {},
      companies: [],
      pickupStations: [],
      dropoffStations: [],
      stationIdMap: {
        from: {},
        to: {},
      },
      companyIdMap: {},
      meta: null,
      isLoadingTrips: false,
      isLoadingSeats: false,
      searchError: "",
      showResults: false,
      sortBy: "default",
      searchForm: {
        vehicleType: "",
        from: "",
        to: "",
        departureDate: "",
        passengers: "1",
        pickupStation: "",
        dropoffStation: "",
        company: "",
      },
      customPassengers: null,
      filters: {
        time: {
          morning: false,
          afternoon: false,
          evening: false,
        },
        price: {
          max: PRICE_FILTER_DEFAULTS.max,
        },
        available: false,
        freeCancel: false,
        vehicleTypes: [],
        companies: [],
      },
      priceLimits: { ...PRICE_FILTER_DEFAULTS },
      priceFilterLocked: false,
      suppressPriceWatch: false,
      trips: [],
      userInfo: {},
      seatModal: {
        visible: false,
        trip: null,
        seatsSelected: [],
        layout: [],
        seats: [],
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
        return Number.isNaN(count) ? 0 : count;
      }
      return Number(this.searchForm.passengers || 0);
    },
    maxSelectableSeats() {
      const requested = this.passengerCount;
      const seatList = this.seatModal?.seats || [];
      const availableFromSeats = seatList.filter((seat) => seat.available).length;
      const tripAvailable = this.seatModal?.trip?.availableSeats ?? 0;
      const available =
        availableFromSeats > 0 ? availableFromSeats : tripAvailable;
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

      if (
        this.filters.time.morning ||
        this.filters.time.afternoon ||
        this.filters.time.evening
      ) {
        filtered = filtered.filter((trip) => {
          const hour = this.getTripDepartureHour(trip);
          if (hour === null) {
            return false;
          }
          return (
            (this.filters.time.morning && hour >= 6 && hour < 12) ||
            (this.filters.time.afternoon && hour >= 12 && hour < 18) ||
            (this.filters.time.evening && hour >= 18 && hour < 24)
          );
        });
      }

      filtered = filtered.filter((trip) => {
        const ticketPrice = trip.price ?? trip.displayPrice ?? 0;
        return ticketPrice <= this.filters.price.max;
      });

      if (this.filters.available) {
        filtered = filtered.filter((trip) => trip.availableSeats > 0);
      }

      if (this.filters.freeCancel) {
        filtered = filtered.filter((trip) => trip.freeCancel);
      }

      if (this.filters.vehicleTypes.length > 0) {
        filtered = filtered.filter((trip) =>
          this.filters.vehicleTypes.includes(trip.vehicleTypeKey)
        );
      }

      if (this.filters.companies.length > 0) {
        filtered = filtered.filter((trip) =>
          this.filters.companies.includes(trip.company)
        );
      }

      if (this.searchForm.company) {
        filtered = filtered.filter(
          (trip) => trip.company === this.searchForm.company
        );
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
          return trips.sort(
            (a, b) =>
              (a.price ?? a.displayPrice ?? 0) -
              (b.price ?? b.displayPrice ?? 0)
          );
        case "price-high":
          return trips.sort(
            (a, b) =>
              (b.price ?? b.displayPrice ?? 0) -
              (a.price ?? a.displayPrice ?? 0)
          );
        case "rating":
          return trips.sort((a, b) => b.rating - a.rating);
        default:
          return trips;
      }
    },
    pickupLabel() {
      switch (this.searchForm.vehicleType) {
        case "train":
          return "Ga đón";
        case "plane":
          return "Sân bay đi";
        default:
          return "Bến xe đón";
      }
    },
    dropoffLabel() {
      switch (this.searchForm.vehicleType) {
        case "train":
          return "Ga trả";
        case "plane":
          return "Sân bay đến";
        default:
          return "Bến xe trả";
      }
    },
    availablePickupStations() {
      return this.pickupStations || [];
    },
    availableDropoffStations() {
      return this.dropoffStations || [];
    },
    availableCompanies() {
      return this.companies || [];
    },
    deckRows() {
      const layout = this.seatModal?.layout || [];
      const groups = {
        lower: [],
        upper: [],
        others: [],
      };

      layout.forEach((row) => {
        const deckKey = (row?.[0]?.deckKey || "")
          .toString()
          .toLowerCase();
        if (deckKey === "lower") {
          groups.lower.push(row);
          return;
        }
        if (deckKey === "upper") {
          groups.upper.push(row);
          return;
        }

        const label = (row?.[0]?.label || row?.[0]?.code || "")
          .toString()
          .trim()
          .toUpperCase();
        const prefix = label.charAt(0);

        if (prefix === "A") {
          groups.lower.push(row);
        } else if (prefix === "B") {
          groups.upper.push(row);
        } else {
          groups.others.push(row);
        }
      });

      if (!groups.lower.length && groups.others.length) {
        const half = Math.ceil(groups.others.length / 2);
        groups.lower = groups.others.slice(0, half);
        groups.upper = groups.others.slice(half);
        groups.others = [];
      } else if (!groups.upper.length && groups.others.length) {
        groups.upper = groups.others;
        groups.others = [];
      }

      return groups;
    },
    lowerDeckRows() {
      return this.deckRows.lower;
    },
    upperDeckRows() {
      return this.deckRows.upper.length
        ? this.deckRows.upper
        : this.deckRows.others;
    },
    selectedStations() {
      const pickup =
        (this.meta?.pickupStation || this.searchForm.pickupStation || "")
          .toString()
          .trim();
      const dropoff =
        (this.meta?.dropoffStation || this.searchForm.dropoffStation || "")
          .toString()
          .trim();

      return {
        pickup,
        dropoff,
        fallback: Boolean(this.meta?.stationFiltersFallback),
      };
    },
    hasSelectedStations() {
      const { pickup, dropoff } = this.selectedStations;
      return Boolean(pickup || dropoff);
    },
    routeSummary() {
      const from = (this.meta?.from || this.searchForm.from || "")
        .toString()
        .trim();
      const to = (this.meta?.to || this.searchForm.to || "")
        .toString()
        .trim();
      const date = (this.meta?.departureDate || this.searchForm.departureDate || "")
        .toString()
        .trim();
      const count = this.meta?.count ?? this.trips.length;
      return { from, to, date, count };
    },
    hasRouteSummary() {
      const { from, to } = this.routeSummary;
      return Boolean(from || to);
    },
    resultCount() {
      return this.meta?.count ?? this.trips.length;
    },
  },
  watch: {
    "searchForm.vehicleType"() {
      this.searchForm.pickupStation = "";
      this.searchForm.dropoffStation = "";
      this.fetchStationsFor("from");
      this.fetchStationsFor("to");
      this.fetchCompanies();
    },
    "searchForm.from"() {
      this.searchForm.pickupStation = "";
      this.fetchStationsFor("from");
    },
    "searchForm.to"() {
      this.searchForm.dropoffStation = "";
      this.fetchStationsFor("to");
    },
    "filters.price.max"(value) {
      if (this.suppressPriceWatch) {
        return;
      }
      this.priceFilterLocked = true;
      const clamped = this.clampPriceValue(value);
      if (clamped !== value) {
        this.suppressPriceWatch = true;
        this.filters.price.max = clamped;
        this.suppressPriceWatch = false;
      }
    },
  },
  methods: {
    setPriceFilterMax(value, lockState = null) {
      this.suppressPriceWatch = true;
      this.filters.price.max = value;
      this.suppressPriceWatch = false;
      if (lockState !== null) {
        this.priceFilterLocked = lockState;
      }
    },
    clampPriceValue(value) {
      const numeric = Number(value);
      if (Number.isNaN(numeric)) {
        return this.priceLimits.max;
      }
      return Math.min(this.priceLimits.max, Math.max(this.priceLimits.min, numeric));
    },
    resetPriceLimits() {
      this.priceLimits.min = PRICE_FILTER_DEFAULTS.min;
      this.priceLimits.max = PRICE_FILTER_DEFAULTS.max;
      this.priceLimits.step = PRICE_FILTER_DEFAULTS.step;
      this.priceFilterLocked = false;
      this.setPriceFilterMax(PRICE_FILTER_DEFAULTS.max, false);
    },
    updatePriceLimitsFromTrips(trips) {
      const list = Array.isArray(trips) ? trips : [];
      const prices = list
        .map((trip) => Number(trip?.price ?? trip?.basePrice ?? 0))
        .filter((price) => Number.isFinite(price) && price > 0);

      if (!prices.length) {
        this.resetPriceLimits();
        return;
      }

      const step = PRICE_FILTER_DEFAULTS.step || 1;
      const minPrice = Math.min(...prices);
      const maxPrice = Math.max(...prices);
      const paddedMin = Math.max(0, Math.floor(minPrice / step) * step);
      const paddedMax = Math.ceil((maxPrice + step) / step) * step;

      this.priceLimits.min = Math.min(PRICE_FILTER_DEFAULTS.min, paddedMin);
      this.priceLimits.max = Math.max(PRICE_FILTER_DEFAULTS.max, paddedMax);

      if (!this.priceFilterLocked) {
        this.setPriceFilterMax(this.priceLimits.max, false);
      } else {
        const preferredMax = this.filters.price.max || this.priceLimits.max;
        this.setPriceFilterMax(this.clampPriceValue(preferredMax), true);
      }
    },
    getTripDepartureHour(trip) {
      const candidates = [
        trip?.departureTime,
        trip?.raw?.departure_time,
        trip?.departureDateTime,
        trip?.raw?.gio_khoi_hanh,
      ];
      for (const value of candidates) {
        const hour = this.parseHourCandidate(value);
        if (hour !== null) {
          return hour;
        }
      }
      return null;
    },
    parseHourCandidate(value) {
      if (!value) {
        return null;
      }
      if (value instanceof Date && !Number.isNaN(value.getTime())) {
        return value.getHours();
      }
      if (typeof value === "string") {
        const match = value.match(/(\d{1,2}):(\d{2})/);
        if (match) {
          const hour = Number(match[1]);
          return Number.isNaN(hour) ? null : Math.max(0, Math.min(23, hour));
        }
        const parsed = new Date(value);
        if (!Number.isNaN(parsed.getTime())) {
          return parsed.getHours();
        }
      }
      return null;
    },
    async bootstrapData() {
      await Promise.all([this.fetchCities(), this.fetchCompanies()]);
      this.fetchStationsFor("from");
      this.fetchStationsFor("to");
    },
    async fetchCities() {
      const applyFallback = () => {
        this.cities = DEFAULT_CITY_FALLBACKS.map((city) => city.name);
        this.cityIdMap = DEFAULT_CITY_FALLBACKS.reduce((acc, city) => {
          acc[this.normalizeKey(city.name)] = city.id;
          return acc;
        }, {});
      };

      applyFallback();
      try {
        const { data } = await api.get(CITIES_ENDPOINT);
        const list = data?.data || data || [];
        this.cities = list
          .map((item) => item.ten || item.ten_tinh || item.name)
          .filter(Boolean);
        this.cityIdMap = list.reduce((acc, item) => {
          const label = item.ten || item.ten_tinh || item.name;
          if (label) {
            acc[this.normalizeKey(label)] = item.id || item.ma || item.slug || label;
          }
          return acc;
        }, {});
      } catch (error) {
        console.error("fetchCities error", error);
        applyFallback();
      }
    },
    async fetchStationsFor(position) {
      const cityName = this.searchForm[position];
      const stationType = this.stationTypeForVehicle(
        this.searchForm.vehicleType
      );
      if (!cityName || !stationType) {
        if (position === "from") {
          this.pickupStations = [];
          this.stationIdMap.from = {};
        } else {
          this.dropoffStations = [];
          this.stationIdMap.to = {};
        }
        return;
      }

      const cityId = this.getCityId(cityName);
      if (!cityId) return;

      const params = {
        tinh_thanh_id: cityId,
        loai: stationType,
      };

      try {
        const { data } = await api.get(STATIONS_ENDPOINT, { params });
        const list = (data?.data || data || []).map((item) => ({
          id: item.id || item.ma || item.slug || item.code,
          label: item.ten || item.ten_tram || item.name,
        }));
        const labels = list.map((item) => item.label).filter(Boolean);
        const map = list.reduce((acc, item) => {
          if (item.label) {
            acc[this.normalizeKey(item.label)] = item.id;
          }
          return acc;
        }, {});

        if (position === "from") {
          this.pickupStations = labels;
          this.stationIdMap.from = map;
        } else {
          this.dropoffStations = labels;
          this.stationIdMap.to = map;
        }
      } catch (error) {
        console.error("fetchStations error", error);
      }
    },
    stationTypeForVehicle(type) {
      const normalized = this.normalizeVehicleType(type);
      if (!normalized) return null;
      switch (normalized) {
        case "train":
          return "ga_tau";
        case "plane":
          return "san_bay";
        default:
          return "ben_xe";
      }
    },
    async fetchCompanies() {
      try {
        const params = {};
        const type = this.normalizeVehicleType(this.searchForm.vehicleType);
        if (type) {
          params.loai = type;
        }
        const { data } = await api.get(OPERATORS_ENDPOINT, { params });
        this.companyIdMap = {};
        const list = data?.data || data || [];
        this.companies = list
          .map((item) => {
            const label =
              item.ten ||
              item.ten_nha_van_hanh ||
              item.ten_nha_xe ||
              item.name;
            if (label) {
              this.companyIdMap[this.normalizeKey(label)] =
                item.id || item.ma || item.slug || label;
            }
            return label;
          })
          .filter(Boolean);
        if (
          this.searchForm.company &&
          !this.companies.includes(this.searchForm.company)
        ) {
          this.searchForm.company = "";
        }
      } catch (error) {
        console.error("fetchCompanies error", error);
      }
    },
    normalizeKey(value) {
      if (!value) return "";
      return value
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .trim();
    },
    normalizeForApi(value) {
      if (!value) return null;
      return value
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, " ")
        .trim();
    },
    getCityId(label) {
      if (!label) return null;
      return this.cityIdMap[this.normalizeKey(label)] || null;
    },
    getStationId(position, label) {
      if (!label) return null;
      const map = position === "from" ? this.stationIdMap.from : this.stationIdMap.to;
      return map[this.normalizeKey(label)] || null;
    },
    getCompanyId(label) {
      if (!label) return null;
      return this.companyIdMap[this.normalizeKey(label)] || null;
    },
    async searchTrips() {
      if (!this.isSearchValid || this.isLoadingTrips) return;

      this.isLoadingTrips = true;
      this.searchError = "";
      try {
        const fromLabel = this.searchForm.from || "";
        const toLabel = this.searchForm.to || "";
        const pickupLabel = this.searchForm.pickupStation || "";
        const dropoffLabel = this.searchForm.dropoffStation || "";
        const companyLabel = this.searchForm.company || "";

        const payload = {
          vehicleType: this.normalizeVehicleType(
            this.searchForm.vehicleType
          ),
          from: this.normalizeForApi(fromLabel),
          fromId: this.getCityId(fromLabel),
          to: this.normalizeForApi(toLabel),
          toId: this.getCityId(toLabel),
          departureDate: this.searchForm.departureDate,
          pickupStation: this.normalizeForApi(pickupLabel),
          pickupStationId: this.getStationId("from", pickupLabel),
          dropoffStation: this.normalizeForApi(dropoffLabel),
          dropoffStationId: this.getStationId("to", dropoffLabel),
          passengers: this.passengerCount,
          company: this.normalizeForApi(companyLabel),
          companyId: this.getCompanyId(companyLabel),
        };

        const { data } = await api.post(TRIP_SEARCH_ENDPOINT, payload);
        const trips = data?.data || [];
        this.meta = data?.meta || null;
        this.trips = trips
          .map((item, idx) => this.normalizeTrip(item, idx))
          .filter(Boolean);
        this.updatePriceLimitsFromTrips(this.trips);
        this.showResults = true;

        if (!this.trips.length) {
          const message =
            data?.message ||
            "Không tìm thấy chuyến phù hợp, vui lòng thử lại.";
          this.$toast?.info?.(message);
        } else {
          this.$nextTick(() => {
            document
              .querySelector(".results-section")
              ?.scrollIntoView({ behavior: "smooth" });
          });
        }
      } catch (error) {
        this.searchError =
          error.response?.data?.message ||
          "Không tìm thấy chuyến phù hợp, vui lòng thử lại.";
        this.$toast?.error?.(this.searchError);
        this.resetPriceLimits();
      } finally {
        this.isLoadingTrips = false;
      }
    },
    normalizeTrip(raw, index = 0) {
      if (!raw || typeof raw !== "object") return null;

      const typeInput =
        raw.vehicleTypeKey ||
        raw.vehicle_type ||
        raw.loai_phuong_tien ||
        raw.type ||
        this.searchForm.vehicleType;
      const vehicleTypeKey = this.normalizeVehicleType(typeInput);

      const firstString = (...values) => {
        for (const value of values) {
          if (value === undefined || value === null) continue;
          const str = String(value).trim();
          if (str) {
            return str;
          }
        }
        return "";
      };

      const departureTime = this.extractTime(
        raw.gio_khoi_hanh ||
          raw.departure_time ||
          raw.depart_at ||
          raw.departTime
      );
      const arrivalTime = this.extractTime(
        raw.gio_den || raw.arrival_time || raw.arrive_at || raw.arrivalTime
      );

      const departureDateText = this.extractDate(
        raw.ngay_khoi_hanh ||
          raw.departure_date ||
          raw.depart_date ||
          raw.departureDate ||
          raw.ngay_di ||
          raw.gio_khoi_hanh
      );

      const durationText =
        raw.thoi_gian ||
        raw.duration ||
        this.computeDuration(
          departureTime,
          arrivalTime,
          raw.total_minutes || raw.duration_minutes
        );

      const rawPrice =
        this.toNumber(raw.price) ||
        this.toNumber(raw.basePrice) ||
        this.toNumber(raw.gia_ve) ||
        this.toNumber(raw.gia) ||
        this.toNumber(raw.base_price);
      const displayPrice =
        this.toNumber(raw.displayPrice) ||
        this.toNumber(raw.normalizedPrice) ||
        rawPrice;
      const price = rawPrice || displayPrice;

      const availableSeats =
        this.toNumber(raw.so_ghe_trong) ||
        this.toNumber(raw.available_seats) ||
        this.toNumber(raw.remaining_seats);

      const pickupStationName = firstString(
        raw.pickupStationName,
        raw.pickup_station_name,
        raw.diem_don,
        raw.pickupStation,
        raw.pickup_station,
        this.searchForm.pickupStation
      );
      const dropoffStationName = firstString(
        raw.dropoffStationName,
        raw.dropoff_station_name,
        raw.diem_tra,
        raw.dropoffStation,
        raw.dropoff_station,
        this.searchForm.dropoffStation
      );

      const fromCity = firstString(
        raw.fromCity,
        raw.from_city,
        raw.tinh_thanh_di,
        raw.from,
        this.searchForm.from
      );
      const toCity = firstString(
        raw.toCity,
        raw.to_city,
        raw.tinh_thanh_den,
        raw.to,
        this.searchForm.to
      );

      const fromStation = firstString(
        raw.fromStation,
        raw.noi_di,
        raw.departure_station,
        raw.from_station,
        fromCity
      );
      const toStation = firstString(
        raw.toStation,
        raw.noi_den,
        raw.arrival_station,
        raw.to_station,
        toCity
      );

      const company =
        raw.company ||
        raw.nha_xe ||
        raw.hang_xe ||
        raw.carrier ||
        "Nhà vận hành";

      const rating =
        this.toNumber(raw.diem_danh_gia) ||
        this.toNumber(raw.rating) ||
        4.5;

      return {
        id:
          raw.id ??
          raw.trip_id ??
          raw.ma_chuyen ??
          `${Date.now()}-${index + 1}`,
        company,
        vehicleTypeKey,
        vehicleType:
          raw.vehicleType || this.vehicleTypeLabel(vehicleTypeKey),
        departureTime: departureTime || "--:--",
        arrivalTime: arrivalTime || "",
        fromCity,
        toCity,
        fromStation,
        toStation,
        pickupStationName,
        dropoffStationName,
        departureLabel: departureDateText || "",
        duration: durationText || "",
        price: price > 0 ? price : 0,
        displayPrice: displayPrice > 0 ? displayPrice : price,
        rating,
        availableSeats: availableSeats >= 0 ? availableSeats : 0,
        seatType: raw.loai_ghe || raw.seat_type || "Ghế / Giường tiêu chuẩn",
        freeCancel: this.toBoolean(
          raw.huy_mien_phi ?? raw.free_cancel ?? raw.is_free_cancel
        ),
        raw,
      };
    },
    normalizeVehicleType(value = "") {
      if (!value) return null;
      const normalized = value.toString().toLowerCase();
      if (
        normalized.includes("train") ||
        normalized.includes("tàu") ||
        normalized.includes("tau")
      ) {
        return "train";
      }
      if (
        normalized.includes("plane") ||
        normalized.includes("bay") ||
        normalized.includes("air") ||
        normalized.includes("máy")
      ) {
        return "plane";
        
      }
      if (
        normalized.includes("bus") ||
        normalized.includes("xe") ||
        normalized.includes("coach")
      ) {
        return "bus";
      }
      return null;
    },
    extractTime(value) {
      if (!value) return "";
      if (/^\d{1,2}:\d{2}/.test(value)) {
        return value.slice(0, 5);
      }
      const match = value.match(/(\d{1,2}:\d{2})/);
      if (match) return match[1];
      const date = new Date(value);
      if (!Number.isNaN(date.getTime())) {
        return date.toTimeString().slice(0, 5);
      }
      return "";
    },
    extractDate(value) {
      if (!value) return "";
      if (/^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
        return value;
      }
      if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
        const [year, month, day] = value.split("-");
        return `${day}/${month}/${year}`;
      }
      const date = new Date(value);
      if (!Number.isNaN(date.getTime())) {
        return date.toLocaleDateString("vi-VN");
      }
      return "";
    },
    computeDuration(startTime, endTime, fallbackMinutes) {
      if (fallbackMinutes) {
        const minutes = this.toNumber(fallbackMinutes);
        if (minutes > 0) {
          const h = Math.floor(minutes / 60);
          const m = minutes % 60;
          return `${h}h${String(m).padStart(2, "0")}`;
        }
      }
      if (!startTime || !endTime) return "";
      const base = "1970-01-01";
      const start = new Date(`${base}T${startTime}:00`);
      let end = new Date(`${base}T${endTime}:00`);
      if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
        return "";
      }
      if (end <= start) {
        end.setDate(end.getDate() + 1);
      }
      const diffMin = Math.round((end - start) / 60000);
      const h = Math.floor(diffMin / 60);
      const m = diffMin % 60;
      return `${h}h${String(m).padStart(2, "0")}`;
    },
    toNumber(value, defaultValue = 0) {
      const num = Number(value);
      return Number.isFinite(num) ? num : defaultValue;
    },
    toBoolean(value) {
      if (typeof value === "boolean") return value;
      if (typeof value === "string") {
        return ["1", "true", "yes", "có"].includes(value.toLowerCase());
      }
      if (typeof value === "number") return value === 1;
      return false;
    },
    async selectTrip(trip) {
      if (!trip?.id) return;
      this.isLoadingSeats = true;
      try {
        const { data } = await api.get(SEATS_ENDPOINT(trip.id));
        const payload = data?.data || {};
        const normalizedTrip =
          payload.trip && typeof payload.trip === "object"
            ? {
                ...trip,
                ...payload.trip,
                vehicleTypeKey:
                  payload.trip.vehicleTypeKey ||
                  this.normalizeVehicleType(
                    payload.trip.vehicle_type ||
                      payload.trip.loai_phuong_tien ||
                      trip.vehicleTypeKey
                  ),
                vehicleType:
                  payload.trip.vehicleType ||
                  this.vehicleTypeLabel(
                    payload.trip.vehicleTypeKey || trip.vehicleTypeKey
                  ),
              }
            : trip;

        const seatEntries = this.getSeatEntries(payload);
        const layout = this.buildSeatLayout(
          payload,
          normalizedTrip,
          seatEntries
        );
        this.seatModal = {
          visible: true,
          trip: normalizedTrip,
          seatsSelected: [],
          layout,
          seats: seatEntries,
        };
      } catch (error) {
        const message =
          error.response?.data?.message || "Không tải được sơ đồ ghế.";
        this.$toast?.error?.(message) || alert(message);
      } finally {
        this.isLoadingSeats = false;
      }
    },
    buildSeatLayout(payload, trip, seatEntries = null) {
      const vehicleType = trip?.vehicleTypeKey;
      const isPlaneOrTrain = ["plane", "train"].includes(vehicleType);
      const sourceSeats = Array.isArray(seatEntries)
        ? seatEntries
        : this.getSeatEntries(payload);
      const seatObjects = this.normalizeSeatObjects(sourceSeats, trip);

      if (isPlaneOrTrain && seatObjects.length) {
        return this.arrangeSeatsByVehicleType(seatObjects, vehicleType);
      }

      if (Array.isArray(payload?.layout) && payload.layout.length) {
        return payload.layout;
      }

      if (seatObjects.length) {
        return this.arrangeSeatsByVehicleType(
          seatObjects,
          trip?.vehicleTypeKey
        );
      }

      return this.createDefaultSeatLayout(trip);
    },
    layoutFromSeatList(seats, trip) {
      const seatObjects = this.normalizeSeatObjects(seats, trip);
      return this.arrangeSeatsByVehicleType(
        seatObjects,
        trip?.vehicleTypeKey
      );
    },
    getSeatEntries(payload = {}) {
      if (Array.isArray(payload?.seats) && payload.seats.length) {
        return payload.seats;
      }
      if (Array.isArray(payload?.layout) && payload.layout.length) {
        return payload.layout.reduce((acc, row) => {
          if (!Array.isArray(row)) return acc;
          row.forEach((seat) => {
            if (seat && !seat.isAisle && !seat.isPlaceholder) {
              acc.push(seat);
            }
          });
          return acc;
        }, []);
      }
      return [];
    },
    normalizeSeatObjects(seats = [], trip) {
      const vehicleType = trip?.vehicleTypeKey;
      return seats.map((seat, index) => {
        const fallbackLabel = this.generateSeatLabel(index + 1);
        const booked =
          seat.trang_thai === "booked" || this.toBoolean(seat.booked);
        const unavailable =
          seat.trang_thai === "maintenance" ||
          this.toBoolean(seat.unavailable);
        const availableSource =
          seat.available !== undefined
            ? this.toBoolean(seat.available)
            : seat.trang_thai
            ? seat.trang_thai !== "booked"
            : true;
        const available = availableSource && !booked && !unavailable;

        return {
          id: seat.id || seat.ma_ghe || seat.code || fallbackLabel,
          label:
            seat.label ||
            seat.ten ||
            seat.ma_ghe ||
            seat.code ||
            fallbackLabel,
          available,
          booked,
          unavailable,
          deckKey: this.detectDeckKey(seat, vehicleType, index),
        };
      });
    },
    createDefaultSeatLayout(trip = {}) {
      const totalSeats = 32;
      const availableSeats = Number(trip.availableSeats || totalSeats);
      const seatObjects = [];
      for (let seatNumber = 1; seatNumber <= totalSeats; seatNumber++) {
        const seatLabel = this.generateSeatLabel(seatNumber);
        seatObjects.push({
          id: seatLabel,
          label: seatLabel,
          available: seatNumber <= availableSeats,
          booked: seatNumber > availableSeats,
          unavailable: false,
          deckKey:
            trip?.vehicleTypeKey === "bus" && seatNumber > totalSeats / 2
              ? "upper"
              : trip?.vehicleTypeKey === "bus"
              ? "lower"
              : "",
        });
      }
      return this.arrangeSeatsByVehicleType(seatObjects, trip?.vehicleTypeKey);
    },
    arrangeSeatsByVehicleType(seats, vehicleType) {
      switch (vehicleType) {
        case "bus":
          return this.buildBusDeckLayout(seats);
        case "plane":
          return this.buildPlaneLayout(seats);
        case "train":
          return this.buildTrainLayout(seats);
        default:
          return this.chunkArray(seats, 4);
      }
    },
    buildBusDeckLayout(seats = []) {
      const normalized = seats.map((seat, index) => ({
        ...seat,
        deckKey: seat.deckKey || this.detectDeckKey(seat, "bus", index),
      }));
      const lower = normalized.filter(
        (seat) => seat.deckKey !== "upper"
      );
      const upper = normalized.filter(
        (seat) => seat.deckKey === "upper"
      );
      const rows = [
        ...this.chunkArray(lower, 4),
        ...this.chunkArray(upper, 4),
      ];
      return rows;
    },
    buildPlaneLayout(seats = []) {
      const rows = [];
      const seatsPerSide = 3;
      const working = [...seats];
      let cursor = 0;
      while (cursor < working.length) {
        const left = working.slice(cursor, cursor + seatsPerSide);
        cursor += seatsPerSide;
        const right = working.slice(cursor, cursor + seatsPerSide);
        cursor += seatsPerSide;
        const row = [
          ...this.fillSeatRow(left, seatsPerSide, "plane"),
          this.createAislePlaceholder(rows.length),
          ...this.fillSeatRow(right, seatsPerSide, "plane"),
        ];
        rows.push(row);
      }
      return rows;
    },
    buildTrainLayout(seats = []) {
      const rows = [];
      const seatsPerSide = 4;
      const working = [...seats];
      let cursor = 0;
      while (cursor < working.length) {
        const left = working.slice(cursor, cursor + seatsPerSide);
        cursor += seatsPerSide;
        const right = working.slice(cursor, cursor + seatsPerSide);
        cursor += seatsPerSide;
        const row = [
          ...this.fillSeatRow(left, seatsPerSide, "train"),
          this.createAislePlaceholder(rows.length),
          ...this.fillSeatRow(right, seatsPerSide, "train"),
        ];
        rows.push(row);
      }
      return rows;
    },
    fillSeatRow(seats = [], required = 3, deckKey = "") {
      const filled = [];
      for (let i = 0; i < required; i++) {
        const seat = seats[i];
        if (seat) {
          filled.push({ ...seat, deckKey });
        } else {
          filled.push({
            id: `placeholder-${deckKey}-${Math.random().toString(36).slice(2)}`,
            label: "",
            available: false,
            booked: false,
            unavailable: true,
            isPlaceholder: true,
            deckKey,
          });
        }
      }
      return filled;
    },
    createAislePlaceholder(rowIndex) {
      return {
        id: `aisle-${rowIndex}`,
        label: "",
        available: false,
        booked: false,
        unavailable: true,
        isAisle: true,
      };
    },
    detectDeckKey(seat, vehicleType, index = 0) {
      if (vehicleType !== "bus") {
        return "";
      }
      const raw =
        seat.deck ??
        seat.floor ??
        seat.level ??
        seat.tang ??
        seat.tang_id ??
        seat.layer;
      if (raw !== undefined && raw !== null) {
        const normalized = String(raw).toLowerCase();
        if (["2", "tren", "upper", "b"].some((k) => normalized.includes(k))) {
          return "upper";
        }
        return "lower";
      }
      const label = (seat.label || seat.code || "").toString().toUpperCase();
      if (label.startsWith("B")) return "upper";
      return index % 2 === 0 ? "lower" : "upper";
    },
    chunkArray(items, size) {
      const chunks = [];
      for (let i = 0; i < items.length; i += size) {
        chunks.push(items.slice(i, i + size));
      }
      return chunks;
    },
    generateSeatLabel(index) {
      const deckSize = 20;
      const letters = ["A","B","C","D","E","F","G","H","I","J"];
      const normalized = Math.max(1, index);
      const deck = Math.floor((normalized - 1) / deckSize);
      const letter =
        letters[deck] ||
        String.fromCharCode("A".charCodeAt(0) + deck);
      const number = ((normalized - 1) % deckSize) + 1;
      return `${letter}${number}`;
    },
    toggleSeat(seat) {
      if (
        seat.isAisle ||
        seat.isPlaceholder ||
        seat.booked ||
        seat.unavailable ||
        !seat.available
      ) {
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
      this.seatModal.layout = [];
      this.seatModal.seats = [];
    },
    confirmSeats() {
      const selectedCount = (this.seatModal?.seatsSelected || []).length;
      if (selectedCount === 0) {
        alert("Vui lòng chọn ghế trước khi tiếp tục thanh toán.");
        return;
      }

      const trip = this.seatModal.trip || {};
      const price = Number(trip.price || trip.displayPrice || 0);
      const total = price * selectedCount;
      const seatLabelMap = (this.seatModal?.seats || []).reduce((acc, seat) => {
        const label =
          seat.label ||
          seat.ten ||
          seat.ma_ghe ||
          seat.code ||
          (seat.id ? `Ghế ${seat.id}` : "");
        const keys = [
          seat.id,
          seat.code,
          seat.ma_ghe,
          seat.label,
          seat.so_ghe,
        ]
          .map((key) => (key === undefined || key === null ? null : String(key)))
          .filter(Boolean);
        keys.forEach((key) => {
          acc[key] = label;
          acc[key.replace(/^0+/, "")] = label;
        });
        return acc;
      }, {});

      const formattedSeats = (this.seatModal.seatsSelected || []).map(
        (code) => {
          const normalizedKey = String(code);
          const strippedKey = normalizedKey.replace(/^0+/, "");
          const label =
            seatLabelMap[normalizedKey] ||
            seatLabelMap[strippedKey] ||
            seatLabelMap[normalizedKey.toUpperCase()] ||
            seatLabelMap[strippedKey.toUpperCase()];

          if (label) {
            return label;
          }

          if (
            this.seatModal.trip?.vehicleTypeKey === "train" &&
            typeof code === "string" &&
            code.includes("-")
          ) {
            const cleanCode = code.replace(/^[A-Za-z]/, "");
            const [car, row, seat] = cleanCode.split("-").map(Number);
            const seatNumber = (row - 1) * 8 + seat;
            return `Toa ${car + 1} - Ghế ${seatNumber}`;
          }

          return normalizedKey;
        }
      );
      const seatsParam = formattedSeats.join(",");
      const seatIdsParam = this.seatModal.seatsSelected.join(",");

      this.cancelSeatSelection();

      this.$router
        .push({
          path: "/client-thanh-toan",
          query: {
            total: String(total),
            tripId: String(trip.id || ""),
            seats: seatsParam,
            passengers: String(this.passengerCount || selectedCount),
            from: this.searchForm?.from || trip.fromCity || "",
            to: this.searchForm?.to || trip.toCity || "",
            date: this.searchForm?.departureDate || "",
            pickupStation:
              this.searchForm?.pickupStation ||
              this.selectedStations?.pickup ||
              trip.pickupStationName ||
              trip.fromStation ||
              "",
            dropoffStation:
              this.searchForm?.dropoffStation ||
              this.selectedStations?.dropoff ||
              trip.dropoffStationName ||
              trip.toStation ||
              "",
            company: trip.company || "",
            vehicleType: trip.vehicleType || "",
            departureTime: trip.departureTime || "",
            arrivalTime: trip.arrivalTime || "",
            seatType: trip.seatType || "",
            seatIds: seatIdsParam,
          },
        })
        .catch(() => {});
    },
    formatPrice(price) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(price || 0);
    },
    formatRouteDate(value) {
      if (!value) return "";
      const parsed = new Date(value);
      if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString("vi-VN");
      }
      return value;
    },
    vehicleTypeLabel(typeKey) {
      switch (typeKey) {
        case "plane":
          return "Máy bay";
        case "train":
          return "Tàu hỏa";
        case "bus":
        default:
          return "Xe khách";
      }
    },
    checkAuthStatus() {
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
      localStorage.removeItem("token");
      localStorage.removeItem("userInfo");
      this.userInfo = {};
      alert("Đăng xuất thành công!");
      this.$router.go(0);
    },
  },
  mounted() {
    this.checkAuthStatus();
    this.bootstrapData();
  },
};
</script>

<style scoped>
:global(body) {
  background: #f5f7fb;
  font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
  color: #111827;
}

.container-fluid {
  background: linear-gradient(180deg, #0f172a 0%, #111a2e 15%, #f5f7fb 55%);
  min-height: 100vh;
}

/* Hero area */
.hero {
  position: relative;
  min-height: 88vh;
  padding: clamp(3rem, 8vw, 6.5rem) 0 4rem;
  overflow: hidden;
  color: #fff;
}

.hero__overlay {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 10% 20%, rgba(165, 180, 252, 0.4), transparent 50%),
    radial-gradient(circle at 80% 0%, rgba(96, 165, 250, 0.35), transparent 45%),
    linear-gradient(135deg, #312e81, #1e1b4b);
  opacity: 0.92;
}

.hero__grid {
  position: absolute;
  inset: 10% 5%;
  background-image: linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px);
  background-size: 80px 80px;
  opacity: 0.35;
  z-index: 1;
}

.blob {
  position: absolute;
  width: 420px;
  height: 420px;
  border-radius: 50%;
  filter: blur(50px);
  opacity: 0.35;
  animation: blobFloat 22s ease-in-out infinite;
  z-index: 0;
}

.blob-1 {
  top: -120px;
  left: -40px;
  background: radial-gradient(circle, rgba(129, 140, 248, 0.7), rgba(59, 130, 246, 0.3));
}

.blob-2 {
  bottom: -160px;
  right: -80px;
  background: radial-gradient(circle, rgba(248, 113, 113, 0.6), rgba(249, 115, 22, 0.3));
  animation-delay: 4s;
}

.blob-3 {
  top: 20%;
  right: 30%;
  background: radial-gradient(circle, rgba(16, 185, 129, 0.6), rgba(45, 212, 191, 0.25));
  animation-delay: 8s;
}

@keyframes blobFloat {
  0%,
  100% {
    transform: translate3d(0, 0, 0) scale(1);
  }
  33% {
    transform: translate3d(25px, -35px, 0) scale(1.05);
  }
  66% {
    transform: translate3d(-30px, 40px, 0) scale(0.95);
  }
}

.hero__content {
  position: relative;
  z-index: 2;
}

.hero__glass {
  border-radius: 32px;
  padding: clamp(1.75rem, 4vw, 3rem);
  background: rgba(15, 23, 42, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 35px 90px rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(18px);
}

.hero__title {
  font-size: clamp(2.2rem, 4vw, 3.6rem);
  color: #fff;
  font-weight: 700;
  letter-spacing: 0.01em;
  margin-bottom: 0.6rem;
}

.hero__title .text-primary {
  color: #93c5fd !important;
}

.hero__subtitle {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.85);
}

.search-form {
  background: rgba(15, 23, 42, 0.45);
  border-radius: 28px;
  padding: clamp(1.25rem, 3vw, 2rem);
  border: 1px solid rgba(148, 163, 184, 0.25);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04), 0 20px 55px rgba(15, 23, 42, 0.35);
}

.search-form .form-label {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.85rem;
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.search-form .form-select,
.search-form .form-control {
  background: rgba(15, 23, 42, 0.65);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  color: #f8fafc;
  min-height: 48px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-form .form-select:focus,
.search-form .form-control:focus {
  border-color: rgba(96, 165, 250, 0.8);
  box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
  background: rgba(15, 23, 42, 0.85);
}

.search-btn {
  border-radius: 16px;
  font-size: 1rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: linear-gradient(135deg, #60a5fa, #6366f1, #c084fc);
  border: none;
  padding: 0.85rem 1.25rem;
  box-shadow: 0 15px 35px rgba(99, 102, 241, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.search-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 20px 45px rgba(99, 102, 241, 0.45);
}

.search-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

/* Result cards */
.results-section {
  background: #f5f7fb;
}

.filters-card {
  position: sticky;
  top: 90px;
  border-radius: 22px;
  border: 1px solid #e2e8f0;
  padding: 1.5rem;
  background: #fff;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
}

.filter-group h6 {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #475569;
}

.trip-card {
  border-radius: 24px;
  padding: 1.5rem;
  background: #fff;
  border: 1px solid transparent;
  box-shadow: 0 25px 55px rgba(15, 23, 42, 0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border 0.25s ease;
}

.trip-card:hover {
  transform: translateY(-4px);
  border-color: rgba(99, 102, 241, 0.35);
  box-shadow: 0 35px 65px rgba(79, 70, 229, 0.15);
}

.trip-route {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  align-items: center;
  margin-bottom: 0.85rem;
  font-weight: 600;
  color: #0f172a;
}

.trip-info {
  display: grid;
  gap: 0.5rem;
  color: #475569;
  font-size: 0.95rem;
}

.trip-price {
  text-align: right;
  font-weight: 700;
  font-size: 1.35rem;
  color: #2563eb;
}

.trip-features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-top: 1rem;
}

.trip-features span {
  font-size: 0.8rem;
  color: #475569;
  background: #f1f5f9;
  border-radius: 999px;
  padding: 0.25rem 0.8rem;
}

.seat-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.78);
  backdrop-filter: blur(6px);
  z-index: 1100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(1rem, 4vw, 2rem);
}

.seat-modal {
  width: min(1120px, 100%);
  max-height: 92vh;
  background: #ffffff;
  border-radius: 26px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 40px 110px rgba(15, 23, 42, 0.35);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.seat-modal__header {
  padding: 1.25rem 1.75rem;
  background: linear-gradient(135deg, #1d4ed8, #7c3aed);
  color: #fff;
}

.seat-modal__header h6 {
  font-size: 1.05rem;
  font-weight: 600;
}

.seat-modal__body {
  padding: clamp(1.25rem, 3vw, 2rem);
  background: #f8fafc;
  overflow-y: auto;
  max-height: calc(92vh - 170px);
}

.seat-modal__summary {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  background: #fff;
  border-radius: 18px;
  padding: 1.25rem 1.5rem;
  border: 1px solid #e2e8f0;
  margin-bottom: 1.25rem;
}

.seat-modal__summary-company {
  margin: 0;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #94a3b8;
}

.seat-modal__summary-route {
  margin: 4px 0 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #0f172a;
}

.seat-modal__summary-time {
  text-align: right;
}

.summary-time-main span {
  display: block;
  font-size: 0.75rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #94a3b8;
}

.summary-time-main strong {
  font-size: 1.6rem;
  color: #1d4ed8;
}

.summary-time-sub {
  margin-top: 0.25rem;
  font-size: 0.85rem;
  color: #475569;
}

.seat-modal__body::-webkit-scrollbar {
  width: 8px;
}

.seat-modal__body::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.6);
  border-radius: 999px;
}


.seat-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem 1.5rem;
  background: #fff;
  border-radius: 18px;
  padding: 1rem 1.5rem;
  border: 1px solid #e2e8f0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.9rem;
  color: #475569;
  font-weight: 500;
}

.legend-color {
  width: 18px;
  height: 18px;
  border-radius: 6px;
  border: 1px solid rgba(15, 23, 42, 0.15);
}

.seat-btn {
  border-radius: 10px;
  min-width: 52px;
  min-height: 46px;
  font-weight: 600;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.seat-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
}

.seat-empty {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.seat-selected {
  background: #2563eb;
  color: #fff;
  border-color: #1d4ed8;
}

.seat-unavailable,
.seat-booked {
  background: #e2e8f0;
  color: #94a3b8;
  border-color: transparent;
}

.bus-seat-container,
.generic-seat-container {
  background: #fff;
  border-radius: 22px;
  padding: clamp(1rem, 3vw, 1.75rem);
  border: 1px solid #e2e8f0;
  box-shadow: 0 25px 55px rgba(15, 23, 42, 0.08);
  margin-top: 1.25rem;
}

.generic-seat-container {
  max-width: 760px;
  margin-left: auto;
  margin-right: auto;
}

.two-column-layout {
  display: flex;
  flex-wrap: wrap;
  gap: 1.75rem;
}

.deck-column {
  flex: 1 1 360px;
  background: linear-gradient(180deg, #f8fafc, #fff);
  border-radius: 18px;
  padding: 1.25rem;
  border: 1px solid #e2e8f0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.deck-title {
  font-weight: 700;
  letter-spacing: 0.08em;
  font-size: 0.85rem;
  color: #475569;
  margin-bottom: 0.8rem;
}

.driver-row {
  display: flex;
  justify-content: center;
  padding: 0.75rem 0;
}

.driver-row.empty {
  visibility: hidden;
}

.driver-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  background: rgba(59, 130, 246, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px dashed rgba(59, 130, 246, 0.4);
}

.seat-matrix {
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.seat-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.seat-column {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.aisle {
  width: 26px;
  min-height: 100%;
}

.vertical-divider {
  width: 2px;
  background: linear-gradient(180deg, transparent, rgba(148, 163, 184, 0.45), transparent);
}

.generic-seat-grid {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  align-items: center;
}

.generic-seat-grid .seat-row {
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.seat-modal-footer {
  width: 100%;
  margin-top: 1.5rem;
  background: #fff;
  border-radius: 18px;
  padding: 1rem 1.5rem;
  border: 1px solid #e2e8f0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.seat-modal-footer strong {
  font-size: 1.3rem;
  color: #1d4ed8;
}

.seat-modal-footer .btn-primary {
  min-width: 180px;
  border-radius: 12px;
}

.seat-modal-footer .btn-outline-secondary {
  border-radius: 12px;
}

.seat-btn.seat-aisle {
  pointer-events: none;
  background: transparent;
  border: none;
  box-shadow: none;
  min-width: clamp(32px, 6vw, 56px);
}

.seat-btn.seat-placeholder {
  opacity: 0.35;
  pointer-events: none;
}

@media (max-width: 992px) {
  .hero__glass {
    border-radius: 18px;
  }

  .search-form {
    border-radius: 18px;
  }

  .trip-price {
    text-align: left;
    margin-top: 0.75rem;
  }

  .deck-column {
    flex: 1 1 100%;
  }

  .vertical-divider {
    display: none;
  }
}

@media (max-width: 576px) {
  .hero {
    padding-top: 4.5rem;
  }

  .hero__title {
    font-size: 2rem;
  }

  .search-btn {
    font-size: 0.9rem;
    letter-spacing: 0.04em;
  }

  .seat-modal__header,
  .seat-modal__body {
    padding: 1rem;
  }

  .seat-modal-footer {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
