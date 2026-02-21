import axios from 'axios';
import { RegisterFormData } from '@/forms/RegisterFormData.ts';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export const authService = {
  async register(data: RegisterFormData) {
    const response = await apiClient.post('/auth/register', data.toFormData());
    return response.data;
  },
};
