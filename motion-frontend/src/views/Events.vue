<template>
  <div class="events-page">
    <div class="events-container">
      <div class="page-header">
        <h1 class="page-title">nodarbības</h1>
        <p class="page-subtitle">
          izvēlies sev piemērotāko nodarbību un piesakies dažās sekundēs
        </p>

        <div class="controls-section">
          <div class="search-container">
            <i class="fa-solid fa-search search-icon"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="meklēt nodarbības..."
              class="search-input"
            />
          </div>

          <div class="sort-controls">
            <button
              @click="toggleSort"
              class="sort-btn"
              :class="{ active: sortOrder !== 'none' }"
            >
              <i class="fa-solid fa-sort-alpha-down" v-if="sortOrder === 'asc'"></i>
              <i class="fa-solid fa-sort-alpha-up" v-if="sortOrder === 'desc'"></i>
              <i class="fa-solid fa-sort" v-if="sortOrder === 'none'"></i>
              {{ sortButtonText }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="message" class="success-message">
        {{ message }}
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>

      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>ielādējam nodarbības...</p>
      </div>

      <div v-else-if="filteredEvents.length === 0" class="empty-state">
        <i class="fa-solid fa-calendar-xmark"></i>
        <h3>nodarbības nav atrastas</h3>
        <p>mēģini mainīt meklēšanas frāzi</p>
      </div>

      <div v-else class="events-grid">
        <div
          v-for="(event, index) in filteredEvents"
          :key="event.id"
          class="event-card"
          :class="[
            'card-' + (index % 3),
            isPastEvent(event.start_date) ? 'past-event' : 'upcoming-event'
          ]"
        >
          <div class="card-top">
            <div class="title-block">
              <h3 class="event-name">{{ event.name }}</h3>
              <span class="event-status" :class="isPastEvent(event.start_date) ? 'past' : 'upcoming'">
                {{ isPastEvent(event.start_date) ? 'jau notika' : 'drīzumā' }}
              </span>
            </div>

            <span class="level-badge" :class="event.level">
              {{ levelLabel(event.level) }}
            </span>
          </div>

          <p class="event-description">
            {{ truncateText(event.description, 120) }}
          </p>

          <div class="event-meta">
            <div class="meta-item">
              <i class="fa-solid fa-calendar"></i>
              <span>{{ formatDate(event.start_date) }}</span>
            </div>
            <div class="meta-item">
              <i class="fa-solid fa-clock"></i>
              <span>{{ event.start_time }}</span>
            </div>
            <div class="meta-item">
              <i class="fa-solid fa-location-dot"></i>
              <span>{{ formatLocation(event.hall) }}</span>
            </div>
            <div class="meta-item">
              <i class="fa-solid fa-hourglass-half"></i>
              <span>{{ event.duration_minutes }} min</span>
            </div>
          </div>

          <div class="card-footer">
            <button
  class="join-btn"
  @click="joinEvent(event.id)"
  :disabled="joiningId === event.id || isPastEvent(event.start_date)"
>
  {{ isPastEvent(event.start_date) ? 'nodarbība jau notikusi' : (joiningId === event.id ? 'piesakās...' : 'pieteikties') }}
  <i class="fa-solid fa-arrow-right"></i>
</button>
          </div>
        </div>
      </div>
    </div>

    <div class="footer-gradient"></div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Events',
  data() {
    return {
      events: [],
      loading: true,
      joiningId: null,
      searchQuery: '',
      sortOrder: 'none',
      message: '',
      error: '',
      defaultImage: 'https://via.placeholder.com/600x400/333/fff?text=Nodarb%C4%ABba'
    }
  },
  computed: {
        filteredEvents() {
      let filtered = [...this.events]

      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim()
        filtered = filtered.filter(event => {
          const name = (event.name || '').toLowerCase()
          const description = (event.description || '').toLowerCase()
          const location = (event.hall || '').toLowerCase()
          return name.includes(query) || description.includes(query) || location.includes(query)
        })
      }

      filtered.sort((a, b) => {
        const aPast = this.isPastEvent(a.start_date)
        const bPast = this.isPastEvent(b.start_date)

        if (aPast !== bPast) {
          return aPast ? 1 : -1
        }

        const dateA = new Date(`${a.start_date}T${a.start_time || '00:00:00'}`)
        const dateB = new Date(`${b.start_date}T${b.start_time || '00:00:00'}`)
        return dateA - dateB
      })

      return filtered
    },
    sortButtonText() {
      switch (this.sortOrder) {
        case 'asc': return 'a-z'
        case 'desc': return 'z-a'
        default: return 'kārtot'
      }
    }
  },
  mounted() {
    this.loadEvents()
  },
  methods: {
    async loadEvents() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/events', {
          headers: { Accept: 'application/json' }
        })
        this.events = response.data.events || []
      } catch (error) {
        console.error('Kļūda, ielādējot nodarbības:', error)
        this.error = 'Neizdevās ielādēt nodarbības'
        this.events = []
      } finally {
        this.loading = false
      }
    },
    toggleSort() {
      this.sortOrder = this.sortOrder === 'none' ? 'asc' : this.sortOrder === 'asc' ? 'desc' : 'none'
    },
    async joinEvent(eventId) {
      this.joiningId = eventId
      this.error = ''
      this.message = ''

      try {
        const token = localStorage.getItem('auth_token')

        const response = await axios.post(
          `http://127.0.0.1:8000/api/events/${eventId}/register`,
          {},
          {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: 'application/json',
              'Content-Type': 'application/json'
            }
          }
        )

        this.message = response.data.message || 'Tu veiksmīgi pieteicies nodarbībai!'
        setTimeout(() => {
          this.message = ''
        }, 3000)
      } catch (error) {
        console.error('Kļūda, piesakoties nodarbībai:', error)
        this.error = error.response?.data?.message || 'Neizdevās pieteikties nodarbībai'
      } finally {
        this.joiningId = null
      }
    },
    formatDate(dateString) {
      if (!dateString) return '—'
      return new Date(dateString).toLocaleDateString()
    },
    formatLocation(value) {
      if (!value) return '—'
      return String(value).replaceAll('_', ' ')
    },
    truncateText(text, length) {
      if (!text) return ''
      return text.length > length ? text.substring(0, length) + '...' : text
    },
    levelLabel(level) {
      const map = {
        beginner: 'iesācēju',
        intermediate: 'vidējs',
        advanced: 'augstāks'
      }
      return map[level] || 'nodarbība'
    },
    isPastEvent(dateString) {
      if (!dateString) return false
      const eventDate = new Date(dateString)
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      return eventDate < today
    },
    handleImageError(event) {
      event.target.src = this.defaultImage
    }
  }
}
</script>

<style scoped>
.events-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  padding: 120px 20px 40px;
  box-sizing: border-box;
  overflow-y: auto;
  text-transform: lowercase;
  z-index: 0;
}

.events-page::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-image:
    linear-gradient(
      to bottom,
      rgba(0, 0, 0, 0.2) 0%,
      rgba(0, 0, 0, 0.3) 50%,
      rgba(0, 0, 0, 0.7) 100%
    ),
    url('/images/eventsbg.webp');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
  pointer-events: none;
}

.events-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.page-header {
  text-align: center;
  margin-bottom: 50px;
}

.page-title {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 48px;
  font-weight: 400;
  margin-bottom: 20px;
  text-shadow: 0 0 20px #ffe18d;
}

.page-subtitle {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.9);
  font-size: 18px;
  max-width: 600px;
  margin: 0 auto 35px;
  line-height: 1.6;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}

.controls-section {
  display: flex;
  gap: 20px;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 25px;
}

.search-container {
  position: relative;
  max-width: 420px;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.6);
  z-index: 2;
}

.search-input {
  font-family: 'NeueHaasDisplay', sans-serif;
  width: 100%;
  padding: 12px 15px 12px 45px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 25px;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  backdrop-filter: blur(10px);
  transition: border-color 0.3s ease;
  font-size: 16px;
}

.search-input:focus {
  outline: none;
  border-color: rgba(255, 220, 143, 0.5);
  background: rgba(255, 255, 255, 0.15);
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.sort-controls {
  display: flex;
  gap: 10px;
}

.sort-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 12px 20px;
  border-radius: 25px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.3s ease;
  backdrop-filter: blur(10px);
}

.sort-btn:hover,
.sort-btn.active {
  background: rgba(255, 220, 143, 0.2);
  border-color: rgba(255, 220, 143, 0.5);
  color: #fff;
}

.success-message {
  background: rgba(76, 175, 80, 0.2);
  border: 1px solid rgba(76, 175, 80, 0.5);
  color: #4CAF50;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  font-family: 'NeueHaasDisplay', sans-serif;
  backdrop-filter: blur(5px);
}

.error-message {
  background: rgba(244, 67, 54, 0.2);
  border: 1px solid rgba(244, 67, 54, 0.5);
  color: #f44336;
  padding: 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  text-align: center;
  font-family: 'NeueHaasDisplay', sans-serif;
  backdrop-filter: blur(5px);
}

.loading-state {
  text-align: center;
  padding: 80px 20px;
  color: rgba(255, 255, 255, 0.8);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 220, 143, 0.3);
  border-top: 3px solid #ffdc8f;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: rgba(255, 255, 255, 0.7);
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 20px;
  color: rgba(255, 220, 143, 0.5);
}

.empty-state h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin-bottom: 10px;
}

.events-grid {
  display: flex;
  flex-direction: column;
  gap: 18px;
  margin-bottom: 100px;
}

.event-card {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
  position: relative;
  padding: 18px 22px;
}

.event-card.card-1 {
  transform: none;
}

.event-card.card-2 {
  transform: none;
}

.event-card:hover {
  transform: translateY(-3px);
  border-color: rgba(255, 220, 143, 0.25);
}

.event-card.past-event {
  opacity: 0.72;
  border-left: 4px solid rgba(255, 255, 255, 0.35);
}

.event-card.upcoming-event {
  border-left: 4px solid #ffdc8f;
}

.card-top {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 14px;
}

.title-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.event-name {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin: 0;
  text-shadow: 0 0 10px #ffe18d;
}

.event-status {
  display: inline-flex;
  padding: 5px 10px;
  border-radius: 999px;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 12px;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  width: fit-content;
}

.event-status.past {
  background: rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 0.75);
}

.event-status.upcoming {
  background: rgba(255, 220, 143, 0.18);
  color: #ffdc8f;
}

.level-badge {
  padding: 6px 12px;
  border-radius: 15px;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  font-size: 12px;
  text-transform: uppercase;
  white-space: nowrap;
}

.level-badge.beginner {
  background: rgba(76, 175, 80, 0.3);
  color: #4CAF50;
}

.level-badge.intermediate {
  background: rgba(255, 193, 7, 0.3);
  color: #FFC107;
}

.level-badge.advanced {
  background: rgba(244, 67, 54, 0.3);
  color: #f44336;
}

.event-description {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 16px;
}

.event-meta {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.85);
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 13px;
  min-width: 0;
}

.meta-item i {
  color: #ffdc8f;
  width: 16px;
  flex: 0 0 16px;
}

.meta-item span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-top: 10px;
}

.join-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: linear-gradient(45deg, #ffdc8f, #ffe18d);
  color: #333;
  border: none;
  padding: 10px 18px;
  border-radius: 20px;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: transform 0.2s ease;
  min-width: 170px;
  justify-content: center;
}

.join-btn:hover:not(:disabled) {
  transform: translateX(3px);
}

.join-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.footer-gradient {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 150px;
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.7) 0%,
    rgba(0, 0, 0, 0.4) 50%,
    transparent 100%
  );
  pointer-events: none;
  z-index: 10;
}

@media (max-width: 900px) {
  .event-meta {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .controls-section {
    flex-direction: column;
    gap: 15px;
  }

  .search-container {
    max-width: 100%;
  }

  .card-top {
    flex-direction: column;
    align-items: start;
  }

  .event-meta {
    grid-template-columns: 1fr;
  }

  .page-title {
    font-size: 36px;
  }

  .event-name {
    font-size: 22px;
  }

  .card-footer {
    justify-content: stretch;
  }

  .join-btn {
    width: 100%;
  }
}
</style>