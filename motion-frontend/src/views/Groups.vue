<template>
  <div class="groups-page">
    <div class="groups-container">
      <div class="page-header">
        <h1 class="page-title">pievienojies mūsu kopienai</h1>
        <p class="page-subtitle">
          atklāj deju grupas un iepazīsti stilus, kas atbilst tavai aizrautībai
        </p>
        
        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <button 
            @click="activeTab = 'groups'" 
            class="tab-btn"
            :class="{ active: activeTab === 'groups' }"
          >
            <i class="fa-solid fa-users"></i>
            grupas
          </button>
          <button 
            @click="activeTab = 'styles'" 
            class="tab-btn"
            :class="{ active: activeTab === 'styles' }"
          >
            <i class="fa-solid fa-music"></i>
            stili
          </button>
        </div>

        <!-- Search and Filter Controls -->
        <div class="controls-section">
          <div class="search-container">
            <i class="fa-solid fa-search search-icon"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              :placeholder="activeTab === 'groups' ? 'meklēt grupas...' : 'meklēt stilus...'"
              class="search-input"
            />
          </div>
          
          <div class="filter-controls">
            <select v-model="selectedLevel" class="filter-select">
              <option value="">visi līmeņi</option>
              <option value="beginner">iesācēju</option>
              <option value="intermediate">vidējs</option>
              <option value="advanced">augstāks</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p>ielādē {{ activeTab === 'groups' ? 'grupas' : 'stilus' }}...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredItems.length === 0" class="empty-state">
        <i class="fa-solid fa-search"></i>
        <h3>{{ activeTab === 'groups' ? 'grupas nav atrastas' : 'stili nav atrasti' }}</h3>
        <p>mēģini mainīt meklēšanas vai filtra nosacījumus</p>
      </div>

      <!-- Groups Tab Content -->
      <div v-else-if="activeTab === 'groups'" class="content-grid">
        <div 
          v-for="(group, index) in filteredItems" 
          :key="`group-${group.id || index}`"
          class="content-card group-card"
          :class="['card-variant-' + (index % 4)]"
          @click="selectItem(group, 'group')"
        >
          <div class="card-header">
            <div class="card-icon">
              <i class="fa-solid fa-users"></i>
            </div>
            <div class="card-meta">
              <span class="level-badge" :class="group.level">{{ group.level }}</span>
              <span class="schedule">{{ group.schedule }}</span>
            </div>
          </div>
          
          <div class="card-content">
            <h3 class="card-title">{{ group.name || group.title }}</h3>
            <p class="card-description">{{ group.description }}</p>
            <p class="card-style">stils: {{ group.style }}</p>
            
            <div class="card-details">
              <div class="detail-item">
                <i class="fa-solid fa-calendar"></i>
                <span>{{ group.duration }} nedēļas</span>
              </div>
              <div class="detail-item">
                <i class="fa-solid fa-clock"></i>
                <span>{{ group.time }}</span>
              </div>
              <div class="detail-item">
                <i class="fa-solid fa-dollar-sign"></i>
                <span>€{{ group.price }}</span>
              </div>
            </div>
          </div>
          
          <div class="card-footer">
            <div class="participants">
              <i class="fa-solid fa-user-friends"></i>
              <span>{{ group.current_participants || group.member_count }}/{{ group.max_participants || 20 }}</span>
            </div>
            <button class="join-btn">
              pievienoties grupai
              <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Styles Tab Content -->
      <div v-else class="content-masonry">
        <div 
          v-for="(style, index) in filteredItems" 
          :key="`style-${style.id || index}`"
          class="content-card style-card"
          :class="['masonry-' + (index % 3)]"
          @click="selectItem(style, 'style')"
        >
          <div class="style-header">
            <div class="style-icon">
              <i :class="getStyleIcon(style.title)"></i>
            </div>
            <h3 class="style-title">{{ style.title }}</h3>
          </div>
          
          <div class="style-content">
            <p class="style-description">{{ style.description }}</p>
            
            <div class="style-features">
              <div class="feature-tag" v-for="feature in getStyleFeatures(index)" :key="feature">
                {{ feature }}
              </div>
            </div>
          </div>
          
          <div class="style-footer">
            <div class="difficulty">
              <span class="difficulty-label">grūtības pakāpe:</span>
              <div class="difficulty-dots">
                <span 
                  v-for="i in 5" 
                  :key="i"
                  class="dot"
                  :class="{ active: i <= getStyleDifficulty(index) }"
                ></span>
              </div>
            </div>
            <button class="explore-btn">
              izpētīt stilu
              <i class="fa-solid fa-external-link-alt"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Detail Modal -->
      <transition name="modal">
        <div v-if="selectedItem" class="detail-modal" @click="closeModal">
          <div class="modal-content" @click.stop>
            <button class="close-btn" @click="closeModal">
              <i class="fa-solid fa-times"></i>
            </button>
            
            <div class="modal-header">
              <div class="modal-icon">
                <i :class="selectedType === 'group' ? 'fa-solid fa-users' : getStyleIcon(selectedItem.title || selectedItem.name)"></i>
              </div>
              <div class="modal-info">
                <h2>{{ selectedItem.name || selectedItem.title }}</h2>
                <p class="modal-subtitle">
                  {{ selectedType === 'group' ? selectedItem.level + ' līmeņa grupa' : 'deju stils' }}
                </p>
              </div>
            </div>
            
            <div class="modal-body">
              <p class="modal-description">{{ selectedItem.description }}</p>
              
              <div v-if="selectedType === 'group'" class="group-details">
                <h4>grupas informācija</h4>
                <div class="detail-grid">
                  <div class="detail-card">
                    <i class="fa-solid fa-calendar"></i>
                    <span>{{ selectedItem.duration }} nedēļas</span>
                  </div>
                  <div class="detail-card">
                    <i class="fa-solid fa-clock"></i>
                    <span>{{ selectedItem.time }}</span>
                  </div>
                  <div class="detail-card">
                    <i class="fa-solid fa-dollar-sign"></i>
                    <span>€{{ selectedItem.price }}</span>
                  </div>
                  <div class="detail-card">
                    <i class="fa-solid fa-users"></i>
                    <span>{{ selectedItem.current_participants || selectedItem.member_count }}/{{ selectedItem.max_participants || 20 }} vietas</span>
                  </div>
                </div>
                
                <button class="modal-action-btn" @click="joinGroup(selectedItem.id)">
                  pievienoties šai grupai
                  <i class="fa-solid fa-arrow-right"></i>
                </button>
              </div>
              
              <div v-else class="style-details">
                <h4>ko tu iemācīsies</h4>
                <div class="features-grid">
                  <div 
                    v-for="feature in getStyleFeatures(styles.indexOf(selectedItem))" 
                    :key="feature"
                    class="feature-item"
                  >
                    <i class="fa-solid fa-check"></i>
                    <span>{{ feature }}</span>
                  </div>
                </div>
                
                <button class="modal-action-btn">
                  atrast {{ selectedItem.title }} grupas
                  <i class="fa-solid fa-search"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Groups',
  data() {
    return {
      activeTab: 'groups',
      groups: [],
      styles: [],
      loading: true,
      searchQuery: '',
      selectedLevel: '',
      selectedItem: null,
      selectedType: null
    }
  },
  computed: {
    filteredItems() {
      const items = this.activeTab === 'groups' ? this.groups : this.styles;
      let filtered = [...items];
      
      // Apply search filter
      if (this.searchQuery.trim()) {
        const query = this.searchQuery.toLowerCase().trim();
        filtered = filtered.filter(item => {
          const name = (item.name || item.title || '').toLowerCase();
          const description = (item.description || '').toLowerCase();
          const style = (item.style || '').toLowerCase();
          return name.includes(query) || description.includes(query) || style.includes(query);
        });
      }
      
      // Apply level filter (only for groups)
      if (this.selectedLevel && this.activeTab === 'groups') {
        filtered = filtered.filter(item => item.level === this.selectedLevel);
      }
      
      return filtered;
    }
  },
  watch: {
    activeTab() {
      this.searchQuery = '';
      this.selectedLevel = '';
    }
  },
  mounted() {
    this.loadData();
  },

  methods: {
    async loadData() {
      this.loading = true;
      try {
        const [groupsResponse, stylesResponse] = await Promise.all([
          axios.get('http://127.0.0.1:8000/api/groups'),
          axios.get('http://127.0.0.1:8000/api/styles')
        ]);
        
        this.groups = groupsResponse.data.groups;
        this.styles = stylesResponse.data.styles;
        
        console.log('Ielādētās grupas:', this.groups);
        console.log('Ielādētie stili:', this.styles);
        
      } catch (error) {
        console.error('Kļūda, ielādējot datus:', error);
        this.groups = [];
        this.styles = [
          { title: 'hip-hop', description: 'urbāns deju stils, kas izceļ ritmu un personisko izteiksmi' }
        ];
      } finally {
        this.loading = false;
      }
    },

    async joinGroup(groupId) {
      try {
        const token = localStorage.getItem('auth_token');
        
        if (!token) {
          alert('Lūdzu, pieslēdzies, lai pievienotos grupai');
          this.$router.push('/signin');
          return;
        }
        
        const response = await axios.post(`http://127.0.0.1:8000/api/groups/${groupId}/join`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.data.success) {
          alert('Tu veiksmīgi pievienojies grupai!');
          this.closeModal();
          this.loadData();
          await this.refreshUserData();
        }

      } catch (error) {
        if (error.response && error.response.data) {
          alert(error.response.data.message);
        } else {
          alert('Neizdevās pievienoties grupai. Lūdzu, mēģini vēlreiz.');
        }
        console.error('Grupas pievienošanās kļūda:', error);
      }
    },

    async refreshUserData() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/user', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Accept': 'application/json'
          }
        });
        
        localStorage.setItem('user', JSON.stringify(response.data.user));
        
        window.dispatchEvent(new CustomEvent('user-role-changed', {
          detail: { user: response.data.user }
        }));
        
      } catch (error) {
        console.error('Kļūda, atjaunojot lietotāja datus:', error);
      }
    },

    getStyleIcon(styleTitle) {
      const icons = {
        'hip-hop': 'fa-solid fa-music',
        'contemporary': 'fa-solid fa-heart',
        'breaking': 'fa-solid fa-bolt',
        'jazz': 'fa-solid fa-theater-masks',
        'ballet': 'fa-solid fa-feather',
        'commercial': 'fa-solid fa-video',
        'lyrical': 'fa-solid fa-dove',
        'street jazz': 'fa-solid fa-city'
      };
      return icons[styleTitle?.toLowerCase()] || 'fa-solid fa-music';
    },

    getStyleFeatures(index) {
      const features = [
        ['ritms', 'frīstails', 'battle'],
        ['plūdums', 'emocijas', 'stāstījums'],
        ['spēka elementi', 'toprock', 'freezes'],
        ['tehnika', 'performance', 'izteiksme'],
        ['grācija', 'precizitāte', 'disciplīna'],
        ['komerciālā deja', 'mūzikas video', 'industrija'],
        ['emocijas', 'plūdums', 'saikne'],
        ['urbāns', 'iela', 'attieksme']
      ];
      return features[index] || ['kustība', 'izteiksme', 'radošums'];
    },

    getStyleDifficulty(index) {
      const difficulties = [3, 2, 5, 3, 4, 3, 2, 4];
      return difficulties[index] || 3;
    },

    selectItem(item, type) {
      this.selectedItem = item;
      this.selectedType = type;
    },

    closeModal() {
      this.selectedItem = null;
      this.selectedType = null;
    }
  }
}
</script>

<style scoped>
/* Background setup - same as crew page */
.groups-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  padding: 120px 20px 40px;
  box-sizing: border-box;
  overflow-y: auto;
  text-transform: lowercase;
  z-index: 0;
}

.groups-page::before {
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
    url('/images/groupsbg.webp');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
  pointer-events: none;
}

.groups-container {
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
}

.tab-navigation {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-bottom: 40px;
}

.tab-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 15px 30px;
  border-radius: 30px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  font-size: 16px;
}

.tab-btn:hover,
.tab-btn.active {
  background: rgba(255, 220, 143, 0.2);
  border-color: rgba(255, 220, 143, 0.5);
  color: #fff;
  box-shadow: 0 5px 20px rgba(255, 220, 143, 0.3);
}

.tab-btn i {
  font-size: 18px;
}

/* Controls */
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

.filter-controls {
  display: flex;
  gap: 10px;
}

.filter-select {
  font-family: 'NeueHaasDisplay', sans-serif;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.3);
  padding: 12px 20px;
  border-radius: 25px;
  cursor: pointer;
  backdrop-filter: blur(10px);
  font-size: 14px;
}

.filter-select:focus {
  outline: none;
  border-color: rgba(255, 220, 143, 0.5);
}

.filter-select option {
  background: #333;
  color: #fff;
}

/* Loading and Empty States */
.loading-state, .empty-state {
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

/* Content Grids */
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 30px;
  margin-bottom: 100px;
}

.content-masonry {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 30px;
  align-items: start;
  margin-bottom: 100px;
}

/* Card Styles */
.content-card {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  overflow: hidden;
  backdrop-filter: blur(15px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
  position: relative;
}

.content-card:hover {
  transform: translateY(-5px) scale(1.01);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
  background: rgba(255, 255, 255, 0.12);
}

/* Group Card Variants */
.group-card.card-variant-0 { border-left: 4px solid #ff6b6b; }
.group-card.card-variant-1 { border-left: 4px solid #4ecdc4; }
.group-card.card-variant-2 { border-left: 4px solid #45b7d1; }
.group-card.card-variant-3 { border-left: 4px solid #96ceb4; }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 25px 25px 0;
}

.card-icon {
  width: 50px;
  height: 50px;
  background: rgba(255, 220, 143, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffdc8f;
  font-size: 20px;
}

.card-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 5px;
}

.level-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  text-transform: uppercase;
}

.level-badge.beginner {
  background: rgba(76, 175, 80, 0.2);
  color: #4CAF50;
  border: 1px solid rgba(76, 175, 80, 0.3);
}

.level-badge.intermediate {
  background: rgba(255, 193, 7, 0.2);
  color: #FFC107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.level-badge.advanced {
  background: rgba(244, 67, 54, 0.2);
  color: #f44336;
  border: 1px solid rgba(244, 67, 54, 0.3);
}

.schedule {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
}

.card-content {
  padding: 25px;
}

.card-title {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin-bottom: 15px;
  text-shadow: 0 0 10px #ffe18d;
}

.card-description {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  margin-bottom: 10px;
}

.card-style {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: #ffdc8f;
  font-size: 14px;
  margin-bottom: 20px;
  font-style: italic;
}

.card-details {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.7);
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 14px;
}

.detail-item i {
  color: #ffdc8f;
  width: 16px;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 25px 25px;
}

.participants {
  display: flex;
  align-items: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.7);
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 14px;
}

.participants i {
  color: #ffdc8f;
}

.join-btn, .explore-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: linear-gradient(45deg, #ffdc8f, #ffe18d);
  color: #333;
  border: none;
  padding: 10px 20px;
  border-radius: 20px;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.join-btn:hover, .explore-btn:hover {
  transform: translateX(3px);
  box-shadow: 0 5px 15px rgba(255, 220, 143, 0.4);
}

/* Style Card Specific */
.style-card.masonry-0 { transform: translateY(0px); }
.style-card.masonry-1 { transform: translateY(20px); }
.style-card.masonry-2 { transform: translateY(40px); }

.style-header {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 25px 25px 0;
}

.style-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(45deg, rgba(255, 220, 143, 0.2), rgba(255, 220, 143, 0.1));
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffdc8f;
  font-size: 24px;
}

.style-title {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 28px;
  text-shadow: 0 0 10px #ffe18d;
}

.style-content {
  padding: 25px;
}

.style-description {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  margin-bottom: 20px;
}

.style-features {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
}

.feature-tag {
  background: rgba(255, 220, 143, 0.15);
  color: #ffdc8f;
  padding: 6px 12px;
  border-radius: 15px;
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 12px;
  border: 1px solid rgba(255, 220, 143, 0.2);
}

.style-footer {
  padding: 0 25px 25px;
}

.difficulty {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.difficulty-label {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.7);
  font-size: 14px;
}

.difficulty-dots {
  display: flex;
  gap: 5px;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.3);
  transition: background 0.3s ease;
}

.dot.active {
  background: #ffdc8f;
}

/* Modal Styles */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.detail-modal {
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
  max-width: 700px;
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
  align-items: center;
  gap: 20px;
  padding: 40px;
}

.modal-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(45deg, rgba(255, 220, 143, 0.2), rgba(255, 220, 143, 0.1));
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffdc8f;
  font-size: 32px;
  flex-shrink: 0;
}

.modal-info h2 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 32px;
  margin-bottom: 10px;
  text-shadow: 0 0 15px #ffe18d;
}

.modal-subtitle {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: #ffdc8f;
  font-size: 16px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.modal-body {
  padding: 0 40px 40px;
}

.modal-description {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.6;
  margin-bottom: 30px;
  font-size: 16px;
}

.group-details h4,
.style-details h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 20px;
  margin-bottom: 20px;
  text-shadow: 0 0 10px #ffe18d;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 30px;
}

.detail-card {
  background: rgba(255, 255, 255, 0.05);
  padding: 15px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-card i {
  color: #ffdc8f;
  font-size: 18px;
}

.detail-card span {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.9);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
  margin-bottom: 30px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(255, 255, 255, 0.8);
  font-family: 'NeueHaasDisplay', sans-serif;
}

.feature-item i {
  color: #4CAF50;
  font-size: 14px;
}

.modal-action-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: linear-gradient(45deg, #ffdc8f, #ffe18d);
  color: #333;
  border: none;
  padding: 15px 30px;
  border-radius: 25px;
  font-size: 16px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
  margin: 0 auto;
}

.modal-action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(255, 220, 143, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
  .content-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .content-masonry {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .style-card.masonry-0,
  .style-card.masonry-1,
  .style-card.masonry-2 {
    transform: translateY(0);
  }
  
  .tab-navigation {
    flex-direction: column;
    align-items: center;
    gap: 15px;
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
  
  .modal-body {
    padding: 0 20px 30px;
  }
  
  .page-title {
    font-size: 36px;
  }
  
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .features-grid {
    grid-template-columns: 1fr;
  }
}
</style>
