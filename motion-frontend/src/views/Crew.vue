<template>
  <div class="crew-page">
    <div class="crew-container">
      <div class="page-header">
        <h1 class="page-title">iepazīsti mūsu komandu</h1>
        <p class="page-subtitle">
          aizrautīgi pasniedzēji, kuri palīdz katram dejotājam sasniegt savu labāko versiju
        </p>
        
        <!-- Search and Sort Controls -->
        <div class="controls-section">
          <div class="search-container">
            <i class="fa-solid fa-search search-icon"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="meklēt pasniedzējus..."
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

      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>ielādējam mūsu lieliskos pasniedzējus...</p>
      </div>

      <div v-else-if="filteredInstructors.length === 0" class="empty-state">
        <i class="fa-solid fa-search"></i>
        <h3>pasniedzēji nav atrasti</h3>
        <p>mēģini mainīt meklēšanas frāzi</p>
      </div>

      <div v-else class="instructors-masonry">
        <div 
          v-for="(instructor, index) in filteredInstructors" 
          :key="`${instructor.name}-${index}`"
          class="instructor-card"
          :class="['card-' + (index % 3)]"
          @click="selectInstructor(instructor, index)"
        >
          <div class="card-image-container">
            <img 
              :src="instructor.photo" 
              :alt="instructor.name"
              class="instructor-image"
              @error="handleImageError"
              loading="lazy"
            />
            <div class="image-overlay">
              <div class="overlay-content">
                <i class="fa-solid fa-eye"></i>
                <span>skatīt profilu</span>
              </div>
            </div>
          </div>
          
          <div class="card-content">
            <h3 class="instructor-name">{{ instructor.name }}</h3>
            <p class="instructor-specialty">{{ getSpecialty(index) }}</p>
            <div class="bio-preview">
              {{ truncateBio(instructor.description) }}
            </div>
            <div class="card-footer">
              <div class="experience-badge">
                <i class="fa-solid fa-star"></i>
                <span>{{ getExperience(index) }} gadi</span>
              </div>
              <button class="learn-more-btn">
                uzzināt vairāk
                <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructor Detail Modal -->
      <div v-if="selectedInstructor" class="instructor-modal" @click="closeModal">
        <div class="modal-content" @click.stop>
          <button class="close-btn" @click="closeModal">
            <i class="fa-solid fa-times"></i>
          </button>
          
          <div class="modal-header">
            <img 
              :src="selectedInstructor.photo" 
              :alt="selectedInstructor.name"
              class="modal-image"
              loading="lazy"
            />
            <div class="modal-info">
              <h2>{{ selectedInstructor.name }}</h2>
              <p class="modal-specialty">{{ getSpecialty(selectedIndex) }}</p>
              <div class="modal-stats">
                <div class="stat">
                  <i class="fa-solid fa-calendar"></i>
                  <span>{{ getExperience(selectedIndex) }} gadu pieredze</span>
                </div>
                <div class="stat">
                  <i class="fa-solid fa-users"></i>
                  <span>{{ getStudentCount(selectedIndex) }}+ apmācīti audzēkņi</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="modal-body">
            <h3>par {{ selectedInstructor.name.split(' ')[0] }}</h3>
            <p class="full-bio">{{ selectedInstructor.description }}</p>
            
            <div class="specialties-section">
              <h4>specializācijas</h4>
              <div class="specialty-tags">
                <span 
                  v-for="specialty in getSpecialties(selectedIndex)" 
                  :key="specialty"
                  class="specialty-tag"
                >
                  {{ specialty }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Footer Gradient OUTSIDE the container -->
    <div class="footer-gradient"></div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Crew',
  data() {
    return {
      instructors: [],
      loading: true,
      selectedInstructor: null,
      selectedIndex: null,
      searchQuery: '',
      sortOrder: 'none'
    }
  },
  computed: {
    filteredInstructors() {
      let filtered = [...this.instructors];
      
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        filtered = filtered.filter(instructor => {
          const name = instructor.name.toLowerCase();
          const bio = (instructor.description || '').toLowerCase();
          return name.includes(query) || bio.includes(query);
        });
      }
      
      if (this.sortOrder !== 'none') {
        filtered.sort((a, b) => {
          const nameA = a.name.toLowerCase();
          const nameB = b.name.toLowerCase();
          
          if (this.sortOrder === 'asc') {
            return nameA.localeCompare(nameB);
          } else {
            return nameB.localeCompare(nameA);
          }
        });
      }
      
      return filtered;
    },
    
    sortButtonText() {
      switch (this.sortOrder) {
        case 'asc': return 'a-z';
        case 'desc': return 'z-a';
        default: return 'kārtot';
      }
    }
  },
  mounted() {
    this.loadInstructors();
  },
  methods: {
    async loadInstructors() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/instructors');
        this.instructors = response.data.instructors;
      } catch (error) {
        console.error('Kļūda, ielādējot pasniedzējus:', error);
        this.instructors = [
          { name: 'ketija čarma', photo: 'https://via.placeholder.com/300x400', description: 'komerciālās dejas speciāliste ar ilggadēju pieredzi' },
          { name: 'džeisons rīvs', photo: 'https://via.placeholder.com/300x350', description: 'street deju eksperts un horeogrāfs' }
        ];
      } finally {
        this.loading = false;
      }
    },

    toggleSort() {
      switch (this.sortOrder) {
        case 'none':
          this.sortOrder = 'asc';
          break;
        case 'asc':
          this.sortOrder = 'desc';
          break;
        case 'desc':
          this.sortOrder = 'none';
          break;
      }
    },

    getSpecialty(index) {
      const specialties = [
        'komerciālā deja', 'iela un hiphops', 'laikmetīgā deja un džezs', 'breiks un popping',
        'liriskā deja un balets', 'muzikālais teātris', 'urbānā horeogrāfija', 'frīstails un battle',
        'modernā saplūsme', 'dejas teātris'
      ];
      return specialties[index] || 'deju pasniedzējs';
    },

    getExperience(index) {
      const experiences = [8, 12, 6, 15, 9, 7, 11, 5, 10, 13];
      return experiences[index] || 8;
    },

    getStudentCount(index) {
      const counts = [200, 350, 150, 400, 180, 120, 280, 90, 220, 320];
      return counts[index] || 150;
    },

    getSpecialties(index) {
      const specialtyLists = [
        ['komerciālā deja', 'jazz funk', 'mūzikas video'],
        ['hiphops', 'breiks', 'popping', 'locking'],
        ['laikmetīgā deja', 'džezs', 'liriskā deja'],
        ['breiks', 'spēka elementi', 'battle'],
        ['balets', 'liriskā deja', 'modernais stils'],
        ['džezs', 'teātris', 'brodveja'],
        ['urbānā deja', 'street jazz', 'komerciālā deja'],
        ['frīstails', 'house', 'waacking'],
        ['modernā deja', 'laikmetīgā deja', 'fusion'],
        ['teātris', 'stāstniecība', 'skatuves performance']
      ];
      return specialtyLists[index] || ['deja', 'kustība', 'izteiksme'];
    },

    truncateBio(bio) {
      if (!bio) return 'aizrautīgs deju pasniedzējs...';
      return bio.length > 100 ? bio.substring(0, 100) + '...' : bio;
    },

    selectInstructor(instructor, originalIndex) {
      this.selectedInstructor = instructor;
      this.selectedIndex = this.instructors.findIndex(i => i.name === instructor.name);
    },

    closeModal() {
      this.selectedInstructor = null;
      this.selectedIndex = null;
    },

    handleImageError(event) {
      event.target.src = 'https://via.placeholder.com/300x400/333/fff?text=Pasniedz%C4%93js';
    }
  }
}
</script>

<style scoped>
.crew-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  padding: 120px 20px 40px;
  box-sizing: border-box;
  overflow-y: auto;
  text-transform: lowercase;
  z-index: 0;
}

.crew-page::before {
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
    url('/images/crewbg.webp');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
  pointer-events: none;
}


.crew-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
}

.page-header {
  text-align: center;
  margin-bottom: 60px;
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
  margin: 0 auto 40px;
  line-height: 1.6;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}

/* Search and Sort Controls */
.controls-section {
  display: flex;
  gap: 20px;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 30px;
}

.search-container {
  position: relative;
  max-width: 400px;
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

/* SIMPLIFIED Masonry Layout */
.instructors-masonry {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 30px;
  align-items: start;
  margin-bottom: 100px;
}

.instructor-card {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  overflow: hidden;
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.2s ease;
  cursor: pointer;
  position: relative;
}

/* Staggered heights for masonry effect */
.instructor-card.card-0 {
  transform: translateY(0px);
}

.instructor-card.card-1 {
  transform: translateY(20px);
}

.instructor-card.card-2 {
  transform: translateY(40px);
}

.instructor-card:hover {
  transform: translateY(-5px) scale(1.01);
}

.card-image-container {
  position: relative;
  height: 300px;
  overflow: hidden;
}

.instructor-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.instructor-card:hover .instructor-image {
  transform: scale(1.05);
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(255, 220, 143, 0.8), rgba(255, 220, 143, 0.6));
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.instructor-card:hover .image-overlay {
  opacity: 1;
}

.overlay-content {
  text-align: center;
  color: #fff;
  font-family: 'NeueHaasDisplayBold', sans-serif;
}

.overlay-content i {
  font-size: 24px;
  margin-bottom: 8px;
  display: block;
}

.card-content {
  padding: 25px;
}

.instructor-name {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin-bottom: 8px;
  text-shadow: 0 0 10px #ffe18d;
  text-transform: lowercase;
}

.instructor-specialty {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: #ffdc8f;
  font-size: 14px;
  margin-bottom: 15px;
  text-transform: lowercase;
  letter-spacing: 1px;
}

.bio-preview {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  line-height: 1.5;
  margin-bottom: 20px;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.experience-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #ffdc8f;
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 12px;
}

.learn-more-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: linear-gradient(45deg, #ffdc8f, #ffe18d);
  color: #333;
  border: none;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: transform 0.2s ease;
}

.learn-more-btn:hover {
  transform: translateX(3px);
}

/* Footer Gradient - FIXED POSITION */
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

/* Modal Styles */
.instructor-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  box-sizing: border-box;
}

.modal-content {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: #fff;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
  transition: background 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.modal-header {
  display: flex;
  gap: 30px;
  padding: 40px;
  align-items: center;
}

.modal-image {
  width: 200px;
  height: 250px;
  object-fit: cover;
  border-radius: 15px;
  flex-shrink: 0;
}

.modal-info h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 32px;
  margin-bottom: 10px;
  text-transform: lowercase;
  text-shadow: 0 0 15px #ffe18d;
}

.modal-specialty {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: #ffdc8f;
  font-size: 16px;
  margin-bottom: 20px;
  text-transform: lowercase;
  letter-spacing: 1px;
}

.modal-stats {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-family: 'NeueHaasDisplay', sans-serif;
}

.stat i {
  color: #ffdc8f;
  width: 16px;
}

.modal-body {
  padding: 0 40px 40px;
}

.modal-body h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin-bottom: 20px;
  text-shadow: 0 0 10px #ffe18d;
}

.full-bio {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.6;
  margin-bottom: 30px;
  font-size: 16px;
}

.specialties-section h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 18px;
  margin-bottom: 15px;
}

.specialty-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.specialty-tag {
  background: rgba(255, 220, 143, 0.2);
  color: #ffdc8f;
  padding: 6px 12px;
  border-radius: 15px;
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 12px;
  border: 1px solid rgba(255, 220, 143, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
  .instructors-masonry {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .instructor-card.card-0,
  .instructor-card.card-1,
  .instructor-card.card-2 {
    transform: translateY(0);
  }
  
  .controls-section {
    flex-direction: column;
    gap: 15px;
  }
  
  .search-container {
    max-width: 100%;
  }
  
  .modal-header {
    flex-direction: column;
    text-align: center;
    padding: 30px 20px;
  }
  
  .modal-image {
    width: 150px;
    height: 200px;
  }
  
  .modal-body {
    padding: 0 20px 30px;
  }
  
  .page-title {
    font-size: 36px;
  }
}
</style>
