import { api } from './api';

export interface Category {
  id: number;
  category: string;
  image: string;
  image_origin: string;
  banner_image: string;
  show_in_banner: string;
  show_in_categories: string;
  attributes: string;
  category_text: string;
}

export const categoriesApi = {
  // Get all categories
  getCategories: async (): Promise<Category[]> => {
    try {
      const response = await api.get('/categories');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching categories:', error);
      throw error;
    }
  },

  // Get single category by ID
  getCategory: async (id: number): Promise<Category> => {
    try {
      const response = await api.get(`/categories/${id}`);
      return response.data.data;
    } catch (error) {
      console.error('Error fetching category:', error);
      throw error;
    }
  }
};
