<template>
  <div class="container-xxl d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="card shadow border-0 p-4 w-100" style="max-width: 1100px;">
      <!-- Tabs -->
      <ul class="nav nav-tabs justify-content-center mb-4">
        <li class="nav-item">
          <button
            class="nav-link"
            :class="{ active: activeTab === 'bus' }"
            @click="activeTab = 'bus'"
          >
            <i class="bx bx-bus me-2"></i> Xe khách
          </button>
        </li>
       
        <li class="nav-item">
          <button
            class="nav-link"
            :class="{ active: activeTab === 'train' }"
            @click="activeTab = 'train'"
          >
            <i class="bx bx-train me-2"></i> Tàu hỏa
          </button>
        </li>


        <li class="nav-item">
          <button
            class="nav-link"
            :class="{ active: activeTab === 'plane' }"
            @click="activeTab = 'plane'"
          >
          <i class="fa-solid fa-plane"></i> Máy bay
          </button>
        </li>

      </ul>

      <!-- Search form -->
      <div class="row g-3 align-items-end">
        <!-- From -->
        <div class="col-md-3">
          <label class="form-label text-secondary small">Nơi xuất phát</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <i class="bx bx-radio-circle-marked text-primary"></i>
            </span>
            <input
              type="text"
              class="form-control border-start-0"
              v-model="from"
              placeholder="Chọn tỉnh / thành"
              @focus="showFromList = true"
              @blur="hideList('from')"
            />
          </div>
          <ul
            v-if="showFromList"
            class="list-group position-absolute mt-1 shadow-sm w-100"
            style="max-height: 240px; overflow-y: auto; z-index: 10;"
          >
            <li class="list-group-item text-warning small fst-italic">
              *Lưu ý: tên địa phương trước sáp nhập
            </li>
            <li
              class="list-group-item list-group-item-action"
              v-for="(city, i) in provinces"
              :key="i"
              @mousedown.prevent="selectFrom(city)"
            >
              {{ city }}
            </li>
          </ul>
        </div>

        <!-- Swap -->
        <div class="col-auto d-flex justify-content-center">
          <button class="btn btn-outline-secondary rounded-circle" @click="swapPlaces">
            <i class="bx bx-transfer-alt"></i>
          </button>
        </div>

        <!-- To -->
        <div class="col-md-3">
          <label class="form-label text-secondary small">Nơi đến</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
              <i class="bx bx-map-pin text-danger"></i>
            </span>
            <input
              type="text"
              class="form-control border-start-0"
              v-model="to"
              placeholder="Chọn tỉnh / thành"
              @focus="showToList = true"
              @blur="hideList('to')"
            />
          </div>
          <ul
            v-if="showToList"
            class="list-group position-absolute mt-1 shadow-sm w-100"
            style="max-height: 240px; overflow-y: auto; z-index: 10;"
          >
            <li class="list-group-item text-warning small fst-italic">
              *Lưu ý: tên địa phương sau sáp nhập
            </li>
            <li
              class="list-group-item list-group-item-action"
              v-for="(city, i) in provinces"
              :key="i"
              @mousedown.prevent="selectTo(city)"
            >
              {{ city }}
            </li>
          </ul>
        </div>

        <!-- Date -->
        <div class="col-md-3">
          <label class="form-label text-secondary small">Ngày đi</label>
          <input type="date" class="form-control" v-model="departDate" />
        </div>

        <!-- Search Button -->
        <div class="col-md-2">
          <button class="btn btn-warning w-100 fw-semibold" @click="search">
            Tìm kiếm
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

const activeTab = ref("bus");
const from = ref("An Giang");
const to = ref("An Giang");
const departDate = ref("2025-11-07");
const showFromList = ref(false);
const showToList = ref(false);

const provinces = [
  "An Giang", "Bà Rịa - Vũng Tàu", "Bạc Liêu", "Bắc Giang", "Bắc Kạn", "Bắc Ninh",
  "Bến Tre", "Bình Dương", "Bình Định", "Bình Phước", "Bình Thuận", "Cà Mau",
  "Cao Bằng", "Cần Thơ", "Đà Nẵng", "Đắk Lắk", "Đắk Nông", "Điện Biên", "Đồng Nai",
  "Đồng Tháp", "Gia Lai", "Hà Giang", "Hà Nam", "Hà Nội", "Hà Tĩnh", "Hải Dương",
  "Hải Phòng", "Hậu Giang", "Hòa Bình", "Hưng Yên", "Khánh Hòa", "Kiên Giang",
  "Kon Tum", "Lai Châu", "Lâm Đồng", "Lạng Sơn", "Lào Cai", "Long An", "Nam Định",
  "Nghệ An", "Ninh Bình", "Ninh Thuận", "Phú Thọ", "Phú Yên", "Quảng Bình",
  "Quảng Nam", "Quảng Ngãi", "Quảng Ninh", "Quảng Trị", "Sóc Trăng", "Sơn La",
  "Tây Ninh", "Thái Bình", "Thái Nguyên", "Thanh Hóa", "Thừa Thiên Huế",
  "Tiền Giang", "TP. Hồ Chí Minh", "Trà Vinh", "Tuyên Quang", "Vĩnh Long",
  "Vĩnh Phúc", "Yên Bái",
];

function swapPlaces() {
  const temp = from.value;
  from.value = to.value;
  to.value = temp;
}

function selectFrom(city) {
  from.value = city;
  showFromList.value = false;
}

function selectTo(city) {
  to.value = city;
  showToList.value = false;
}

function hideList(type) {
  setTimeout(() => {
    if (type === "from") showFromList.value = false;
    else showToList.value = false;
  }, 150);
}

function search() {
  alert(`Tìm chuyến từ ${from.value} đến ${to.value} ngày ${departDate.value}`);
}
</script>
