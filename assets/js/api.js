// /assets/js/api.js - Cliente API REST (RUTA CORREGIDA)

class ApiClient {
    constructor() {
        // ✅ USAR BASE_PATH DINÁMICO
        const basePath = window.BASE_PATH || '/helpdesk';
        this.baseUrl = window.location.origin + basePath + '/public/index.php';
        this.csrfToken = null;
        
        console.log('API Base URL:', this.baseUrl); // Debug
    }

    async request(ruta, options = {}) {
        const params = new URLSearchParams({
            api: '1',
            ruta: ruta,
            ...(options.params || {})
        });
        
        const url = `${this.baseUrl}?${params}`;
        console.log('API Request URL:', url); // Debug
        
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            }
        };

        if (options.body) {
            config.body = JSON.stringify(options.body);
        }

        try {
            console.log('Fetching:', url, config); // Debug
            const response = await fetch(url, config);
            const data = await response.json();
            
            console.log('API Response:', response.status, data); // Debug
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Auth
    async login(correo, password) {
        return this.request('login', {
            method: 'POST',
            body: { correo, password }
        });
    }

    async logout() {
        return this.request('logout', { method: 'POST' });
    }

    async getCsrfToken() {
        if (!this.csrfToken) {
            const response = await this.request('csrf-token');
            this.csrfToken = response.csrf_token;
        }
        return this.csrfToken;
    }

    // Tickets
    async getTickets() {
        return this.request('tickets');
    }

    async getTicket(id) {
        return this.request('tickets', { params: { id } });
    }

    async createTicket(data) {
        return this.request('tickets', {
            method: 'POST',
            body: data
        });
    }

    async updateTicket(id, data) {
        return this.request('tickets', {
            method: 'PUT',
            params: { id },
            body: data
        });
    }

    async deleteTicket(id) {
        return this.request('tickets', {
            method: 'DELETE',
            params: { id }
        });
    }

    // Usuarios
    async getUsuarios() {
        return this.request('usuarios');
    }

    async getUsuario(id) {
        return this.request('usuarios', { params: { id } });
    }

    async createUsuario(data) {
        return this.request('usuarios', {
            method: 'POST',
            body: data
        });
    }

    async updateUsuario(id, data) {
        return this.request('usuarios', {
            method: 'PUT',
            params: { id },
            body: data
        });
    }

    async deleteUsuario(id) {
        return this.request('usuarios', {
            method: 'DELETE',
            params: { id }
        });
    }

    // Comentarios
    async getComentarios(ticketId) {
        return this.request('comments', { 
            params: { ticket_id: ticketId } 
        });
    }

    async createComentario(ticketId, data) {
        return this.request('comments', {
            method: 'POST',
            params: { ticket_id: ticketId },
            body: data
        });
    }
    
    // SLA
    async getSLA() {
        return this.request('sla');
    }

    async updateSLA(data) {
        return this.request('sla', {
            method: 'PUT',
            body: data
        });
    }

    // Settings
    async getSettings() {
        return this.request('settings');
    }

    async updateSettings(data) {
        return this.request('settings', {
            method: 'PUT',
            body: data
        });
    }

    // Auditoría
    async getAuditoria() {
        return this.request('audit');
    }
}

// Instancia global
window.api = new ApiClient();