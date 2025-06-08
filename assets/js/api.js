// /assets/js/api.js - Cliente API REST (MEJORADO)

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
            
            // 🔧 MEJORAR MANEJO DE RESPUESTAS
            let data;
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                // Es JSON válido
                data = await response.json();
            } else {
                // No es JSON, obtener como texto para debug
                const textData = await response.text();
                console.error('Respuesta no es JSON:', textData);
                
                // Intentar extraer mensaje de error de HTML/PHP
                if (textData.includes('Fatal error') || textData.includes('Warning') || textData.includes('Notice')) {
                    throw new Error('Error del servidor: Revisa los logs de PHP');
                } else if (textData.includes('404') || response.status === 404) {
                    throw new Error('Endpoint no encontrado');
                } else {
                    throw new Error('Respuesta inválida del servidor');
                }
            }
            
            console.log('API Response:', response.status, data); // Debug
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            
            // 🔧 MEJORAR MENSAJES DE ERROR
            if (error.name === 'SyntaxError' && error.message.includes('JSON')) {
                throw new Error('Error: El servidor no devolvió una respuesta válida');
            } else if (error.message.includes('Failed to fetch')) {
                throw new Error('Error de conexión: Verifica que el servidor esté funcionando');
            } else {
                throw error;
            }
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
        // 🔧 MEJORAR DEBUG PARA ELIMINACIÓN
        console.log(`Eliminando usuario ID: ${id}`);
        
        try {
            const result = await this.request('usuarios', {
                method: 'DELETE',
                params: { id }
            });
            
            console.log('Usuario eliminado exitosamente:', result);
            return result;
            
        } catch (error) {
            console.error('Error en deleteUsuario:', error);
            throw error;
        }
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

    // 🆕 MÉTODOS ADICIONALES
    async getRoles() {
        return this.request('usuarios/roles');
    }

    // 🔧 MÉTODO DE DEBUG
    async testConnection() {
        try {
            const response = await this.request('prueba-db');
            console.log('Test de conexión exitoso:', response);
            return response;
        } catch (error) {
            console.error('Test de conexión falló:', error);
            throw error;
        }
    }
}

// Instancia global
window.api = new ApiClient();