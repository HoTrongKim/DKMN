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
     <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-semibold mb-3 px-3 py-2">
       <h5 class="text-white">DKMN • Đặt vé chuyến đi</h5>
     </span>
     <h1 class="hero__title">Tìm và đặt vé <span class="text-primary">dễ dàng</span></h1>
     <p class="hero__subtitle text-white">Hệ thống đặt vé xe khách, tàu hỏa, máy bay với giá tốt nhất</p>
   </div>

   <!-- 🔍 Search Form -->
   <div id="search" class="search-form">
     <div class="row g-3">
       <!-- Vehicle Type -->
       <div class="col-md-3">
         <label class="form-label text-white fw-semibold">Loại phương tiện</label>
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
           <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
         </select>
       </div>

       <!-- To -->
       <div class="col-md-3">
         <label class="form-label text-white fw-semibold">Nơi đến</label>
         <select v-model="searchForm.to" class="form-select">
           <option value="" disabled>Chọn nơi đến</option>
           <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
         </select>
       </div>

       <!-- Departure Date -->
       <div class="col-md-3">
         <label class="form-label text-white fw-semibold">Ngày đi</label>
         <input v-model="searchForm.departureDate" type="date" class="form-control" />
       </div>
     </div>

     <div class="row g-3 mt-2">
       <!-- Return Date -->
       <div class="col-md-3">
         <label class="form-label text-white fw-semibold">Ngày về (tùy chọn)</label>
         <input v-model="searchForm.returnDate" type="date" class="form-control" />
       </div>

       <!-- Passengers -->
       <div class="col-md-3">
         <label class="form-label text-white fw-semibold">Số hành khách</label>
         <select v-model="searchForm.passengers" class="form-select">
           <option value="1">1 hành khách</option>
           <option value="2">2 hành khách</option>
           <option value="3">3 hành khách</option>
           <option value="4">4 hành khách</option>
           <option value="5">5+ hành khách</option>
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
             <input class="form-check-input" type="checkbox" id="morning" v-model="filters.time.morning">
             <label class="form-check-label" for="morning">Sáng (6h-12h)</label>
           </div>
           <div class="form-check">
             <input class="form-check-input" type="checkbox" id="afternoon" v-model="filters.time.afternoon">
             <label class="form-check-label" for="afternoon">Chiều (12h-18h)</label>
           </div>
           <div class="form-check">
             <input class="form-check-input" type="checkbox" id="evening" v-model="filters.time.evening">
             <label class="form-check-label" for="evening">Tối (18h-24h)</label>
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
             >
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
             <input class="form-check-input" type="checkbox" id="available" v-model="filters.available">
             <label class="form-check-label" for="available">Còn ghế</label>
           </div>
           <div class="form-check">
             <input class="form-check-input" type="checkbox" id="freeCancel" v-model="filters.freeCancel">
             <label class="form-check-label" for="freeCancel">Hủy miễn phí</label>
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
                 <div class="d-flex justify-content-between align-items-start mb-2">
                   <div>
                     <h6 class="mb-1">{{ trip.company }}</h6>
                     <small class="text-muted">{{ trip.vehicleType }}</small>
                   </div>
                   <div class="rating">
                     <i class="bx bxs-star text-warning"></i>
                     <span>{{ trip.rating }}</span>
                   </div>
                 </div>
                 
                 <div class="trip-details">
                   <div class="d-flex align-items-center">
                     <div class="time-info">
                       <div class="departure-time">{{ trip.departureTime }}</div>
                       <div class="station">{{ trip.fromStation }}</div>
                     </div>
                     
                     <div class="trip-route">
                       <div class="duration">{{ trip.duration }}</div>
                       <div class="route-line"></div>
                     </div>
                     
                     <div class="time-info text-end">
                       <div class="arrival-time">{{ trip.arrivalTime }}</div>
                       <div class="station">{{ trip.toStation }}</div>
                     </div>
                   </div>
                 </div>

                 <div class="trip-features mt-2">
                   <span v-if="trip.freeCancel" class="badge bg-success me-2">Hủy miễn phí</span>
                   <span v-if="trip.availableSeats > 0" class="badge bg-info me-2">{{ trip.availableSeats }} ghế trống</span>
                   <span class="badge bg-secondary">{{ trip.seatType }}</span>
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
                   {{ trip.availableSeats > 0 ? 'Chọn chuyến' : 'Hết vé' }}
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
       <p class="section-subtitle">Những lý do khiến DKMN trở thành lựa chọn hàng đầu</p>
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
      'Hà Nội', 'Hải Phòng', 'Quảng Ninh', 'Bắc Ninh', 'Hưng Yên', 'Hà Nam',
      'Nam Định', 'Ninh Bình', 'Thanh Hóa', 'Nghệ An', 'Hà Tĩnh', 'Quảng Bình',
      'Quảng Trị', 'Thừa Thiên Huế', 'Đà Nẵng', 'Quảng Nam', 'Quảng Ngãi',
      'Bình Định', 'Phú Yên', 'Khánh Hòa', 'Ninh Thuận', 'Bình Thuận',
      'Kon Tum', 'Gia Lai', 'Đắk Lắk', 'Đắk Nông', 'Lâm Đồng',
      'Bình Phước', 'Bình Dương', 'Đồng Nai', 'Bà Rịa - Vũng Tàu',
      'TP. Hồ Chí Minh', 'Long An', 'Cần Thơ'
    ],
    searchForm: {
      vehicleType: '',
      from: '',
      to: '',
      departureDate: '',
      returnDate: '',
      passengers: '1'
    },
    showResults: false,
    sortBy: 'default',
    filters: {
      time: {
        morning: false,
        afternoon: false,
        evening: false
      },
      price: {
        max: 1000000
      },
      available: false,
      freeCancel: false
    },
    userInfo: {},
    trips: [
      {
        id: 1,
        company: 'Xe khách Phương Trang',
        vehicleType: 'Xe khách',
        departureTime: '08:00',
        arrivalTime: '12:30',
        fromStation: 'Bến xe Miền Tây',
        toStation: 'Bến xe Đà Lạt',
        duration: '4h30',
        price: 250000,
        rating: 4.5,
        availableSeats: 15,
        seatType: 'Ghế ngồi',
        freeCancel: true
      },
      {
        id: 2,
        company: 'Vietnam Airlines',
        vehicleType: 'Máy bay',
        departureTime: '14:20',
        arrivalTime: '15:50',
        fromStation: 'Sân bay Tân Sơn Nhất',
        toStation: 'Sân bay Đà Lạt',
        duration: '1h30',
        price: 1200000,
        rating: 4.8,
        availableSeats: 8,
        seatType: 'Hạng phổ thông',
        freeCancel: false
      },
      {
        id: 3,
        company: 'Tàu hỏa SE1',
        vehicleType: 'Tàu hỏa',
        departureTime: '22:00',
        arrivalTime: '06:00+1',
        fromStation: 'Ga Sài Gòn',
        toStation: 'Ga Đà Lạt',
        duration: '8h00',
        price: 450000,
        rating: 4.2,
        availableSeats: 0,
        seatType: 'Giường nằm',
        freeCancel: true
      }
    ]
  }
},
computed: {
  isLoggedIn() {
    return localStorage.getItem('token') && Object.keys(this.userInfo).length > 0;
  },
  isSearchValid() {
    return this.searchForm.vehicleType && 
           this.searchForm.from && 
           this.searchForm.to && 
           this.searchForm.departureDate;
  },
  filteredTrips() {
    let filtered = [...this.trips];
    
    // Filter by time
    if (this.filters.time.morning || this.filters.time.afternoon || this.filters.time.evening) {
      filtered = filtered.filter(trip => {
        const hour = parseInt(trip.departureTime.split(':')[0]);
        return (this.filters.time.morning && hour >= 6 && hour < 12) ||
               (this.filters.time.afternoon && hour >= 12 && hour < 18) ||
               (this.filters.time.evening && hour >= 18 && hour < 24);
      });
    }
    
    // Filter by price
    filtered = filtered.filter(trip => trip.price <= this.filters.price.max);
    
    // Filter by available seats
    if (this.filters.available) {
      filtered = filtered.filter(trip => trip.availableSeats > 0);
    }
    
    // Filter by free cancel
    if (this.filters.freeCancel) {
      filtered = filtered.filter(trip => trip.freeCancel);
    }
    
    return filtered;
  },
  sortedTrips() {
    const trips = [...this.filteredTrips];
    
    switch (this.sortBy) {
      case 'time-early':
        return trips.sort((a, b) => a.departureTime.localeCompare(b.departureTime));
      case 'time-late':
        return trips.sort((a, b) => b.departureTime.localeCompare(a.departureTime));
      case 'price-low':
        return trips.sort((a, b) => a.price - b.price);
      case 'price-high':
        return trips.sort((a, b) => b.price - a.price);
      case 'rating':
        return trips.sort((a, b) => b.rating - a.rating);
      default:
        return trips;
    }
  }
},
methods: {
  searchTrips() {
    if (this.isSearchValid) {
      this.showResults = true;
      // Scroll to results
      this.$nextTick(() => {
        document.querySelector('.results-section').scrollIntoView({ 
          behavior: 'smooth' 
        });
      });
    }
  },
  selectTrip(trip) {
    // Navigate to booking page with trip data
    this.$router.push({
      name: 'Booking',
      params: { tripId: trip.id },
      query: { 
        passengers: this.searchForm.passengers,
        from: this.searchForm.from,
        to: this.searchForm.to,
        date: this.searchForm.departureDate
      }
    });
  },
  formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND'
    }).format(price);
  },
  checkAuthStatus() {
    // Kiểm tra trạng thái đăng nhập từ localStorage
    const token = localStorage.getItem('token');
    const userInfo = localStorage.getItem('userInfo');
    
    if (token && userInfo) {
      try {
        this.userInfo = JSON.parse(userInfo);
      } catch (error) {
        console.error('Error parsing user info:', error);
        this.logout();
      }
    }
  },
  logout() {
    // Xóa thông tin đăng nhập
    localStorage.removeItem('token');
    localStorage.removeItem('userInfo');
    this.userInfo = {};
    
    // Thông báo đăng xuất thành công
    alert('Đăng xuất thành công!');
    
    // Reload trang để cập nhật UI
    this.$router.go(0);
  }
},
mounted() {
  // Kiểm tra trạng thái đăng nhập khi component được mount
  this.checkAuthStatus();
}
};
</script>


<style>

</style>