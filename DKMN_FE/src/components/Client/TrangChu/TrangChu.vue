<template>
  <div>
    <!-- Hero Section with Carousel -->
    <section class="hero-modern">
      <!-- Carousel Background -->
      <div class="hero-carousel">
        <div 
          v-for="(slide, index) in heroSlides" 
          :key="index"
          class="carousel-slide"
          :class="{ active: currentSlide === index }"
        >
          <img :src="slide.image" :alt="slide.title" class="carousel-bg-image" />
          <div class="slide-overlay"></div>
        </div>
      </div>

      <!-- Hero Content -->
      <div class="hero-content-wrapper">
        <!-- Animated Hero Title from Slides -->
        <div class="hero-text-animated">
          <transition name="fade-slide" mode="out-in">
            <div :key="currentSlide" class="hero-main-content">
              <span class="hero-eyebrow">✈️ Hệ thống đặt vé trực tuyến</span>
              <h1 class="hero-title">
                {{ heroSlides[currentSlide].title }}
              </h1>
              <p class="hero-subtitle-text">
                {{ heroSlides[currentSlide].subtitle }}
              </p>
            </div>
          </transition>
        </div>
        
        <!-- Hero Stats -->
        <div class="hero-stats">
          <div class="stat-item">
            <div class="stat-icon">👥</div>
            <span class="stat-number">50K+</span>
            <span class="stat-label">Khách hàng</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <div class="stat-icon">🚌</div>
            <span class="stat-number">1000+</span>
            <span class="stat-label">Chuyến/ngày</span>
          </div>
          <div class="stat-divider"></div>
          <div class="stat-item">
            <div class="stat-icon">⭐</div>
            <span class="stat-number">4.9</span>
            <span class="stat-label">Đánh giá</span>
          </div>
        </div>
      </div>

          <div id="search" class="search-form">
            <!-- Header with Tabs -->
            <div class="search-header">
              <div class="search-title">
                <i class="bx bx-search-alt"></i>
                <span>Tìm chuyến đi của bạn</span>
              </div>
              <div class="search-tabs">
                <button 
                  class="tab-btn" 
                  :class="{ active: searchType === 'one-way' }"
                  @click="searchType = 'one-way'"
                >
                  Một chiều
                </button>
                <button 
                  class="tab-btn" 
                  :class="{ active: searchType === 'round-trip' }"
                  @click="searchType = 'round-trip'"
                >
                  Khứ hồi
                </button>
              </div>
            </div>

            <!-- Main Search Inputs -->
            <div class="search-grid" :class="{ 'is-round-trip': searchType === 'round-trip' }">
              <!-- Vehicle Type -->
              <div class="input-group-modern">
                <label class="input-label">Phương tiện</label>
                <div class="input-wrapper">
                  <i class="bx input-icon" :class="{
                    'bx-bus': searchForm.vehicleType === 'bus' || !searchForm.vehicleType,
                    'bx-train': searchForm.vehicleType === 'train',
                    'bx-plane-alt': searchForm.vehicleType === 'plane'
                  }"></i>
                  <select v-model="searchForm.vehicleType" class="form-select-modern">
                    <option value="">Chọn phương tiện</option>
                    <option value="bus">Xe khách</option>
                    <option value="train">Tàu hỏa</option>
                    <option value="plane">Máy bay</option>
                  </select>
                </div>
              </div>

              <!-- From Station -->
              <div class="input-group-modern">
                <label class="input-label">Nơi đi</label>
                <div class="input-wrapper">
                  <i class="bx bx-map-pin input-icon"></i>
                  <select v-model="searchForm.from" class="form-select-modern">
                    <option value="" disabled>Chọn nơi đi</option>
                    <option v-for="city in cities" :key="city" :value="city">
                      {{ city }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Swap Button -->
              <button class="btn-swap" @click="swapLocations" title="Đổi địa điểm">
                <i class="bx bx-transfer-alt"></i>
              </button>

              <!-- To Station -->
              <div class="input-group-modern">
                <label class="input-label">Nơi đến</label>
                <div class="input-wrapper">
                  <i class="bx bx-map input-icon"></i>
                  <select v-model="searchForm.to" class="form-select-modern">
                    <option value="" disabled>Chọn nơi đến</option>
                    <option v-for="city in cities" :key="city" :value="city">
                      {{ city }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Date -->
              <div class="input-group-modern">
                <label class="input-label">Ngày đi</label>
                <div class="input-wrapper">
                  <i class="bx bx-calendar input-icon"></i>
                  <input
                    v-model="searchForm.departureDate"
                    type="date"
                    class="form-control-modern"
                  />
                </div>
              </div>

              <!-- Return Date -->
              <div class="input-group-modern" v-if="searchType === 'round-trip'">
                <label class="input-label">Ngày về</label>
                <div class="input-wrapper">
                  <i class="bx bx-calendar input-icon"></i>
                  <input
                    v-model="searchForm.returnDate"
                    type="date"
                    class="form-control-modern"
                    :min="searchForm.departureDate"
                  />
                </div>
              </div>
              
              <!-- Submit Button Desktop -->
              <button
                @click="searchTrips"
                class="search-submit-btn"
                :disabled="!isSearchValid || isLoadingTrips"
              >
                <span v-if="isLoadingTrips" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bx bx-search me-2"></i>
                {{ isLoadingTrips ? "Đang tìm..." : "Tìm chuyến ngay" }}
              </button>
            </div>

            <!-- Advanced Filters (Second Row) -->
            <div class="search-grid mt-3" style="grid-template-columns: repeat(3, 1fr);" v-if="['bus','train','plane'].includes(searchForm.vehicleType)">
               <!-- Pickup Station -->
               <div class="input-group-modern" v-if="searchForm.from">
                 <label class="input-label">{{ pickupLabel }}</label>
                 <div class="input-wrapper">
                   <i class="bx bx-current-location input-icon"></i>
                   <select v-model="searchForm.pickupStation" class="form-select-modern">
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
               </div>
               
               <!-- Dropoff Station -->
               <div class="input-group-modern" v-if="searchForm.to">
                 <label class="input-label">{{ dropoffLabel }}</label>
                 <div class="input-wrapper">
                   <i class="bx bx-target-lock input-icon"></i>
                   <select v-model="searchForm.dropoffStation" class="form-select-modern">
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
               </div>

               <!-- Passengers -->
               <div class="input-group-modern">
                 <label class="input-label">Hành khách</label>
                 <div class="input-wrapper">
                   <i class="bx bx-user input-icon"></i>
                   <select v-model="searchForm.passengers" class="form-select-modern">
                     <option value="1">1 Khách</option>
                     <option value="2">2 Khách</option>
                     <option value="3">3 Khách</option>
                     <option value="4">4 Khách</option>
                     <option value="5">5 Khách</option>
                     <option value="5+">Nhiều hơn</option>
                   </select>
                 </div>
                 <div v-if="searchForm.passengers === '5+'" class="mt-2">
                    <input
                      v-model.number="customPassengers"
                      type="number"
                      min="6"
                      class="form-control-modern"
                      placeholder="Số khách"
                      style="padding-left: 1rem"
                    />
                 </div>
               </div>
            </div>

            <div v-if="searchError" class="alert alert-danger mt-3 mb-0">
              {{ searchError }}
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

<!-- Layout cho tàu hỏa: chia khoang, hiển thị lối đi -->
<div v-else-if="seatModal.trip?.vehicleTypeKey === 'train'" class="train-seat-container">
  <div class="train-map mb-3">
    <div class="train-map__line">
      <span class="dot"></span>
      <span class="segment"></span>
      <span class="dot"></span>
    </div>
    <div class="train-map__label">Sơ đồ toa · Lối đi giữa</div>
  </div>
  <div v-if="!trainCabins.length" class="text-center text-muted py-4">
    Chưa có dữ liệu ghế cho chuyến này.
  </div>
  <div v-else class="train-cabin-grid">
    <div class="cabin-card" v-for="cabin in trainCabins" :key="cabin.cabinKey">
      <div class="cabin-card__header">
        Khoang {{ cabin.cabinKey }}
        <small class="text-muted ms-2">{{ cabin.rows.length * 2 }} chỗ</small>
      </div>
      <div class="cabin-card__body">
        <div class="cabin-row" v-for="(row, idx) in cabin.rows" :key="cabin.cabinKey + '-r' + idx">
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
  </div>
</div>

<!-- Layout máy bay / mặc định -->
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
      {{ bookingStep === 'selecting-outbound' ? 'Chọn vé chiều về' : 'Tiếp tục thanh toán' }}
    </button>
  </div>
</div>


        </div>
      </div>
    </div>

   
    
    <!-- Popular Routes Section -->
    <div v-if="!showResults" id="popular-routes">
      <PopularRoutes @select-route="handleSelectRoute" />
    </div>

    <!-- Search Results Section -->
    <section v-if="showResults" class="results-section py-5">
      <div v-if="bookingStep === 'selecting-return'" class="container mb-4">
          <div class="alert alert-primary d-flex align-items-center shadow-sm" style="border-left: 5px solid #0d6efd;">
             <i class='bx bx-calendar-check fs-2 me-3'></i>
             <div>
                <h5 class="alert-heading m-0 mb-1 fw-bold">Bước 2: Chọn chuyến về</h5>
                <p class="m-0 small opacity-75">
                  Bạn đang tìm vé: <strong>{{ searchForm.from }}</strong> đi <strong>{{ searchForm.to }}</strong> 
                  <span v-if="searchForm.departureDate"> - Ngày {{ formatRouteDate(searchForm.departureDate) }}</span>
                </p>
             </div>
             <div class="ms-auto d-none d-md-block">
               <span class="badge bg-primary">Khứ hồi</span>
            </div>
         </div>
      </div>
      <div class="container">
        <div class="row">
          <!-- Filters Sidebar -->
          <div class="col-lg-3">
            <div class="filters-card">
              <div class="filters-card__header">
                <div>
                  <span class="filters-card__eyebrow">Tùy chọn chi tiết</span>
                  <h5 class="mb-0">Bộ lọc</h5>
                </div>
                <span class="filters-card__icon">
                  <i class="bx bx-slider-alt"></i>
                </span>
              </div>

              <!-- Time Filter -->
              <div class="filter-group mb-4">
                <p class="filter-group__title">Giờ khởi hành</p>
                <label class="filter-option">
                  <input
                    class="filter-checkbox"
                    type="checkbox"
                    id="morning"
                    v-model="filters.time.morning"
                  />
                  <span>Sáng (6h-12h)</span>
                </label>
                <label class="filter-option">
                  <input
                    class="filter-checkbox"
                    type="checkbox"
                    id="afternoon"
                    v-model="filters.time.afternoon"
                  />
                  <span>Chiều (12h-18h)</span>
                </label>
                <label class="filter-option">
                  <input
                    class="filter-checkbox"
                    type="checkbox"
                    id="evening"
                    v-model="filters.time.evening"
                  />
                  <span>Tối (18h-24h)</span>
                </label>
              </div>

              <div class="filter-divider"></div>

              <!-- Price Filter -->
              <div class="filter-group mb-4">
                <p class="filter-group__title">Giá vé</p>
                <div class="price-range">
                  <input
                    type="range"
                    class="form-range"
                    :min="priceLimits.min"
                    :max="priceLimits.max"
                    :step="priceLimits.step"
                    v-model.number="filters.price.max"
                  />
                  <div class="price-range__labels">
                    <span>{{ formatPrice(priceLimits.min) }}</span>
                    <span>{{ formatPrice(filters.price.max) }}</span>
                  </div>
                </div>
              </div>

              <div class="filter-divider"></div>

              <!-- Other Filters -->
              <div class="filter-group mb-4">
                <p class="filter-group__title">Khác</p>
                <label class="filter-option">
                  <input
                    class="filter-checkbox"
                    type="checkbox"
                    id="available"
                    v-model="filters.available"
                  />
                  <span>Còn ghế</span>
                </label>
              </div>

              <!-- Company Filter (NEW - Checkbox list) -->
              <div class="filter-group mb-4" v-if="availableCompanies.length > 0">
                <p class="filter-group__title">Nhà vận hành</p>
                <div class="company-filter-list" style="max-height: 200px; overflow-y: auto;">
                  <label 
                    v-for="company in availableCompanies" 
                    :key="'filter-company-' + company"
                    class="filter-option filter-option--compact"
                  >
                    <input
                      class="filter-checkbox"
                      type="checkbox"
                      :value="company"
                      v-model="filters.companies"
                    />
                    <span>{{ company }}</span>
                  </label>
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
              <transition-group name="trip-list" tag="div">
                <div
                  v-for="trip in sortedTrips"
                  :key="trip.id"
                  class="trip-card-modern"
                >
                  <!-- Image Column -->
                  <div class="trip-card-image-col">
                    <img :src="getTripImage(trip)" class="trip-card-image" loading="lazy" alt="Vehicle Image" />
                    <div style="position: absolute; top: 12px; left: 12px; z-index: 2;">
                         <span class="badge-premium shadow-sm">{{ trip.vehicleType }}</span>
                    </div>
                  </div>

                  <!-- Content Column -->
                  <div class="trip-card-content-col">
                  <!-- Header -->
                  <div class="trip-header">
                    <div class="company-info">
                      <div class="company-logo-placeholder">
                        <i class="bx" :class="{
                          'bx-bus': trip.vehicleTypeKey === 'bus' || !trip.vehicleTypeKey,
                          'bx-train': trip.vehicleTypeKey === 'train',
                          'bx-plane-alt': trip.vehicleTypeKey === 'plane'
                        }"></i>
                      </div>
                      <div>
                        <h4 class="company-name">{{ trip.company }}</h4>
                        <div class="trip-badges">
                          <span class="badge-rating">
                            <i class="bx bxs-star"></i> {{ trip.rating }}
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="price-display">
                      <span class="price-amount">{{ formatPrice(trip.displayPrice || trip.price) }}</span>
                      <span class="price-unit">/khách</span>
                    </div>
                  </div>

                  <!-- Timeline -->
                  <div class="trip-timeline my-2">
                    <div class="timeline-point text-start">
                      <span class="time-large">{{ trip.departureTime || "--:--" }}</span>
                      <span class="location-label">{{ trip.fromStation }}</span>
                    </div>
                    
                    <div class="timeline-connector">
                      <span class="duration-badge">{{ trip.duration || "—" }}</span>
                      <div class="route-line"></div>
                      <small class="text-muted mt-1">{{ trip.seatType }}</small>
                    </div>

                    <div class="timeline-point text-end">
                      <span class="time-large">{{ trip.arrivalTime || "--:--" }}</span>
                      <span class="location-label">{{ trip.toStation }}</span>
                    </div>
                  </div>
                  
                  <!-- Sub Info (Pickup/Dropoff) -->
                  <div class="d-flex justify-content-between text-muted small mb-3 px-2">
                     <span v-if="trip.pickupStationName">Đón: {{ trip.pickupStationName }}</span>
                     <span v-if="trip.dropoffStationName" class="ms-auto">Trả: {{ trip.dropoffStationName }}</span>
                  </div>

                  <!-- Footer -->
                  <div class="card-footer-actions mt-auto">
                    <div class="trip-amenities">
                      <span class="amenity-item" v-if="trip.freeCancel">
                        <i class="bx bx-check-shield"></i> Hủy miễn phí
                      </span>
                      <span class="amenity-item">
                        <i class="bx bx-chair"></i> {{ trip.availableSeats }} ghế trống
                      </span>
                    </div>
                    <button
                      @click="selectTrip(trip)"
                      class="btn-select"
                      :disabled="isLoadingSeats"
                    >
                      {{ isLoadingSeats ? "Đang tải..." : "Chọn ghế" }}
                    </button>
                  </div>
                  </div>
                </div>
              </transition-group>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div id="features">
      <FeaturesSection />
    </div>
    <ReviewsSection />
  </div>
</template>

<script>
import api from "../../../services/api";
import FeaturesSection from "./FeaturesSection.vue";
import GioiThieu from "./GioiThieu.vue";
import ReviewsSection from "./ReviewsSection.vue";
import PopularRoutes from "./PopularRoutes.vue";
import './TrangChuPremium.css';

// Import vehicle images
import imgBus1 from '../../../assets/images/hero/bus-travel-1.png';
import imgBus2 from '../../../assets/images/hero/bus-travel-2.png';
import imgTrain from '../../../assets/images/hero/train-travel.png';
import imgPlane from '../../../assets/images/hero/plane-travel.png';

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
const PORTAL_NOTICES_ENDPOINT =
  import.meta.env?.VITE_PORTAL_NOTICES_ENDPOINT || `${API_PREFIX}/portal/thong-bao`;

export default {
  name: "TrangChu",
  components: {
    GioiThieu,
    FeaturesSection,
    ReviewsSection,
    PopularRoutes,
  },
  data() {
    return {
      cities: [],
      searchType: 'one-way', // Added for new search form UI
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
      parallax: {
        x: 0,
        y: 0,
        left: null,
        top: null,
      },
      sortBy: "default",
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
        companies: [],
      },
      priceLimits: { ...PRICE_FILTER_DEFAULTS },
      priceFilterLocked: false,
      suppressPriceWatch: false,
      trips: [],
      userInfo: {},
      heroSelectedTrip: null,
      bookingStep: 'default',
      tempOutboundData: null,
      seatModal: {
        visible: false,
        trip: null,
        seatsSelected: [],
        layout: [],
        seats: [],
      },
      portalNotices: [],
      isLoadingNotices: false,
      noticesError: "",
      currentSlide: 0,
      heroSlides: [
        {
          image: imgBus1,
          title: "Khám Phá Việt Nam",
          subtitle: "Cùng DKMN - Đặt vé xe, tàu, máy bay dễ dàng"
        },
        {
          image: imgPlane,
          title: "Bay Cùng DKMN",
          subtitle: "Săn vé máy bay giá rẻ ngay hôm nay"
        },
        {
          image: imgBus2,
          title: "Thoải Mái & An Toàn",
          subtitle: "Xe giường nằm VIP cao cấp"
        },
        {
          image: imgTrain,
          title: "Hành Trình Đáng Nhớ",
          subtitle: "Khám phá mọi miền đất nước bằng đường sắt"
        }
      ],
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
    trainCabins() {
      if (this.seatModal.trip?.vehicleTypeKey !== "train") return [];
      const seats = this.seatModal?.seats || [];
      return this.groupTrainCabins(seats);
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
    mouseGlowStyle() {
      const size = 280;
      if (this.parallax.left === null || this.parallax.top === null) {
        return { opacity: 0 };
      }
      return {
        opacity: 0.8,
        left: `${this.parallax.left - size / 2}px`,
        top: `${this.parallax.top - size / 2}px`,
        transform: `translate3d(${this.parallax.x / 18}px, ${this.parallax.y / 18}px, 0)`,
      };
    },
    heroTicketDisplay() {
      const from = (this.searchForm.from || this.heroSelectedTrip?.fromCity || "TP.HCM").toUpperCase();
      const to = (this.searchForm.to || this.heroSelectedTrip?.toCity || "Hà Nội").toUpperCase();
      const pickup =
        this.searchForm.pickupStation ||
        this.heroSelectedTrip?.pickupStationName ||
        this.heroSelectedTrip?.fromStation ||
        "";
      const dropoff =
        this.searchForm.dropoffStation ||
        this.heroSelectedTrip?.dropoffStationName ||
        this.heroSelectedTrip?.toStation ||
        "";
      const dateText =
        (this.searchForm.departureDate && this.formatRouteDate(this.searchForm.departureDate)) ||
        this.heroSelectedTrip?.departureLabel ||
        "";
      const timeText = this.heroSelectedTrip?.departureTime || "";
      const departure = [dateText, timeText].filter(Boolean).join(" · ");
      const type = this.searchForm.vehicleType || this.heroSelectedTrip?.vehicleTypeKey || "plane";
      return {
        from,
        to,
        departure,
        pickup,
        dropoff,
        iconClass: this.heroVehicleIcon(type),
      };
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
    scrollToSearch() {
      this.$nextTick(() => {
        const el = document.getElementById("search");
        el?.scrollIntoView({ behavior: "smooth", block: "start" });
      });
    },
    heroVehicleIcon(type) {
      const key = this.normalizeVehicleType(type) || "plane";
      switch (key) {
        case "bus":
          return "bx bx-bus";
        case "train":
          return "bx bx-train";
        default:
          return "bx bxs-plane-alt";
      }
    },
    handleParallax(evt) {
      if (!evt?.currentTarget) return;
      const rect = evt.currentTarget.getBoundingClientRect();
      const x = evt.clientX - rect.left - rect.width / 2;
      const y = evt.clientY - rect.top - rect.height / 2;
      this.parallax.x = x;
      this.parallax.y = y;
      this.parallax.left = evt.clientX - rect.left;
      this.parallax.top = evt.clientY - rect.top;
    },
    resetParallax() {
      this.parallax = { x: 0, y: 0, left: null, top: null };
    },
    parallaxLayer(depth = 24) {
      const factor = depth || 24;
      return {
        transform: `translate3d(${this.parallax.x / factor}px, ${this.parallax.y / factor}px, 0)`,
      };
    },
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
        trip?.raw?.departureTime,
        trip?.raw?.departure_time,
        trip?.departureDateTime,
        trip?.raw?.departureDateTime,
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
    /**
     * Khởi tạo dữ liệu ban đầu cho trang chủ.
     * 
     * Logic:
     * 1. Gọi song song `fetchCities` và `fetchCompanies` để lấy danh sách tỉnh thành và nhà xe.
     * 2. Gọi `fetchStationsFor` cho cả điểm đi và điểm đến (nếu có default).
     * 3. Gọi `fetchPortalNotices` để lấy thông báo từ hệ thống.
     */
    async bootstrapData() {
      await Promise.all([this.fetchCities(), this.fetchCompanies()]);
      this.fetchStationsFor("from");
      this.fetchStationsFor("to");
      this.fetchPortalNotices();
    },
    /**
     * Lấy danh sách tỉnh thành phố.
     * 
     * API: `GET /dkmn/tinh-thanh/get-data`
     * Backend Controller: `TinhThanhController::getData` (dự đoán)
     * 
     * Logic:
     * - Gọi API lấy danh sách.
     * - Map dữ liệu vào `this.cities` để hiển thị dropdown.
     * - Tạo `cityIdMap` để map từ tên thành phố sang ID khi search.
     * - Fallback: Sử dụng danh sách mặc định (Hà Nội, TP.HCM, Đà Nẵng) nếu API lỗi.
     */
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
    /**
     * Lấy danh sách bến xe/ga/sân bay theo tỉnh thành.
     * 
     * @param {string} position - 'from' (điểm đi) hoặc 'to' (điểm đến).
     * 
     * API: `GET /dkmn/tram/get-data`
     * Backend Controller: `TramController::getData` (dự đoán)
     * 
     * Logic:
     * - Xác định loại trạm (bến xe, ga tàu, sân bay) dựa trên `vehicleType`.
     * - Lấy ID tỉnh thành từ `cityIdMap`.
     * - Gọi API với tham số `tinh_thanh_id` và `loai`.
     * - Cập nhật `pickupStations` hoặc `dropoffStations` tương ứng.
     */
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
    /**
     * Lấy danh sách nhà vận hành (nhà xe, hãng bay, đường sắt).
     * 
     * API: `GET /dkmn/nha-van-hanh/get-data`
     * Backend Controller: `NhaVanHanhController::getData` (dự đoán)
     * 
     * Logic:
     * - Gọi API, có thể filter theo loại phương tiện nếu cần.
     * - Cập nhật `this.companies` cho dropdown filter.
     * - Tạo `companyIdMap` để map tên sang ID.
     */
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
    async fetchPortalNotices() {
      this.isLoadingNotices = true;
      this.noticesError = "";
      try {
        const token =
          localStorage.getItem("token") || localStorage.getItem("key_client");
        const userInfo = localStorage.getItem("userInfo");
        const endpoint =
          token && userInfo ? `${API_PREFIX}/thong-bao` : PORTAL_NOTICES_ENDPOINT;

        const { data } = await api.get(endpoint);
        const list = data?.data || data || [];
        this.portalNotices = list
          .map((item, idx) => ({
            id: item.id || item.ma || idx,
            title: item.title || item.subject || item.tieu_de || "Thông báo",
            message: item.message || item.noi_dung || item.content || "",
            createdAt:
              item.createdAt ||
              item.created_at ||
              item.ngay_tao ||
              item.ngay_gui ||
              null,
          }))
          .filter((n) => n.message);
      } catch (error) {
        console.error("fetchPortalNotices error", error);
        this.noticesError = "Không thể tải thông báo.";
      } finally {
        this.isLoadingNotices = false;
      }
    },
    formatNoticeTime(ts) {
      if (!ts) return "";
      try {
        const d = new Date(ts);
        if (Number.isNaN(d.getTime())) return ts;
        return d.toLocaleString("vi-VN");
      } catch (error) {
        return ts;
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
    /**
     * Tìm kiếm chuyến đi theo tiêu chí.
     * 
     * API: `POST /dkmn/chuyen-di/search`
     * Backend Controller: `ChuyenDiController::search` (dự đoán)
     * 
     * Logic:
     * 1. Validate form (nơi đi, nơi đến, ngày đi, số khách).
     * 2. Chuẩn bị payload:
     *    - Map tên thành phố/trạm sang ID tương ứng.
     *    - Format ngày tháng.
     * 3. Gọi API search.
     * 4. Xử lý kết quả:
     *    - Map dữ liệu trả về qua `normalizeTrip` để chuẩn hóa field name.
     *    - Cập nhật `this.trips` và `this.meta`.
     *    - Tính toán lại `priceLimits` cho bộ lọc giá.
     *    - Scroll xuống phần kết quả.
     * 5. Xử lý lỗi: Hiển thị toast error.
     */
    async searchTrips() {
      if (!this.isSearchValid || this.isLoadingTrips) return;

      this.searchError = "";

      // Round Trip Logic Initialization
      if (this.searchType === 'round-trip') {
          if (!this.searchForm.returnDate && this.bookingStep !== 'selecting-return') {
              this.searchError = "Vui lòng chọn ngày về cho vé khứ hồi";
              this.$toast.error(this.searchError);
              return;
          }
           if (this.bookingStep === 'default' || this.bookingStep === 'selecting-return') {
              // Only reset to outbound if not already in flow? 
              // Actually if user clicks Search manually, they might want to restart?
              // If manually clicking search while in 'selecting-return', assume restart or re-filter?
              // Let's assume restart if inputs changed significantly, but for now force outbound start if 'default'.
              if (this.bookingStep === 'default') {
                  this.bookingStep = 'selecting-outbound';
              }
          }
      } else {
          this.bookingStep = 'default';
      }

      this.isLoadingTrips = true;
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
        this.heroSelectedTrip = this.trips[0] || null;
        this.updatePriceLimitsFromTrips(this.trips);
        this.showResults = true;

        if (!this.trips.length) {
          const message =
            data?.message ||
            "Không tìm thấy chuyến phù hợp, vui lòng thử lại.";
          this.$toast?.info?.(message);
        } else {
          this.$toast.success(`Đã tìm thấy ${this.trips.length} chuyến phù hợp! 🎉`);
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
        this.heroSelectedTrip = null;
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
        raw.departureTime ||
          raw.gio_khoi_hanh ||
          raw.departureDateTime ||
          raw.departure_time ||
          raw.depart_at ||
          raw.departTime
      );
      const arrivalTime = this.extractTime(
        raw.arrivalTime ||
          raw.gio_den ||
          raw.arrivalDateTime ||
          raw.arrival_time ||
          raw.arrive_at
      );

      const departureDateText = this.extractDate(
        raw.ngay_khoi_hanh ||
          raw.departure_date ||
          raw.depart_date ||
          raw.departureDate ||
          raw.departureDateTime ||
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
    /**
     * Chọn chuyến đi và tải sơ đồ ghế.
     * 
     * @param {Object} trip - Thông tin chuyến đi được chọn.
     * 
     * API: `GET /dkmn/chuyen-di/{id}/ghe`
     * Backend Controller: `ChuyenDiController::getSeats` (dự đoán)
     * 
     * Logic:
     * 1. Gọi API lấy thông tin chi tiết và sơ đồ ghế của chuyến đi.
     * 2. Parse dữ liệu ghế (`getSeatEntries`, `buildSeatLayout`).
     * 3. Mở modal chọn ghế (`seatModal.visible = true`).
     * 4. Cập nhật state `seatModal` với layout và danh sách ghế.
     */
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
        this.heroSelectedTrip = normalizedTrip;
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
      const cabinLayouts = this.groupTrainCabins(seats);

      const rows = [];
      for (let i = 0; i < cabinLayouts.length; i += 2) {
        const leftCabin = cabinLayouts[i];
        const rightCabin = cabinLayouts[i + 1];
        const maxRows = Math.max(
          leftCabin.rows.length,
          rightCabin ? rightCabin.rows.length : 0
        );
        for (let r = 0; r < maxRows; r++) {
          const leftRow = leftCabin.rows[r] || this.fillSeatRow([], 2, "train");
          const rightRow = rightCabin
            ? rightCabin.rows[r] || this.fillSeatRow([], 2, "train")
            : [];
          const aisle = this.createAislePlaceholder(rows.length);
          rows.push([...leftRow, aisle, ...rightRow]);
        }
        // thêm hàng trống phân tách giữa các cụm khoang cho dễ nhìn
        if (i + 2 < cabinLayouts.length) {
          rows.push([
            ...this.fillSeatRow([], 2, "train"),
            this.createAislePlaceholder(rows.length),
            ...this.fillSeatRow([], 2, "train"),
          ]);
        }
      }
      return rows;
    },
    groupTrainCabins(seats = []) {
      const cabinsMap = seats.reduce((acc, seat, index) => {
        const label =
          seat.label || seat.ma_ghe || seat.so_ghe || seat.id || `T${index + 1}`;
        const match = /^([A-Za-z]+)(\d+)/.exec(String(label).trim());
        const cabinKey = match ? match[1].toUpperCase() : "CABIN";
        const seatNumber = match ? Number(match[2]) || index + 1 : index + 1;

        if (!acc[cabinKey]) acc[cabinKey] = [];
        acc[cabinKey].push({
          ...seat,
          cabinKey,
          seatNumber,
        });
        return acc;
      }, {});

      const cabins = Object.entries(cabinsMap).sort(([a], [b]) =>
        a.localeCompare(b)
      );

      return cabins.map(([cabinKey, list]) => {
        const sorted = [...list].sort(
          (a, b) => (a.seatNumber || 0) - (b.seatNumber || 0)
        );
        const rows = [];
        for (let i = 0; i < sorted.length; i += 2) {
          const chunk = sorted.slice(i, i + 2);
          rows.push(this.fillSeatRow(chunk, 2, "train"));
        }
        return { cabinKey, rows };
      });
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
        this.$toast.info(`Đã bỏ chọn ghế ${seat.label || seat.id}`);
      } else {
        if (this.seatModal.seatsSelected.length >= maxSelect) {
          this.$toast.warning(`Bạn chỉ có thể chọn tối đa ${maxSelect} ghế!`);
          return;
        }
        this.seatModal.seatsSelected.push(seat.id);
        this.$toast.success(`Đã chọn ghế ${seat.label || seat.id} ✓`);
      }
    },
    cancelSeatSelection() {
      this.seatModal.visible = false;
      this.seatModal.trip = null;
      this.seatModal.seatsSelected = [];
      this.seatModal.layout = [];
      this.seatModal.seats = [];
    },
    /**
     * Xác nhận chọn ghế và chuyển sang trang thanh toán.
     * 
     * Logic:
     * 1. Kiểm tra số lượng ghế đã chọn.
     * 2. Tính tổng tiền tạm tính.
     * 3. Format danh sách ghế đã chọn (tên ghế, số ghế).
     * 4. Điều hướng sang `/client-thanh-toan` với các query params:
     *    - `tripId`: ID chuyến đi.
     *    - `seats`: Danh sách ghế (chuỗi).
     *    - `total`: Tổng tiền.
     *    - Các thông tin khác (nơi đi, đến, ngày giờ...).
     */
    confirmSeats() {
      const selectedCount = (this.seatModal?.seatsSelected || []).length;
      if (selectedCount === 0) {
        this.$toast.error("Vui lòng chọn ghế trước khi tiếp tục thanh toán.");
        return;
      }

      // ROUND TRIP LOGIC: PHASE 1 (Select Outbound)
      if (this.searchType === 'round-trip' && this.bookingStep === 'selecting-outbound') {
          this.tempOutboundData = {
              trip: this.seatModal.trip,
              seats: [...this.seatModal.seatsSelected],
              pickupStation: this.searchForm.pickupStation,
              dropoffStation: this.searchForm.dropoffStation
          };
          
          this.cancelSeatSelection();
          
          // Prepare for Inbound Search
          this.bookingStep = 'selecting-return';
          
          // Swap Locations
          const oldFrom = this.searchForm.from;
          const oldTo = this.searchForm.to;
          this.searchForm.from = oldTo;
          this.searchForm.to = oldFrom;
          
          // Set Date to Return Date
          this.$toast.info("Đang tìm chuyến về...");
          this.searchForm.departureDate = this.searchForm.returnDate; // Trigger reactive search? No, explicitly call searchTrips
          
          this.searchTrips();
          
          // Toast and Scroll
          this.$toast.success("Đã chọn chiều đi. Vui lòng chọn chuyến về.");
           this.$nextTick(() => {
             window.scrollTo({ top: 0, behavior: 'smooth' });
           });
          return;
      }

      // ROUND TRIP LOGIC: PHASE 2 (Select Inbound / Finalize)
      if (this.bookingStep === 'selecting-return' && this.tempOutboundData) {
          localStorage.setItem('pending_round_trip', JSON.stringify({
              outbound: this.tempOutboundData,
              inbound: {
                  trip: this.seatModal.trip,
                  seats: [...this.seatModal.seatsSelected],
                  pickupStation: this.searchForm.pickupStation,
                  dropoffStation: this.searchForm.dropoffStation
              }
          }));
          // Continue to generic payment flow (which handles singular trip)
          // Ideally backend/payment page reads `pending_round_trip` from localStorage
      }

      this.$toast.success(`Đang chuyển đến trang thanh toán với ${selectedCount} ghế đã chọn...`);
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
    startCarousel() {
      this.carouselInterval = setInterval(() => {
        this.currentSlide = (this.currentSlide + 1) % this.heroSlides.length;
      }, 5000);
    },
    stopCarousel() {
      if (this.carouselInterval) {
        clearInterval(this.carouselInterval);
      }
    },
    swapLocations() {
      const temp = this.searchForm.from;
      this.searchForm.from = this.searchForm.to;
      this.searchForm.to = temp;
      
      // Clear stations when swapping as they might not be valid for new locations
      this.searchForm.pickupStation = "";
      this.searchForm.dropoffStation = "";
    },
    getTripImage(trip) {
       if (trip.vehicleTypeKey === 'train') return imgTrain;
       if (trip.vehicleTypeKey === 'plane') return imgPlane;
       return (trip.id % 2 === 0) ? imgBus1 : imgBus2;
    },
    handleSelectRoute(route) {
       this.searchForm.vehicleType = route.vehicleTypeKey;
       this.searchForm.from = route.from;
       this.searchForm.to = route.to;
       
       // Scroll to search form (top)
       window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    checkAuthStatus() {
      const token = localStorage.getItem("token");
      const userInfo = localStorage.getItem("userInfo");

      if (token && userInfo) {
        try {
          this.userInfo = JSON.parse(userInfo);
          this.isLoggedIn = true;
        } catch (e) {
          console.error("Lỗi parsing userInfo:", e);
          this.isLoggedIn = false;
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
    this.startCarousel();
    
    // Hiển thị toast chào mừng
    setTimeout(() => {
      const userName = this.userInfo?.ho_ten || this.userInfo?.ten || '';
      if (userName) {
        this.$toast.success(`Chào mừng trở lại, ${userName}! 👋`);
      } else {
        this.$toast.info('Chào mừng đến với hệ thống đặt vé DKMN! 🎫');
      }
    }, 500);
  },
  beforeUnmount() {
    this.stopCarousel();
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
  background:
    radial-gradient(circle at 12% 14%, rgba(56, 189, 248, 0.18), transparent 30%),
    radial-gradient(circle at 82% 18%, rgba(45, 212, 191, 0.16), transparent 32%),
    linear-gradient(180deg, #040d1f 0%, #051a31 42%, #082744 72%, #0b2f57 100%);
  min-height: 100vh;
}

/* Hero Modern with Carousel */
.hero-modern {
  position: relative;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  overflow: hidden;
  padding-top: 80px;
}

.hero-carousel {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
}

.carousel-slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 1.5s ease-in-out;
}

.carousel-slide.active {
  opacity: 1;
}

.carousel-bg-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  animation: kenBurns 20s ease-in-out infinite alternate;
}

@keyframes kenBurns {
  0% { transform: scale(1); }
  100% { transform: scale(1.1); }
}

.slide-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(0, 70, 150, 0.4), rgba(0, 100, 200, 0.4));
  z-index: 1;
}

.hero-content-wrapper {
  position: relative;
  z-index: 2;
  text-align: center;
  margin-bottom: 3rem;
  max-width: 800px;
  padding: 0 20px;
}

.hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  padding: 10px 24px;
  border-radius: 50px;
  color: white;
  margin-bottom: 24px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  font-weight: 500;
  letter-spacing: 0.5px;
}

.pulse {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #4ade80;
  box-shadow: 0 0 0 rgba(74, 222, 128, 0.4);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.4); }
  70% { box-shadow: 0 0 0 10px rgba(74, 222, 128, 0); }
  100% { box-shadow: 0 0 0 0 rgba(74, 222, 128, 0); }
}

.hero__title-highlight {
  color: #fbbf24;
  font-weight: 700;
  text-transform: uppercase;
}

.hero__subtitle {
  font-size: 1.1rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Ensure Search Form has correct z-index */
.hero__search {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
}
.hero__stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.9rem;
}

.hero__stat {
  padding: 0.75rem 0.95rem;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
}

.hero__stat-value {
  display: block;
  font-size: 1.4rem;
  font-weight: 800;
  color: #8bf0ff;
}

.hero__stat-label {
  color: #cbd5e1;
  font-size: 0.9rem;
}

.hero__scene-block {
  position: relative;
  min-height: 360px;
}

.hero__scene {
  position: relative;
  min-height: 360px;
  border-radius: 26px;
  background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.12), rgba(4, 13, 31, 0.9));
  border: 1px solid rgba(59, 130, 246, 0.28);
  overflow: hidden;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.4);
}

.hero__floating-squares {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.floating-square {
  position: absolute;
  width: clamp(60px, 7vw, 90px);
  height: clamp(60px, 7vw, 90px);
  border-radius: 18px;
  background: linear-gradient(140deg, rgba(59, 130, 246, 0.3), rgba(14, 165, 233, 0.1));
  border: 1px solid rgba(125, 211, 252, 0.25);
  box-shadow: 0 20px 50px rgba(6, 182, 212, 0.25);
  filter: drop-shadow(0 10px 25px rgba(59, 130, 246, 0.35));
  backdrop-filter: blur(4px);
  animation: squareOrbit 14s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

.floating-square::after {
  content: "";
  position: absolute;
  inset: 18%;
  border-radius: 12px;
  border: 1px dashed rgba(255, 255, 255, 0.4);
  opacity: 0.5;
}

.floating-square--primary {
  top: 18%;
  left: 14%;
  animation-duration: 18s;
  animation-delay: -2s;
}

.floating-square--secondary {
  bottom: 18%;
  right: 16%;
  animation-duration: 16s;
  animation-delay: -6s;
  background: linear-gradient(140deg, rgba(14, 165, 233, 0.35), rgba(99, 102, 241, 0.2));
  animation-name: squareOrbitReverse;
}

.floating-square--tertiary {
  top: 12%;
  right: 10%;
  width: clamp(44px, 5vw, 70px);
  height: clamp(44px, 5vw, 70px);
  border-radius: 14px;
  animation-duration: 20s;
  animation-delay: -10s;
  background: linear-gradient(140deg, rgba(236, 72, 153, 0.4), rgba(59, 130, 246, 0.18));
  animation-name: squareBob;
}

.hero__rings {
  position: absolute;
  inset: 10% 14%;
  border-radius: 50%;
}

.ring {
  position: absolute;
  inset: 0;
  border-radius: 50%;
  border: 1px solid rgba(59, 130, 246, 0.35);
  animation: ringSpin 26s linear infinite;
}

.ring--two {
  inset: 12%;
  border-color: rgba(16, 185, 129, 0.35);
  animation-duration: 30s;
}

.hero__flight-path {
  position: absolute;
  inset: 12% 10% auto 10%;
  height: 52%;
}

.hero__plane {
  position: absolute;
  top: 50%;
  left: 10%;
  width: 86px;
  height: 86px;
  border-radius: 24px;
  display: grid;
  place-items: center;
  color: #0b1a2d;
  background: linear-gradient(135deg, #22d3ee, #38bdf8, #c084fc);
  box-shadow: 0 24px 50px rgba(34, 211, 238, 0.45);
  animation: planeFlight 8s ease-in-out infinite;
}

.hero__merge-line {
  position: absolute;
  left: 6%;
  right: 6%;
  bottom: 18%;
  height: 6px;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(34, 211, 238, 0.1), rgba(59, 130, 246, 0.32), rgba(94, 234, 212, 0.14));
  box-shadow: 0 0 25px rgba(14, 165, 233, 0.25);
}

.hero__merge-dot {
  position: absolute;
  inset: -10px auto auto 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: radial-gradient(circle, #7ce7ff, rgba(14, 165, 233, 0.3));
  box-shadow: 0 0 30px rgba(14, 165, 233, 0.7);
}

.hero__vehicle {
  position: absolute;
  bottom: -18px;
  width: 74px;
  height: 74px;
  border-radius: 20px;
  display: grid;
  place-items: center;
  color: #0b1224;
  font-size: 1.6rem;
}

.hero__vehicle--bus {
  left: -12%;
  background: linear-gradient(135deg, #0ea5e9, #22d3ee);
  box-shadow: 0 18px 36px rgba(34, 211, 238, 0.4);
  animation: busMerge 7s ease-in-out infinite;
}

.hero__vehicle--train {
  right: -12%;
  background: linear-gradient(135deg, #c084fc, #60a5fa);
  box-shadow: 0 18px 36px rgba(96, 165, 250, 0.4);
  animation: trainMerge 7s ease-in-out infinite;
}

.hero__wave {
  position: absolute;
  right: 8%;
  bottom: 8%;
  width: 120px;
  height: 120px;
  border-radius: 30px;
  background: radial-gradient(circle, rgba(14, 165, 233, 0.2), rgba(14, 116, 144, 0.18));
  backdrop-filter: blur(4px);
  overflow: hidden;
}

.hero__wave::before {
  content: "";
  position: absolute;
  inset: 0;
  background: repeating-linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.12) 0,
    rgba(255, 255, 255, 0.12) 6px,
    transparent 6px,
    transparent 14px
  );
  animation: waveMove 6s linear infinite;
}

.hero__ship {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  color: #e0f2fe;
  font-size: 1.6rem;
  animation: shipWave 8s ease-in-out infinite;
}

.hero__particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.hero__particles span {
  position: absolute;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.6);
  box-shadow: 0 0 12px rgba(134, 239, 172, 0.7);
  animation: particleFloat 6s ease-in-out infinite;
}

.hero__particles span:nth-child(1) {
  top: 24%;
  left: 18%;
  animation-duration: 7s;
}
.hero__particles span:nth-child(2) {
  top: 42%;
  right: 22%;
  animation-delay: 1s;
}
.hero__particles span:nth-child(3) {
  bottom: 20%;
  left: 28%;
  animation-duration: 8s;
}
.hero__particles span:nth-child(4) {
  bottom: 32%;
  right: 14%;
  animation-delay: 2s;
}

.hero__cursor-glow {
  position: absolute;
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(34, 211, 238, 0.32), rgba(14, 165, 233, 0));
  filter: blur(24px);
  pointer-events: none;
  transform: translate3d(0, 0, 0);
  mix-blend-mode: screen;
  transition: opacity 0.2s ease;
  z-index: 1;
}

.hero__search {
  margin-top: clamp(1.5rem, 4vw, 2.5rem);
  position: relative;
  z-index: 2;
}

.notice-section {
  background: linear-gradient(140deg, rgba(11, 18, 36, 0.92), rgba(15, 23, 42, 0.88));
  border: 1px solid rgba(96, 165, 250, 0.25);
  border-radius: 18px;
  padding: 16px;
  margin: 20px auto 10px;
  max-width: 1100px;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
}
.notice-header h5 {
  font-weight: 700;
  color: #e2e8f0;
}
.notice-eyebrow {
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #60a5fa;
  font-size: 0.78rem;
}
.notice-card {
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(17, 24, 39, 0.95) 100%);
  padding: 12px;
}
.notice-list {
  display: grid;
  gap: 10px;
}
.notice-item {
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(96, 165, 250, 0.22);
  background: rgba(255, 255, 255, 0.03);
  box-shadow: 0 8px 20px rgba(5, 9, 20, 0.3);
}
.notice-item__title {
  font-weight: 700;
  color: #e2e8f0;
}
.notice-item__body {
  font-size: 0.95rem;
  margin: 4px 0;
  color: #cbd5e1;
}
.notice-item__meta {
  color: #94a3b8;
}

.search-form {
  background: #FFFFFF;
  border-radius: 20px;
  padding: clamp(1.5rem, 3vw, 2.5rem);
  border: none;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
  max-width: 1200px;
  margin: 0 auto;
}

.search-form .form-label {
  color: #374151;
  font-size: 13px;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
}

.search-form .form-select,
.search-form .form-control {
  background: #FFFFFF;
  border: 2px solid #E5E7EB;
  border-radius: 10px;
  color: #1F2937;
  min-height: 48px;
  padding: 12px 16px;
  font-size: 15px;
  transition: all 0.3s ease;
}

.search-form .form-select:focus,
.search-form .form-control:focus {
  border-color: #0064D2;
  box-shadow: 0 0 0 4px rgba(0, 100, 210, 0.1);
  outline: none;
  background: #FFFFFF;
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

.filters-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.filters-card__eyebrow {
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #94a3b8;
  display: block;
  margin-bottom: 0.2rem;
}

.filters-card__icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #c7d2fe, #a5b4fc);
  color: #312e81;
  display: grid;
  place-items: center;
  font-size: 1.25rem;
  box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25);
}

.filter-group__title {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #475569;
  font-weight: 700;
  margin-bottom: 0.85rem;
}

.filter-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.4), transparent);
  margin: 0.5rem 0 1.25rem;
}

.filter-option {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin-bottom: 0.6rem;
  padding: 0.55rem 0.75rem;
  border-radius: 12px;
  background: rgba(148, 163, 184, 0.12);
  cursor: pointer;
  transition: background 0.2s ease, transform 0.2s ease;
}

.filter-option span {
  font-size: 0.92rem;
  color: #1f2937;
  font-weight: 500;
}

.filter-option .filter-checkbox:checked + span {
  color: #1d4ed8;
  font-weight: 700;
}

.filter-option:hover {
  background: rgba(99, 102, 241, 0.15);
  transform: translateX(3px);
}

.filter-option--compact {
  padding: 0.4rem 0.6rem;
  margin-bottom: 0.35rem;
}

.filter-option__label {
  margin-bottom: 0;
  font-size: 0.9rem;
  color: #1f2937;
  width: 100%;
  cursor: pointer;
}

.filter-checkbox {
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 6px;
  border: 2px solid #94a3b8;
  background: #fff;
  transition: border 0.2s ease, background 0.2s ease;
  position: relative;
}

.filter-checkbox:checked {
  border-color: #4f46e5;
  background: #4f46e5;
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
}

.filter-checkbox:checked::after {
  content: "";
  position: absolute;
  top: 50%;
  left: 50%;
  width: 6px;
  height: 10px;
  border: solid #fff;
  border-width: 0 2px 2px 0;
  transform: translate(-50%, -60%) rotate(45deg);
}

.price-range {
  padding: 0.5rem 0.5rem 0.25rem;
  background: rgba(148, 163, 184, 0.12);
  border-radius: 16px;
}

.price-range .form-range {
  accent-color: #2563eb;
}

.price-range .form-range::-webkit-slider-thumb {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
  border: none;
}

.price-range .form-range::-webkit-slider-runnable-track {
  height: 4px;
  background: #e2e8f0;
  border-radius: 999px;
}

.price-range .form-range::-moz-range-thumb {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: linear-gradient(135deg, #2563eb, #7c3aed);
  border: none;
  box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
}

.price-range .form-range::-moz-range-track {
  height: 4px;
  background: #e2e8f0;
  border-radius: 999px;
}

.price-range__labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  color: #475569;
  font-weight: 600;
}

.trip-card {
  border-radius: 26px;
  padding: 1.75rem;
  background: linear-gradient(135deg, #ffffff, #eef2ff);
  border: 1px solid rgba(99, 102, 241, 0.1);
  box-shadow: 0 25px 55px rgba(15, 23, 42, 0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border 0.25s ease;
}

.trip-card:hover {
  transform: translateY(-6px);
  border-color: rgba(79, 70, 229, 0.4);
  box-shadow: 0 35px 70px rgba(79, 70, 229, 0.2);
}

.trip-card__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.vehicle-badge {
  background: rgba(59, 130, 246, 0.15);
  color: #1d4ed8;
  font-weight: 600;
  border-radius: 999px;
  padding: 0.15rem 0.75rem;
  font-size: 0.78rem;
}

.trip-card__rating {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-weight: 600;
  color: #f59e0b;
  font-size: 1rem;
  background: rgba(245, 158, 11, 0.12);
  padding: 0.4rem 0.75rem;
  border-radius: 999px;
}

.trip-card__body {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.trip-schedule {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
  align-items: flex-start;
}

.schedule-point {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  color: #475569;
}

.schedule-point .time {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
}

.schedule-point .station {
  font-weight: 600;
  color: #1e293b;
}

.schedule-divider {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  color: #475569;
  font-weight: 600;
}

.divider-line {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.divider-line .line {
  width: 70px;
  height: 2px;
  background: linear-gradient(90deg, #a5b4fc, #312e81);
  border-radius: 999px;
}

.divider-line .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #4338ca;
  box-shadow: 0 0 0 4px rgba(67, 56, 202, 0.15);
}

.trip-card__meta {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
}

.meta-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.meta-badge {
  font-size: 0.8rem;
  font-weight: 600;
  color: #475569;
  background: rgba(148, 163, 184, 0.2);
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
}

.meta-badge.available {
  background: rgba(14, 165, 233, 0.18);
  color: #0369a1;
}

.meta-badge.success {
  background: rgba(34, 197, 94, 0.2);
  color: #15803d;
}

.trip-card__price {
  min-width: 220px;
  text-align: right;
  background: rgba(59, 130, 246, 0.08);
  padding: 1rem 1.5rem;
  border-radius: 20px;
  color: #1d4ed8;
  font-weight: 700;
}

.trip-card__price .price {
  font-size: 1.6rem;
}

.btn-select {
  width: 100%;
  border: none;
  border-radius: 14px;
  padding: 0.6rem 1rem;
  background: linear-gradient(120deg, #2563eb, #5b21b6);
  color: #fff;
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-select:not(:disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 20px 35px rgba(79, 70, 229, 0.35);
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
.generic-seat-container,
.train-seat-container {
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

.train-seat-container {
  background: linear-gradient(180deg, #f5f7fb 0%, #fff 70%);
}
.train-map {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}
.train-map__line {
  display: flex;
  align-items: center;
  gap: 8px;
}
.train-map__line .dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #4f46e5;
  box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
}
.train-map__line .segment {
  width: 120px;
  height: 6px;
  border-radius: 999px;
  background: linear-gradient(90deg, #4f46e5, #7c3aed);
}
.train-map__label {
  font-weight: 600;
  color: #4b5563;
}
.train-cabin-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
}
.cabin-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.07);
  overflow: hidden;
}
.cabin-card__header {
  padding: 10px 12px;
  font-weight: 700;
  background: linear-gradient(90deg, #4f46e5, #6366f1);
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.cabin-card__body {
  padding: 10px;
}
.cabin-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.cabin-row .seat-btn {
  width: 100%;
  min-height: 42px;
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

  .hero__grid-modern {
    grid-template-columns: 1fr;
  }

  .hero__scene {
    min-height: 300px;
  }

  .search-form {
    border-radius: 18px;
  }

  .trip-schedule {
    grid-template-columns: 1fr;
  }

  .schedule-point {
    text-align: left !important;
  }

  .trip-card__meta {
    flex-direction: column;
    align-items: flex-start;
  }

  .trip-card__price {
    width: 100%;
    text-align: left;
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

  .hero__actions {
    flex-direction: column;
  }

  .hero-btn {
    width: 100%;
    text-align: center;
  }

  .hero__ticket-card {
    width: 100%;
  }

  .hero__scene {
    min-height: 260px;
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
