#!/usr/bin/env python3
"""
Script to apply VeXeRe design to TrangChu.vue
This script makes all necessary changes safely
"""

import re
import shutil
from pathlib import Path

# File path
FILE_PATH = Path("src/components/Client/TrangChu/TrangChu.vue")
BACKUP_PATH = FILE_PATH.with_suffix('.vue.before_vexere')

print("🚀 Starting VeXeRe Redesign Application...")

# Create backup
print(f"📦 Creating backup: {BACKUP_PATH}")
shutil.copy2(FILE_PATH, BACKUP_PATH)

# Read the file
print(f"📖 Reading {FILE_PATH}")
with open(FILE_PATH, 'r', encoding='utf-8') as f:
    content = f.read()

print("🔧 Applying changes...")

# Step 1: Update wrapper class
print("  ✓ Updating page wrapper")
content = content.replace(
    '<div class="container-fluid">',
    '<div class="page-wrapper">'
)

# Step 2: Remove GioiThieu
content = re.sub(r'<GioiThieu\s*/>', '', content)

# Step 3: Replace hero section (lines 4-250 approximately)
# Find the section tag and replace everything until the /section tag
hero_start_pattern = r'<section[^>]*class="hero[^"]*"[^>]*>'
hero_end_pattern = r'</section>'

# New hero HTML
new_hero = '''<!-- Hero Section with Carousel -->
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
        </div>
      </div>
      
      <!-- Blue Overlay -->
      <div class="hero-overlay"></div>

      <!-- Hero Content -->
      <div class="container hero-container">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <!-- Title -->
            <div class="hero-header text-center mb-4">
              <h1 class="hero-title">
                Vé Xe, Tàu, Máy Bay<br>
                <span class="highlight">Giá Rẻ Mỗi Ngày</span>
              </h1>
              <p class="hero-subtitle">
                An toàn - Tiện lợi - Nhanh chóng
              </p>
            </div>

            <!-- Search Card -->
            <div class="search-card">
              <!-- Tabs -->
              <div class="search-tabs">
                <button 
                  class="search-tab" 
                  :class="{ active: searchForm.vehicleType === 'bus' }"
                  @click="searchForm.vehicleType = 'bus'"
                >
                  <i class="bx bx-bus"></i>
                  <span>Xe khách</span>
                </button>
                <button 
                  class="search-tab" 
                  :class="{ active: searchForm.vehicleType === 'plane' }"
                  @click="searchForm.vehicleType = 'plane'"
                >
                  <i class="bx bxs-plane-alt"></i>
                  <span>Máy bay</span>
                </button>
                <button 
                  class="search-tab" 
                  :class="{ active: searchForm.vehicleType === 'train' }"
                  @click="searchForm.vehicleType = 'train'"
                >
                  <i class="bx bx-train"></i>
                  <span>Tàu hỏa</span>
                </button>
              </div>

              <!-- Search Form -->
              <form @submit.prevent="searchTrips">
                <div class="row g-3">
                  <div class="col-md-3">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bx bx-map"></i> Nơi đi
                      </label>
                      <select v-model="searchForm.from" class="form-control-modern" required>
                        <option value="">Chọn điểm đi</option>
                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bx bx-map-pin"></i> Nơi đến
                      </label>
                      <select v-model="searchForm.to" class="form-control-modern" required>
                        <option value="">Chọn điểm đến</option>
                        <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bx bx-calendar"></i> Ngày đi
                      </label>
                      <input type="date" v-model="searchForm.departureDate" class="form-control-modern" :min="new Date().toISOString().split('T')[0]" required />
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group-modern">
                      <label class="form-label-modern">
                        <i class="bx bx-user"></i> Hành khách
                      </label>
                      <select v-model="searchForm.passengers" class="form-control-modern">
                        <option value="1">1 người</option>
                        <option value="2">2 người</option>
                        <option value="3">3 người</option>
                        <option value="4">4 người</option>
                        <option value="5">5 người</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="text-center mt-4">
                  <button type="submit" class="btn-search-modern" :disabled="!isSearchValid || isLoadingTrips">
                    <i class="bx bx-search-alt"></i>
                    <span>{{ isLoadingTrips ? 'Đang tìm...' : 'Tìm chuyến' }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>'''

# Replace the entire hero section
# This is tricky - we need to find from first section to its closing tag
# Let's use a more conservative approach - find by markers
if 'hero position-relative' in content:
    # Find start
    start_idx = content.find('<section')
    # Find the matching </section>
    section_count = 0
    i = start_idx
    section_end = -1
    while i < len(content):
        if content[i:i+8] == '<section':
            section_count += 1
        elif content[i:i+10] == '</section>':
            section_count -= 1
            if section_count == 0:
                section_end = i + 10
                break
        i += 1
    
    if section_end > start_idx:
        print(f"  ✓ Replacing hero section (chars {start_idx} to {section_end})")
        content = content[:start_idx] + new_hero + content[section_end:]

print("✅ Template changes applied")
print("📝 Writing updated file...")

# Write the file back
with open(FILE_PATH, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"✅ Done! File updated successfully")
print(f"📦 Backup saved at: {BACKUP_PATH}")
print("\n🎯 Next steps:")
print("1. Add carousel data to data() section")
print("2. Add carousel methods")
print("3. Update CSS - see walkthrough.md")
