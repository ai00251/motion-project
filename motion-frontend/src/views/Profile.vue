<template>
  <div class="profile-page">
    <div class="profile-container">
      <h2 class="profile-title">my profile</h2>
      
      <div v-if="user" class="profile-content">
        <!-- navigation tabs -->
        <div class="tab-navigation">
          <button 
            @click="activeTab = 'info'" 
            :class="['tab-button', { active: activeTab === 'info' }]"
          >
            personal info
          </button>
          <button 
            @click="activeTab = 'groups'" 
            :class="['tab-button', { active: activeTab === 'groups' }]"
          >
            groups & contracts
          </button>
          <button 
            @click="activeTab = 'events'" 
            :class="['tab-button', { active: activeTab === 'events' }]"
          >
            my events
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
                <h3>basic information</h3>
                <div class="info-row">
                  <span class="info-label">full name</span>
                  <span class="info-value">{{ user.name }} {{ user.surname }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">email</span>
                  <span class="info-value">{{ user.email }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">phone</span>
                  <span class="info-value">{{ user.phone_number }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">birth date</span>
                  <span class="info-value">{{ formatDate(user.birth_date) }}</span>
                </div>
              </div>
            </div>
            
            <button class="action-btn primary" @click="startEdit">
              <i class="fa-solid fa-edit"></i> edit profile
            </button>
          </div>

          <!-- edit form -->
          <div v-if="editMode" class="edit-section">
            <h3>edit personal information</h3>
            <form @submit.prevent="saveProfile" class="edit-form">
              <div class="form-row">
                <div class="form-group">
                  <label>first name</label>
                  <input 
                    v-model="editUser.name" 
                    type="text" 
                    class="form-input"
                    required
                  >
                </div>
                <div class="form-group">
                  <label>last name</label>
                  <input 
                    v-model="editUser.surname" 
                    type="text" 
                    class="form-input"
                    required
                  >
                </div>
              </div>
              
              <div class="form-group">
                <label>email</label>
                <input 
                  v-model="editUser.email" 
                  type="email" 
                  class="form-input"
                  required
                >
              </div>
              
              <div class="form-group">
                <label>phone number</label>
                <input 
                  v-model="editUser.phone_number" 
                  type="text" 
                  class="form-input"
                  required
                >
              </div>
              
              <div class="form-group">
                <label>birth date</label>
                <input 
                  v-model="editUser.birth_date" 
                  type="date" 
                  class="form-input"
                  required
                >
              </div>
              
              <div class="form-actions">
                <button type="submit" class="action-btn primary" :disabled="loading">
                  {{ loading ? 'saving...' : 'save changes' }}
                </button>
                <button type="button" class="action-btn secondary" @click="cancelEdit">
                  cancel
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- groups & contracts tab -->
        <div v-if="activeTab === 'groups'" class="tab-content">
          <div class="section-header">
            <h3>my groups & contracts</h3>
            <button class="action-btn primary" @click="loadUserGroups">
              <i class="fa-solid fa-refresh"></i> refresh
            </button>
          </div>
          
          <div v-if="loadingGroups" class="loading-state">
            loading groups...
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
                <p><strong>type:</strong> {{ group.type }}</p>
                <p><strong>joined:</strong> {{ formatDate(group.joined_date) }}</p>
                <p><strong>role:</strong> {{ group.role }}</p>
              </div>
              <div class="card-actions">
                <button class="action-btn small">view details</button>
                <button class="action-btn small danger" @click="leaveGroup(group.id)">
                  leave group
                </button>
              </div>
            </div>
          </div>
          
          <div v-else class="empty-state">
            <i class="fa-solid fa-users"></i>
            <h4>no groups found</h4>
            <p>you're not currently part of any dance groups or contracts.</p>
            <button class="action-btn primary">explore groups</button>
          </div>
        </div>

        <!-- events tab -->
        <div v-if="activeTab === 'events'" class="tab-content">
          <div class="section-header">
            <h3>my events</h3>
            <button class="action-btn primary" @click="loadUserEvents">
              <i class="fa-solid fa-refresh"></i> refresh
            </button>
          </div>
          
          <div v-if="loadingEvents" class="loading-state">
            loading events...
          </div>
          
          <div v-else-if="userEvents.length > 0" class="content-grid">
            <div v-for="event in userEvents" :key="event.id" class="content-card">
              <div class="card-header">
                <h4>{{ event.name }}</h4>
                <span class="status-badge" :class="event.attendance_status">
                  {{ event.attendance_status }}
                </span>
              </div>
              <div class="card-body">
                <p><i class="fa-solid fa-calendar"></i> {{ formatDate(event.date) }}</p>
                <p><i class="fa-solid fa-clock"></i> {{ event.time }}</p>
                <p><i class="fa-solid fa-location-dot"></i> {{ event.location }}</p>
                <p><strong>description:</strong> {{ event.description }}</p>
              </div>
              <div class="card-actions">
                <button 
                  v-if="event.attendance_status === 'pending'" 
                  class="action-btn small primary"
                  @click="updateEventStatus(event.id, 'confirmed')"
                >
                  confirm
                </button>
                <button 
                  v-if="event.attendance_status !== 'cancelled'" 
                  class="action-btn small danger"
                  @click="updateEventStatus(event.id, 'cancelled')"
                >
                  cancel
                </button>
              </div>
            </div>
          </div>
          
          <div v-else class="empty-state">
            <i class="fa-solid fa-calendar-xmark"></i>
            <h4>no events found</h4>
            <p>you have no upcoming events scheduled.</p>
            <button class="action-btn primary">browse events</button>
          </div>
        </div>

        <!-- logout section -->
        <div class="logout-section">
          <button class="action-btn danger full-width" @click="logout">
            <i class="fa-solid fa-right-from-bracket"></i> logout
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
      editUser: {},
      editMode: false,
      activeTab: 'info',
      loading: false,
      loadingGroups: false,
      loadingEvents: false,
      message: '',
      error: '',
      userGroups: [],
      userEvents: []
    }
  },
  mounted() {
    this.loadUserData();
    this.loadUserGroups();
    this.loadUserEvents();
  },
  methods: {
    async loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
        this.editUser = { ...this.user };
      } else {
        this.$router.push('/signin');
      }
    },

    async loadUserGroups() {
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
        console.error('error loading groups:', error);
        this.userGroups = [];
      } finally {
        this.loadingGroups = false;
      }
    },

    async loadUserEvents() {
      this.loadingEvents = true;
      try {
        const response = await axios.get('http://127.0.0.1:8000/api/user/events', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'Accept': 'application/json'
          }
        });
        this.userEvents = response.data.events || [];
      } catch (error) {
        console.error('error loading events:', error);
        this.userEvents = [];
      } finally {
        this.loadingEvents = false;
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

        // update local user data
        this.user = response.data.user;
        localStorage.setItem('user', JSON.stringify(this.user));
        
        this.editMode = false;
        this.message = 'profile updated successfully!';
        
        // clear message after 3 seconds
        setTimeout(() => {
          this.message = '';
        }, 3000);

      } catch (error) {
        console.error('error updating profile:', error);
        if (error.response && error.response.data) {
          this.error = error.response.data.message || 'failed to update profile';
        } else {
          this.error = 'network error. please try again.';
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

        // update local event status
        const event = this.userEvents.find(e => e.id === eventId);
        if (event) {
          event.attendance_status = status;
        }

        this.message = `event status updated to ${status}`;
        setTimeout(() => {
          this.message = '';
        }, 3000);

      } catch (error) {
        console.error('error updating event status:', error);
        this.error = 'failed to update event status';
      }
    },

    async leaveGroup(groupId) {
      if (confirm('are you sure you want to leave this group?')) {
        try {
          await axios.delete(`http://127.0.0.1:8000/api/user/groups/${groupId}`, {
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
              'Accept': 'application/json'
            }
          });

          // remove group from local array
          this.userGroups = this.userGroups.filter(g => g.id !== groupId);
          this.message = 'successfully left the group';

        } catch (error) {
          console.error('error leaving group:', error);
          this.error = 'failed to leave group';
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
/* base page styling */
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
}

.tab-button {
  font-family: 'NeueHaasDisplay', sans-serif;
  flex: 1;
  padding: 12px 20px;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.3s;
  font-size: 16px;
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
}

.section-header h3 {
  font-family: 'NeueHaasDisplayBold', sans-serif;
  color: #fff;
  font-size: 24px;
  margin: 0;
  text-shadow: 0 0 8px #ffe18d;
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

/* loading states */
.loading-state {
  text-align: center;
  padding: 40px;
  color: rgba(255, 255, 255, 0.7);
  font-family: 'NeueHaasDisplay', sans-serif;
}

/* logout section */
.logout-section {
  margin-top: 40px;
  padding-top: 30px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
