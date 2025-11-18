<template>
    <!-- Features Section -->
    <section id="features" class="features-section">
      <div class="container-lg">
        <div class="features-header text-center">
          <span class="feature-eyebrow">Lý do chọn DKMN</span>
          <h2 class="section-title">Tại sao chọn DKMN?</h2>
          <p class="section-subtitle">
            Trải nghiệm đặt vé toàn diện với công nghệ thông minh và dịch vụ đồng hành mọi lúc
          </p>
        </div>

        <div class="row g-4">
          <div class="col-md-4" v-for="feature in features" :key="feature.title">
            <div class="feature-card" :class="feature.gradient">
              <div class="feature-card__icon" :class="feature.accent">
                <i :class="feature.icon"></i>
              </div>
              <div class="feature-card__body">
                <h4>{{ feature.title }}</h4>
                <p>{{ feature.description }}</p>
              </div>
              <span class="feature-card__badge">{{ feature.badge }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  export default {
    name: "FeaturesSection",
    data() {
      return {
        features: [
          {
            icon: "bx bx-search-alt",
            title: "Tìm kiếm thông minh",
            description:
              "Tùy chỉnh hành trình với nhiều bộ lọc linh hoạt, gợi ý theo nhu cầu từng khách hàng.",
            badge: "Công nghệ AI",
            accent: "accent-blue",
            gradient: "card-gradient-blue",
          },
          {
            icon: "bx bx-shield-check",
            title: "Đặt vé an toàn",
            description:
              "Mã hóa thông tin thanh toán, cảnh báo lịch trình và chính sách hoàn/hủy rõ ràng.",
            badge: "Bảo mật 2 lớp",
            accent: "accent-purple",
            gradient: "card-gradient-purple",
          },
          {
            icon: "bx bx-support",
            title: "Hỗ trợ 24/7",
            description:
              "Đội ngũ tư vấn tận tâm qua chat, hotline và email, luôn đồng hành trên mọi hành trình.",
            badge: "Support+",
            accent: "accent-green",
            gradient: "card-gradient-green",
          },
        ],
      };
    },
  };
  </script>
  
  <style scoped>
.features-section {
    position: relative;
    background: radial-gradient(circle at 18% 18%, rgba(37, 99, 235, 0.2), transparent 40%),
      radial-gradient(circle at 82% 14%, rgba(14, 165, 233, 0.16), transparent 36%),
      linear-gradient(135deg, #0b1224, #0f172a 48%, #0b1224);
    padding: clamp(4rem, 9vw, 6rem) 0 clamp(5rem, 9vw, 8rem);
    overflow: hidden;
    color: #e2e8f0;
    width: 100vw;
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    margin-top: -1px;
  }

.features-section::before,
.features-section::after {
    content: "";
    position: absolute;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.5;
    z-index: 0;
}

.features-section::before {
    background: rgba(37, 99, 235, 0.3);
    top: -150px;
    left: -120px;
}

.features-section::after {
    background: rgba(16, 185, 129, 0.22);
    bottom: -160px;
    right: -80px;
}
  
  .section-title {
    font-weight: 700;
    color: #e2e8f0;
    font-size: clamp(2rem, 4.5vw, 2.8rem);
    margin-bottom: 0.25rem;
  }
  
  .feature-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.35em;
    font-size: 0.78rem;
    color: #60a5fa;
    display: inline-block;
    margin-bottom: 0.75rem;
  }

  .section-subtitle {
    max-width: 640px;
    margin: 0 auto;
    color: #cbd5e1;
    font-size: 1rem;
  }

  .feature-card {
    border-radius: 28px;
    padding: 2rem;
    box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
    border: 1px solid rgba(148, 163, 184, 0.28);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    position: relative;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    background: linear-gradient(145deg, #0f172a, #111a2e);
    overflow: hidden;
    z-index: 0;
  }

  .feature-card::after {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.05);
    border-radius: inherit;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    pointer-events: none;
    z-index: -1;
  }

  .feature-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 55px 110px rgba(37, 99, 235, 0.3);
    border-color: rgba(59, 130, 246, 0.35);
  }

  .feature-card__icon {
    width: 72px;
    height: 72px;
    border-radius: 22px;
    font-size: 2rem;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 15px 30px rgba(5, 9, 20, 0.35);
  }

  .feature-card__body h4 {
    font-weight: 800;
    color: #e2e8f0;
    margin-bottom: 0.4rem;
    letter-spacing: 0.01em;
  }

  .feature-card__body p {
    color: #cbd5e1;
    margin: 0;
    line-height: 1.6;
    font-weight: 600;
  }

  .feature-card__badge {
    align-self: flex-start;
    background: rgba(99, 102, 241, 0.16);
    color: #c7d2fe;
    font-weight: 700;
    padding: 0.35rem 0.95rem;
    border-radius: 999px;
    font-size: 0.85rem;
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.26);
  }

  .accent-blue {
    background: linear-gradient(135deg, #1d4ed8, #3b82f6);
  }

  .accent-purple {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
  }

  .accent-green {
    background: linear-gradient(135deg, #059669, #34d399);
  }

  @media (max-width: 768px) {
    .feature-card {
      height: 100%;
    }
  }
  
  .card-gradient-blue {
    background: linear-gradient(140deg, rgba(59, 130, 246, 0.16), rgba(15, 23, 42, 0.9));
    border-color: rgba(59, 130, 246, 0.32);
  }

  .card-gradient-purple {
    background: linear-gradient(140deg, rgba(168, 85, 247, 0.16), rgba(15, 23, 42, 0.9));
    border-color: rgba(168, 85, 247, 0.32);
  }

  .card-gradient-green {
    background: linear-gradient(140deg, rgba(34, 197, 94, 0.16), rgba(15, 23, 42, 0.9));
    border-color: rgba(34, 197, 94, 0.3);
  }
  </style>
