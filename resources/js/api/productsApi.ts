import { api } from './api';

export interface ProductFormData {
  name: string;
  url: string; // Contact phone
  supplier: string; // Address  
  category: string;
  category_text: string;
  price_1: string; // Selling price
  price_2: string; // Original price
  description: string;
  local_id: string;
  has_colors: string;
  has_sizes: string;
  colors: string;
  sizes: string;
  p_type: string;
}

export interface RawProduct {
  id: number;
  name: string;
  metric: string;
  currency: string;
  description: string;
  summary: string;
  price_1: string;
  price_2: string;
  feature_photo: string;
  rates: string;
  date_added: string;
  date_updated: string;
  user: string;
  category: string;
  sub_category: string;
  supplier: string;
  url: string;
  status: string;
  in_stock: string;
  keywords: string;
  category_text: string;
  has_colors: string;
  colors: string;
  has_sizes: string;
  sizes: string;
  local_id: string;
  p_type: string;
  images?: string[];
  variants?: any[];
  pricesList?: any[];
  local_photos?: any[];
  online_photos?: any[];
}

export const productsApi = {
  // Get all products
  getProducts: async (params?: any): Promise<RawProduct[]> => {
    try {
      const response = await api.get('/products', { params });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching products:', error);
      throw error;
    }
  },

  // Get single product by ID
  getProduct: async (id: number): Promise<RawProduct> => {
    try {
      const response = await api.get(`/products/${id}`);
      return response.data.data;
    } catch (error) {
      console.error('Error fetching product:', error);
      throw error;
    }
  },

  // Create new product
  createProduct: async (formData: FormData): Promise<any> => {
    try {
      const response = await api.post('/product-create', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error: any) {
      console.error('Error creating product:', error);
      throw new Error(error.response?.data?.message || 'Failed to create product');
    }
  },

  // Update existing product
  updateProduct: async (id: number, formData: FormData): Promise<any> => {
    try {
      formData.append('id', id.toString());
      formData.append('is_edit', 'Yes');
      
      const response = await api.post('/product-create', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error: any) {
      console.error('Error updating product:', error);
      throw new Error(error.response?.data?.message || 'Failed to update product');
    }
  },

  // Delete product
  deleteProduct: async (id: number): Promise<any> => {
    try {
      const response = await api.post('/products-delete', { id });
      return response.data;
    } catch (error: any) {
      console.error('Error deleting product:', error);
      throw new Error(error.response?.data?.message || 'Failed to delete product');
    }
  },

  // Get user's products
  getUserProducts: async (userId: number): Promise<RawProduct[]> => {
    try {
      const response = await api.get('/products', { 
        params: { user: userId } 
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching user products:', error);
      throw error;
    }
  },

  // Upload product images
  uploadImages: async (images: File[], localId: string): Promise<any> => {
    try {
      const formData = new FormData();
      
      images.forEach((image, index) => {
        formData.append(`images[${index}]`, image);
      });
      
      formData.append('parent_local_id', localId);
      formData.append('type', 'Product');
      
      const response = await api.post('/images-upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      
      return response.data;
    } catch (error: any) {
      console.error('Error uploading images:', error);
      throw new Error(error.response?.data?.message || 'Failed to upload images');
    }
  },

  // Delete product image
  deleteImage: async (imageId: number): Promise<any> => {
    try {
      const response = await api.post('/images-delete', { id: imageId });
      return response.data;
    } catch (error: any) {
      console.error('Error deleting image:', error);
      throw new Error(error.response?.data?.message || 'Failed to delete image');
    }
  }
};
