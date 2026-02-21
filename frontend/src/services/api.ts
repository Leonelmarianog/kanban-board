import axios from 'axios';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export const authService = {
  async register(userData: Record<string, unknown>) {
    const response = await apiClient.post('/auth/register', userData);
    return response.data;
  },
};
