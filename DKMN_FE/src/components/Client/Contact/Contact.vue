<template>
  <div class="contact-page">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-background"></div>
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <span class="gradient-text">Liên hệ với chúng tôi</span>
          </h1>
          <p class="hero-subtitle">
            Chúng tôi luôn sẵn sàng hỗ trợ và giải đáp mọi thắc mắc của bạn
          </p>
        </div>
      </div>
    </section>

    <!-- Contact Info Section -->
    <section class="contact-info-section">
      <div class="container">
        <div class="contact-grid">
          <!-- Contact Card -->
          <div class="contact-card" v-for="(contact, index) in contactInfo" :key="index">
            <div class="contact-icon">
              <i :class="contact.icon"></i>
            </div>
            <h3 class="contact-title">{{ contact.title }}</h3>
            <p class="contact-value">{{ contact.value }}</p>
            <a v-if="contact.link" :href="contact.link" class="contact-link">
              {{ contact.linkText || 'Liên hệ ngay' }}
              <i class="bx bx-right-arrow-alt"></i>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form-section">
      <div class="container">
        <div class="form-wrapper">
          <div class="form-header">
            <h2 class="form-title">Gửi tin nhắn cho chúng tôi</h2>
            <p class="form-description">
              Điền thông tin bên dưới và chúng tôi sẽ phản hồi trong thời gian sớm nhất
            </p>
          </div>

          <form @submit.prevent="submitForm" class="contact-form">
            <div class="form-row">
              <div class="form-group">
                <label for="name" class="form-label">
                  <i class="bx bx-user me-2"></i>
                  Họ và tên
                </label>
                <input
                  type="text"
                  id="name"
                  v-model="form.name"
                  class="form-input"
                  placeholder="Nhập họ và tên của bạn"
                  required
                />
              </div>

              <div class="form-group">
                <label for="email" class="form-label">
                  <i class="bx bx-envelope me-2"></i>
                  Email
                </label>
                <input
                  type="email"
                  id="email"
                  v-model="form.email"
                  class="form-input"
                  placeholder="your.email@example.com"
                  required
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="phone" class="form-label">
                  <i class="bx bx-phone me-2"></i>
                  Số điện thoại
                </label>
                <input
                  type="tel"
                  id="phone"
                  v-model="form.phone"
                  class="form-input"
                  placeholder="0123456789"
                />
              </div>

              <div class="form-group">
                <label for="subject" class="form-label">
                  <i class="bx bx-message-square-detail me-2"></i>
                  Chủ đề
                </label>
                <select id="subject" v-model="form.subject" class="form-input" required>
                  <option value="">Chọn chủ đề</option>
                  <option value="support">Hỗ trợ kỹ thuật</option>
                  <option value="booking">Vấn đề đặt vé</option>
                  <option value="payment">Thanh toán</option>
                  <option value="feedback">Góp ý</option>
                  <option value="other">Khác</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="message" class="form-label">
                <i class="bx bx-edit me-2"></i>
                Nội dung tin nhắn
              </label>
              <textarea
                id="message"
                v-model="form.message"
                class="form-input"
                rows="6"
                placeholder="Nhập nội dung tin nhắn của bạn..."
                required
              ></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="submit-button" :disabled="isSubmitting">
                <span v-if="!isSubmitting">
                  <i class="bx bx-send me-2"></i>
                  Gửi tin nhắn
                </span>
                <span v-else>
                  <span class="spinner-border spinner-border-sm me-2"></span>
                  Đang gửi...
                </span>
              </button>
            </div>

            <div v-if="submitMessage" :class="['submit-message', submitMessageType]">
              <i :class="submitMessageType === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle'"></i>
              {{ submitMessage }}
            </div>
          </form>
        </div>
      </div>
    </section>

    <!-- Map/Additional Info Section -->
    <section class="additional-info-section">
      <div class="container">
        <div class="info-grid">
          <div class="info-card">
            <div class="info-icon">
              <i class="bx bx-time-five"></i>
            </div>
            <h3 class="info-title">Thời gian hỗ trợ</h3>
            <p class="info-text">24/7 - Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
          </div>

          <div class="info-card">
            <div class="info-icon">
              <i class="bx bx-message-rounded-dots"></i>
            </div>
            <h3 class="info-title">Phản hồi nhanh</h3>
            <p class="info-text">Chúng tôi sẽ phản hồi trong vòng 24 giờ</p>
          </div>

          <div class="info-card">
            <div class="info-icon">
              <i class="bx bx-shield-quarter"></i>
            </div>
            <h3 class="info-title">Bảo mật thông tin</h3>
            <p class="info-text">Thông tin của bạn được bảo mật tuyệt đối</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'Contact',
  data() {
    return {
      contactInfo: [
        {
          icon: 'bx bx-user',
          title: 'Người liên hệ',
          value: 'Trần Công Minh',
          link: null
        },
        {
          icon: 'bx bx-envelope',
          title: 'Email',
          value: 'tranminh6464aimbot@gmail.com',
          link: 'mailto:tranminh6464aimbot@gmail.com',
          linkText: 'Gửi email'
        },
        {
          icon: 'bx bx-phone',
          title: 'Số điện thoại',
          value: '0392864769',
          link: 'tel:0392864769',
          linkText: 'Gọi ngay'
        }
      ],
      form: {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: ''
      },
      isSubmitting: false,
      submitMessage: '',
      submitMessageType: ''
    }
  },
  methods: {
    async submitForm() {
      this.isSubmitting = true
      this.submitMessage = ''
      this.submitMessageType = ''
      
      try {
        const api = (await import('../../../services/api')).default
        const response = await api.post('/dkmn/lien-he', {
          ho_ten: this.form.name,
          email: this.form.email,
          so_dien_thoai: this.form.phone,
          chu_de: this.form.subject,
          noi_dung: this.form.message
        })
        
        if (response.data?.status) {
          this.submitMessage = response.data.message || 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.'
          this.submitMessageType = 'success'
          
          // Reset form
          this.form = {
            name: '',
            email: '',
            phone: '',
            subject: '',
            message: ''
          }
          
          // Clear message after 5 seconds
          setTimeout(() => {
            this.submitMessage = ''
          }, 5000)
        } else {
          throw new Error(response.data?.message || 'Có lỗi xảy ra')
        }
      } catch (error) {
        this.submitMessage = error.response?.data?.message || error.message || 'Không thể gửi tin nhắn. Vui lòng thử lại sau.'
        this.submitMessageType = 'error'
      } finally {
        this.isSubmitting = false
      }
    }
  }
}
</script>

<style scoped>
.contact-page {
  min-height: 100vh;
  background: radial-gradient(circle at 18% 18%, rgba(37, 99, 235, 0.2), transparent 40%),
    radial-gradient(circle at 82% 14%, rgba(14, 165, 233, 0.16), transparent 36%),
    linear-gradient(135deg, #0b1224, #0f172a 48%, #0b1224);
}

/* Hero Section */
.hero-section {
  position: relative;
  padding: 120px 0 80px;
  background: radial-gradient(circle at 12% 14%, rgba(56, 189, 248, 0.18), transparent 30%),
    radial-gradient(circle at 82% 18%, rgba(45, 212, 191, 0.16), transparent 32%),
    linear-gradient(180deg, #040d1f 0%, #051a31 42%, #082744 72%, #0b2f57 100%);
  overflow: hidden;
}

.hero-background {
  position: absolute;
  inset: 0;
  background: 
    radial-gradient(circle at 20% 50%, rgba(37, 99, 235, 0.3), transparent 50%),
    radial-gradient(circle at 80% 50%, rgba(139, 92, 246, 0.3), transparent 50%);
  animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

.hero-content {
  text-align: center;
  position: relative;
  z-index: 1;
}

.hero-title {
  font-size: 4rem;
  font-weight: 800;
  margin-bottom: 1.5rem;
  color: #e2e8f0;
}

.gradient-text {
  background: linear-gradient(120deg, #60a5fa, #34d399, #a78bfa);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  background-size: 200% auto;
  animation: gradient 3s linear infinite;
}

@keyframes gradient {
  to { background-position: 200% center; }
}

.hero-subtitle {
  font-size: 1.5rem;
  color: #cbd5e1;
  max-width: 600px;
  margin: 0 auto;
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Contact Info Section */
.contact-info-section {
  padding: 80px 0;
  background: transparent;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.contact-card {
  padding: 2.5rem;
  background: linear-gradient(145deg, #0f172a, #111a2e);
  border-radius: 20px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
}

.contact-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 55px 110px rgba(37, 99, 235, 0.3);
  border-color: rgba(59, 130, 246, 0.35);
}

.contact-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  color: #ffffff;
}

.contact-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.75rem;
}

.contact-value {
  font-size: 1.125rem;
  color: #cbd5e1;
  margin-bottom: 1rem;
  word-break: break-word;
}

.contact-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: #60a5fa;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.contact-link:hover {
  color: #3b82f6;
  gap: 0.75rem;
}

/* Contact Form Section */
.contact-form-section {
  padding: 80px 0;
  background: transparent;
}

.form-wrapper {
  max-width: 800px;
  margin: 0 auto;
  padding: 3rem;
  background: linear-gradient(145deg, #0f172a, #111a2e);
  border-radius: 24px;
  box-shadow: 0 30px 70px rgba(5, 9, 20, 0.35);
  border: 1px solid rgba(148, 163, 184, 0.28);
}

.form-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.form-title {
  font-size: 2rem;
  font-weight: 700;
  color: #e2e8f0;
  margin-bottom: 0.75rem;
}

.form-description {
  color: #cbd5e1;
  font-size: 1.125rem;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-weight: 600;
  color: #e2e8f0;
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}

.form-input {
  padding: 0.875rem 1.25rem;
  border: 2px solid rgba(148, 163, 184, 0.28);
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: rgba(15, 23, 42, 0.6);
  color: #e2e8f0;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  background: rgba(15, 23, 42, 0.8);
}

.form-input::placeholder {
  color: #94a3b8;
}

textarea.form-input {
  resize: vertical;
  min-height: 120px;
  font-family: inherit;
}

.form-actions {
  display: flex;
  justify-content: center;
  margin-top: 1rem;
}

.submit-button {
  display: inline-flex;
  align-items: center;
  padding: 1rem 2.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: #ffffff;
  font-weight: 600;
  font-size: 1.125rem;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
}

.submit-button:hover:not(:disabled) {
  transform: translateY(-4px);
  box-shadow: 0 15px 40px rgba(59, 130, 246, 0.4);
}

.submit-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.submit-message {
  padding: 1rem 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 500;
  margin-top: 1rem;
}

.submit-message.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #6ee7b7;
}

.submit-message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

/* Additional Info Section */
.additional-info-section {
  padding: 80px 0;
  background: transparent;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.info-card {
  text-align: center;
  padding: 2rem;
  color: #ffffff;
}

.info-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
}

.info-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #ffffff;
}

.info-text {
  color: #cbd5e1;
  line-height: 1.6;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.125rem;
  }
  
  .form-wrapper {
    padding: 2rem 1.5rem;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .contact-grid,
  .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>

