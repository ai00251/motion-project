<template>
  <div class="profile-page">
    <div class="profile-container">
      <h2 class="profile-title">mans profils</h2>
      
      <div v-if="user" class="profile-content">
        <!-- navigation tabs -->
        <div class="tab-navigation">
          <button 
            @click="activeTab = 'info'" 
            :class="['tab-button', { active: activeTab === 'info' }]"
          >
            personīgā informācija
          </button>
          <!-- Only show groups tab for clients -->
          <button 
            v-if="userRole === 'client'"
            @click="activeTab = 'groups'" 
            :class="['tab-button', { active: activeTab === 'groups' }]"
          >
            grupas un līgumi
          </button>
          <!-- Admin tabs -->
          <button 
            v-if="userRole === 'admin'"
            @click="activeTab = 'admin-groups'" 
            :class="['tab-button', { active: activeTab === 'admin-groups' }]"
          >
            pārvaldīt grupas
          </button>
          <!-- Events tab for non-admins -->
          <button 
            v-if="userRole !== 'admin'"
            @click="activeTab = 'events'" 
            :class="['tab-button', { active: activeTab === 'events' }]"
          >
            manas nodarbības
          </button>
        </div>

        <!-- success/error messages -->
        <div v-if="message" class="success-message">
          {{ message }}
        </div>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <!-- personal information tab -->
        <div v-if="activeTab === 'info'" class="tab-content">
          <div v-if="!editMode" class="info-display">
            <div class="info-grid">
              <div class="info-card">
                <h3>pamatinformācija</h3>
                <div class="info-row">
                  <span class="info-label">pilns vārds</span>
                  <span class="info-value">{{ user.name }} {{ user.surname }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">e-pasts</span>
                  <span class="info-value">{{ user.email }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">tālrunis</span>
                  <span class="info-value">{{ user.phone_number }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">dzimšanas datums</span>
                  <span class="info-value">{{ formatDate(user.birth_date) }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">loma</span>
                  <span class="info-value">{{ userRole || 'lietotājs' }}</span>
                </div>
              </div>
            </div>
            
            <button class="action-btn primary" @click="startEdit">
              <i class="fa-solid fa-edit"></i> rediģēt profilu
            </button>
          </div>

          <!-- edit form -->
          <div v-if="editMode" class="edit-section">
            <h3>rediģēt personīgo informāciju</h3>
            <form @submit.prevent="saveProfile" class="edit-form">
              <div class="form-row">
                <div class="form-group">
                  <label>vārds</label>
                  <input 
                    v-model="editUser.name" 
                    type="text" 
                    class="form-input"
                    required
                  >
                </div>
                <div class="form-group">
                  <label>uzvārds</label>
                  <input 
                    v-model="editUser.surname" 
                    type="text" 
                    class="form-input"
                    required
                  >
                </div>
              </div>
              
              <div class="form-group">
                <label>e-pasts</label>
                <input 
                  v-model="editUser.email" 
                  type="email" 
                  class="form-input"
                  required
                >
              </div>
              
              <div class="form-group">
                <label>tālruņa numurs</label>
                <input 
                  v-model="editUser.phone_number" 
                  type="text" 
                  class="form-input"
                  required
                >
              </div>
              
              <div class="form-group">
                <label>dzimšanas datums</label>
                <input 
                  v-model="editUser.birth_date" 
                  type="date" 
                  class="form-input"
                  required
                >
              </div>
              
              <div class="form-actions">
                <button type="submit" class="action-btn primary" :disabled="loading">
                  {{ loading ? 'saglabā...' : 'saglabāt izmaiņas' }}
                </button>
                <button type="button" class="action-btn secondary" @click="cancelEdit">
                  atcelt
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- groups & contracts tab - only show for clients -->
        <div v-if="activeTab === 'groups' && userRole === 'client'" class="tab-content">
          <div class="section-header">
            <h3>manas grupas un līgumi</h3>
            <button class="action-btn primary" @click="loadUserGroups">
              <i class="fa-solid fa-refresh"></i> atjaunot
            </button>
          </div>
          
          <div v-if="loadingGroups" class="loading-state">
            ielādē grupas...
          </div>
          
          <div v-else-if="userGroups.length > 0" class="content-grid">
            <div v-for="group in userGroups" :key="group.id" class="content-card">
              <div class="card-header">
                <h4>{{ group.name }}</h4>
                <span class="status-badge" :class="group.status.toLowerCase()">
                  {{ group.status }}
                </span>
              </div>
              <div class="card-body">
                <p><strong>tips:</strong> {{ group.type }}</p>
                <p><strong>pievienojās:</strong> {{ formatDate(group.joined_date) }}</p>
                <p><strong>loma:</strong> {{ group.role }}</p>
              </div>
              <div class="card-actions">
                <button class="action-btn small danger" @click="leaveGroup(group.id)">
                  pamest grupu
                </button>
              </div>
            </div>
          </div>
          
          <div v-else class="empty-state">
            <i class="fa-solid fa-users"></i>
            <h4>grupas nav atrastas</h4>
            <p>tu pašlaik neesi nevienā deju grupā vai līgumā.</p>
            <button class="action-btn primary">apskatīt grupas</button>
          </div>
        </div>

        <!-- Admin Groups Management Tab -->
        <div v-if="activeTab === 'admin-groups' && userRole === 'admin'" class="tab-content">
          <div class="section-header">
            <h3>pārvaldīt grupas</h3>
            <button class="action-btn primary" @click="showAddGroupForm = true">
              <i class="fa-solid fa-plus"></i> pievienot jaunu grupu
            </button>
          </div>

          <!-- Add Group Form -->
          <div v-if="showAddGroupForm" class="form-section">
            <h4>izveidot jaunu grupu</h4>
            <form @submit.prevent="createGroup" class="admin-form">
              <div class="form-row">
                <div class="form-group">
                  <label>grupas nosaukums</label>
                  <input 
                    v-model="newGroup.title" 
                    type="text" 
                    class="form-input"
                    placeholder="piem., Fresh Moves"
                    required
                  >
                </div>
                <div class="form-group">
                  <label>līmenis</label>
                  <select v-model="newGroup.level" class="form-input" required>
                    <option value="">izvēlies līmeni</option>
                    <option value="beginner">iesācēju</option>
                    <option value="intermediate">vidējs</option>
                    <option value="advanced">augstāks</option>
                  </select>
                </div>
              </div>
              
              <div class="form-group">
                <label>dalībnieku skaits</label>
                <input 
                  v-model="newGroup.member_count" 
                  type="number" 
                  class="form-input"
                  min="0"
                  max="50"
                  placeholder="0"
                >
              </div>
              
              <div class="form-actions">
                <button type="submit" class="action-btn primary" :disabled="loadingCreate">
                  {{ loadingCreate ? 'veido...' : 'izveidot grupu' }}
                </button>
                <button type="button" class="action-btn secondary" @click="cancelAddGroup">
                  atcelt
                </button>
              </div>
            </form>
          </div>

          <!-- Groups List -->
          <div v-if="loadingGroups" class="loading-state">
            ielādē grupas...
          </div>
          
          <div v-else-if="allGroups.length > 0" class="content-grid">
            <div v-for="group in allGroups" :key="group.group_id" class="content-card">
              <div class="card-header">
                <h4>{{ group.title }}</h4>
                <span class="status-badge" :class="group.level">
                  {{ group.level }}
                </span>
              </div>
              <div class="card-body">
                <p><strong>dalībnieki:</strong> {{ group.member_count || 0 }}</p>
                <p><strong>izveidots:</strong> {{ formatDate(group.created_at) }}</p>
              </div>
              <div class="card-actions">
                <button class="action-btn small danger" @click="deleteGroup(group.group_id)">
                  dzēst
                </button>
              </div>
            </div>
          </div>
          
          <div v-else class="empty-state">
            <i class="fa-solid fa-users"></i>
            <h4>grupas nav atrastas</h4>
            <p>sāc, izveidojot savu pirmo deju grupu.</p>
          </div>
        </div>

        <!-- events tab - only for non-admins -->
        <div v-if="activeTab === 'events' && userRole !== 'admin'" class="tab-content">
          <div class="section-header">
            <h3>manas nodarbības</h3>
            <button class="action-btn primary" @click="loadUserEvents">
              <i class="fa-solid fa-refresh"></i> atjaunot
            </button>
          </div>
          
          <div v-if="loadingEvents" class="loading-state">
            ielādē nodarbības...
          </div>
          
<div v-else-if="userEvents.length > 0" class="content-grid">
  <div
    v-for="event in sortedUserEvents"
    :key="event.id"
    class="content-card"
    :class="isPastEvent(event) ? 'past-event-card' : 'upcoming-event-card'"
  >
    <div class="card-header">
      <h4>{{ event.name }}</h4>
      <span
        class="status-badge"
        :class="isPastEvent(event) ? 'past' : event.attendance_status"
      >
        {{ isPastEvent(event) ? 'notikusi' : event.attendance_status }}
      </span>
    </div>

    <div class="card-body">
      <p><i class="fa-solid fa-calendar"></i> {{ formatDate(event.start_date) }}</p>
      <p><i class="fa-solid fa-clock"></i> {{ event.start_time }}</p>
      <p><i class="fa-solid fa-location-dot"></i> {{ formatLocation(event.hall) }}</p>
      <p><i class="fa-solid fa-hourglass-half"></i> {{ event.duration_minutes }} min</p>
      <p><strong>apraksts:</strong> {{ event.description }}</p>
    </div>

    <div class="card-actions">
      <button
        v-if="!isPastEvent(event) && event.attendance_status !== 'cancelled'"
        class="action-btn small danger"
        @click="updateEventStatus(event.id, 'cancelled')"
      >
        atcelt
      </button>

      <span v-if="isPastEvent(event)" class="past-event-note">
        šo nodarbību vairs nevar atcelt
      </span>
    </div>
  </div>
</div>
          
          <div v-else class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <h4>nodarbības nav atrastas</h4>
            <p>tev nav ieplānotu gaidāmo nodarbību.</p>
            <router-link to="/events" class="action-btn primary"> apskatīt nodarbības</router-link>
          </div>
        </div>

        <!-- logout section -->
        <div class="logout-section">
          <button class="action-btn danger full-width" @click="logout">
            <i class="fa-solid fa-right-from-bracket"></i> izrakstīties
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Profile',
  data() {
    return {
      user: null,
      userRole: null,
      editUser: {},
      editMode: false,
      activeTab: 'info',
      loading: false,
      loadingGroups: false,
      loadingEvents: false,
      loadingCreate: false,
      message: '',
      error: '',
      userGroups: [],
      userEvents: [],
      allGroups: [],
      showAddGroupForm: false,
      newGroup: {
        title: '',
        level: '',
        member_count: 0
      }
    }
  },
  computed: {
  sortedUserEvents() {
    return [...this.userEvents].sort((a, b) => {
      const aPast = this.isPastEvent(a)
      const bPast = this.isPastEvent(b)

      if (aPast !== bPast) {
        return aPast ? 1 : -1
      }

      const dateA = new Date(`${a.start_date}T${a.start_time || '00:00:00'}`)
      const dateB = new Date(`${b.start_date}T${b.start_time || '00:00:00'}`)
      return dateA - dateB
    })
  }
},
  mounted() {
    this.loadUserData();
    window.addEventListener('user-role-changed', this.handleRoleChange);
  },
  beforeUnmount() {
    window.removeEventListener('user-role-changed', this.handleRoleChange);
  },

  methods: {

    isPastEvent(event) {
  if (!event?.start_date) return false
  const eventDateTime = new Date(`${event.start_date}T${event.start_time || '00:00:00'}`)
  return eventDateTime < new Date()
},

    handleRoleChange(event) {
      this.user = event.detail.user;
      this.userRole = event.detail.user.role;
      
      if (this.userRole === 'client') {
        this.loadUserGroups();
        this.loadUserEvents();
      } else if (this.userRole === 'admin') {
        this.loadAllGroups();
      }
    },

    formatLocation(value) {
  if (!value) return '—'
  return String(value).replaceAll('_', ' ')
},

    async loadUserData() {
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/user', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Accept': 'application/json'
          }
        });
        
        this.user = response.data.user;
        this.userRole = response.data.role || response.data.user.role;
        this.editUser = { ...this.user };
        
        localStorage.setItem('user', JSON.stringify(this.user));
        
        if (this.userRole === 'client') {
          this.loadUserGroups();
          this.loadUserEvents();
        } else if (this.userRole === 'admin') {
          this.loadAllGroups();
        }
        
      } catch (error) {
        console.error('Kļūda, ielādējot lietotāja datus:', error);
        const userData = localStorage.getItem('user');
        if (userData) {
          this.user = JSON.parse(userData);
          this.userRole = 'user';
          this.editUser = { ...this.user };
        } else {
          this.$router.push('/signin');
        }
      }
    },

    async loadUserGroups() {
      if (this.userRole !== 'client') return;
      
      this.loadingGroups = true;
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/user/groups', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Accept': 'application/json'
          }
        });
        this.userGroups = response.data.groups || [];
      } catch (error) {
        console.error('Kļūda, ielādējot grupas:', error);
        this.userGroups = [];
      } finally {
        this.loadingGroups = false;
      }
    },

    async loadUserEvents() {
  this.loadingEvents = true
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/user/events', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        Accept: 'application/json'
      }
    })
    console.log('USER EVENTS RESPONSE:', response.data)
    this.userEvents = response.data.events || []
} catch (error) {
  console.error('Kļūda, ielādējot nodarbības:', error)
  console.log('STATUS:', error.response?.status)
  console.log('DATA:', error.response?.data)
  console.log('FULL ERROR:', error)
  this.userEvents = []
} finally {
    this.loadingEvents = false
  }
},

    // Admin Methods
    async loadAllGroups() {
      this.loadingGroups = true;
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/admin/groups', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Accept': 'application/json'
          }
        });
        this.allGroups = response.data.groups || [];
      } catch (error) {
        console.error('Kļūda, ielādējot grupas:', error);
        this.allGroups = [];
      } finally {
        this.loadingGroups = false;
      }
    },

    async createGroup() {
      this.loadingCreate = true;
      this.error = '';
      
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/admin/groups', this.newGroup, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        this.allGroups.push(response.data.group);
        this.message = 'Grupa veiksmīgi izveidota!';
        this.cancelAddGroup();
        
        setTimeout(() => {
          this.message = '';
        }, 3000);

      } catch (error) {
        console.error('Kļūda, veidojot grupu:', error);
        this.error = error.response?.data?.message || 'Neizdevās izveidot grupu';
      } finally {
        this.loadingCreate = false;
      }
    },

    cancelAddGroup() {
      this.showAddGroupForm = false;
      this.newGroup = {
        title: '',
        level: '',
        member_count: 0
      };
    },

    async deleteGroup(groupId) {
      if (confirm('Vai tiešām vēlies dzēst šo grupu?')) {
        try {
          await axios.delete(`http://127.0.0.1:8000/api/admin/groups/${groupId}`, {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
              'Accept': 'application/json'
            }
          });

          this.allGroups = this.allGroups.filter(g => g.group_id !== groupId);
          this.message = 'Grupa veiksmīgi dzēsta';

        } catch (error) {
          console.error('Kļūda, dzēšot grupu:', error);
          this.error = 'Neizdevās dzēst grupu';
        }
      }
    },

    startEdit() {
      this.editMode = true;
      this.editUser = { ...this.user };
    },

    async saveProfile() {
      this.loading = true;
      this.message = '';
      this.error = '';

      try {
        const response = await axios.put('http://127.0.0.1:8000/api/user/profile', this.editUser, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        this.user = response.data.user;
        localStorage.setItem('user', JSON.stringify(this.user));
        
        this.editMode = false;
        this.message = 'Profils veiksmīgi atjaunināts!';
        
        setTimeout(() => {
          this.message = '';
        }, 3000);

      } catch (error) {
        console.error('Kļūda, atjauninot profilu:', error);
        if (error.response && error.response.data) {
          this.error = error.response.data.message || 'Neizdevās atjaunināt profilu';
        } else {
          this.error = 'Tīkla kļūda. Lūdzu, mēģini vēlreiz.';
        }
      } finally {
        this.loading = false;
      }
    },

    cancelEdit() {
      this.editMode = false;
      this.editUser = { ...this.user };
      this.error = '';
    },

    async updateEventStatus(eventId, status) {
      try {
        await axios.put(`http://127.0.0.1:8000/api/user/events/${eventId}/status`, 
          { status }, 
          {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            }
          }
        );

        const event = this.userEvents.find(e => e.id === eventId);
        if (event) {
          event.attendance_status = status;
        }

        this.message = `Nodarbības statuss atjaunināts uz: ${status}`;
        setTimeout(() => {
          this.message = '';
        }, 3000);

      } catch (error) {
        console.error('Kļūda, atjauninot nodarbības statusu:', error);
        this.error = 'Neizdevās atjaunināt nodarbības statusu';
      }
    },

    async leaveGroup(groupId) {
      if (confirm('Vai tiešām vēlies pamest šo grupu?')) {
        try {
          await axios.delete(`http://127.0.0.1:8000/api/user/groups/${groupId}`, {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
              'Accept': 'application/json'
            }
          });

          this.userGroups = this.userGroups.filter(g => g.id !== groupId);
          this.message = 'Tu veiksmīgi pameti grupu';

        } catch (error) {
          console.error('Kļūda, pametot grupu:', error);
          this.error = 'Neizdevās pamest grupu';
        }
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString();
    },

    logout() {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.dispatchEvent(new CustomEvent('auth-changed'));
      this.$router.push('/');
    }
  }
}
</script>

<style scoped>

.profile-page {
  position: relative;
  min-height: 100vh;
  width: 100%;
  padding: 120px 20px 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-sizing: border-box;
  overflow-y: auto;
  z-index: 0;
}

.profile-page::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-image: 
    linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)),
    url('/images/signinbg.webp');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  z-index: -1;
  pointer-events: none;
}

.profile-container {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 40px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
  max-width: 1000px;
  width: 100%;
}

.profile-title {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 32px;
  font-weight: 400;
  line-height: 1.5;
  text-align: center;
  margin-bottom: 30px;
  text-shadow:
    0 0 8px #ffe18d,
    0 0 20px #ffe18d80;
}

/* tab navigation */
.tab-navigation {
  display: flex;
  gap: 5px;
  margin-bottom: 30px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
  padding: 5px;
  flex-wrap: wrap;
}

.tab-button {
  font-family: 'NeueHaasDisplay', sans-serif;
  flex: 1;
  min-width: 120px;
  padding: 12px 20px;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s;
  font-size: 14px;
  text-align: center;
}

.tab-button.active {
  background: rgba(255, 220, 143, 0.2);
  color: #fff;
  text-shadow: 0 0 8px #ffe18d;
}

.tab-button:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.1);
}

/* messages */
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

/* content layout */
.tab-content {
  min-height: 400px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-header h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin: 0;
  text-shadow: 0 0 8px #ffe18d;
}

/* form sections */
.form-section {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 30px;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.form-section h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 20px;
  margin: 0 0 20px 0;
  text-shadow: 0 0 8px #ffe18d;
}

.admin-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.admin-form textarea {
  resize: vertical;
  min-height: 80px;
}

/* info display */
.info-display {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.info-grid {
  display: grid;
  gap: 20px;
}

.info-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 25px;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.info-card h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 20px;
  margin: 0 0 20px 0;
  text-shadow: 0 0 8px #ffe18d;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
}

.info-value {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: #fff;
  font-size: 16px;
}

/* edit form */
.edit-section h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin-bottom: 25px;
  text-shadow: 0 0 8px #ffe18d;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
}

.form-input {
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 16px;
  padding: 12px 16px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  backdrop-filter: blur(5px);
  transition: all 0.3s;
}

.form-input:focus {
  outline: none;
  border-color: rgba(255, 220, 143, 0.5);
  box-shadow: 0 0 10px rgba(255, 220, 143, 0.3);
}

.form-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.form-actions {
  display: flex;
  gap: 15px;
  margin-top: 20px;
  flex-wrap: wrap;
}

/* content grid */
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.content-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 20px;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.content-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.card-header h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 18px;
  margin: 0;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-family: 'NeueHaasDisplayBold', sans-serif;
  text-transform: uppercase;
}

.status-badge.active, .status-badge.confirmed {
  background: rgba(76, 175, 80, 0.3);
  color: #4CAF50;
}

.status-badge.pending {
  background: rgba(255, 193, 7, 0.3);
  color: #FFC107;
}

.status-badge.cancelled {
  background: rgba(244, 67, 54, 0.3);
  color: #f44336;
}

.status-badge.beginner {
  background: rgba(76, 175, 80, 0.3);
  color: #4CAF50;
}

.status-badge.intermediate {
  background: rgba(255, 193, 7, 0.3);
  color: #FFC107;
}

.status-badge.advanced {
  background: rgba(244, 67, 54, 0.3);
  color: #f44336;
}

.card-body {
  margin-bottom: 15px;
}

.card-body p {
  font-family: 'NeueHaasDisplay', sans-serif;
  color: rgba(255, 255, 255, 0.8);
  font-size: 14px;
  margin: 8px 0;
}

.card-body i {
  margin-right: 8px;
  color: rgba(255, 220, 143, 0.8);
}

.card-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

/* buttons */
.action-btn {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.action-btn.primary {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  background: #ffdc8f9e;
  color: #ffffff;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: 
    box-shadow 0.3s,
    background 0.3s,
    color 0.3s,
    transform 0.2s cubic-bezier(.4,2,.6,1);
}

.action-btn.primary:hover {
  box-shadow: 0 0 32px #ffdc8f9e;
  transform: scale(1.05);
}

.action-btn.primary:active {
  transform: scale(0.98);
}

.action-btn.primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.action-btn.secondary {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.action-btn.secondary:hover {
  background: rgba(255, 255, 255, 0.3);
}

.action-btn.danger {
  background: rgba(244, 67, 54, 0.6);
  color: #fff;
}

.action-btn.danger:hover {
  background: rgba(244, 67, 54, 0.8);
  box-shadow: 0 0 20px rgba(244, 67, 54, 0.5);
}

.action-btn.small {
  padding: 6px 12px;
  font-size: 12px;
}

.action-btn.full-width {
  width: 100%;
  justify-content: center;
  padding: 15px;
  font-size: 16px;
}

/* empty states */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: rgba(255, 255, 255, 0.7);
}

.empty-state i {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.5;
  color: rgba(255, 220, 143, 0.5);
}

.empty-state h4 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 20px;
  margin: 20px 0 10px 0;
}

.empty-state p {
  font-family: 'NeueHaasDisplay', sans-serif;
  margin-bottom: 30px;
}

.loading-state {
  text-align: center;
  padding: 40px;
  color: rgba(255, 255, 255, 0.7);
  font-family: 'NeueHaasDisplay', sans-serif;
}

.logout-section {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.past-event-card {
  opacity: 0.7;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
}

.upcoming-event-card {
  border: 1px solid rgba(255, 220, 143, 0.2);
}

.status-badge.past {
  background: rgba(255, 255, 255, 0.18);
  color: rgba(255, 255, 255, 0.75);
}

.past-event-note {
  font-family: 'NeueHaasDisplay', sans-serif;
  font-size: 13px;
  color: rgba(255, 255, 255, 0.6);
}

</style>
